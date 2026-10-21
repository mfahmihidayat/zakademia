<?php
declare(strict_types=1);
require __DIR__ . '/../survei/lib_survei.php';

$slug = strtolower((string)($_GET['slug'] ?? ''));
if ($slug === '' && preg_match('#^/s/([A-Za-z0-9-]+)/?#', (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), $m)) $slug = strtolower($m[1]);
$s = null;
if ($slug !== '') {
    $q = sv_db()->prepare('SELECT * FROM surveys WHERE slug = ?');
    $q->execute([$slug]);
    $s = $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
$def = $s ? json_decode($s['def'], true) : null;

/* ---------- kirim jawaban ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$s) json_out(['ok' => false, 'error' => 'Survei tidak ditemukan.'], 404);
    if ($why = sv_closed_reason($s, $def)) json_out(['ok' => false, 'error' => $why], 410);
    $in = json_decode((string)file_get_contents('php://input', false, null, 0, 200000), true);
    if (!is_array($in)) json_out(['ok' => false, 'error' => 'Format kiriman tidak dikenal.'], 400);
    if (!empty($in['hp'])) json_out(['ok' => true]);
    $ip = ip_key();
    if (!throttle_ok('sv|' . $s['id'] . '|' . $ip, 20, 3600)) json_out(['ok' => false, 'error' => 'Terlalu banyak kiriman dari jaringan ini. Coba lagi nanti.'], 429);
    [$ans, $err] = sv_clean_answers($def, is_array($in['a'] ?? null) ? $in['a'] : []);
    if ($err) json_out(['ok' => false, 'error' => 'Masih ada jawaban yang perlu diperbaiki.', 'errors' => $err], 422);
    throttle_hit('sv|' . $s['id'] . '|' . $ip);
    sv_db()->prepare('INSERT INTO responses (survey_id, created, answers, duration, ip_hash) VALUES (?,?,?,?,?)')
        ->execute([$s['id'], gmdate('c'), json_encode($ans, JSON_UNESCAPED_UNICODE), max(0, min(86400, (int)($in['dur'] ?? 0))), $ip]);
    json_out(['ok' => true]);
}

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
header('Referrer-Policy: same-origin');
$why = !$s ? 'Survei tidak ditemukan. Periksa kembali tautan yang Anda terima.' : sv_closed_reason($s, $def);
$u = current_user();
$owner = $s && $u && ((int)$u['id'] === (int)$s['user_id'] || $u['role'] === 'admin');
$previewOwner = $why && $owner && $s['status'] === 'draf';
$title = $s ? $def['title'] : 'Survei';
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · Zakademia</title>
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($s ? clip($def['desc'] ?: 'Survei melalui Zakademia', 180) : 'Zakademia') ?>">
<link rel="icon" href="/assets/images/favicon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/survei/assets/sv.css?v=3">
</head>
<body>
<main class="wrap" style="padding-top:24px" id="app">
<?php if ($why && !$previewOwner): ?>
  <div class="sv"><div class="card sv-done"><div class="ok" style="background:var(--mist)">i</div><h1><?= e($s ? $def['title'] : 'Survei tidak ditemukan') ?></h1><p class="mute"><?= e($why) ?></p>
  <?php if ($owner && $s['status'] === 'aktif' && sv_quota_full($s)): ?><p class="mute small">Anda melihat ini sebagai pemilik: batas <?= sv_quota($s) ?> jawaban sudah tercapai.</p><p><a class="btn" href="<?= e(sv_consult_url($s)) ?>" target="_blank" rel="noopener">Konsultasi untuk melanjutkan</a></p><?php endif; ?></div>
  <p class="sv-foot">Dibuat dengan <a href="/">Zakademia</a></p></div>
<?php endif; ?>
</main>
<?php if (!$why || $previewOwner): ?>
<script src="/survei/assets/form.js?v=3"></script>
<script>
(function(){
  var DEF=<?= json_encode($def, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,SLUG=<?= json_encode($s['slug']) ?>,PREVIEW=<?= $previewOwner ? 'true' : 'false' ?>;
  var app=document.getElementById('app'),onceKey='zk-sv-done-'+SLUG;
  function foot(){var p=document.createElement('p');p.className='sv-foot';p.innerHTML='Dibuat dengan <a href="/">Zakademia</a>';return p;}
  if(PREVIEW){var n=document.createElement('div');n.className='err-box';n.style.cssText='max-width:720px;margin:0 auto 14px';n.textContent='Survei ini masih draf: hanya Anda yang dapat melihatnya, dan jawaban tidak disimpan. Ubah status menjadi Aktif agar responden dapat mengisi.';app.before(n);}
  var already=false;try{already=DEF.opt&&DEF.opt.once&&localStorage.getItem(onceKey);}catch(_){}
  if(already&&!PREVIEW){app.innerHTML='<div class="sv"><div class="card sv-done"><div class="ok">✓</div><h1></h1><p class="mute">Anda sudah mengisi survei ini dari perangkat ini. Terima kasih.</p></div></div>';app.querySelector('h1').textContent=DEF.title;app.firstChild.appendChild(foot());return;}
  ZKSurvey.render(app,DEF,{preview:PREVIEW,draftKey:PREVIEW?null:'zk-sv-draft-'+SLUG,footer:foot,
    onDone:function(){if(DEF.opt&&DEF.opt.once&&!PREVIEW)try{localStorage.setItem(onceKey,'1');}catch(_){}},
    onSubmit:function(a,dur){if(PREVIEW)return Promise.resolve();
      return fetch(location.pathname.replace(/\/$/,''),{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({a:a,dur:dur,hp:''})})
        .then(function(r){return r.json().catch(function(){return {ok:false,error:'Server tidak merespons (HTTP '+r.status+').'};});})
        .then(function(j){if(!j.ok)throw {message:j.error,errors:j.errors};});}});
})();
</script>
<?php endif; ?>
<script src="/js/stat.js" defer></script>
</body>
</html>
