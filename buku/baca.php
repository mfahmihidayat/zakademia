<?php
declare(strict_types=1);
/**
 * Pembaca buku digital dengan tampilan flip (halaman dibalik). /buku/<slug>/baca
 * Tanpa akses: hanya halaman pratinjau yang dimuat; halaman selanjutnya tertutup dan diarahkan ke pembelian.
 */
require __DIR__ . '/lib_buku.php';
start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$slug = bk_slug($_GET['b'] ?? '');
if ($slug === '') { http_response_code(404); exit('Buku tidak ditemukan.'); }
$b = BK_BUKU[$slug];
$conf = bk_conf();
$c = $conf['buku'][$slug];
$meta = bk_meta($slug);
if (!$meta) { http_response_code(503); exit('Halaman buku belum tersedia.'); }
$u = current_user();
$akses = $u ? bk_access($u, $slug) : null;
$penuh = $akses && user_verified($u);
$jml = (int)$meta['jumlah'];
$prat = min((int)$c['pratinjau'], $jml);
$batas = $penuh ? $jml : $prat;
$self = '/buku/' . $slug . '/';
$cfg = ['slug' => $slug, 'jumlah' => $jml, 'batas' => $batas, 'penuh' => (bool)$penuh, 'w' => (int)$meta['lebar'], 'h' => (int)$meta['tinggi'],
    'judul' => $b['judul'], 'lynk' => $c['lynk_url'], 'email' => $u ? $u['email'] : '', 'masuk' => '/asesmen/masuk.php?next=' . urlencode($self . 'baca'), 'beli' => $self];
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1"><meta name="robots" content="noindex">
<title><?= e($b['judul']) ?> · Baca · Zakademia</title>
<link rel="icon" href="/assets/images/favicon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
<style>
:root{--bg:#1C2A24;--ink:#FFFFF0;--deep:#0B4D33;--gold:#C99A3B;--mist:#F2F5E6}
*{box-sizing:border-box}html,body{height:100%}
body{margin:0;background:var(--bg);color:var(--ink);font:400 14px/1.5 "Plus Jakarta Sans",system-ui,sans-serif;display:flex;flex-direction:column;overflow:hidden;-webkit-user-select:none;user-select:none}
header{display:flex;align-items:center;gap:12px;padding:10px 14px;background:rgba(0,0,0,.25);flex:none}
header .brand{display:flex;align-items:center;gap:8px;color:var(--ink);text-decoration:none;font-weight:800}header .brand img{width:26px;height:26px}
header .t{font-weight:600;opacity:.85;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;min-width:0}
header .pill{font-size:12px;padding:3px 10px;border-radius:999px;background:var(--gold);color:#10291F;font-weight:800;white-space:nowrap}
header a.x{color:var(--ink);text-decoration:none;font-weight:600;opacity:.85}
main{flex:1;min-height:0;position:relative;display:flex;align-items:center;justify-content:center;padding:10px}
#buku{width:100%;height:100%;display:flex;align-items:center;justify-content:center}
.page{background:#fff;overflow:hidden}
.page img{width:100%;height:100%;object-fit:fill;display:block;pointer-events:none;-webkit-user-drag:none}
.page.hard{background:var(--deep)}
.page .wm{position:absolute;inset:0;pointer-events:none;background:repeating-linear-gradient(-30deg,transparent 0 120px,rgba(16,41,31,.035) 120px 122px)}
.page .lock{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:24px;text-align:center;background:linear-gradient(180deg,#F7F6EE,#E9EFE3);color:#10291F}
.page .lock b{font-size:clamp(14px,2.2vw,20px);color:var(--deep)}
.page .lock p{margin:0;font-size:clamp(11px,1.5vw,14px);max-width:34ch;color:#4A6355}
.page .lock a{display:inline-block;padding:8px 16px;border-radius:999px;background:var(--gold);color:#10291F;font-weight:800;text-decoration:none;font-size:clamp(11px,1.5vw,14px);pointer-events:auto}
.page .lock a.g{background:transparent;border:1.5px solid #B8C5B9}
footer{flex:none;display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 12px;background:rgba(0,0,0,.25);flex-wrap:wrap}
footer button,footer a.btn{font:inherit;font-weight:700;color:var(--ink);background:rgba(255,255,240,.1);border:1px solid rgba(255,255,240,.25);border-radius:999px;padding:8px 14px;cursor:pointer;min-height:38px;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
footer button:disabled{opacity:.35;cursor:default}
footer input[type=range]{width:min(360px,40vw);accent-color:var(--gold)}
footer .no{font-variant-numeric:tabular-nums;min-width:90px;text-align:center}
footer input.go{width:64px;font:inherit;border-radius:10px;border:1px solid rgba(255,255,240,.25);background:rgba(255,255,240,.1);color:var(--ink);padding:6px 8px;text-align:center}
.tip{position:absolute;bottom:14px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,.6);padding:6px 14px;border-radius:999px;font-size:12px;opacity:0;transition:opacity .4s;pointer-events:none}
.tip.on{opacity:1}
@media(max-width:640px){header .t{font-size:13px}footer .no{min-width:70px}footer input[type=range]{width:34vw}}
@media print{body{display:none}}
</style></head>
<body>
<header><a class="brand" href="/"><img src="/assets/images/logo-color.png" alt="">Zakademia</a><span class="t"><?= e($b['judul']) ?></span>
<?= $penuh ? '' : '<span class="pill">Pratinjau ' . $prat . ' hlm.</span>' ?><a class="x" href="<?= e($self) ?>">Tutup</a></header>
<main><div id="buku"></div><div class="tip" id="tip">Geser atau sentuh sudut halaman untuk membalik</div></main>
<footer>
  <button id="first" title="Halaman pertama">⏮</button><button id="prev" title="Sebelumnya">‹</button>
  <span class="no" id="no">1 / <?= $jml ?></span>
  <button id="next" title="Berikutnya">›</button><button id="last" title="Halaman terakhir">⏭</button>
  <input type="range" id="slider" min="1" max="<?= $jml ?>" value="1" aria-label="Halaman">
  <input class="go" id="go" inputmode="numeric" placeholder="hlm." aria-label="Lompat ke halaman">
  <button id="full" title="Layar penuh">⛶</button>
</footer>
<script>window.BK=<?= json_encode($cfg, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<script src="/js/flip/page-flip.browser.js"></script>
<script src="/js/flip/baca.js?v=1"></script>
</body></html>
