<?php
declare(strict_types=1);
/**
 * Perakit ilustrasi Zakademia: menyusun gambar vektor (SVG 600x460) dari perpustakaan unsur bergaya datar hijau-emas,
 * dipilih otomatis dari judul, rubrik, dan isi tulisan. Deterministik (variasi lewat angka $varian), tanpa layanan luar.
 * Pemakaian: zk_ilu_rakit($judul, $rubrik, $teks, $varian) -> string SVG.  zk_ilu_unsur() -> daftar nama unsur.
 */
const ILU_W = ['g' => '#0B4D33', 'g2' => '#14613F', 'g3' => '#083A26', 'l' => '#6FAF78', 'ls' => '#B9D9BE', 'go' => '#C99A3B', 'gl' => '#E2BC67', 'cr' => '#FFFFF0', 'mi' => '#F2F5E6', 'ink' => '#10291F', 'w' => '#FFFFFF', 'am' => '#8A6418', 'tn' => '#D9B48C', 'bg' => '#DCE5D7'];

/** Setiap unsur digambar dalam kotak 200x200 (titik 0,0 kiri atas). */
function zk_ilu_unsur(): array
{
    $c = ILU_W;
    return [
        'koin' => "<circle cx='100' cy='100' r='70' fill='{$c['go']}'/><circle cx='100' cy='100' r='54' fill='none' stroke='{$c['gl']}' stroke-width='6'/><text x='100' y='118' font-family='Plus Jakarta Sans,Inter,Arial,sans-serif' font-weight='800' font-size='52' fill='{$c['cr']}' text-anchor='middle'>Rp</text>",
        'tumpukan_koin' => "<ellipse cx='100' cy='160' rx='70' ry='18' fill='{$c['am']}'/><rect x='30' y='120' width='140' height='40' rx='18' fill='{$c['go']}'/><ellipse cx='100' cy='120' rx='70' ry='18' fill='{$c['gl']}'/><rect x='40' y='80' width='120' height='36' rx='16' fill='{$c['go']}'/><ellipse cx='100' cy='80' rx='60' ry='16' fill='{$c['gl']}'/><rect x='50' y='44' width='100' height='32' rx='14' fill='{$c['go']}'/><ellipse cx='100' cy='44' rx='50' ry='14' fill='{$c['gl']}'/>",
        'tangan_memberi' => "<path d='M24 150c0-22 18-36 44-36h70c18 0 30 10 30 24s-12 26-30 26H72c-26 0-48-2-48-14z' fill='{$c['tn']}' stroke='{$c['am']}' stroke-width='3'/><path d='M60 116c-10-14-4-30 12-32 10-2 18 6 20 16' fill='{$c['tn']}' stroke='{$c['am']}' stroke-width='3' stroke-linejoin='round'/><rect x='24' y='164' width='150' height='14' rx='7' fill='{$c['am']}' opacity='.35'/><circle cx='108' cy='64' r='34' fill='{$c['go']}'/><circle cx='108' cy='64' r='26' fill='none' stroke='{$c['gl']}' stroke-width='4'/><text x='108' y='73' font-family='Plus Jakarta Sans,Inter,Arial,sans-serif' font-weight='800' font-size='24' fill='{$c['cr']}' text-anchor='middle'>Rp</text>",
        'masjid' => "<rect x='30' y='120' width='140' height='60' rx='8' fill='{$c['g']}'/><path d='M50 120c0-40 25-60 50-72 25 12 50 32 50 72z' fill='{$c['g2']}'/><circle cx='100' cy='46' r='8' fill='{$c['go']}'/><path d='M100 28v12' stroke='{$c['go']}' stroke-width='4'/><rect x='12' y='60' width='18' height='120' rx='6' fill='{$c['g']}'/><rect x='170' y='60' width='18' height='120' rx='6' fill='{$c['g']}'/><circle cx='21' cy='56' r='9' fill='{$c['go']}'/><circle cx='179' cy='56' r='9' fill='{$c['go']}'/><path d='M86 180v-34a14 14 0 0 1 28 0v34z' fill='{$c['gl']}'/>",
        'sertifikat' => "<rect x='30' y='40' width='140' height='120' rx='12' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><rect x='50' y='64' width='100' height='10' rx='5' fill='{$c['g']}'/><rect x='50' y='86' width='80' height='8' rx='4' fill='{$c['ls']}'/><rect x='50' y='104' width='90' height='8' rx='4' fill='{$c['ls']}'/><circle cx='140' cy='136' r='18' fill='{$c['go']}'/><path d='M132 150l-4 24 12-8 12 8-4-24' fill='{$c['go']}'/>",
        'tanah' => "<path d='M20 150c30-30 60-40 100-40s60 10 60 30-30 30-80 30-80 0-80-20z' fill='{$c['l']}'/><path d='M40 140c20-18 50-26 80-26s50 8 50 20-24 22-64 22-66-2-66-16z' fill='{$c['ls']}'/><rect x='94' y='60' width='12' height='70' rx='4' fill='{$c['am']}'/><rect x='70' y='50' width='60' height='40' rx='8' fill='{$c['go']}'/><rect x='80' y='62' width='40' height='6' rx='3' fill='{$c['cr']}'/><rect x='80' y='74' width='28' height='6' rx='3' fill='{$c['cr']}'/>",
        'pohon' => "<rect x='90' y='120' width='20' height='60' rx='6' fill='{$c['am']}'/><circle cx='100' cy='90' r='50' fill='{$c['l']}'/><circle cx='70' cy='110' r='34' fill='{$c['g2']}'/><circle cx='132' cy='108' r='34' fill='{$c['g2']}'/><circle cx='100' cy='70' r='30' fill='{$c['ls']}' opacity='.7'/>",
        'padi' => "<path d='M100 180V60' stroke='{$c['l']}' stroke-width='8' stroke-linecap='round'/><path d='M60 180c10-40 20-60 40-80M140 180c-10-40-20-60-40-80' stroke='{$c['l']}' stroke-width='6' fill='none' stroke-linecap='round'/>" . implode('', array_map(fn($i) => "<ellipse cx='" . (100 + ($i % 2 ? 14 : -14)) . "' cy='" . (50 + $i * 14) . "' rx='9' ry='15' fill='{$c['gl']}' transform='rotate(" . ($i % 2 ? 25 : -25) . " " . (100 + ($i % 2 ? 14 : -14)) . " " . (50 + $i * 14) . ")'/>", range(0, 5))) . "<circle cx='100' cy='34' r='10' fill='{$c['go']}'/>",
        'emas' => "<path d='M40 150l20-40h80l20 40z' fill='{$c['go']}'/><path d='M60 110l12-20h56l12 20z' fill='{$c['gl']}'/><path d='M40 150h120v16H40z' fill='{$c['am']}'/><rect x='84' y='124' width='32' height='12' rx='6' fill='{$c['cr']}' opacity='.7'/><path d='M150 60l6 14 14 6-14 6-6 14-6-14-14-6 14-6z' fill='{$c['gl']}'/>",
        'buku_tabungan' => "<rect x='40' y='50' width='120' height='110' rx='12' fill='{$c['g']}'/><rect x='56' y='66' width='88' height='78' rx='8' fill='{$c['w']}'/><rect x='66' y='80' width='60' height='8' rx='4' fill='{$c['ls']}'/><rect x='66' y='96' width='70' height='8' rx='4' fill='{$c['ls']}'/><rect x='66' y='112' width='44' height='8' rx='4' fill='{$c['go']}'/><circle cx='150' cy='160' r='22' fill='{$c['go']}'/><text x='150' y='167' font-family='sans-serif' font-weight='800' font-size='22' fill='{$c['cr']}' text-anchor='middle'>Rp</text>",
        'formulir' => "<rect x='46' y='30' width='108' height='140' rx='12' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><rect x='62' y='52' width='60' height='10' rx='5' fill='{$c['g']}'/><rect x='62' y='74' width='76' height='8' rx='4' fill='{$c['ls']}'/><rect x='62' y='92' width='76' height='8' rx='4' fill='{$c['ls']}'/><rect x='62' y='110' width='50' height='8' rx='4' fill='{$c['ls']}'/><rect x='62' y='132' width='40' height='22' rx='8' fill='{$c['go']}'/><path d='M120 160l10 10 20-24' stroke='{$c['g']}' stroke-width='6' fill='none' stroke-linecap='round' stroke-linejoin='round'/>",
        'centang' => "<circle cx='100' cy='100' r='64' fill='{$c['g']}'/><path d='M70 102l20 20 40-44' stroke='{$c['cr']}' stroke-width='12' fill='none' stroke-linecap='round' stroke-linejoin='round'/>",
        'perisai' => "<path d='M100 24l64 24v52c0 44-30 70-64 84-34-14-64-40-64-84V48z' fill='{$c['g']}'/><path d='M100 40l48 18v42c0 34-22 54-48 66-26-12-48-32-48-66V58z' fill='{$c['g2']}'/><path d='M76 100l16 16 32-36' stroke='{$c['gl']}' stroke-width='10' fill='none' stroke-linecap='round' stroke-linejoin='round'/>",
        'grafik_batang' => "<rect x='30' y='40' width='140' height='120' rx='14' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><rect x='50' y='110' width='20' height='36' rx='6' fill='{$c['ls']}'/><rect x='80' y='90' width='20' height='56' rx='6' fill='{$c['l']}'/><rect x='110' y='70' width='20' height='76' rx='6' fill='{$c['g2']}'/><rect x='140' y='56' width='20' height='90' rx='6' fill='{$c['g']}'/><path d='M60 100 90 80 120 62 150 48' stroke='{$c['go']}' stroke-width='5' fill='none' stroke-linecap='round'/><circle cx='150' cy='48' r='7' fill='{$c['go']}'/>",
        'grafik_lingkar' => "<circle cx='100' cy='100' r='64' fill='{$c['ls']}'/><path d='M100 100V36a64 64 0 0 1 60 84z' fill='{$c['g']}'/><path d='M100 100l60 20a64 64 0 0 1-56 44z' fill='{$c['go']}'/><circle cx='100' cy='100' r='30' fill='{$c['cr']}'/>",
        'kaca_pembesar' => "<circle cx='86' cy='86' r='50' fill='{$c['w']}' stroke='{$c['g']}' stroke-width='12'/><path d='M122 122l46 46' stroke='{$c['g']}' stroke-width='18' stroke-linecap='round'/><path d='M60 90c10-20 30-26 46-16' stroke='{$c['ls']}' stroke-width='6' fill='none' stroke-linecap='round'/>",
        'orang' => "<circle cx='100' cy='64' r='30' fill='{$c['tn']}'/><path d='M40 180c0-40 26-66 60-66s60 26 60 66z' fill='{$c['g']}'/><path d='M70 64c4-24 20-36 30-36s26 12 30 36c-10-6-20-8-30-8s-20 2-30 8z' fill='{$c['g2']}'/>",
        'dua_orang' => "<circle cx='70' cy='70' r='24' fill='{$c['tn']}'/><path d='M20 170c0-34 22-56 50-56s50 22 50 56z' fill='{$c['g2']}'/><circle cx='136' cy='78' r='22' fill='{$c['tn']}'/><path d='M94 176c0-30 18-52 42-52s42 22 42 52z' fill='{$c['go']}'/>",
        'hati' => "<path d='M100 170c-50-36-74-60-74-90a36 36 0 0 1 74-14 36 36 0 0 1 74 14c0 30-24 54-74 90z' fill='{$c['go']}'/><path d='M60 84c0-14 10-26 24-28' stroke='{$c['gl']}' stroke-width='8' fill='none' stroke-linecap='round'/>",
        'anak' => "<circle cx='100' cy='80' r='34' fill='{$c['tn']}'/><path d='M66 70c4-26 18-40 34-40s30 14 34 40c-10-8-22-10-34-10s-24 2-34 10z' fill='{$c['g3']}'/><path d='M52 180c0-30 22-50 48-50s48 20 48 50z' fill='{$c['l']}'/><circle cx='88' cy='84' r='4' fill='{$c['ink']}'/><circle cx='112' cy='84' r='4' fill='{$c['ink']}'/><path d='M90 98c6 6 14 6 20 0' stroke='{$c['ink']}' stroke-width='3' fill='none' stroke-linecap='round'/>",
        'piring' => "<ellipse cx='100' cy='130' rx='76' ry='26' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><ellipse cx='100' cy='126' rx='56' ry='16' fill='{$c['mi']}'/><ellipse cx='100' cy='116' rx='40' ry='18' fill='{$c['cr']}' stroke='{$c['gl']}' stroke-width='3'/><circle cx='80' cy='112' r='8' fill='{$c['l']}'/><circle cx='112' cy='106' r='8' fill='{$c['go']}'/><path d='M60 60c0-10 8-16 8-26M100 56c0-10 8-16 8-26M140 60c0-10 8-16 8-26' stroke='{$c['ls']}' stroke-width='4' fill='none' stroke-linecap='round'/>",
        'kalender' => "<rect x='36' y='44' width='128' height='124' rx='14' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><rect x='36' y='44' width='128' height='34' rx='14' fill='{$c['g']}'/><rect x='60' y='30' width='10' height='28' rx='5' fill='{$c['go']}'/><rect x='130' y='30' width='10' height='28' rx='5' fill='{$c['go']}'/>" . implode('', array_map(fn($i) => "<rect x='" . (54 + ($i % 4) * 26) . "' y='" . (92 + intdiv($i, 4) * 26) . "' width='18' height='18' rx='5' fill='" . ($i === 6 ? $c['go'] : $c['mi']) . "'/>", range(0, 11))),
        'bulan_bintang' => "<path d='M120 40a62 62 0 1 0 40 108 50 50 0 1 1-40-108z' fill='{$c['go']}'/><path d='M150 60l7 18 18 7-18 7-7 18-7-18-18-7 18-7z' fill='{$c['gl']}'/>",
        'ponsel' => "<rect x='60' y='24' width='80' height='152' rx='16' fill='{$c['g']}'/><rect x='70' y='40' width='60' height='112' rx='8' fill='{$c['w']}'/><rect x='80' y='56' width='40' height='8' rx='4' fill='{$c['ls']}'/><rect x='80' y='72' width='28' height='8' rx='4' fill='{$c['ls']}'/><rect x='80' y='96' width='40' height='40' rx='8' fill='{$c['go']}'/><circle cx='100' cy='164' r='5' fill='{$c['cr']}'/>",
        'dokumen' => "<rect x='50' y='30' width='100' height='140' rx='12' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><path d='M120 30v30h30' fill='{$c['mi']}' stroke='{$c['ls']}' stroke-width='4'/><rect x='66' y='80' width='68' height='8' rx='4' fill='{$c['g']}'/><rect x='66' y='98' width='68' height='8' rx='4' fill='{$c['ls']}'/><rect x='66' y='116' width='50' height='8' rx='4' fill='{$c['ls']}'/><rect x='66' y='134' width='60' height='8' rx='4' fill='{$c['ls']}'/>",
        'buku_terbuka' => "<path d='M20 60c30-10 56-8 80 6 24-14 50-16 80-6v100c-30-10-56-8-80 6-24-14-50-16-80-6z' fill='{$c['w']}' stroke='{$c['g']}' stroke-width='5' stroke-linejoin='round'/><path d='M100 66v100' stroke='{$c['g']}' stroke-width='4'/><rect x='36' y='80' width='48' height='6' rx='3' fill='{$c['ls']}'/><rect x='36' y='96' width='48' height='6' rx='3' fill='{$c['ls']}'/><rect x='116' y='80' width='48' height='6' rx='3' fill='{$c['ls']}'/><rect x='116' y='96' width='34' height='6' rx='3' fill='{$c['go']}'/>",
        'rumah_sakit' => "<rect x='30' y='70' width='140' height='110' rx='10' fill='{$c['w']}' stroke='{$c['ls']}' stroke-width='4'/><rect x='70' y='40' width='60' height='40' rx='8' fill='{$c['g']}'/><path d='M100 50v20M90 60h20' stroke='{$c['cr']}' stroke-width='6' stroke-linecap='round'/>" . implode('', array_map(fn($i) => "<rect x='" . (46 + ($i % 3) * 40) . "' y='" . (90 + intdiv($i, 3) * 34) . "' width='24' height='22' rx='5' fill='{$c['ls']}'/>", range(0, 5))) . "<rect x='86' y='150' width='28' height='30' rx='6' fill='{$c['go']}'/>",
        'topi_wisuda' => "<path d='M100 50l80 32-80 32-80-32z' fill='{$c['g']}'/><path d='M52 96v36c0 14 22 26 48 26s48-12 48-26V96l-48 20z' fill='{$c['g2']}'/><path d='M180 82v44' stroke='{$c['go']}' stroke-width='5' stroke-linecap='round'/><circle cx='180' cy='132' r='8' fill='{$c['go']}'/>",
        'gedung' => "<rect x='40' y='60' width='60' height='120' rx='6' fill='{$c['g']}'/><rect x='104' y='90' width='56' height='90' rx='6' fill='{$c['g2']}'/>" . implode('', array_map(fn($i) => "<rect x='" . (52 + ($i % 2) * 22) . "' y='" . (74 + intdiv($i, 2) * 24) . "' width='12' height='14' rx='3' fill='{$c['gl']}'/>", range(0, 7))) . "<rect x='116' y='104' width='32' height='10' rx='3' fill='{$c['ls']}'/><rect x='116' y='124' width='32' height='10' rx='3' fill='{$c['ls']}'/>",
        'tangan_bersama' => "<path d='M30 120c20-20 50-24 70-10 20-14 50-10 70 10l-30 30H60z' fill='{$c['tn']}' stroke='{$c['am']}' stroke-width='3'/><circle cx='100' cy='70' r='26' fill='{$c['go']}'/><path d='M100 56v28M90 70h20' stroke='{$c['cr']}' stroke-width='6' stroke-linecap='round'/>",
        'palu' => "<rect x='96' y='90' width='14' height='90' rx='6' fill='{$c['am']}' transform='rotate(-35 103 135)'/><rect x='56' y='40' width='90' height='44' rx='10' fill='{$c['g']}' transform='rotate(-35 100 62)'/><rect x='30' y='160' width='100' height='14' rx='6' fill='{$c['go']}'/>",
        'peta_indonesia' => "<path d='M20 100c20-16 40-10 60-6 16-10 30-4 44 6 14-6 30-6 46 2 8 4 12 12 10 20-20 6-40 4-60 0-16 8-34 8-52 0-20 4-36 0-48-10z' fill='{$c['l']}'/><circle cx='70' cy='100' r='6' fill='{$c['go']}'/><circle cx='120' cy='104' r='6' fill='{$c['go']}'/><circle cx='160' cy='110' r='6' fill='{$c['go']}'/>",
        'megafon' => "<path d='M40 80h40l70-40v120l-70-40H40z' fill='{$c['g']}'/><rect x='24' y='84' width='24' height='32' rx='8' fill='{$c['g2']}'/><path d='M166 86c10 6 10 22 0 28' stroke='{$c['go']}' stroke-width='8' fill='none' stroke-linecap='round'/><path d='M176 70c20 14 20 46 0 60' stroke='{$c['gl']}' stroke-width='6' fill='none' stroke-linecap='round'/>",
        'bintang' => "<path d='M100 24l22 46 50 7-36 35 9 50-45-24-45 24 9-50-36-35 50-7z' fill='{$c['go']}'/><path d='M100 60l10 22 24 3-17 17 4 24-21-11' fill='{$c['gl']}' opacity='.7'/>",
    ];
}

/** Kata kunci -> unsur. Urutan menentukan prioritas. */
function zk_ilu_peta(): array
{
    return [
        'wakaf uang|cwls|sukuk' => ['tumpukan_koin', 'sertifikat', 'masjid'], 'tanah wakaf|sertifikat|sertipikat' => ['tanah', 'sertifikat', 'masjid'], 'wakaf produktif|produktif' => ['pohon', 'tumpukan_koin', 'gedung'],
        'nazhir|nadzir' => ['perisai', 'masjid', 'dokumen'], 'wakaf|waqf' => ['masjid', 'tanah', 'sertifikat'],
        'emas|logam|perhiasan' => ['emas', 'kalender', 'koin'], 'tabungan|deposito|simpanan|rekening' => ['buku_tabungan', 'koin', 'kalender'], 'pajak|spt|pkp' => ['formulir', 'koin', 'centang'],
        'pertanian|padi|gabah|sawah|panen|petani' => ['padi', 'koin', 'tanah'], 'perdagangan|usaha|toko|dagang|umkm|bisnis' => ['gedung', 'tumpukan_koin', 'grafik_batang'], 'penghasilan|profesi|gaji' => ['koin', 'formulir', 'kalender'],
        'fidyah|puasa|makan|pangan|gizi' => ['piring', 'kalender', 'tangan_memberi'], 'ramadan|idulfitri|lebaran|fitrah' => ['bulan_bintang', 'koin', 'tangan_memberi'], 'kurban|qurban|iduladha' => ['tangan_bersama', 'bulan_bintang', 'piring'],
        'asnaf|mustahik|penerima|fakir|miskin|penyaluran' => ['dua_orang', 'tangan_memberi', 'hati'], 'muzaki|donatur|retensi|setia|loyal' => ['dua_orang', 'hati', 'grafik_lingkar'], 'amil|lembaga|baznas|laz|tata kelola|kelembagaan|akuntabilitas|transparansi' => ['gedung', 'perisai', 'dokumen'],
        'stunting|anak|balita|ibu' => ['anak', 'piring', 'hati'], 'kesehatan|rumah sakit|rs |mata|dokter' => ['rumah_sakit', 'hati', 'tangan_bersama'], 'pendidikan|sekolah|beasiswa|santri|pesantren|mahasiswa|kampus' => ['topi_wisuda', 'buku_terbuka', 'anak'],
        'data|statistik|angka|triliun|miliar|persen|indeks|analitik|tren|pertumbuhan|target' => ['grafik_batang', 'kaca_pembesar', 'grafik_lingkar'], 'digital|aplikasi|qris|online|daring|platform|instagram|media sosial' => ['ponsel', 'koin', 'megafon'],
        'penelitian|riset|metode|metodologi|kajian|jurnal|literatur' => ['kaca_pembesar', 'buku_terbuka', 'dokumen'], 'undang|peraturan|regulasi|hukum|fatwa|pma|uu ' => ['palu', 'dokumen', 'perisai'], 'rakornas|rapat|konferensi|forum|seminar|webinar|workshop' => ['megafon', 'dua_orang', 'kalender'],
        'nasional|indonesia|provinsi|daerah|nusantara' => ['peta_indonesia', 'grafik_batang', 'koin'], 'buku|terbit|bab' => ['buku_terbuka', 'bintang', 'dokumen'], 'sedekah|infak|infaq|berbagi|kebaikan|dermawan' => ['tangan_memberi', 'hati', 'koin'],
        'zakat' => ['koin', 'tangan_memberi', 'dua_orang'],
    ];
}
function zk_ilu_rubrik(string $r): array
{
    return ['flash' => ['megafon', 'kalender', 'bintang'], 'pedia' => ['buku_terbuka', 'kaca_pembesar', 'koin'], 'story' => ['dua_orang', 'hati', 'tangan_bersama'], 'tips' => ['centang', 'formulir', 'koin'], 'stat' => ['grafik_batang', 'grafik_lingkar', 'peta_indonesia'], 'talk' => ['megafon', 'dua_orang', 'ponsel']][$r] ?? ['koin', 'dokumen', 'bintang'];
}

/** Pilih tiga unsur: dari kata kunci judul (bobot 3) dan isi (bobot 1), dilengkapi unsur rubrik. */
function zk_ilu_pilih(string $judul, string $rubrik, string $teks = ''): array
{
    $j = mb_strtolower($judul . ' '); $t = mb_strtolower(mb_substr($teks, 0, 3000)); $skor = [];
    foreach (zk_ilu_peta() as $pola => $unsur) {
        $re = '/\b(' . $pola . ')/u';
        $n = (preg_match($re, $j) ? 3 : 0) + min(2, preg_match_all($re, $t));
        if (in_array($pola, ['zakat', 'wakaf|waqf'], true)) $n *= 0.6; // kata umum: jangan mengalahkan kata khusus
        if ($n) foreach ($unsur as $i => $u) $skor[$u] = ($skor[$u] ?? 0) + $n * [3, 1, 0.5][$i];
    }
    foreach (zk_ilu_rubrik($rubrik) as $i => $u) $skor[$u] = ($skor[$u] ?? 0) + (1.5 - $i * 0.5);
    arsort($skor);
    return array_slice(array_keys($skor), 0, 3);
}

/** Susun SVG. $varian 0..5 mengubah tata letak dan hiasan; $unsur (opsional) memaksa pilihan unsur. */
function zk_ilu_rakit(string $judul, string $rubrik = '', string $teks = '', int $varian = 0, array $unsur = []): string
{
    $c = ILU_W; $semua = zk_ilu_unsur();
    $pilih = array_values(array_filter($unsur, fn($u) => isset($semua[$u])));
    if (count($pilih) < 3) foreach (zk_ilu_pilih($judul, $rubrik, $teks) as $u) if (!in_array($u, $pilih, true)) $pilih[] = $u;
    while (count($pilih) < 3) $pilih[] = 'bintang';
    $tata = [
        [[200, 90, 1.5], [60, 250, 0.9], [420, 270, 0.85]], [[90, 110, 1.4], [370, 60, 0.95], [400, 260, 0.9]], [[210, 60, 1.3], [40, 230, 1.0], [430, 230, 0.8]],
        [[300, 110, 1.45], [60, 70, 0.85], [60, 270, 0.85]], [[150, 150, 1.5], [430, 60, 0.9], [440, 280, 0.75]], [[200, 120, 1.35], [40, 40, 0.8], [440, 260, 0.9]],
    ][$varian % 6];
    $hias = $varian % 2 ? "<circle cx='520' cy='80' r='46' fill='{$c['gl']}' opacity='.9'/><circle cx='70' cy='410' r='90' fill='{$c['ls']}' opacity='.5'/>" : "<circle cx='90' cy='70' r='40' fill='{$c['gl']}' opacity='.9'/><circle cx='540' cy='400' r='84' fill='{$c['ls']}' opacity='.5'/><path d='M470 60l8 20 20 8-20 8-8 20-8-20-20-8 20-8z' fill='{$c['go']}' opacity='.8'/>";
    $out = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 460' width='600' height='460' role='img' aria-label='" . htmlspecialchars($judul, ENT_QUOTES) . "'>"
        . "<defs><radialGradient id='bg' cx='70%' cy='20%' r='80%'><stop offset='0' stop-color='{$c['mi']}'/><stop offset='1' stop-color='{$c['cr']}'/></radialGradient></defs><rect width='600' height='460' rx='40' fill='url(#bg)'/>" . $hias;
    // unsur sekunder dulu, utama terakhir agar di depan
    foreach ([1, 2, 0] as $k) { [$x, $y, $s] = $tata[$k]; $out .= "<g transform='translate($x $y) scale($s)'>" . ($k ? "<circle cx='100' cy='100' r='96' fill='{$c['w']}' opacity='.55'/>" : "<circle cx='100' cy='100' r='98' fill='{$c['w']}' opacity='.75'/>") . $semua[$pilih[$k]] . "</g>"; }
    return $out . '</svg>';
}
