<?php
declare(strict_types=1);
/** Halaman pengantar layanan untuk pengunjung yang belum masuk: apa itu, untuk siapa, cara kerja, lalu Masuk/Daftar. */
const ZK_LAYANAN = [
    'asesmen' => ['judul' => 'Asesmen', 'lede' => 'Ukur kesiapan penghimpunan lembaga Anda pada enam dimensi, gratis, sekitar 15 menit.',
        'untuk' => 'Pimpinan dan tim penghimpunan LAZ, BAZNAS daerah, UPZ, dan nazhir yang ingin tahu posisi lembaganya sebelum menyusun rencana.',
        'manfaat' => ['Peta kesiapan pada enam dimensi: pemahaman donatur, perjalanan donatur, kanal dan kampanye, data dan analitik, tata kelola, kapasitas tim', 'Skor kinerja penghimpunan dibanding patokan lembaga sejenis', 'Hasil dikirim ke email dan tersimpan di akun; dapat ditindaklanjuti dengan Rekomendasi'],
        'langkah' => ['Masuk atau daftar dengan email lembaga', 'Jawab pertanyaan tentang kondisi lembaga, bisa dilanjutkan kapan saja', 'Terima peta kesiapan dan ringkasan di email'],
        'gambar' => 'layanan-asesmen', 'tombol' => 'Mulai asesmen', 'next' => '/asesmen/'],
    'survei' => ['judul' => 'Survei', 'lede' => 'Buat survei donatur atau penerima manfaat dalam hitungan menit, bagikan lewat tautan dan kode QR, baca hasilnya langsung.',
        'untuk' => 'Lembaga yang ingin mendengar donatur dan mustahik tanpa alat survei berbayar.',
        'manfaat' => ['Lima belas jenis pertanyaan: pilihan, skala, peringkat, isian, dan lainnya', 'Tautan pendek dan kode QR untuk disebar lewat WhatsApp atau dicetak', 'Hasil dirangkum otomatis dan dapat diunduh sebagai CSV'],
        'langkah' => ['Masuk atau daftar', 'Susun pertanyaan dari templat atau dari kosong', 'Bagikan tautannya dan pantau jawaban yang masuk'],
        'gambar' => 'layanan-survei', 'tombol' => 'Buat survei', 'next' => '/survei/'],
    'analitik' => ['judul' => 'Analitik', 'lede' => 'Hitung kesehatan penghimpunan dari angka rekap tahunan: retensi, nilai donatur, biaya akuisisi, dan biaya per rupiah terhimpun.',
        'untuk' => 'Tim data dan penghimpunan yang punya rekap tahunan tetapi belum punya alat untuk membacanya.',
        'manfaat' => ['Tidak perlu mengunggah data donatur, cukup angka rekap', 'Angka dibandingkan dengan patokan yang lazim bagi lembaga zakat', 'Hasil tersimpan per tahun sehingga tren terlihat dari waktu ke waktu'],
        'langkah' => ['Masuk atau daftar', 'Isi angka rekap tahun berjalan dan tahun lalu', 'Baca penjelasan tiap angka dan langkah yang disarankan'],
        'gambar' => 'layanan-analitik', 'tombol' => 'Buka analitik', 'next' => '/analitik/'],
    'rekomendasi' => ['judul' => 'Rekomendasi', 'lede' => 'Rencana tindak lanjut yang disusun dari jawaban asesmen lembaga Anda: prioritas, langkah, dan ukuran keberhasilan.',
        'untuk' => 'Lembaga yang sudah mengisi asesmen dan ingin peta jalan yang konkret.',
        'manfaat' => ['Prioritas perbaikan berdasarkan dimensi terlemah', 'Langkah 90 hari yang bisa langsung dikerjakan tim', 'Dapat dibuka kembali kapan saja dari akun'],
        'langkah' => ['Isi asesmen lebih dulu', 'Dapatkan akses lewat Lynk.id atau konfirmasi transfer', 'Buka rekomendasi dan bagikan ke tim'],
        'gambar' => 'layanan-rekomendasi', 'tombol' => 'Lihat rekomendasi', 'next' => '/rekomendasi/'],
];
function zk_layanan_intro(string $key): void
{
    $l = ZK_LAYANAN[$key]; $next = $l['next'];
    header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: no-store');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . share_meta($key === 'survei' ? 'riset' : $key, $next)
        . '<link rel="icon" href="/assets/images/favicon.png"><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"><link rel="stylesheet" href="/survei/assets/sv.css?v=3">' . zk_kepala_aset()
        . '<style>.intro{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(280px,.9fr);gap:36px;align-items:center}.intro h1{font-size:clamp(34px,5vw,56px);line-height:1.1;margin:0 0 10px;color:var(--gold-text,#8A6418)}.intro .lede{font-size:19px;line-height:1.6;color:var(--mute);margin:0 0 18px}
.intro img{width:100%;height:auto;border-radius:28px;box-shadow:var(--shadow)}.acts{display:flex;gap:10px;flex-wrap:wrap}.tiga{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:40px}
.tiga .card{padding:20px 22px}.tiga h2{font-size:17px;margin:0 0 8px}.tiga ul,.tiga ol{margin:0;padding-left:20px;display:flex;flex-direction:column;gap:6px;font-size:15px;color:var(--mute)}.tiga p{margin:0;font-size:15px;color:var(--mute)}
@media(max-width:820px){.intro{grid-template-columns:1fr}.intro img{max-width:420px}}</style></head><body>' . zk_kepala()
        . '<main class="wrap"><div class="intro"><div><h1>' . e($l['judul']) . '.</h1><p class="lede">' . e($l['lede']) . '</p><div class="acts"><a class="btn" href="/asesmen/masuk.php?next=' . rawurlencode($next) . '">Masuk untuk ' . e(mb_strtolower($l['tombol'])) . '</a><a class="btn ghost" href="/asesmen/daftar.php?next=' . rawurlencode($next) . '">Daftar akun gratis</a></div></div>'
        . '<img src="/assets/images/ilu/' . $l['gambar'] . '.webp" alt="Ilustrasi ' . e($l['judul']) . '" width="600" height="460"></div>'
        . '<div class="tiga"><div class="card"><h2>Untuk siapa</h2><p>' . e($l['untuk']) . '</p></div><div class="card"><h2>Yang Anda dapat</h2><ul>' . implode('', array_map(fn($x) => '<li>' . e($x) . '</li>', $l['manfaat'])) . '</ul></div><div class="card"><h2>Cara kerja</h2><ol>' . implode('', array_map(fn($x) => '<li>' . e($x) . '</li>', $l['langkah'])) . '</ol></div></div>'
        . '<p class="mute small" style="margin-top:28px">Akun Zakademia gratis dan dipakai untuk semua layanan: asesmen, survei, analitik, kelas, dan buku digital.</p></main><script src="/js/stat.js?v=2" defer></script></body></html>';
    exit;
}
