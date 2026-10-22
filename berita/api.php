<?php
declare(strict_types=1);
/** JSON kabar terbaru untuk running text beranda; cache 10 menit di peramban/CDN. */
require __DIR__ . '/lib_berita.php';
header('Cache-Control: public, max-age=600');
header('Access-Control-Allow-Origin: https://zakademia.com');
json_out(br_tampil((int)($_GET['n'] ?? 25) ?: 25));
