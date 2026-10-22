<?php
declare(strict_types=1);
/** Halaman daftar kabar zakat dan wakaf (sumber luar, diperbarui otomatis). */
require __DIR__ . '/lib_berita.php';
require_once __DIR__ . '/../literasi/lib_literasi.php';
start_session();
$d = br_tampil(60);
$rel = function (int $ts): string { $s = time() - $ts; if ($s < 3600) return max(1, (int)($s / 60)) . ' menit lalu'; if ($s < 86400) return (int)($s / 3600) . ' jam lalu'; if ($s < 7 * 86400) return (int)($s / 86400) . ' hari lalu'; return lt_tgl(gmdate('c', $ts)); };
echo lt_head('Kabar Zakat dan Wakaf | Zakademia', 'Berita terbaru seputar zakat, wakaf, BAZNAS, LAZ, BWI, dan Kemenag dari berbagai sumber, diperbarui otomatis.', LT_BASE . '/berita/', LT_BASE . '/assets/images/og-image.png', '', true);
echo '<main id="isi" class="lt-wrap"><header class="lt-ihead"><p class="lt-eyebrow">Kabar</p><h1>Kabar zakat dan wakaf hari ini.</h1><p>Judul berita dari BWI, Dompet Dhuafa, Rumah Zakat, LAZISMU, dan pemberitaan media tentang BAZNAS dan Kemenag, dikumpulkan otomatis setiap 30 menit. Klik untuk membaca di sumbernya.</p></header><ol class="br-list">';
foreach ($d['item'] as $i) echo '<li><a href="' . e($i['url'] ?: '#') . '" target="_blank" rel="noopener"><b>' . e($i['judul']) . '</b><span>' . e($i['asal']) . ' · ' . e($rel((int)$i['ts'])) . '</span></a></li>';
echo '</ol><p class="lt-empty">' . ($d['item'] ? 'Diperbarui ' . e($rel($d['diperbarui'])) . '. Isi berita menjadi tanggung jawab masing-masing sumber.' : 'Belum ada kabar yang terkumpul. Coba beberapa menit lagi.') . '</p></main>';
echo '<style>.br-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}.br-list a{display:flex;flex-direction:column;gap:4px;padding:14px 18px;background:var(--paper);border:1px solid var(--line);border-radius:16px 16px 16px 6px;text-decoration:none;color:var(--ink)}.br-list a:hover{border-color:var(--gold)}.br-list b{font-size:16px;line-height:1.35}.br-list span{font-size:13px;color:var(--mute);font-weight:600}</style>';
echo lt_foot();
