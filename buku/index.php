<?php
declare(strict_types=1);
/**
 * zakademia.com/buku/<slug>/ — halaman buku digital.
 * Semua orang dapat melihat keterangan buku dan membaca pratinjau. Pembeli (lewat Lynk.id atau tambahan admin)
 * masuk dengan email pembelian, lalu membaca seluruh buku di /buku/<slug>/baca.
 */
require __DIR__ . '/lib_buku.php';
start_session();
header('Referrer-Policy: same-origin');

$slug = bk_slug($_GET['b'] ?? '');
if ($slug === '') { $slug = array_key_first(BK_BUKU); if (($_GET['b'] ?? '') !== '') { http_response_code(404); } }
$b = BK_BUKU[$slug];
$conf = bk_conf();
$c = $conf['buku'][$slug];
$meta = bk_meta($slug);
$isiFile = __DIR__ . '/isi_' . $slug . '.php'; $BI = null; if (is_file($isiFile)) { require $isiFile; $BI = BK_ISI[$slug] ?? null; }
$self = '/buku/' . $slug . '/';
$u = current_user();
$akses = $u ? bk_access($u, $slug) : null;
$error = '';
$info = flash();
$tahap = '';
$klaimEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $u) {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!user_verified($u)) redirect('/asesmen/verifikasi.php?next=' . urlencode($self));
    elseif ($act === 'klaim') {
        $klaimEmail = clip($_POST['email'] ?? '', 160);
        if (!valid_email($klaimEmail)) $error = 'Alamat email belum benar.';
        elseif (!throttle_ok('bkklaim|' . $u['id'], 8, 3600)) $error = 'Terlalu banyak percobaan. Coba lagi dalam satu jam.';
        else {
            throttle_hit('bkklaim|' . $u['id']);
            if (!bk_unclaimed($slug, $klaimEmail)) $error = 'Pembelian dengan email ini belum tercatat atau sudah dipakai akun lain. Bila baru membayar, tunggu beberapa menit lalu coba lagi, atau hubungi info@zakademia.com.';
            else {
                $err = otp_send('bk|' . $u['id'], $klaimEmail, 'membuka buku digital');
                if ($err) $error = $err;
                else { $_SESSION['bk_klaim'] = strtolower($klaimEmail); $tahap = 'kode'; $info = 'Kode verifikasi sudah dikirim ke ' . $klaimEmail . '.'; }
            }
        }
    } elseif ($act === 'kode') {
        $klaimEmail = (string)($_SESSION['bk_klaim'] ?? '');
        $email = otp_check('bk|' . $u['id'], (string)($_POST['kode'] ?? ''), $err);
        if (!$email || $email !== $klaimEmail) { $error = $err ?: 'Kode tidak cocok.'; $tahap = 'kode'; }
        elseif ($row = bk_unclaimed($slug, $email)) {
            bk_db()->prepare('UPDATE bk_akses SET user_id = ?, diklaim = ? WHERE id = ?')->execute([(int)$u['id'], gmdate('c'), $row['id']]);
            unset($_SESSION['bk_klaim']);
            flash('Akses buku berhasil dibuka untuk akun ini.');
            redirect($self);
        } else $error = 'Pembelian ini sudah dipakai akun lain.';
    }
}

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com');
$judul = $b['judul'] . ': ' . $b['sub'];
$jml = $meta ? (int)$meta['jumlah'] : 0;
$prat = (int)$c['pratinjau'];
$harga = bk_rp((string)$c['harga']);
$coret = bk_rp((string)($c['harga_coret'] ?? ''));
$nh = (int)preg_replace('/\D/', '', $harga); $nc = (int)preg_replace('/\D/', '', $coret);
$hemat = ($nh > 0 && $nc > $nh) ? (int)round(($nc - $nh) * 100 / $nc) : 0;
if (!$hemat) $coret = '';
$lynk = $c['lynk_url'];
$masuk = '/asesmen/masuk.php?next=' . urlencode($self);
$daftar = '/asesmen/daftar.php?next=' . urlencode($self);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($b['judul']) ?>: <?= e($b['sub']) ?> | Buku Zakademia</title>
<meta name="description" content="<?= e($b['ringkas']) ?>">
<link rel="canonical" href="<?= e($base . $self) ?>">
<meta property="og:type" content="book"><meta property="og:site_name" content="Zakademia">
<meta property="og:title" content="<?= e($judul) ?>"><meta property="og:description" content="<?= e($b['ringkas']) ?>">
<meta property="og:url" content="<?= e($base . $self) ?>"><meta property="og:image" content="<?= e($base . '/assets/images/og-buku-' . $slug . '.png?v=1') ?>">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/assets/images/favicon.png">
<script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@graph' => array_values(array_filter([
    ['@type' => 'Book', '@id' => $base . $self . '#buku', 'name' => $b['judul'], 'alternativeHeadline' => $b['sub'], 'description' => $b['ringkas'], 'url' => $base . $self,
     'image' => $base . '/assets/images/og-buku-' . $slug . '.png', 'inLanguage' => 'id', 'bookFormat' => 'https://schema.org/EBook', 'datePublished' => $b['tahun'] ?? '2026',
     'numberOfPages' => $jml ?: null, 'genre' => 'Metodologi penelitian', 'about' => ['Filantropi Islam', 'Zakat', 'Wakaf', 'Metodologi penelitian'],
     'author' => array_map(fn($n) => ['@type' => 'Person', 'name' => trim($n)], explode(',', $b['penulis'])),
     'publisher' => ['@type' => 'Organization', 'name' => 'Zakademia', 'url' => $base . '/', 'logo' => $base . '/assets/images/logo-color.png'],
     'hasPart' => array_map(fn($i, $x) => ['@type' => 'Chapter', 'position' => $i + 1, 'name' => 'Bab ' . ($i + 1) . ': ' . $x[0]] + ($BI ? ['url' => $base . $self . 'bab-' . ($i + 1) . '/', 'description' => $x[1]] : []), array_keys($b['bab'] ?? []), $b['bab'] ?? []),
     'offers' => $lynk !== '' ? ['@type' => 'Offer', 'url' => $lynk, 'availability' => 'https://schema.org/InStock', 'priceCurrency' => 'IDR'] + (preg_match('/[\d.]+/', str_replace('.', '', $harga), $m) && (int)$m[0] > 0 ? ['price' => (int)$m[0]] : []) : null],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Zakademia', 'item' => $base . '/'], ['@type' => 'ListItem', 'position' => 2, 'name' => 'Publikasi', 'item' => $base . '/#kajian/buku'], ['@type' => 'ListItem', 'position' => 3, 'name' => $b['judul'], 'item' => $base . $self]]],
    !empty($b['faq']) ? ['@type' => 'FAQPage', 'mainEntity' => array_map(fn($f) => ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]], $b['faq'])] : null,
])) ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/survei/assets/sv.css?v=3"><?= zk_kepala_aset() ?><script src="/js/stat.js?v=2" defer></script>
<style>
.bk{max-width:1040px;margin:0 auto;display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:clamp(22px,4vw,48px);align-items:start}
@media(max-width:720px){.bk{grid-template-columns:1fr}.bk .cov{max-width:280px;margin:0 auto}}
.cov{border-radius:14px;overflow:hidden;box-shadow:0 24px 50px -28px rgba(11,77,51,.6);border:1px solid var(--line);background:#fff}
.cov img{display:block;width:100%;height:auto}
.kicker{font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gold)}
h1.judul{font-size:clamp(26px,3.4vw,38px);line-height:1.15;letter-spacing:-.02em;margin:6px 0 4px;color:var(--deep)}
.sub{font-size:18px;color:var(--ink);margin:0 0 10px;font-weight:600}
.au{color:var(--mute);margin:0 0 18px}
.ring{line-height:1.75;margin:0 0 18px}
.cta{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin:14px 0 8px}
.harga{font-size:22px;font-weight:800;color:var(--deep)}
.hargabox{display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
.coret{font-size:16px;font-weight:600;color:var(--mute);text-decoration:line-through;text-decoration-thickness:1.5px}
.sr-only{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap}
.hemat{font-size:12.5px;font-weight:700;color:#8A3B12;background:#FCE9D9;border-radius:999px;padding:3px 10px;align-self:center}
.card2{background:var(--paper);border:1px solid var(--line);border-radius:18px;padding:20px 22px;display:flex;flex-direction:column;gap:12px;margin-top:22px}
.card2 h2{font-size:17px;margin:0}
.card2 label{font-weight:600;font-size:14px;display:flex;flex-direction:column;gap:6px}
.card2 input{font:inherit;font-size:16px;border:1px solid var(--line);border-radius:12px;padding:11px 14px;min-height:48px;background:var(--bg);color:var(--ink);width:100%}
.fitur{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:18px 0 0}
.fitur div{background:var(--mist);border-radius:14px;padding:12px 14px;font-size:13.5px;line-height:1.5}
.fitur b{display:block;color:var(--deep);font-size:14px}
.err{background:#FBEFE6;border:1px solid #EBCBB4;color:#6E2C16;border-radius:12px;padding:10px 14px;font-size:14px}
.isi{max-width:1040px;margin:48px auto 0;display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:clamp(22px,4vw,48px)}
@media(max-width:720px){.isi{grid-template-columns:1fr}}
.isi h2{font-size:24px;margin:0 0 12px;color:var(--deep)}
.isi ol{margin:0;padding:0;list-style:none;display:grid;gap:10px;counter-reset:b}
.isi ol li{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:12px 14px}

.isi ol b{display:block;color:var(--deep)}.isi ol span{font-size:14px;color:var(--mute);line-height:1.5}
.isi details{border-bottom:1px solid var(--line);padding:12px 0}.isi summary{font-weight:700;cursor:pointer;color:var(--deep)}.isi details p{margin:8px 0 0;line-height:1.65}
.prakata p{line-height:1.8;margin:0 0 14px;font-size:16px}
.isi ol b a{color:var(--deep);text-decoration:none}.isi ol b a:hover{text-decoration:underline}
.toc h3{font-size:13px;letter-spacing:.08em;text-transform:uppercase;color:var(--gold);margin:16px 0 8px}.toc h3:first-child{margin-top:0}
.toc details{border:1px solid var(--line);border-radius:12px;background:var(--paper);padding:10px 14px;margin-bottom:8px}.toc summary{font-weight:700;cursor:pointer}.toc summary a{color:var(--deep);text-decoration:none}.toc summary a:hover{text-decoration:underline}
.toc details>ul{margin:10px 0 2px;padding-left:4px;list-style:none;line-height:1.6;font-size:14.5px}.toc .k{font-family:'IBM Plex Mono',ui-monospace,monospace;font-size:12.5px;color:var(--gold);font-weight:600}
.toc details ul ul{list-style:disc;padding-left:22px;color:var(--mute);font-size:14px;margin:2px 0 6px}
.ok{background:var(--mist);border:1px solid var(--line);color:var(--deep);border-radius:12px;padding:10px 14px;font-size:14px}
</style></head><body>
<?= zk_kepala() ?>
<main class="wrap" style="padding-top:32px;padding-bottom:70px">
<div class="bk">
  <div>
    <div class="cov"><img src="/assets/images/cover-<?= e($slug) ?>.webp" alt="Sampul <?= e($b['judul']) ?>" width="600" height="800" fetchpriority="high"></div>
  </div>
  <div>
    <span class="kicker">Buku digital · <?= e($b['penerbit']) ?></span>
    <h1 class="judul"><?= e($b['judul']) ?></h1>
    <p class="sub"><?= e($b['sub']) ?></p>
    <p class="au"><?= e($b['penulis']) ?><?= $jml ? ' · ' . $jml . ' halaman' : '' ?></p>
    <?= $info ? '<p class="ok" role="status">' . e($info) . '</p>' : '' ?><?= $error ? '<p class="err" role="alert">' . e($error) . '</p>' : '' ?>
    <p class="ring"><?= e($b['ringkas']) ?></p>
<?php if ($akses): ?>
    <div class="cta"><a class="btn" href="<?= e($self) ?>baca">Baca buku</a><span class="mute" style="font-size:14px">Akses aktif untuk <?= e($u['email']) ?><?= ($akses['sumber'] ?? '') === 'admin' ? ' (admin)' : '' ?>.</span></div>
<?php else: ?>
    <div class="cta">
      <?= $harga !== '' ? '<span class="hargabox">' . ($coret !== '' ? '<s class="coret"><span class="sr-only">Harga normal </span>' . e($coret) . '</s>' : '') . '<span class="harga">' . ($coret !== '' ? '<span class="sr-only">Harga diskon </span>' : '') . e($harga) . '</span>' . ($hemat ? '<span class="hemat">Hemat ' . $hemat . '%</span>' : '') . '</span>' : '' ?>
      <?= $lynk !== '' ? '<a class="btn gold" href="' . e($lynk) . '" target="_blank" rel="noopener">Beli di Lynk.id</a>' : '<span class="mute" style="font-size:14px">Tautan pembelian segera tersedia.</span>' ?>
      <a class="btn ghost" href="<?= e($self) ?>baca">Baca pratinjau (<?= $prat ?> halaman)</a>
    </div>
    <p class="mute" style="font-size:13.5px">Setelah membayar, buku terbuka otomatis untuk akun dengan email yang sama dengan email pembayaran. Pembayaran lewat transfer? Hubungi <a href="https://wa.me/6285692090699" target="_blank" rel="noopener">WhatsApp Zakademia</a>, akses dibuka manual oleh admin.</p>
<?php endif; ?>
    <div class="fitur">
      <div><b>Dibaca di situs</b>Tampilan buku yang dibalik halaman demi halaman, di ponsel maupun komputer.</div>
      <div><b>Akses seumur akun</b>Terikat pada akun Zakademia Anda, bisa dibuka kapan saja.</div>
      <div><b>10 bab lengkap</b>Studi kasus, kotak Dalam Jurnal, kuis, glosarium, dan indeks.</div>
    </div>
<?php if (!$akses): ?>
    <div class="card2">
<?php if (!$u): ?>
      <h2>Sudah membeli?</h2>
      <p class="mute" style="margin:0">Masuk atau daftar dengan email yang dipakai saat membayar. Akses terbuka otomatis setelah email terverifikasi.</p>
      <div class="cta" style="margin:0"><a class="btn" href="<?= e($masuk) ?>">Masuk</a><a class="btn ghost" href="<?= e($daftar) ?>">Daftar akun</a></div>
<?php elseif (!user_verified($u)): ?>
      <h2>Verifikasi email dulu</h2>
      <p class="mute" style="margin:0">Akses buku dicocokkan dengan email akun, jadi email <?= e($u['email']) ?> perlu diverifikasi.</p>
      <div class="cta" style="margin:0"><a class="btn" href="/asesmen/verifikasi.php?next=<?= e(urlencode($self)) ?>">Verifikasi sekarang</a></div>
<?php else: ?>
      <h2>Sudah membayar dengan email lain?</h2>
      <p class="mute" style="margin:0">Akses untuk <?= e($u['email']) ?> belum ditemukan. Masukkan email yang dipakai saat membayar; kami kirim kode ke email itu untuk memastikan pembelian milik Anda.</p>
<?php if ($tahap === 'kode'): ?>
      <form method="post" action="<?= e($self) ?>" style="display:flex;flex-direction:column;gap:12px"><?= csrf_field() ?><input type="hidden" name="act" value="kode">
        <label>Kode verifikasi dari <?= e($klaimEmail) ?><input name="kode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus></label>
        <button class="btn" type="submit">Buka akses</button></form>
<?php else: ?>
      <form method="post" action="<?= e($self) ?>" style="display:flex;flex-direction:column;gap:12px"><?= csrf_field() ?><input type="hidden" name="act" value="klaim">
        <label>Email yang dipakai saat membayar<input type="email" name="email" value="<?= e($klaimEmail) ?>" required autocomplete="email"></label>
        <button class="btn ghost" type="submit">Kirim kode verifikasi</button></form>
<?php endif; ?>
<?php endif; ?>
    </div>
<?php endif; ?>
  </div>
</div>
<?php if (!empty($b['bab'])): ?>
<section class="isi" aria-labelledby="h-isi">
  <div><h2 id="h-isi">Isi buku</h2><p class="mute" style="line-height:1.65;margin:0">Buku <b><?= e($b['judul']) ?></b> karya <?= e($b['penulis']) ?> membahas <?= count($b['bab']) ?> bab metode penelitian zakat dan wakaf, dari pendekatan normatif-syariah hingga big data dan pemodelan formal.</p>
  <?php if (!empty($b['faq'])): ?><h2 style="margin-top:28px">Pertanyaan umum</h2><?php foreach ($b['faq'] as $f): ?><details><summary><?= e($f[0]) ?></summary><p><?= e($f[1]) ?></p></details><?php endforeach; endif; ?></div>
  <ol><?php foreach ($b['bab'] as $i => $x): ?><li><div><b><?= $BI ? '<a href="' . e($self . 'bab-' . ($i + 1) . '/') . '">' : '' ?>Bab <?= $i + 1 ?>. <?= e($x[0]) ?><?= $BI ? '</a>' : '' ?></b><span><?= e($x[1]) ?></span></div></li><?php endforeach; ?></ol>
</section>
<?php endif; ?>
<?php if ($BI): ?>
<section class="isi" aria-labelledby="h-prakata" style="margin-top:40px">
  <div><h2 id="h-prakata">Dari prakata penulis</h2><p class="mute" style="line-height:1.65;margin:0">Mengapa buku ini ditulis, dan sikap metodologis yang ditawarkannya. <a href="<?= e($self) ?>baca" style="color:var(--deep);font-weight:600">Baca prakata lengkap di pratinjau</a>.</p></div>
  <div class="prakata"><?php foreach ($BI['prakata'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?><p class="mute" style="font-size:14px">— <?= e($b['penulis']) ?>, Jakarta, Oktober 2026</p></div>
</section>
<section class="isi" aria-labelledby="h-daftar" style="margin-top:40px">
  <div><h2 id="h-daftar">Daftar isi lengkap</h2><p class="mute" style="line-height:1.65;margin:0">Tiga bagian, sepuluh bab, dan subbab yang dibahas. Setiap judul bab membuka ringkasan bab tersebut.</p></div>
  <div class="toc"><?php $bg = 0; foreach ($BI['bab'] as $i => $x): if ($x['bagian'] !== $bg) { $bg = $x['bagian']; echo '<h3>' . e($BI['bagian'][$bg]) . '</h3>'; } ?>
    <details><summary><a href="<?= e($self . 'bab-' . ($i + 1) . '/') ?>">Bab <?= $i + 1 ?> <?= e($x['judul']) ?></a></summary>
      <ul><?php foreach ($x['subbab'] as $sb): ?><li><span class="k"><?= e($sb[0]) ?></span> <?= e($sb[1]) ?><?= $sb[2] ? '<ul>' . implode('', array_map(fn($y) => '<li>' . e($y) . '</li>', $sb[2])) . '</ul>' : '' ?></li><?php endforeach; ?></ul>
    </details>
  <?php endforeach; ?></div>
</section>
<?php endif; ?>
</main>
</body></html>
