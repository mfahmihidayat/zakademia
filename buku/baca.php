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
*{box-sizing:border-box}[hidden]{display:none!important}html,body{height:100%}
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
header .hb{display:inline-flex;align-items:center;gap:6px;font:inherit;font-weight:600;color:var(--ink);background:rgba(255,255,240,.1);border:1px solid rgba(255,255,240,.25);border-radius:999px;padding:6px 12px;cursor:pointer}
header .hb:hover{background:rgba(255,255,240,.2)}
.cari{position:absolute;top:0;right:0;bottom:0;width:min(380px,100%);background:#10291F;border-left:1px solid rgba(255,255,240,.15);z-index:20;display:flex;flex-direction:column;box-shadow:-10px 0 30px rgba(0,0,0,.4)}
.cari form{display:flex;gap:6px;padding:12px}.cari input{flex:1;min-width:0;font:inherit;font-size:15px;padding:9px 12px;border-radius:10px;border:1px solid rgba(255,255,240,.3);background:rgba(255,255,240,.08);color:var(--ink)}
.cari form button{font:inherit;font-size:18px;color:var(--ink);background:rgba(255,255,240,.12);border:1px solid rgba(255,255,240,.3);border-radius:10px;width:40px;cursor:pointer}
.cari .ci{padding:0 14px 8px;font-size:13px;opacity:.8}
.cari .cr{list-style:none;margin:0;padding:0 8px 14px;overflow:auto;flex:1}
.cari .cr li button{display:block;width:100%;text-align:left;font:inherit;color:var(--ink);background:rgba(255,255,240,.06);border:1px solid rgba(255,255,240,.12);border-radius:10px;padding:8px 10px;margin-bottom:6px;cursor:pointer;line-height:1.4;font-size:13.5px}
.cari .cr li button:hover{border-color:var(--gold)}.cari .cr b{color:var(--gold);font-size:12px;display:block;margin-bottom:2px}.cari mark{background:var(--gold);color:#10291F;border-radius:3px;padding:0 2px}
.cari .cr li.lock button{opacity:.75;border-style:dashed}
.zoom{position:fixed;inset:0;z-index:40;background:#0d1f18;display:flex;flex-direction:column}
.zt{display:flex;align-items:center;gap:8px;padding:8px 12px;background:rgba(0,0,0,.35);flex-wrap:wrap}.zt #zNo{font-weight:700;margin-right:auto}
.zt button{font:inherit;font-weight:700;color:var(--ink);background:rgba(255,255,240,.1);border:1px solid rgba(255,255,240,.25);border-radius:999px;padding:6px 12px;cursor:pointer;min-height:36px}
.zb{display:flex;align-items:center;gap:6px}.zb span{font-variant-numeric:tabular-nums;min-width:52px;text-align:center;font-size:13px}
.zs{flex:1;overflow:auto;cursor:grab;position:relative;touch-action:none}.zs.drag{cursor:grabbing}
.zs img{display:block;user-select:none;-webkit-user-drag:none;box-shadow:0 20px 50px rgba(0,0,0,.6);margin:16px}
.zh{font-size:12px;opacity:.7;text-align:center;padding:6px}
@media(max-width:640px){header .t{font-size:13px}header .hb span{display:none}footer .no{min-width:70px}footer input[type=range]{width:34vw}.zh{display:none}}
@media print{body{display:none}}
</style></head>
<body>
<header><a class="brand" href="/"><img src="/assets/images/logo-color.png" alt="">Zakademia</a><span class="t"><?= e($b['judul']) ?></span>
<?= $penuh ? '' : '<span class="pill">Pratinjau ' . $prat . ' hlm.</span>' ?><button type="button" class="hb" id="btnCari" title="Cari di buku (Ctrl+F)" aria-label="Cari di buku"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg><span>Cari</span></button><button type="button" class="hb" id="btnZoom" title="Perbesar halaman ini" aria-label="Perbesar"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6M8 11h6M11 8v6"/></svg><span>Perbesar</span></button><a class="x" href="<?= e($self) ?>">Tutup</a></header>
<main><div id="buku"></div><div class="tip" id="tip">Geser atau sentuh sudut halaman untuk membalik</div></main>
<aside class="cari" id="cari" hidden aria-label="Cari di buku">
  <form id="fCari"><input type="search" id="qCari" placeholder="Cari kata atau frasa" autocomplete="off" minlength="3" required><button type="submit" aria-label="Cari">›</button><button type="button" id="xCari" aria-label="Tutup pencarian">×</button></form>
  <div class="ci" id="ciCari">Ketik minimal 3 huruf, lalu Enter.</div>
  <ol class="cr" id="crCari"></ol>
</aside>
<div class="zoom" id="zoom" hidden>
  <div class="zt"><span id="zNo"></span><div class="zb"><button type="button" id="zMin" aria-label="Perkecil">−</button><span id="zPct">150%</span><button type="button" id="zPlus" aria-label="Perbesar">+</button><button type="button" id="zFit">Pas layar</button></div><button type="button" id="zPrev" aria-label="Halaman sebelumnya">‹</button><button type="button" id="zNext" aria-label="Halaman berikutnya">›</button><button type="button" id="zX" aria-label="Tutup perbesaran">Tutup</button></div>
  <div class="zs" id="zs"><img id="zImg" alt="" draggable="false"></div>
  <div class="zh">Ctrl + gulir atau cubit untuk memperbesar, seret untuk menggeser, panah untuk pindah halaman, Esc untuk menutup.</div>
</div>
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
<script src="/js/flip/baca.js?v=2"></script>
<script src="/js/stat.js?v=2" defer></script>
</body></html>
