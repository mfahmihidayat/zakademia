<?php
declare(strict_types=1);
/** /asesmen/: pengunjung yang belum masuk melihat pengantar layanan; yang sudah masuk mendapat formulir asesmen (index.html). */
require __DIR__ . '/lib.php';
start_session();
if (!current_user()) { require __DIR__ . '/../inc/layanan.php'; zk_layanan_intro('asesmen'); }
header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: no-store');
readfile(__DIR__ . '/index.html');
