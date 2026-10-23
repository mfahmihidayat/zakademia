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
$warna = ['#0B4D33', '#14613F', '#8A6418', '#2E5C4A', '#5C4A12', '#1F4F3C'];
foreach ($d['item'] as $n => $i) {
    $gbr = !empty($i['img']) ? '<span class="br-img"><img src="' . e($i['img']) . '" alt="" loading="lazy" referrerpolicy="no-referrer" onerror="this.parentNode.className=\'br-img kosong\';this.remove()"><i>' . e(mb_substr($i['asal'], 0, 1)) . '</i></span>'
        : '<span class="br-img kosong" style="--c:' . $warna[$n % 6] . '"><i>' . e(mb_substr($i['asal'], 0, 1)) . '</i></span>';
    echo '<li><a href="' . e($i['url'] ?: '#') . '" target="_blank" rel="noopener">' . $gbr . '<span class="br-t"><b>' . e($i['judul']) . '</b><span>' . e($i['asal']) . ' · ' . e($rel((int)$i['ts'])) . '</span></span></a></li>';
}
echo '</ol><p class="lt-empty">' . ($d['item'] ? 'Diperbarui ' . e($rel($d['diperbarui'])) . '. Isi berita menjadi tanggung jawab masing-masing sumber.' : 'Belum ada kabar yang terkumpul. Coba beberapa menit lagi.') . '</p></main>';
echo '<style>.br-list{list-style:none;margin:0;padding:0;display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))}
.br-list a{display:flex;flex-direction:column;background:var(--paper);border:1px solid var(--line);border-radius:20px 20px 20px 6px;overflow:hidden;text-decoration:none;color:var(--ink);height:100%;transition:transform .25s,box-shadow .25s}.br-list a:hover{transform:translateY(-3px);box-shadow:var(--shadow-lift);border-color:var(--gold)}
.br-img{position:relative;display:block;aspect-ratio:16/9;background:var(--mist);overflow:hidden}.br-img img{width:100%;height:100%;object-fit:cover;display:block}.br-img i{display:none}
.br-img.kosong{background:linear-gradient(135deg,var(--c,#0B4D33),color-mix(in srgb,var(--c,#0B4D33) 60%,#E2BC67));display:flex;align-items:center;justify-content:center}.br-img.kosong i{display:block;font-style:normal;font-weight:800;font-size:56px;color:rgba(255,255,240,.85)}
.br-t{display:flex;flex-direction:column;gap:6px;padding:14px 16px 16px;flex:1}.br-t b{font-size:15.5px;line-height:1.35}.br-t span{font-size:13px;color:var(--mute);font-weight:600;margin-top:auto}</style>';
echo lt_foot();
