<?php
declare(strict_types=1);
/** Data tulisan kontributor yang terbit: kartu untuk halaman Literasi, entri indeks pencarian, dan sitemap. */
require __DIR__ . '/lib_literasi.php';
$rows = lt_db()->query('SELECT t.*, p.nama AS penulis FROM lt_tulisan t LEFT JOIN lt_penulis p ON p.user_id = t.user_id WHERE t.status = \'terbit\' ORDER BY t.terbit DESC LIMIT 500')->fetchAll(PDO::FETCH_ASSOC);
if (isset($_GET['sitemap'])) {
    header('Content-Type: application/xml; charset=utf-8'); header('Cache-Control: public, max-age=3600');
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($rows as $t) echo '<url><loc>' . e(LT_BASE . lt_url($t)) . '</loc><lastmod>' . e(substr((string)$t['diubah'], 0, 10)) . '</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>';
    echo '</urlset>'; exit;
}
header('Cache-Control: public, max-age=300');
if (isset($_GET['indeks'])) {
    json_out(array_map(fn($t) => ['t' => $t['judul'], 'k' => 'Literasi · ' . (LT_RUBRIK[$t['rubrik']][0] ?? '') . ' · Kontributor', 'u' => lt_url($t), 'd' => $t['dek'],
        'x' => mb_substr(implode(' ', lt_poin($t)) . ' ' . ($t['penulis'] ?? '') . ' ' . strip_tags(lt_html($t['isi'])), 0, 600)], $rows));
}
json_out(['ok' => true, 'data' => array_map(fn($t) => ['r' => $t['rubrik'], 'tanggal' => substr((string)$t['terbit'], 0, 10), 'html' => lt_card($t)], $rows)]);
