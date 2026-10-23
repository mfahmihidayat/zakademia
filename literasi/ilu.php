<?php
declare(strict_types=1);
/** Ilustrasi tulisan kontributor: /literasi/ilu.php?id=N (SVG) atau &png=1 (PNG bila Imagick ada, kalau tidak gambar OG bawaan).
 *  Admin dapat mencoba rakitan tanpa menyimpan: ?j=Judul&r=rubrik&v=0&u=koin,padi,hati */
require __DIR__ . '/lib_literasi.php';
require dirname(__DIR__) . '/inc/ilu.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $t = lt_tulisan($id);
    if (!$t) { http_response_code(404); exit; }
    if ($t['status'] !== 'terbit') { start_session(); if (!is_admin() && (int)(current_user()['id'] ?? 0) !== (int)$t['user_id']) { http_response_code(403); exit; } }
    $svg = lt_ilu_svg($t);
} else {
    start_session(); if (!is_admin()) { http_response_code(403); exit; }
    $svg = zk_ilu_rakit(clip($_GET['j'] ?? 'Zakademia', 200), clip($_GET['r'] ?? '', 20), clip($_GET['x'] ?? '', 3000), (int)($_GET['v'] ?? 0), array_filter(explode(',', clip($_GET['u'] ?? '', 200))));
}
header('Cache-Control: public, max-age=' . ($id ? 86400 : 0));
if (isset($_GET['png'])) {
    if (class_exists('Imagick')) {
        try { $im = new Imagick(); $im->setBackgroundColor(new ImagickPixel('transparent')); $im->setResolution(144, 144); $im->readImageBlob($svg); $im->setImageFormat('png'); header('Content-Type: image/png'); echo $im->getImageBlob(); exit; } catch (Throwable $e) {}
    }
    header('Location: /assets/images/og-kontributor.png', true, 302); exit;
}
header('Content-Type: image/svg+xml; charset=utf-8');
if (isset($_GET['unduh'])) header('Content-Disposition: attachment; filename="ilustrasi-' . ($id ?: 'zakademia') . '.svg"');
echo $svg;
