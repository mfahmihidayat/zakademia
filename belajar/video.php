<?php
declare(strict_types=1);
/** Video materi (MP4 di belajar/video/) hanya untuk peserta terdaftar atau admin; mendukung Range agar bisa diputar sambil diunduh. */
require __DIR__ . '/lib_lms.php';
start_session();
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['k'] ?? ''))); $n = (int)($_GET['n'] ?? 0);
$k = $slug ? lms_kelas($slug) : null; $u = current_user();
$f = __DIR__ . '/video/' . $slug . '-' . $n . '.mp4';
if (!$k || $n < 1 || !is_file($f)) { http_response_code(404); exit('Video tidak ditemukan.'); }
if (!$u || (!is_admin() && !lms_terdaftar((int)$u['id'], (int)$k['id']))) { http_response_code(403); exit('Masuk dan daftar kelas untuk menonton video ini.'); }
$size = filesize($f); $start = 0; $end = $size - 1;
header('Content-Type: video/mp4'); header('Accept-Ranges: bytes'); header('Cache-Control: private, max-age=3600'); header('X-Content-Type-Options: nosniff');
if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
    if ($m[1] !== '') $start = (int)$m[1]; if ($m[2] !== '') $end = min((int)$m[2], $size - 1);
    if ($start > $end || $start >= $size) { http_response_code(416); header("Content-Range: bytes */$size"); exit; }
    http_response_code(206); header("Content-Range: bytes $start-$end/$size");
}
header('Content-Length: ' . ($end - $start + 1));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') exit;
$h = fopen($f, 'rb'); fseek($h, $start); $left = $end - $start + 1;
while ($left > 0 && !feof($h)) { $c = fread($h, min(65536, $left)); echo $c; $left -= strlen($c); flush(); }
fclose($h);
