<?php
declare(strict_types=1);
require __DIR__ . '/../asesmen/lib.php';
/* Tautan publik per layanan: menampilkan pratinjau (judul, deskripsi, gambar) saat dibagikan, lalu membuka bagian layanan di situs. */
$L = [
    'riset' => ['Riset Kebutuhan', 'Penelitian terapan yang dirancang berdasarkan kebutuhan informasi lembaga, mencakup perilaku donatur, evaluasi program, dan pemetaan potensi.'],
    'analitik' => ['Data & Analitik', 'Pengelolaan, standardisasi, dan analisis data kelembagaan sebagai dasar pemantauan kinerja.'],
    'pendampingan' => ['Pendampingan Strategi', 'Pendampingan penyusunan dan implementasi strategi kelembagaan berbasis temuan penelitian.'],
    'belajar' => ['Ruang Belajar', 'Penguatan kapasitas metodologi penelitian bagi pengelola dan pemerhati filantropi Islam.'],
];
$k = strtolower((string)($_GET['l'] ?? ''));
if ($k === '' && preg_match('#^/layanan/([a-z-]+)/?#', (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), $m)) $k = $m[1];
$alias = ['riset-kebutuhan' => 'riset', 'survei' => 'riset', 'data' => 'analitik', 'data-analitik' => 'analitik', 'ruang-belajar' => 'belajar', 'asesmen' => 'pendampingan', 'pendampingan-strategi' => 'pendampingan'];
$k = $alias[$k] ?? $k;
$to = isset($L[$k]) ? '/#layanan/' . $k : '/#layanan';
[$t, $d] = $L[$k] ?? ['Layanan Zakademia', 'Empat layanan berbasis penelitian untuk mendukung pengambilan keputusan lembaga filantropi Islam.'];
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com');
$h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: public, max-age=300');
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<?= share_meta(isset($L[$k]) ? $k : '', '/layanan/' . $k) ?>
<link rel="canonical" href="<?= $h($base . '/layanan/' . $k) ?>">
<link rel="icon" href="/assets/images/favicon.png">
<meta http-equiv="refresh" content="0;url=<?= $h($to) ?>">
<script>location.replace(<?= json_encode($to) ?>);</script>
</head><body style="font-family:system-ui,sans-serif;background:#FFFFF0;color:#10291F;padding:40px 20px">
<p>Membuka <a href="<?= $h($to) ?>"><?= $h($t) ?></a> di Zakademia…</p>
<script src="/js/stat.js?v=2" defer></script>
</body></html>
