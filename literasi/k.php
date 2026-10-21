<?php
declare(strict_types=1);
/** Halaman tulisan kontributor: /literasi/k/<slug>/ (terbit) atau pratinjau k.php?id=N&pra=1 (penulis/admin). */
require __DIR__ . '/lib_literasi.php';
start_session();
header('Content-Type: text/html; charset=utf-8');
$pdo = lt_db();
if (!empty($_GET['pra'])) {
    $t = lt_tulisan((int)($_GET['id'] ?? 0)); $u = current_user();
    if (!$t || !$u || ((int)$t['user_id'] !== (int)$u['id'] && !is_admin())) { http_response_code(404); exit('Tulisan tidak ditemukan.'); }
    header('Cache-Control: no-store'); header('X-Robots-Tag: noindex');
    echo lt_halaman($t, true); exit;
}
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['s'] ?? '')));
$t = $slug ? lt_tulisan_slug($slug) : null;
if (!$t || $t['status'] !== 'terbit') { http_response_code(404); header('Cache-Control: no-store'); readfile(__DIR__ . '/../404.html'); exit; }
if (!is_admin()) $pdo->prepare('UPDATE lt_tulisan SET dilihat = dilihat + 1 WHERE id = ?')->execute([(int)$t['id']]);
header('Cache-Control: public, max-age=300');
echo lt_halaman($t);
