<?php
declare(strict_types=1);
/**
 * Mengirim satu halaman buku (gambar WebP). Halaman pratinjau terbuka untuk semua pengunjung;
 * halaman lain hanya untuk akun yang punya akses (pembeli atau admin). Email pembaca disematkan samar di tepi bawah halaman.
 * /buku/hal.php?b=metodologi&n=12
 */
require __DIR__ . '/lib_buku.php';
start_session();

$slug = bk_slug($_GET['b'] ?? '');
$n = (int)($_GET['n'] ?? 0);
$meta = $slug ? bk_meta($slug) : null;
if (!$meta || $n < 1 || $n > (int)$meta['jumlah']) { http_response_code(404); exit; }

$conf = bk_conf();
$c = $conf['buku'][$slug];
$u = current_user();
$akses = $u ? bk_access($u, $slug) : null;
$bebas = $n <= (int)$c['pratinjau'] || $n === (int)$meta['jumlah'];
if (!$bebas && (!$akses || !$u || !user_verified($u))) { http_response_code(403); header('Cache-Control: no-store'); exit; }

$f = __DIR__ . '/halaman/' . $slug . '/p' . sprintf('%04d', $n) . '.webp';
if (!is_file($f)) { http_response_code(404); exit; }

// Cegah penyalinan massal: paling banyak 600 halaman per 10 menit per akun/IP.
$tk = 'bkhal|' . ($u ? (string)$u['id'] : ip_key());
if (!throttle_ok($tk, 600, 600)) { http_response_code(429); exit; }
throttle_hit($tk);
if ($akses && !empty($akses['id']) && $u) bk_db()->prepare('UPDATE bk_akses SET terakhir = ? WHERE id = ?')->execute([gmdate('c'), (int)$akses['id']]);

header('Content-Type: image/webp');
header('Cache-Control: private, max-age=' . ($bebas ? 86400 : 1800));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline');

// Tanda air: email pembaca, samar, di tepi bawah halaman (hanya halaman berbayar).
if (!$bebas && $u && function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
    $im = @imagecreatefromwebp($f);
    if ($im) {
        $w = imagesx($im); $h = imagesy($im);
        $txt = strtolower($u['email']) . ' · zakademia.com';
        $col = imagecolorallocatealpha($im, 60, 90, 70, 96);
        $fw = imagefontwidth(2) * strlen($txt);
        imagestring($im, 2, (int)max(8, ($w - $fw) / 2), $h - 18, $txt, $col);
        imagestring($im, 2, (int)max(8, ($w - $fw) / 2), (int)($h * 0.38), $txt, $col);
        imagewebp($im, null, 72);
        imagedestroy($im);
        exit;
    }
}
header('Content-Length: ' . filesize($f));
readfile($f);
