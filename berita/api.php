<?php
declare(strict_types=1);
/** JSON kabar terbaru untuk running text beranda; cache 10 menit di peramban/CDN. */
require __DIR__ . '/lib_berita.php';
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: https://zakademia.com');
$data = br_tampil((int)($_GET['n'] ?? 25) ?: 25);
$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
header('Content-Type: application/json; charset=utf-8'); header('Content-Length: ' . strlen($json)); header('Connection: close');
echo $json;
// kirim respons dulu, baru perbarui di belakang layar bila sudah basi
if (function_exists('fastcgi_finish_request')) fastcgi_finish_request(); else { @ob_end_flush(); flush(); }
ignore_user_abort(true); set_time_limit(90);
br_perbarui_bila_basi();
