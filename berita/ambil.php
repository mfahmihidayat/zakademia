<?php
declare(strict_types=1);
/** Pemicu pembaruan dari cron: /berita/ambil.php?k=KUNCI (kunci tampil di Pusat kendali). */
require __DIR__ . '/lib_berita.php';
$d = br_baca();
if (!hash_equals($d['kunci'], (string)($_GET['k'] ?? ''))) json_out(['ok' => false], 403);
$d = br_perbarui();
json_out(['ok' => true, 'jumlah' => count($d['item']), 'log' => $d['log'][0]]);
