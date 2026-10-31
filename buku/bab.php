<?php
declare(strict_types=1);
/**
 * zakademia.com/buku/<slug>/bab-<n>/ — halaman ringkasan satu bab buku digital (publik, dapat diindeks).
 * Isi dari buku/isi_<slug>.php. Menautkan ke pratinjau dan pembelian.
 */
require __DIR__ . '/lib_buku.php';
start_session();
header('Referrer-Policy: same-origin');

$slug = bk_slug($_GET['b'] ?? '');
$n = (int)($_GET['n'] ?? 0);
$isiFile = __DIR__ . '/isi_' . $slug . '.php';
if ($slug === '' || !is_file($isiFile)) { http_response_code(404); exit('Buku tidak ditemukan.'); }
require $isiFile;
$B = BK_ISI[$slug];
$b = BK_BUKU[$slug];
$total = count($B['bab']);
if ($n < 1 || $n > $total) { http_response_code(404); exit('Bab tidak ditemukan.'); }
$bab = $B['bab'][$n - 1];
$conf = bk_conf();
$c = $conf['buku'][$slug];
$u = current_user();
$akses = $u ? bk_access($u, $slug) : null;
$prat = (int)$c['pratinjau'];
$lynk = $c['lynk_url'];
$harga = bk_rp((string)$c['harga']);
$base = 'https://zakademia.com';
$self = '/buku/' . $slug . '/';
$url = $self . 'bab-' . $n . '/';
$judul = 'Bab ' . $n . ' ' . $bab['judul'];
$title = $judul . ' | ' . $b['judul'];
$desc = $bab['ringkas'];
$img = $base . '/assets/images/og-buku-' . $slug . '.png';
$prev = $n > 1 ? $B['bab'][$n - 2] : null;
$next = $n < $total ? $B['bab'][$n] : null;

$ld = ['@context' => 'https://schema.org', '@graph' => [
    ['@type' => 'Chapter', '@id' => $base . $url . '#bab', 'name' => $bab['judul'], 'headline' => $judul, 'position' => $n, 'description' => $desc, 'url' => $base . $url, 'inLanguage' => 'id',
     'isPartOf' => ['@type' => 'Book', '@id' => $base . $self . '#buku', 'name' => $b['judul'], 'url' => $base . $self],
     'author' => array_map(fn($x) => ['@type' => 'Person', 'name' => trim($x)], explode(',', $b['penulis'])),
     'publisher' => ['@type' => 'Organization', 'name' => 'Zakademia', 'url' => $base . '/'],
     'keywords' => implode(', ', $bab['konsep']), 'hasPart' => array_map(fn($s) => ['@type' => 'CreativeWork', 'name' => $s[0] . ' ' . $s[1]], $bab['subbab'])],
    ['@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Zakademia', 'item' => $base . '/'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $b['judul'], 'item' => $base . $self],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $judul, 'item' => $base . $url]]],
]];
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=600');
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> | Zakademia</title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($base . $url) ?>">
<meta property="og:type" content="article"><meta property="og:site_name" content="Zakademia">
<meta property="og:title" content="<?= e($title) ?>"><meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($base . $url) ?>"><meta property="og:image" content="<?= e($img) ?>">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/assets/images/favicon.png">
<script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/survei/assets/sv.css?v=3"><?= zk_kepala_aset() ?><script src="/js/stat.js?v=2" defer></script>
<style>
.bb{max-width:1040px;margin:0 auto;display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:clamp(22px,4vw,48px);align-items:start}
@media(max-width:820px){.bb{grid-template-columns:1fr}.bb aside{order:-1}}
.crumb{font-size:14px;color:var(--mute);margin:0 0 14px}.crumb a{color:var(--deep);font-weight:600;text-decoration:none}.crumb a:hover{text-decoration:underline}
.kicker{font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gold)}
h1.judul{font-size:clamp(26px,3.4vw,38px);line-height:1.15;letter-spacing:-.02em;margin:6px 0 6px;color:var(--deep)}
.bagian{color:var(--mute);margin:0 0 20px;font-weight:600;font-size:15px}
.ring p{line-height:1.8;margin:0 0 16px;font-size:16.5px}
.bb h2{font-size:21px;margin:30px 0 12px;color:var(--deep)}
.sub ol{margin:0;padding:0;list-style:none;display:grid;gap:8px}
.sub li{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:12px 14px}
.sub li b{color:var(--deep)}.sub li .k{font-family:'IBM Plex Mono',ui-monospace,monospace;font-size:13px;color:var(--gold);font-weight:600;margin-right:6px}
.sub ul{margin:6px 0 0;padding-left:18px;color:var(--mute);font-size:14.5px;line-height:1.6}
.konsep{display:flex;flex-wrap:wrap;gap:6px;margin:0}.konsep span{background:var(--mist);border-radius:999px;padding:5px 11px;font-size:13.5px;font-weight:600;color:var(--deep)}
.tanya{margin:0;padding-left:20px;line-height:1.65}.tanya li{margin-bottom:6px}
.kartu{background:var(--paper);border:1px solid var(--line);border-radius:18px;padding:18px 20px;position:sticky;top:84px}
.kartu img{display:block;width:100%;height:auto;border-radius:10px;box-shadow:0 18px 40px -24px rgba(11,77,51,.6);margin-bottom:14px}
.kartu b.j{display:block;color:var(--deep);font-size:16px;line-height:1.3}.kartu .au{color:var(--mute);font-size:13.5px;margin:4px 0 12px}
.kartu .btn{width:100%;justify-content:center;box-sizing:border-box;margin-bottom:8px}
.harga{font-size:20px;font-weight:800;color:var(--deep);margin:0 0 10px}
.nav{display:flex;justify-content:space-between;gap:12px;margin-top:34px;padding-top:18px;border-top:1px solid var(--line);flex-wrap:wrap}
.nav a{text-decoration:none;color:var(--deep);font-weight:700;font-size:15px;max-width:48%}.nav a small{display:block;color:var(--mute);font-weight:600;font-size:12.5px}
.daftar{margin:36px auto 0;max-width:1040px}.daftar ol{list-style:none;margin:0;padding:0;display:grid;gap:8px;grid-template-columns:repeat(auto-fill,minmax(230px,1fr))}
.daftar a{display:block;background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:10px 12px;text-decoration:none;color:var(--ink);font-size:14.5px}
.daftar a[aria-current]{border-color:var(--deep);background:var(--mist)}.daftar a b{color:var(--deep)}
</style></head><body>
<?= zk_kepala() ?>
<main class="wrap" style="padding-top:32px;padding-bottom:70px">
<div class="bb">
  <article>
    <p class="crumb"><a href="/#kajian/buku">Publikasi</a> / <a href="<?= e($self) ?>"><?= e($b['judul']) ?></a></p>
    <span class="kicker">Bab <?= $n ?> dari <?= $total ?></span>
    <h1 class="judul"><?= e($bab['judul']) ?></h1>
    <p class="bagian"><?= e($B['bagian'][$bab['bagian']]) ?> · <?= e($b['penulis']) ?></p>
    <div class="ring"><?php foreach ($bab['isi'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?></div>

    <h2>Isi bab</h2>
    <div class="sub"><ol><?php foreach ($bab['subbab'] as $s): ?><li><span class="k"><?= e($s[0]) ?></span><b><?= e($s[1]) ?></b><?= $s[2] ? '<ul>' . implode('', array_map(fn($x) => '<li>' . e($x) . '</li>', $s[2])) . '</ul>' : '' ?></li><?php endforeach; ?></ol></div>

    <h2>Konsep kunci</h2>
    <p class="mute" style="margin:0 0 10px;font-size:14px">Istilah yang dijelaskan di kamus tepi halaman bab ini.</p>
    <p class="konsep"><?php foreach ($bab['konsep'] as $k): ?><span><?= e($k) ?></span><?php endforeach; ?></p>

    <h2>Pertanyaan yang dijawab bab ini</h2>
    <ol class="tanya"><?php foreach ($bab['tanya'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ol>
    <p class="mute" style="font-size:14px">Setiap bab ditutup dengan Inti Bab, Konsep Kunci, Pertanyaan Tinjauan, Soal dan Aplikasi, serta Proyek Penelitian Mandiri.</p>

    <nav class="nav" aria-label="Bab sebelum dan sesudah">
      <?= $prev ? '<a href="' . e($self . 'bab-' . ($n - 1) . '/') . '"><small>← Bab ' . ($n - 1) . '</small>' . e($prev['judul']) . '</a>' : '<span></span>' ?>
      <?= $next ? '<a href="' . e($self . 'bab-' . ($n + 1) . '/') . '" style="text-align:right"><small>Bab ' . ($n + 1) . ' →</small>' . e($next['judul']) . '</a>' : '<span></span>' ?>
    </nav>
  </article>
  <aside>
    <div class="kartu">
      <a href="<?= e($self) ?>"><img src="/assets/images/cover-<?= e($slug) ?>.webp" alt="Sampul <?= e($b['judul']) ?>" width="600" height="800"></a>
      <b class="j"><?= e($b['judul']) ?></b>
      <p class="au"><?= e($b['sub']) ?></p>
<?php if ($akses): ?>
      <a class="btn" href="<?= e($self) ?>baca">Baca buku</a>
<?php else: ?>
      <?= $harga !== '' ? '<p class="harga">' . e($harga) . '</p>' : '' ?>
      <?= $lynk !== '' ? '<a class="btn gold" href="' . e($lynk) . '" target="_blank" rel="noopener">Beli di Lynk.id</a>' : '' ?>
      <a class="btn ghost" href="<?= e($self) ?>baca">Baca pratinjau (<?= $prat ?> halaman)</a>
<?php endif; ?>
      <p class="mute" style="font-size:13px;margin:6px 0 0">Buku digital, dibaca di zakademia.com. <a href="<?= e($self) ?>" style="color:var(--deep);font-weight:600">Tentang buku ini</a></p>
    </div>
  </aside>
</div>
<section class="daftar" aria-labelledby="h-semua">
  <h2 id="h-semua" style="font-size:21px;color:var(--deep);margin:0 0 12px">Semua bab</h2>
  <ol><?php foreach ($B['bab'] as $i => $x): ?><li><a href="<?= e($self . 'bab-' . ($i + 1) . '/') ?>"<?= $i + 1 === $n ? ' aria-current="page"' : '' ?>><b>Bab <?= $i + 1 ?></b> <?= e($x['judul']) ?></a></li><?php endforeach; ?></ol>
</section>
</main>
</body></html>
