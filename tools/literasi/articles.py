# -*- coding: utf-8 -*-
"""Isi artikel Literasi Zakademia. Penanda {n} di teks menjadi rujukan bernomor ke daftar sumber."""

RUBRIK = {
    'flash': ('ZakaFlash', 'Apa yang baru?', 'rb-flash.webp'),
    'pedia': ('ZakaPedia', 'Apa artinya?', 'rb-pedia.webp'),
    'story': ('ZakaStory', 'Siapa yang merasakan?', 'rb-story.webp'),
    'tips': ('ZakaTips', 'Bagaimana caranya?', 'rb-tips.webp'),
    'stat': ('ZakaStat', 'Berapa besarnya?', 'rb-stat.webp'),
    'talk': ('ZakaTalk', 'Apa kata kamu?', 'rb-talk.webp'),
}

ARTIKEL = [
# ---------------------------------------------------------------- ZakaFlash
dict(
slug='rakornas-baznas-2026-target-70-triliun', rubrik='flash', tanggal='2026-10-15', menit=4,
judul='Rakornas BAZNAS 2026: Target Rp70 Triliun dan Wacana Revisi UU Zakat',
og=['Target Rp70 triliun', 'dan wacana revisi UU Zakat.'],
dek='Pertemuan para pengelola zakat se-Indonesia menetapkan target penghimpunan 2027 dan membuka kembali pembicaraan tentang undang-undang zakat. Apa artinya bagi lembaga?',
poin=[
 'Rakornas BAZNAS 2026 berlangsung 13–15 Oktober 2026 di Jakarta dan diikuti sekitar 1.300 peserta dari BAZNAS provinsi dan kabupaten/kota.',
 'Target penghimpunan zakat, infak, sedekah, dan dana sosial keagamaan lainnya (ZIS-DSKL) nasional 2027 ditetapkan Rp70 triliun.',
 'Realisasi 2025 tercatat Rp44,70 triliun. Untuk mencapai target, penghimpunan perlu tumbuh sekitar 57 persen dalam dua tahun.',
 'Menko Kumham Imipas Yusril Ihza Mahendra mendorong pengkajian ulang UU Nomor 23 Tahun 2011 tentang Pengelolaan Zakat.',
],
isi='''
<h2>Apa yang terjadi</h2>
<p>Badan Amil Zakat Nasional (BAZNAS) menggelar Rapat Koordinasi Nasional (Rakornas) pada 13–15 Oktober 2026 di Jakarta dengan tema <em>Konsolidasi Zakat Indonesia yang Produktif dan Berdampak</em>. Sekitar 1.300 peserta dari BAZNAS provinsi dan kabupaten/kota se-Indonesia hadir, bersama sejumlah pejabat negara, di antaranya Ketua MPR Ahmad Muzani, Menteri Agama Nasaruddin Umar, dan Menko Kumham Imipas Yusril Ihza Mahendra.{1}</p>
<p>Ketua BAZNAS RI Sodik Mudjahid mengumumkan target penghimpunan ZIS-DSKL nasional sebesar Rp70 triliun pada 2027. Target ini mencakup BAZNAS di semua tingkatan dan Lembaga Amil Zakat (LAZ) di seluruh Indonesia, serta mengacu pada Rencana Strategis BAZNAS 2026–2031.{1}{2}</p>
<blockquote><p>“Setiap penerima manfaat adalah saudara kita yang sedang kita bantu bangkit, dari mustahik menjadi muzaki.”</p><cite>Sodik Mudjahid, Ketua BAZNAS RI{1}</cite></blockquote>

<h2>Dari Rp44,7 triliun ke Rp70 triliun</h2>
<p>Menurut Laporan Pengelolaan Zakat Nasional Akhir Tahun 2025, penghimpunan ZIS-DSKL nasional mencapai Rp44,70 triliun, naik 9,54 persen dari Rp40,81 triliun pada 2024.{3} Artinya, target 2027 menuntut pertumbuhan sekitar 57 persen dalam dua tahun, jauh di atas laju tahun lalu.</p>
<p>Sodik menyebut target itu hanya dapat dicapai bila seluruh BAZNAS daerah bergerak bersama dan mendapat dukungan pemerintah pusat dan daerah, dunia usaha, serta LAZ mitra.{1}</p>

<h2>Wacana revisi UU Zakat</h2>
<p>Dalam pembukaan Rakornas, Yusril mendorong pengkajian ulang UU Nomor 23 Tahun 2011 tentang Pengelolaan Zakat. Tujuannya memperkuat kelembagaan BAZNAS dan memperluas peran zakat dalam pengentasan kemiskinan. Ia juga mengingatkan bahwa zakat adalah kewajiban agama, sehingga tidak semestinya diperlakukan seperti penerimaan pajak. Sejauh pemberitaan, belum disebutkan pasal mana yang akan diubah maupun jadwal pembahasannya.{2}</p>
<p>Pada forum yang sama, Menteri Agama Nasaruddin Umar menekankan pengawasan berlapis melalui audit keuangan dan audit syariah. Direktur Pemberdayaan Zakat dan Wakaf Kemenag, Waryono, mendorong pemanfaatan Data Tunggal Sosial dan Ekonomi Nasional (DTSEN) agar penyaluran lebih tepat sasaran dan tidak tumpang tindih.{2}</p>

<h2>Apa artinya bagi lembaga</h2>
<ul>
<li><b>Data dan pelaporan akan makin disorot.</b> Target sebesar ini hanya bermakna bila setiap rupiah tercatat rapi dan dapat diaudit, baik secara keuangan maupun syariah.</li>
<li><b>Ketepatan sasaran menjadi ukuran.</b> Dorongan memakai DTSEN berarti lembaga perlu siap mencocokkan data penerima manfaat dengan data nasional.</li>
<li><b>Pertumbuhan tidak cukup dari donatur baru.</b> Laporan 2025 mencatat jumlah muzaki turun dari 31,5 juta menjadi 28,7 juta, meski dana naik.{3} Menjaga muzaki lama agar terus menunaikan zakat sama pentingnya dengan mencari muzaki baru.</li>
</ul>
<p class="cta-in">Ingin tahu kondisi penghimpunan lembaga Anda? Coba <a href="/analitik/">Kesehatan Penghimpunan</a>: isi angka rekap tahunan, hasilnya langsung keluar.</p>
''',
sumber=[
 ('BAZNAS RI', 'Perluas Manfaat, BAZNAS RI Tetapkan Target Pengumpulan ZIS-DSKL Nasional 2027 Capai Rp70 Triliun', '13 Oktober 2026', 'https://baznas.go.id/news-show/Perluas_Manfaat__BAZNAS_RI_Tetapkan_Target_Pengumpulan_ZIS_DSKL_Nasional_2027_Capai_Rp70_Triliun/4404'),
 ('Periskop.id', 'Yusril Dorong Revisi UU Zakat, Baznas Bidik Rp70 Triliun di 2027', '15 Oktober 2026', 'https://periskop.id/regulasi/20261015/yusril-revisi-uu-zakat-baznas-target-70-triliun-2027'),
 ('BAZNAS', 'Laporan Pengelolaan Zakat Nasional Akhir Tahun 2025', '2026', 'https://baznas.go.id/assets/images/szn/LPZN-Tahun-2025.pdf'),
 ('JPNN', 'Rakornas 2026, Baznas Pasang Target Zakat Nasional Rp 70 Triliun', '14 Oktober 2026', 'https://www.jpnn.com/news/rakornas-2026-baznas-pasang-target-zakat-nasional-rp-70-triliun'),
]),

# ---------------------------------------------------------------- ZakaPedia
dict(
slug='wakaf-uang-wakaf-melalui-uang-cwls', rubrik='pedia', tanggal='2026-10-15', menit=5,
judul='Wakaf Uang, Wakaf melalui Uang, dan CWLS: Apa Bedanya?',
og=['Wakaf uang,', 'wakaf melalui uang,', 'dan CWLS.'],
dek='Tiga istilah yang sering tertukar. Perbedaannya terletak pada apa yang dijaga: pokok uangnya, atau aset yang dibeli dengan uang itu.',
poin=[
 'Wakaf uang: pokok uangnya dijaga dan tidak boleh berkurang. Yang disalurkan adalah hasil pengelolaannya.',
 'Wakaf melalui uang: uang dipakai untuk membeli atau membangun aset wakaf, misalnya tanah, masjid, atau sekolah.',
 'CWLS (Cash Waqf Linked Sukuk): wakaf uang yang ditempatkan pada sukuk negara. Imbal hasilnya membiayai program sosial.',
],
isi='''
<h2>Wakaf, pengertian dasarnya</h2>
<p>Undang-Undang Nomor 41 Tahun 2004 mendefinisikan wakaf sebagai perbuatan hukum wakif untuk memisahkan dan/atau menyerahkan sebagian harta benda miliknya untuk dimanfaatkan selamanya atau untuk jangka waktu tertentu, guna keperluan ibadah dan/atau kesejahteraan umum menurut syariah.{1} Kuncinya ada pada kata <em>dimanfaatkan</em>: harta wakaf tidak habis dibagikan, melainkan dijaga agar manfaatnya terus mengalir. Pihak yang menerima dan mengelola harta wakaf disebut nazhir.</p>

<h2>Wakaf uang</h2>
<p>Dalam wakaf uang, yang diwakafkan adalah uang itu sendiri. Pokoknya dikelola, misalnya dalam usaha atau instrumen keuangan syariah, lalu <b>hasilnya</b> yang disalurkan untuk kepentingan sosial dan keagamaan. Pokoknya tetap utuh.{2}</p>
<p>Majelis Ulama Indonesia telah memfatwakan kebolehan wakaf uang pada 2002, sebelum UU Wakaf terbit.{2} Undang-undang kemudian mengaturnya secara khusus: wakif dapat mewakafkan benda bergerak berupa uang melalui lembaga keuangan syariah yang ditunjuk Menteri Agama (LKS-PWU).{1} Di LKS-PWU, wakif mengikrarkan wakafnya dan menerima Sertifikat Wakaf Uang.{2}</p>

<h2>Wakaf melalui uang</h2>
<p>Dalam praktik penggalangan, banyak ajakan berbunyi “wakaf pembangunan masjid” atau “wakaf sumur”. Di sini uang wakif <b>dibelanjakan</b> untuk membeli tanah atau membangun sesuatu. Setelah aset itu berdiri, asetnyalah yang menjadi harta wakaf, bukan uangnya. Bentuk ini lazim disebut <em>wakaf melalui uang</em>.</p>
<p>Keduanya sah dan sama-sama bernilai. Yang penting, wakif mengetahui dengan jelas mana yang sedang ia ikuti, karena yang dijaga berbeda: pokok uang, atau aset yang dibangun.</p>

<h2>CWLS: wakaf uang yang bertemu sukuk negara</h2>
<p>Cash Waqf Linked Sukuk (CWLS) adalah sukuk negara yang dibeli dengan dana wakaf uang. Pemerintah membayar imbal hasil, lalu imbal hasil itu disalurkan nazhir untuk program sosial. Pokoknya dikembalikan saat jatuh tempo.</p>
<p>Seri ritel SWR006 (2025) terjual Rp306,28 miliar dari 799 wakif dengan tenor dua tahun dan imbal hasil mengambang dengan batas bawah 5,70 persen per tahun. Sebanyak 85,86 persen wakifnya adalah wakif baru. Imbal hasilnya disalurkan oleh nazhir yang disetujui Badan Wakaf Indonesia, antara lain untuk layanan ambulans, sarana pendidikan, dan pemberdayaan usaha mikro.{3}</p>
<p>Saat tulisan ini terbit, seri SWR007 sedang ditawarkan hingga 21 Oktober 2026 pukul 12.00 WIB, dengan imbal hasil mengambang minimal 6,70 persen per tahun, tenor dua tahun, dan pemesanan mulai Rp1 juta. Imbal hasilnya direncanakan antara lain untuk wakaf pangan produktif, klinik gratis, asrama pesantren, dan kesejahteraan guru.{5}</p>
<p>Seri pertama, SW001 (2020), senilai Rp50,84 miliar, membiayai pusat retina dan operasi katarak gratis bagi pasien dhuafa di rumah sakit mata berbasis wakaf di Serang, Banten.{4} Kisahnya kami tulis di <a href="/literasi/rs-mata-achmad-wardi-wakaf-yang-mengembalikan-penglihatan/">ZakaStory</a>.</p>

<h2>Ringkasnya</h2>
<div class="tbl"><table>
<thead><tr><th></th><th>Wakaf uang</th><th>Wakaf melalui uang</th><th>CWLS</th></tr></thead>
<tbody>
<tr><th>Yang diserahkan</th><td>Uang</td><td>Uang</td><td>Uang, ditempatkan pada sukuk negara</td></tr>
<tr><th>Yang dijaga</th><td>Pokok uangnya</td><td>Aset yang dibeli atau dibangun</td><td>Pokok uangnya</td></tr>
<tr><th>Yang dimanfaatkan</th><td>Hasil pengelolaan</td><td>Aset itu sendiri</td><td>Imbal hasil sukuk</td></tr>
<tr><th>Contoh</th><td>Wakaf di LKS-PWU</td><td>Pembangunan masjid atau sekolah</td><td>Seri SW dan SWR</td></tr>
</tbody></table></div>
<p>Sebelum berwakaf, tanyakan kepada nazhir: apakah uang ini akan dijaga pokoknya, atau dibelanjakan menjadi aset? Nazhir yang amanah akan menjawabnya dengan jelas.</p>
''',
sumber=[
 ('JDIH Kementerian Keuangan', 'Undang-Undang Nomor 41 Tahun 2004 tentang Wakaf', '2004', 'https://jdih.kemenkeu.go.id/fulltext/2004/41TAHUN2004UU.htm'),
 ('NU Online', 'Mengenal Wakaf Uang dan Caranya', '25 Januari 2021', 'https://nu.or.id/nasional/mengenal-wakaf-uang-dan-caranya-5d9oo'),
 ('Kementerian Keuangan', 'Keterangan Pers Hasil Penjualan CWLS Ritel Seri SWR006', '20 Oktober 2025', 'https://media.kemenkeu.go.id/getmedia/dcacc8fb-4314-49df-bb76-5c386be846f7/KP_Hasil_Penjualan_SWR006_v3?ext=.pdf'),
 ('Bisnis.com', 'Sukuk Wakaf Rp50,84 Miliar Atasi Katarak, Berikan Imbal Hasil 5 Persen', '10 Maret 2020', 'https://finansial.bisnis.com/read/20200310/11/1211719/sukuk-wakaf-rp5084-miliar-atasi-katarak-berikan-imbal-hasil-5-persen'),
 ('EmitenNews', 'Pemerintah Rilis Sukuk Wakaf SWR007 Imbalan Minimal 6,70%', '5 September 2026', 'https://emitennews.com/news/pemerintah-rilis-sukuk-wakaf-swr007-imbalan-minimal-670'),
]),

# ---------------------------------------------------------------- ZakaStory
dict(
slug='rs-mata-achmad-wardi-wakaf-yang-mengembalikan-penglihatan', rubrik='story', tanggal='2026-10-15', menit=4,
judul='Ketika Wakaf Mengembalikan Penglihatan: Kisah RS Mata Achmad Wardi',
og=['Ketika wakaf', 'mengembalikan penglihatan.'],
dek='Di Serang, Banten, sebidang tanah wakaf tumbuh menjadi rumah sakit mata. Ribuan pasien dhuafa kembali bisa membaca, menjahit, dan bekerja.',
poin=[
 'RS Mata Achmad Wardi berdiri di atas tanah wakaf dan diresmikan Dompet Dhuafa bersama Badan Wakaf Indonesia pada April 2018.',
 'Sukuk wakaf seri SW001 (2020) senilai Rp50,84 miliar membiayai pusat retina serta operasi katarak gratis bagi pasien dhuafa.',
 'Melalui program Gerakan Banten Melihat, rumah sakit ini mencatat 2.468 operasi gratis sepanjang 2018–2024.',
],
isi='''
<p class="lead">Ibu Maisaroh, 59 tahun, menjahit untuk menghidupi keluarganya. Ketika katarak membuat matanya tak lagi bisa melihat, pekerjaan itu terhenti. Setelah menjalani operasi gratis di RS Mata Achmad Wardi, ia kembali bisa membaca Al-Qur’an dan menjahit.{1}</p>
<p>Kisah serupa datang dari Pak Muardi, petani berusia 62 tahun dari Lebak, yang lama tak bisa bekerja karena penglihatannya kabur. Sami, nelayan dari Serang, menggambarkan matanya sebelum operasi: “Jadi seperti bayang-bayangan aja gitu. Seperti bayangan orang lewat gitu.”{1}{2}</p>

<h2>Bermula dari sebidang tanah</h2>
<p>Rumah sakit di Jalan Raya Taktakan, Kota Serang, ini berdiri di atas tanah wakaf almarhum Achmad Wardi. Dompet Dhuafa dan Badan Wakaf Indonesia meresmikannya pada April 2018 sebagai rumah sakit khusus mata pertama di Banten. Sejak awal, rumah sakit ini melayani pasien dhuafa secara gratis atau dengan biaya rendah.{2}{3}</p>

<h2>Ketika wakaf bertemu sukuk negara</h2>
<p>Pada Maret 2020, pemerintah menerbitkan sukuk wakaf seri SW001 senilai Rp50,84 miliar dengan Badan Wakaf Indonesia sebagai nazhir. Dananya bekerja dengan dua cara. Diskon penerbitan dipakai untuk merenovasi dan melengkapi pusat retina di rumah sakit ini. Kupon 5 persen per tahun membiayai operasi katarak gratis bagi pasien dhuafa, ditambah pengadaan ambulans. Selama lima tahun masa sukuk, sasarannya 2.513 pasien. Pokok dananya kembali utuh kepada wakif saat jatuh tempo pada Maret 2025.{4}</p>
<p>Di sinilah keistimewaan wakaf produktif: dananya tidak habis dibagikan, tetapi manfaatnya terus berputar.</p>

<h2>Gerakan Banten Melihat</h2>
<p>Sejak 2018, rumah sakit ini menjalankan Gerakan Banten Melihat, program operasi katarak gratis bagi warga kurang mampu. Operasinya memakai teknik fakoemulsifikasi dengan lensa tanam berkualitas tinggi, sama dengan layanan untuk pasien berbayar. Tim juga turun ke desa-desa terpencil untuk pemeriksaan keliling, dan menyediakan aplikasi pemeriksaan awal daring bernama SOCA.{1}</p>
<div class="stat-row"><div><b>2.468</b><span>operasi katarak gratis, 2018–2024</span></div><div><b>Rp50,84 M</b><span>sukuk wakaf SW001 (2020)</span></div><div><b>2018</b><span>diresmikan di atas tanah wakaf</span></div></div>
<p>Pendanaannya kini datang dari banyak arah: wakaf, donatur perorangan, mitra perusahaan, dan lembaga filantropi.{1}</p>

<h2>Pelajaran bagi lembaga</h2>
<ul>
<li><b>Aset wakaf bisa menjadi layanan publik.</b> Tanah yang dikelola dengan rencana yang jelas dapat tumbuh menjadi layanan kesehatan yang berkelanjutan.</li>
<li><b>Ukur dampaknya dalam angka.</b> Jumlah operasi, pasien yang kembali bekerja, dan wilayah yang dijangkau membuat wakif dan donatur yakin dananya bekerja.</li>
<li><b>Gabungkan sumber dana.</b> Wakaf, sukuk wakaf, donasi, dan kemitraan saling menguatkan.</li>
</ul>
<p class="cta-in">Ingin tahu beda wakaf uang, wakaf melalui uang, dan sukuk wakaf? Baca <a href="/literasi/wakaf-uang-wakaf-melalui-uang-cwls/">ZakaPedia</a>.</p>
''',
sumber=[
 ('PERSI', 'RS Mata Achmad Wardi: Gerakan Banten Melihat (dokumen PERSI Award 2025)', '2025', 'https://www.persi.or.id/wp-content/uploads/2025/10/RS-Mata-Achmad-Wardi-GERAKAN-BANTEN-MELIHAT.pdf'),
 ('IDX Channel', 'Berkat Operasi Gratis di RS Wakaf, Sami Kini Melihat Kembali', '11 April 2022', 'https://www.idxchannel.com/syariah/berkat-operasi-gratis-di-rs-wakaf-sami-kini-melihat-kembali'),
 ('RMOL', 'Dompet Dhuafa dan BWI Resmikan Rumah Sakit Mata Pertama di Serang', '20 April 2018', 'https://rmol.id/nusantara/read/2018/04/20/336227/dompet-dhuafa-dan-bwi-resmikan-rumah-sakit-mata-pertama-di-serang'),
 ('Bisnis.com', 'Sukuk Wakaf Rp50,84 Miliar Atasi Katarak, Berikan Imbal Hasil 5 Persen', '10 Maret 2020', 'https://finansial.bisnis.com/read/20200310/11/1211719/sukuk-wakaf-rp5084-miliar-atasi-katarak-berikan-imbal-hasil-5-persen'),
]),

# ---------------------------------------------------------------- ZakaTips
dict(
slug='cara-menghitung-zakat-penghasilan-2026', rubrik='tips', tanggal='2026-10-15', menit=4,
judul='Cara Menghitung Zakat Penghasilan 2026, Lengkap dengan Contoh',
og=['Cara menghitung', 'zakat penghasilan 2026.'],
dek='Nisab tahun ini Rp7.640.144 per bulan. Begini cara memeriksa apakah penghasilan Anda sudah wajib zakat, menghitungnya, dan menyimpan bukti pembayarannya.',
poin=[
 'Nisab zakat penghasilan 2026: Rp91.681.728 per tahun atau Rp7.640.144 per bulan, setara 85 gram emas.',
 'Kadarnya 2,5 persen dari penghasilan.',
 'Bayar melalui BAZNAS atau LAZ resmi dan simpan bukti setornya. Zakat yang dibayarkan ke lembaga resmi dapat dikurangkan dari penghasilan kena pajak.',
],
isi='''
<h2>1. Kenali nisab tahun ini</h2>
<p>Nisab adalah batas minimal penghasilan yang membuat seseorang wajib menunaikan zakat penghasilan. Melalui SK Ketua BAZNAS Nomor 15 Tahun 2026 yang ditetapkan 21 Februari 2026, nisab zakat penghasilan dan jasa ditetapkan <b>Rp91.681.728 per tahun</b> atau <b>Rp7.640.144 per bulan</b>. Angka ini setara dengan nilai 85 gram emas. Kadarnya 2,5 persen.{1}</p>

<h2>2. Tentukan dasar hitungnya</h2>
<p>BAZNAS menganjurkan zakat dihitung dari penghasilan <b>bruto</b>, yaitu penghasilan sebelum dikurangi apa pun. Ada pula pendapat yang membolehkan penghitungan dari penghasilan <b>neto</b>, setelah dikurangi kebutuhan pokok diri dan keluarga.{2} Pilih salah satu, lalu gunakan secara konsisten.</p>

<h2>3. Hitung</h2>
<div class="rumus">Zakat penghasilan = 2,5% × penghasilan</div>
<p>Bila penghasilan Anda tetap setiap bulan, bandingkan dengan nisab bulanan. Bila penghasilan tidak tetap, jumlahkan penghasilan setahun lalu bandingkan dengan nisab tahunan.{2}</p>
<div class="contoh"><b>Contoh 1 · Gaji tetap</b>Gaji Rp9.000.000 per bulan, di atas nisab Rp7.640.144.<br>Zakat = 2,5% × Rp9.000.000 = <b>Rp225.000 per bulan</b>.</div>
<div class="contoh"><b>Contoh 2 · Penghasilan tidak tetap</b>Seorang pekerja lepas menerima total Rp96.000.000 dalam setahun, di atas nisab Rp91.681.728.<br>Zakat = 2,5% × Rp96.000.000 = <b>Rp2.400.000 per tahun</b>.</div>
<div class="contoh"><b>Contoh 3 · Di bawah nisab</b>Gaji Rp6.500.000 per bulan, di bawah nisab. Belum wajib zakat penghasilan, tetapi tetap dapat berinfak dan bersedekah.</div>

<div class="kalk" id="kalk">
 <b>Hitung zakat Anda</b>
 <label>Penghasilan<input id="kIn" inputmode="numeric" placeholder="contoh: 9.000.000" autocomplete="off"></label>
 <div class="kper" role="radiogroup" aria-label="Periode penghasilan"><label><input type="radio" name="kPer" value="b" checked> per bulan</label><label><input type="radio" name="kPer" value="t"> per tahun</label></div>
 <p id="kOut" aria-live="polite">Masukkan penghasilan Anda.</p>
</div>

<h2>4. Bayar ke lembaga resmi dan simpan buktinya</h2>
<p>UU Nomor 23 Tahun 2011 menyatakan bahwa zakat yang dibayarkan muzaki kepada BAZNAS atau LAZ dikurangkan dari penghasilan kena pajak, dan lembaga wajib memberikan bukti setoran kepada setiap muzaki.{3}{4} Daftar badan dan lembaga penerima zakat yang diakui untuk keperluan pajak dimuat dalam Peraturan Direktur Jenderal Pajak Nomor PER-3/PJ/2024.{4}</p>
<p>Pastikan bukti setor mencantumkan nama dan NPWP Anda, jumlah dan tanggal pembayaran, serta nama lembaga amil zakat.{4} Untuk penerapan pada kondisi pajak Anda, konsultasikan dengan konsultan pajak.</p>

<h2>5. Jadikan kebiasaan</h2>
<ul>
<li>Atur pembayaran otomatis setiap tanggal gajian agar zakat tidak terlewat.</li>
<li>Catat penghasilan tambahan seperti bonus dan honor. Untuk penghasilan tidak tetap, rekap di akhir tahun.</li>
<li>Untuk keadaan khusus, misalnya utang besar atau penghasilan bersama pasangan, tanyakan kepada amil atau ustaz yang Anda percaya.</li>
</ul>
<script>
(function(){var NB=7640144,NT=91681728,i=document.getElementById('kIn'),o=document.getElementById('kOut');if(!i)return;
var f=new Intl.NumberFormat('id-ID');function p(){return Number(String(i.value).replace(/[^0-9]/g,''))||0;}
function run(){var v=p(),per=document.querySelector('input[name=kPer]:checked').value,n=per==='b'?NB:NT;i.value=v?f.format(v):'';
 if(!v){o.textContent='Masukkan penghasilan Anda.';return;}
 o.innerHTML=v>=n?'Sudah mencapai nisab. Zakat Anda <b>Rp'+f.format(Math.round(v*0.025))+' '+(per==='b'?'per bulan':'per tahun')+'</b>.':'Belum mencapai nisab (Rp'+f.format(n)+' '+(per==='b'?'per bulan':'per tahun')+'). Belum wajib zakat penghasilan; infak dan sedekah tetap dianjurkan.';}
i.addEventListener('input',run);document.querySelectorAll('input[name=kPer]').forEach(function(r){r.addEventListener('change',run);});})();
</script>
''',
sumber=[
 ('Warta Ekonomi', 'Baznas Tetapkan Nisab Zakat 2026 Naik 7%: Gaji 7,64 Juta Wajib Zakat', '25 Februari 2026', 'https://wartaekonomi.co.id/read602278/baznas-tetapkan-nisab-zakat-2026-naik-7-sesuai-kenaikan-upah-nasional-gaji-764-juta-wajib-zakat?page=all'),
 ('CNN Indonesia', 'Bagaimana Cara Menghitung Zakat Penghasilan?', '25 Maret 2024', 'https://www.cnnindonesia.com/edukasi/20240325104555-569-1078561/bagaimana-cara-menghitung-zakat-penghasilan'),
 ('Peraturan.go.id', 'Undang-Undang Nomor 23 Tahun 2011 tentang Pengelolaan Zakat', '2011', 'https://peraturan.go.id/files/uu23-2011bt.pdf'),
 ('CNBC Indonesia', 'Zakat Bisa Jadi Pengurang Tagihan Pajak Anda, Ini Aturannya', '16 Maret 2026', 'https://www.cnbcindonesia.com/news/20260315182802-4-719183/zakat-bisa-jadi-pengurang-tagihan-pajak-anda-ini-aturannya'),
]),

# ---------------------------------------------------------------- ZakaStat
dict(
slug='membaca-angka-zakat-nasional-2025', rubrik='stat', tanggal='2026-10-15', menit=5,
judul='Rp44,7 Triliun: Membaca Angka Zakat Nasional 2025',
og=['Rp44,7 triliun:', 'membaca angka zakat 2025.'],
dek='Penghimpunan naik, jumlah muzaki tercatat turun, dan jarak dengan potensi masih lebar. Enam angka untuk memahami peta zakat, wakaf, dan kedermawanan Indonesia.',
poin=[
 'Penghimpunan ZIS-DSKL nasional 2025: Rp44,70 triliun, naik 9,54 persen.',
 'Sekitar 73 persen di antaranya dicatat di luar neraca lembaga amil.',
 'Jumlah muzaki tercatat 28,7 juta, turun dari 31,5 juta pada 2024.',
 'Realisasi baru sekitar 14 persen dari potensi zakat Rp327,6 triliun. Wakaf uang baru sekitar 1,6 persen dari potensinya.',
],
isi='''
<p class="lead">Setiap tahun BAZNAS menerbitkan Laporan Pengelolaan Zakat Nasional. Laporan untuk tahun 2025 memuat angka-angka yang layak dibaca pelan-pelan oleh siapa pun yang mengelola dana umat.{1}</p>

<div class="bigstat"><span class="n">1</span><b>Rp44,70 triliun</b><p>Total penghimpunan zakat, infak, sedekah, dan dana sosial keagamaan lainnya (ZIS-DSKL) nasional pada 2025, naik 9,54 persen dari Rp40,81 triliun pada 2024. Penyalurannya Rp44,29 triliun.{1}</p></div>

<div class="bigstat"><span class="n">2</span><b>73 persen</b><p>Porsi penghimpunan yang dicatat <em>di luar neraca</em> (Rp32,82 triliun). Dana ini dilaporkan, tetapi tidak tercatat langsung dalam laporan keuangan BAZNAS dan LAZ. Yang tercatat di neraca Rp11,88 triliun.{1}</p></div>
<p>Dari Rp11,88 triliun yang tercatat di neraca, komposisinya sebagai berikut:</p>
<div class="bars" role="img" aria-label="Komposisi penghimpunan di neraca 2025">
 <div class="br"><span>Zakat maal</span><i style="--w:100%"></i><em>Rp4,30 T · −1,78%</em></div>
 <div class="br"><span>Infak/sedekah</span><i style="--w:97%"></i><em>Rp4,18 T · +9,24%</em></div>
 <div class="br"><span>Kurban</span><i style="--w:55%"></i><em>Rp2,35 T · −13,46%</em></div>
 <div class="br"><span>Zakat fitrah</span><i style="--w:17%"></i><em>Rp0,72 T · +15,55%</em></div>
 <div class="br"><span>DSKL</span><i style="--w:8%"></i><em>Rp0,33 T · +28,86%</em></div>
</div>
<p class="note">Persentase menunjukkan pertumbuhan dibanding 2024. Sumber: Laporan Pengelolaan Zakat Nasional 2025.{1} Penjumlahan per baris tabel laporan menunjukkan LAZ menghimpun sekitar Rp7,48 triliun dan BAZNAS di semua tingkatan sekitar Rp4,40 triliun dari dana yang tercatat di neraca.</p>

<div class="bigstat"><span class="n">3</span><b>28,7 juta muzaki</b><p>Jumlah muzaki tercatat turun dari 31,5 juta pada 2024, meski dana yang terhimpun naik.{1} Artinya, rata-rata nilai per muzaki meningkat. Bagi lembaga, ini pengingat bahwa menjaga muzaki agar terus menunaikan zakat sama pentingnya dengan mencari muzaki baru.</p></div>

<div class="bigstat"><span class="n">4</span><b>±14 persen dari potensi</b><p>BAZNAS memperkirakan potensi zakat Indonesia Rp327,6 triliun per tahun.{2} Dibandingkan realisasi Rp44,70 triliun, yang terhimpun baru sekitar 14 persen. Potensi adalah perkiraan, tetapi jaraknya cukup lebar untuk menunjukkan ruang tumbuh yang besar.</p></div>

<div class="bigstat"><span class="n">5</span><b>Rp2,9 triliun wakaf uang</b><p>Wakaf uang yang tercatat Badan Wakaf Indonesia pada 2024. Potensinya diperkirakan Rp181 triliun per tahun, sehingga realisasinya baru sekitar 1,6 persen.{3} Indonesia juga memiliki sekitar 451 ribu titik aset wakaf, banyak di antaranya belum dikelola secara produktif.{4}</p></div>

<div class="bigstat"><span class="n">6</span><b>1,55 persen penghasilan</b><p>Menurut World Giving Report 2025, orang Indonesia rata-rata menyumbangkan 1,55 persen penghasilannya, di atas rata-rata global 1,04 persen. Dengan metode baru yang juga mengukur nilai sumbangan, Indonesia berada di peringkat 21 dari 101 negara.{5}</p></div>

<h2>Cara membaca angka-angka ini</h2>
<ul>
<li><b>Potensi bukan target.</b> Angka potensi dihitung dengan asumsi tertentu. Gunakan sebagai gambaran ruang tumbuh, bukan sebagai ukuran gagal atau berhasil.</li>
<li><b>Perhatikan catatan di luar neraca.</b> Sebagian besar penghimpunan dicatat di luar laporan keuangan lembaga amil. Mutu pencatatan dan pelaporan menentukan seberapa utuh gambaran yang kita punya.</li>
<li><b>Tumbuh dari dua arah.</b> Dana bisa naik karena muzaki baru, atau karena muzaki lama menunaikan lebih rutin dan lebih besar. Data 2025 mengisyaratkan arah yang kedua.</li>
</ul>
<p class="cta-in">Ingin membaca angka penghimpunan lembaga Anda sendiri? Coba <a href="/analitik/">Kesehatan Penghimpunan</a>.</p>
''',
sumber=[
 ('BAZNAS', 'Laporan Pengelolaan Zakat Nasional Akhir Tahun 2025', '2026', 'https://baznas.go.id/assets/images/szn/LPZN-Tahun-2025.pdf'),
 ('Periskop.id', 'Yusril Dorong Revisi UU Zakat, Baznas Bidik Rp70 Triliun di 2027', '15 Oktober 2026', 'https://periskop.id/regulasi/20261015/yusril-revisi-uu-zakat-baznas-target-70-triliun-2027'),
 ('Fortune Indonesia', 'BWI Ungkap Potensi Wakaf Indonesia Capai Rp400 Triliun', '23 Juli 2025', 'https://www.fortuneidn.com/sharia/bwi-ungkap-potensi-wakaf-indonesia-capai-rp400-triliun-00-4vfn9-gq3hdk'),
 ('NU Online', 'Aset Wakaf di Indonesia Capai 451 Ribu Titik, tapi Banyak yang Belum Dikelola Secara Produktif', '30 Oktober 2025', 'https://nu.or.id/nasional/aset-wakaf-di-indonesia-capai-451-ribu-titik-tapi-banyak-yang-belum-dikelola-secara-produktif-NCaLC'),
 ('CNN Indonesia', 'Daftar 25 Negara Paling Dermawan di Dunia, Indonesia Peringkat Berapa?', '2 Agustus 2025', 'https://www.cnnindonesia.com/internasional/20250802131851-106-1257926/daftar-25-negara-paling-dermawan-di-dunia-indonesia-peringkat-berapa'),
]),

# ---------------------------------------------------------------- ZakaTalk
dict(
slug='zakat-infak-sedekah-wakaf-apa-bedanya', rubrik='talk', tanggal='2026-10-15', menit=4,
judul='Zakat, Infak, Sedekah, dan Wakaf: Apa Bedanya?',
og=['Zakat, infak, sedekah,', 'dan wakaf: apa bedanya?'],
dek='Pertanyaan yang paling sering kami terima. Zaida dan Wafiy menjawabnya satu per satu.',
poin=[
 'Zakat wajib, memiliki nisab dan kadar, serta diberikan kepada delapan golongan penerima.',
 'Infak dan sedekah bersifat sukarela untuk kemaslahatan umum. Sedekah boleh berupa nonharta.',
 'Wakaf menahan pokok harta agar manfaatnya mengalir selamanya atau untuk jangka waktu tertentu.',
],
isi='''
<div class="qa"><p class="q">“Bukankah semuanya sama-sama memberi? Kenapa harus dibedakan?”</p>
<div class="a zaida"><img src="/assets/images/zaida.webp" alt=""><div><b>Zaida</b><p>Betul, semuanya kebaikan. Bedanya ada pada hukum, ketentuan, dan siapa yang berhak menerima. Karena itu lembaga amil wajib mencatatnya terpisah, dan kita pun perlu tahu sedang menunaikan yang mana.</p></div></div></div>

<div class="qa"><p class="q">“Apa itu zakat?”</p>
<div class="a zaida"><img src="/assets/images/zaida.webp" alt=""><div><b>Zaida</b><p>Menurut UU Pengelolaan Zakat, zakat adalah harta yang wajib dikeluarkan oleh seorang muslim atau badan usaha untuk diberikan kepada yang berhak menerimanya sesuai dengan syariat Islam.{1} Kata kuncinya <em>wajib</em>. Zakat punya batas minimal (nisab), kadar tertentu, dan penerimanya delapan golongan sebagaimana disebut dalam QS At-Taubah ayat 60: fakir, miskin, amil, mualaf, hamba sahaya, orang berutang, fisabilillah, dan ibnu sabil.</p></div></div></div>

<div class="qa"><p class="q">“Lalu infak dan sedekah?”</p>
<div class="a zaida"><img src="/assets/images/zaida.webp" alt=""><div><b>Zaida</b><p>Keduanya sukarela. Undang-undang mendefinisikan infak sebagai harta yang dikeluarkan oleh seseorang atau badan usaha di luar zakat untuk kemaslahatan umum. Sedekah adalah harta <em>atau nonharta</em> yang dikeluarkan di luar zakat untuk kemaslahatan umum.{1}{2} Jadi tenaga, ilmu, bahkan senyum bisa menjadi sedekah, sedangkan infak selalu berupa harta. Tidak ada nisab, dan penerimanya lebih luas.</p></div></div></div>

<div class="qa"><p class="q">“Kalau wakaf?”</p>
<div class="a wafiy"><img src="/assets/images/wafiy.webp" alt=""><div><b>Wafiy</b><p>Wakaf itu menahan pokoknya, mengalirkan manfaatnya. UU Wakaf menyebutnya perbuatan hukum wakif untuk memisahkan atau menyerahkan sebagian harta bendanya untuk dimanfaatkan selamanya atau untuk jangka waktu tertentu, guna keperluan ibadah dan/atau kesejahteraan umum menurut syariah.{3} Hartanya dikelola nazhir. Contohnya tanah wakaf yang menjadi <a href="/literasi/rs-mata-achmad-wardi-wakaf-yang-mengembalikan-penglihatan/">rumah sakit mata</a> di Serang. Rumah sakitnya tetap berdiri, manfaatnya dirasakan ribuan pasien.</p></div></div></div>

<div class="qa"><p class="q">“Kalau saya menitipkan semuanya ke satu lembaga, apakah tercampur?”</p>
<div class="a zaida"><img src="/assets/images/zaida.webp" alt=""><div><b>Zaida</b><p>Seharusnya tidak. Undang-undang mengatur bahwa infak, sedekah, dan dana sosial keagamaan lainnya yang diterima BAZNAS dicatat dalam pembukuan tersendiri.{2} Saat menyalurkan, sebutkan peruntukannya dengan jelas: zakat, infak, sedekah, atau wakaf. Simpan bukti setornya.</p></div></div></div>

<h2>Ringkasnya</h2>
<div class="tbl"><table>
<thead><tr><th></th><th>Zakat</th><th>Infak</th><th>Sedekah</th><th>Wakaf</th></tr></thead>
<tbody>
<tr><th>Hukum</th><td>Wajib bila memenuhi syarat</td><td>Sukarela</td><td>Sukarela</td><td>Sukarela</td></tr>
<tr><th>Bentuk</th><td>Harta</td><td>Harta</td><td>Harta atau nonharta</td><td>Harta yang pokoknya ditahan</td></tr>
<tr><th>Ketentuan</th><td>Nisab dan kadar</td><td>Bebas</td><td>Bebas</td><td>Ikrar wakaf, dikelola nazhir</td></tr>
<tr><th>Penerima</th><td>Delapan golongan</td><td>Kemaslahatan umum</td><td>Kemaslahatan umum</td><td>Sesuai peruntukan wakaf</td></tr>
</tbody></table></div>

<div class="ask"><b>Punya pertanyaan untuk ZakaTalk berikutnya?</b><p>Kirimkan ke <a href="mailto:info@zakademia.com?subject=Pertanyaan%20ZakaTalk">info@zakademia.com</a> atau lewat <a href="https://instagram.com/zakademia.official" target="_blank" rel="noopener">Instagram @zakademia.official</a>. Pertanyaan terpilih akan dijawab Zaida dan Wafiy.</p></div>
''',
sumber=[
 ('Peraturan.go.id', 'Undang-Undang Nomor 23 Tahun 2011 tentang Pengelolaan Zakat', '2011', 'https://peraturan.go.id/files/uu23-2011bt.pdf'),
 ('Hukumonline', 'Pengelolaan Zakat, Infak, dan Sedekah oleh BAZNAS', '—', 'https://www.hukumonline.com/klinik/a/pengelolaan-zakat--infak-dan-sedekah-oleh-baznas-lt5ce23056410bd/'),
 ('JDIH Kementerian Keuangan', 'Undang-Undang Nomor 41 Tahun 2004 tentang Wakaf', '2004', 'https://jdih.kemenkeu.go.id/fulltext/2004/41TAHUN2004UU.htm'),
]),
# ---------------------------------------------------------------- Seri 22–29 Oktober 2026
dict(
slug='zakat-emas-nisab-85-gram', rubrik='pedia', tanggal='2026-10-21', menit=3,
judul='Zakat Emas: Nisab 85 Gram, Kadar 2,5 Persen',
og=['Punya emas 85 gram?', 'Sudah wajib zakat.'],
dek='Emas yang disimpan juga ada zakatnya. Ketentuannya dihitung dalam gram, sehingga nilai rupiahnya ikut naik turun bersama harga emas.',
poin=[
 'Zakat emas wajib bila kepemilikan mencapai nisab 85 gram dan sudah dimiliki satu tahun (haul).',
 'Kadarnya 2,5 persen dari emas yang dimiliki.',
 'Nisab perak 595 gram dengan kadar yang sama, 2,5 persen.',
],
isi="""
<h2>Ketentuannya</h2>
<p>Peraturan Menteri Agama Nomor 52 Tahun 2014 menetapkan bahwa zakat emas wajib atas kepemilikan emas yang telah mencapai nisab 85 gram, dengan kadar 2,5 persen.{1} Zakat ditunaikan setelah emas dimiliki selama satu tahun (haul), yaitu 12 bulan qamariah, melalui amil zakat resmi.{1}</p>
<p>Untuk perak, nisabnya 595 gram dengan kadar yang sama, 2,5 persen.{1}</p>

<h2>Contoh hitungan</h2>
<p>Seseorang menyimpan emas 100 gram selama setahun penuh. Karena melebihi 85 gram, zakatnya 2,5 persen × 100 gram = <b>2,5 gram emas</b>, atau senilai harga 2,5 gram emas pada saat zakat ditunaikan.</p>
<p>Karena nisab dihitung dalam gram, nilainya dalam rupiah tidak tetap. Bila harga emas naik, nilai rupiah nisab ikut naik.</p>

<h2>Bagaimana dengan perhiasan?</h2>
<p>Para ulama berbeda pendapat tentang emas yang dipakai sehari-hari sebagai perhiasan. Bila ragu, tanyakan kepada lembaga amil zakat tempat Anda menunaikan zakat, dan sebutkan apakah emas tersebut disimpan atau dipakai.</p>
<p class="cta-in">Punya penghasilan rutin juga? Baca <a href="/literasi/cara-menghitung-zakat-penghasilan-2026/">cara menghitung zakat penghasilan 2026</a>.</p>
""",
sumber=[
 ('Peraturan.go.id', 'Peraturan Menteri Agama Nomor 52 Tahun 2014 tentang Syarat dan Tata Cara Penghitungan Zakat Mal dan Zakat Fitrah', '2014', 'https://peraturan.go.id/files/bn1830-2014.pdf'),
]),

dict(
slug='zakat-tabungan-dan-deposito', rubrik='tips', tanggal='2026-10-21', menit=3,
judul='Zakat Tabungan: Kapan Saldo Mengendap Wajib Dizakati?',
og=['Saldo mengendap setahun?', 'Cek nisabnya.'],
dek='Uang yang tersimpan di tabungan atau deposito termasuk harta yang dizakati bila nilainya mencapai nisab dan sudah tersimpan setahun.',
poin=[
 'Zakat atas uang dan surat berharga berlaku bila nilainya setara 85 gram emas.',
 'Uang tersebut sudah dimiliki selama satu tahun (haul).',
 'Kadarnya 2,5 persen.',
],
isi="""
<h2>Syaratnya</h2>
<p>Dalam PMA Nomor 52 Tahun 2014, uang dan surat berharga dikenai zakat bila nilainya mencapai nisab senilai 85 gram emas, dengan kadar 2,5 persen. Zakat ditunaikan setelah mencapai haul, melalui amil zakat resmi.{1}</p>
<p>Tabungan, deposito, dan giro adalah bentuk penyimpanan uang. Yang dihitung adalah saldo yang memang tersimpan, bukan uang yang keluar masuk untuk kebutuhan bulanan.</p>

<h2>Contoh hitungan</h2>
<ul>
<li>Saldo tabungan Rp300 juta tersimpan selama setahun, dan nilainya di atas nisab 85 gram emas.</li>
<li>Zakatnya: 2,5% × Rp300.000.000 = <b>Rp7.500.000</b>.</li>
</ul>
<p>Karena nisab dihitung dari harga emas, cek dulu harga emas saat zakat akan ditunaikan untuk memastikan saldo Anda sudah melewati nisab.</p>

<h2>Tips praktis</h2>
<ul>
<li><b>Tetapkan satu tanggal haul</b>, misalnya setiap awal Ramadan, lalu hitung saldo pada tanggal itu setiap tahun.</li>
<li><b>Pisahkan dana kebutuhan rutin</b> dari tabungan jangka panjang agar hitungannya lebih jelas.</li>
<li><b>Simpan bukti setor</b> zakat dari lembaga resmi.</li>
</ul>
""",
sumber=[
 ('Peraturan.go.id', 'Peraturan Menteri Agama Nomor 52 Tahun 2014 tentang Syarat dan Tata Cara Penghitungan Zakat Mal dan Zakat Fitrah', '2014', 'https://peraturan.go.id/files/bn1830-2014.pdf'),
]),

dict(
slug='cara-menghitung-zakat-perdagangan', rubrik='tips', tanggal='2026-10-21', menit=4,
judul='Cara Menghitung Zakat Usaha: dari Aset Lancar, Bukan Omzet',
og=['Zakat usaha dihitung', 'dari aset lancar.'],
dek='Pemilik usaha sering mengira zakat dihitung dari omzet. Aturannya memakai aset lancar dikurangi kewajiban jangka pendek.',
poin=[
 'Rumus zakat perniagaan: (aset lancar − kewajiban jangka pendek) × 2,5%.',
 'Wajib bila hasilnya mencapai nisab senilai 85 gram emas.',
 'Ditunaikan setelah usaha berjalan satu tahun (haul).',
],
isi="""
<h2>Rumusnya</h2>
<p>PMA Nomor 52 Tahun 2014 mengatur bahwa harta perniagaan dihitung dari aktiva lancar dikurangi kewajiban jangka pendek pada saat haul. Bila selisihnya mencapai nisab senilai 85 gram emas, zakat wajib dibayar dengan kadar 2,5 persen.{1}</p>
<p class="rumus">Zakat = (aset lancar − utang jangka pendek) × 2,5%</p>

<h2>Apa saja aset lancar?</h2>
<ul>
<li><b>Kas dan saldo bank</b> milik usaha.</li>
<li><b>Stok barang dagangan</b>, dinilai dengan harga saat ini.</li>
<li><b>Piutang yang lancar</b>, yaitu yang masih dapat ditagih.</li>
</ul>
<p>Aset tetap seperti bangunan, kendaraan operasional, dan mesin tidak dimasukkan, karena tidak untuk dijual.</p>

<h2>Contoh hitungan</h2>
<ul>
<li>Aset lancar: Rp500 juta</li>
<li>Utang jangka pendek: Rp100 juta</li>
<li>Selisih Rp400 juta, di atas nisab.</li>
<li>Zakat: 2,5% × Rp400.000.000 = <b>Rp10.000.000</b></li>
</ul>
<p class="cta-in">Untuk penghasilan pribadi pemilik usaha, lihat <a href="/literasi/cara-menghitung-zakat-penghasilan-2026/">zakat penghasilan 2026</a>.</p>
""",
sumber=[
 ('Peraturan.go.id', 'Peraturan Menteri Agama Nomor 52 Tahun 2014 tentang Syarat dan Tata Cara Penghitungan Zakat Mal dan Zakat Fitrah', '2014', 'https://peraturan.go.id/files/bn1830-2014.pdf'),
]),

dict(
slug='fidyah-2026-besaran-dan-ketentuan', rubrik='pedia', tanggal='2026-10-21', menit=3,
judul='Fidyah 2026: Siapa yang Menunaikan dan Berapa Besarnya?',
og=['Tak mampu puasa?', 'Tunaikan fidyah.'],
dek='Bagi yang tidak mampu berpuasa dan tidak dapat menggantinya, ada fidyah. BAZNAS RI menetapkan acuan Rp65.000 per jiwa per hari untuk 2026.',
poin=[
 'Fidyah ditunaikan oleh orang yang tidak mampu berpuasa, seperti lansia dan orang sakit yang tidak diharapkan sembuh.',
 'Acuan BAZNAS RI 2026: Rp65.000 per jiwa per hari.',
 'BAZNAS daerah dan LAZ dapat memakai besaran berbeda sesuai harga pangan setempat.',
],
isi="""
<h2>Siapa yang menunaikan fidyah</h2>
<p>Dasar fidyah adalah QS Al-Baqarah ayat 184: bagi orang yang berat menjalankan puasa, wajib membayar fidyah, yaitu memberi makan seorang miskin.{1} Dalam praktik, fidyah ditunaikan antara lain oleh lansia yang tidak lagi kuat berpuasa dan orang sakit yang tidak diharapkan sembuh.</p>

<h2>Berapa besarnya</h2>
<p>Melalui Keputusan Ketua BAZNAS RI Nomor 14 Tahun 2026, fidyah ditetapkan sebesar <b>Rp65.000 per jiwa per hari</b>.{2}{3} Besaran ini berlaku untuk pembayaran melalui BAZNAS RI. BAZNAS provinsi, kabupaten/kota, dan LAZ dapat menjadikannya acuan, atau menetapkan besaran sendiri bila harga pangan di daerahnya berbeda.{2}</p>

<h2>Contoh hitungan</h2>
<ul>
<li>Utang puasa 10 hari: 10 × Rp65.000 = <b>Rp650.000</b>.</li>
<li>Utang puasa 30 hari: 30 × Rp65.000 = <b>Rp1.950.000</b>.</li>
</ul>
<p>Saat menyalurkan, sebutkan bahwa dana tersebut adalah fidyah, bukan zakat atau infak, agar dicatat dan disalurkan sesuai peruntukannya.</p>
""",
sumber=[
 ('NU Online', 'QS Al-Baqarah ayat 184', '—', 'https://quran.nu.or.id/al-baqarah/184'),
 ('Jawa Pos', 'BAZNAS Tetapkan Zakat Fitrah 2026 Sebesar Rp50.000 dan Fidyah Rp65.000 per Jiwa', '10 Februari 2026', 'https://www.jawapos.com/nasional/2602100086/baznas-tetapkan-zakat-fitrah-2026-sebesar-rp-50000-dan-fidyah-rp-65000-per-jiwa'),
 ('Metro TV', 'BAZNAS RI Tetapkan Zakat Fitrah dan Fidyah 2026, Segini Besarannya', '2026', 'https://www.metrotvnews.com/read/k8oCQ9Rz-baznas-ri-tetapkan-zakat-fitrah-dan-fidyah-2026-segini-besarannya'),
]),

dict(
slug='delapan-asnaf-penerima-zakat', rubrik='pedia', tanggal='2026-10-21', menit=4,
judul='Delapan Asnaf: Siapa Saja yang Berhak Menerima Zakat?',
og=['Siapa saja', 'penerima zakat?'],
dek='Zakat tidak boleh disalurkan ke sembarang orang. Al-Qur’an menyebut delapan golongan yang berhak menerimanya.',
poin=[
 'Delapan golongan penerima zakat disebut dalam QS At-Taubah ayat 60.',
 'UU Pengelolaan Zakat mewajibkan zakat didistribusikan kepada mustahik sesuai syariat Islam.',
 'Lembaga amil menyalurkan zakat berdasarkan skala prioritas.',
],
isi="""
<h2>Dasar hukumnya</h2>
<p>QS At-Taubah ayat 60 menyebut delapan golongan (asnaf) yang berhak menerima zakat.{1} UU Nomor 23 Tahun 2011 menegaskan bahwa zakat wajib didistribusikan kepada mustahik sesuai dengan syariat Islam, berdasarkan skala prioritas dengan memperhatikan prinsip pemerataan, keadilan, dan kewilayahan.{2}</p>

<h2>Delapan golongan itu</h2>
<div class="tbl"><table>
<thead><tr><th>Golongan</th><th>Secara umum dipahami sebagai</th></tr></thead>
<tbody>
<tr><th>1. Fakir</th><td>Orang yang tidak memiliki harta dan penghasilan yang mencukupi kebutuhan dasarnya</td></tr>
<tr><th>2. Miskin</th><td>Orang yang memiliki penghasilan, tetapi belum mencukupi kebutuhannya</td></tr>
<tr><th>3. Amil</th><td>Pihak yang mengelola zakat: menghimpun, mencatat, dan menyalurkan</td></tr>
<tr><th>4. Mualaf</th><td>Orang yang hatinya perlu dikuatkan dalam Islam, termasuk yang baru memeluk Islam</td></tr>
<tr><th>5. Riqab</th><td>Untuk memerdekakan orang dari perbudakan</td></tr>
<tr><th>6. Gharimin</th><td>Orang yang terlilit utang untuk kebutuhan yang dibolehkan dan tidak mampu melunasinya</td></tr>
<tr><th>7. Fisabilillah</th><td>Untuk kepentingan di jalan Allah</td></tr>
<tr><th>8. Ibnu sabil</th><td>Musafir yang kehabisan bekal dalam perjalanan</td></tr>
</tbody></table></div>
<p>Rincian kriteria setiap golongan dibahas panjang dalam kitab fikih dan dapat berbeda antarmazhab. Lembaga amil biasanya menetapkan kriteria operasional sebelum menyalurkan.</p>

<h2>Mengapa ini penting bagi muzaki</h2>
<p>Menunaikan zakat melalui lembaga resmi membantu memastikan dana sampai ke golongan yang berhak. Tanyakan laporan penyaluran kepada lembaga tempat Anda berzakat.</p>
""",
sumber=[
 ('NU Online', 'QS At-Taubah ayat 60', '—', 'https://quran.nu.or.id/at-taubah/60'),
 ('Peraturan.go.id', 'Undang-Undang Nomor 23 Tahun 2011 tentang Pengelolaan Zakat', '2011', 'https://peraturan.go.id/files/uu23-2011bt.pdf'),
]),

dict(
slug='zakat-pertanian-653-kg-gabah', rubrik='tips', tanggal='2026-10-21', menit=3,
judul='Zakat Pertanian: Nisab 653 Kg Gabah, Ditunaikan Saat Panen',
og=['Panen mencapai', '653 kg gabah?'],
dek='Berbeda dari zakat harta lain, zakat pertanian tidak menunggu setahun. Kadarnya tergantung cara pengairan.',
poin=[
 'Nisab zakat pertanian, perkebunan, dan kehutanan adalah 653 kg gabah.',
 'Kadarnya 10 persen bila tadah hujan, atau 5 persen bila memakai irigasi dan perawatan lainnya.',
 'Zakat ditunaikan saat panen.',
],
isi="""
<h2>Ketentuannya</h2>
<p>PMA Nomor 52 Tahun 2014 menetapkan nisab zakat pertanian, perkebunan, dan kehutanan senilai 653 kg gabah. Kadarnya 10 persen bila tadah hujan, atau 5 persen bila menggunakan irigasi dan perawatan lainnya.{1} Zakat ini ditunaikan pada saat panen.{1}</p>

<h2>Contoh hitungan</h2>
<ul>
<li>Panen 2.000 kg gabah dari sawah beririgasi: 5% × 2.000 kg = <b>100 kg gabah</b>.</li>
<li>Panen 2.000 kg gabah dari sawah tadah hujan: 10% × 2.000 kg = <b>200 kg gabah</b>.</li>
<li>Panen 600 kg gabah: di bawah nisab, belum wajib zakat. Tetap dapat bersedekah.</li>
</ul>

<h2>Mengapa kadarnya berbeda?</h2>
<p>Sawah beririgasi dan dirawat membutuhkan biaya tambahan dari petani, sedangkan sawah tadah hujan diairi oleh hujan. Karena itu kadarnya dibedakan.</p>
""",
sumber=[
 ('Peraturan.go.id', 'Peraturan Menteri Agama Nomor 52 Tahun 2014 tentang Syarat dan Tata Cara Penghitungan Zakat Mal dan Zakat Fitrah', '2014', 'https://peraturan.go.id/files/bn1830-2014.pdf'),
]),

dict(
slug='zakat-pengurang-penghasilan-kena-pajak', rubrik='tips', tanggal='2026-10-21', menit=4,
judul='Zakat sebagai Pengurang Penghasilan Kena Pajak: Syarat Terbaru',
og=['Zakat mengurangi', 'penghasilan kena pajak.'],
dek='Zakat yang dibayar ke lembaga resmi dapat dikurangkan dari penghasilan bruto. PMK 114/2025 memperjelas syarat dan isi bukti bayarnya.',
poin=[
 'Zakat yang dibayar ke BAZNAS atau LAZ resmi dikurangkan dari penghasilan kena pajak.',
 'PMK 114/2025 mensyaratkan bukti pembayaran yang sah, penerima ber-NPWP, dan tidak menimbulkan rugi fiskal.',
 'Yang berkurang adalah dasar penghitungan pajak, bukan tagihan pajak secara langsung.',
],
isi="""
<h2>Dasar hukumnya</h2>
<p>UU Nomor 23 Tahun 2011 menyatakan bahwa zakat yang dibayarkan muzaki kepada BAZNAS atau LAZ dikurangkan dari penghasilan kena pajak, dan lembaga wajib memberikan bukti setoran zakat kepada setiap muzaki.{1} Ketentuan teknisnya diatur dalam PP Nomor 60 Tahun 2010{2} dan diperbarui melalui PMK Nomor 114 Tahun 2025.{3}</p>

<h2>Tiga syarat menurut PMK 114/2025</h2>
<ol>
<li><b>Tidak menyebabkan rugi fiskal</b> pada tahun pajak zakat itu dibayarkan.</li>
<li><b>Didukung bukti pembayaran yang sah.</b></li>
<li><b>Diterima oleh badan atau lembaga amil zakat yang memiliki NPWP.</b></li>
</ol>
<p>Ketiga syarat ini berlaku bersamaan.{3}{4}</p>

<h2>Isi bukti pembayaran yang sah</h2>
<ul>
<li>Nomor dan tanggal bukti pembayaran.</li>
<li>Nama lengkap dan NPWP atau NIK pembayar.</li>
<li>Jumlah zakat yang dibayar.</li>
<li>Nama dan NPWP lembaga penerima.</li>
<li>Tanda tangan petugas atau validasi pembayaran.</li>
</ul>
<p>Saat lapor SPT Tahunan orang pribadi, nilai zakat diisi pada bagian pengurang penghasilan neto, dan salinan bukti pembayarannya dilampirkan.{5}</p>

<h2>Yang perlu dipahami</h2>
<p>Zakat Rp3 juta tidak memotong tagihan pajak sebesar Rp3 juta. Zakat mengurangi penghasilan yang menjadi dasar penghitungan pajak, sehingga pajak yang terutang ikut berkurang sesuai tarif yang berlaku.</p>
""",
sumber=[
 ('Peraturan.go.id', 'Undang-Undang Nomor 23 Tahun 2011 tentang Pengelolaan Zakat', '2011', 'https://peraturan.go.id/files/uu23-2011bt.pdf'),
 ('Peraturan.go.id', 'Peraturan Pemerintah Nomor 60 Tahun 2010 tentang Zakat atau Sumbangan Keagamaan yang Sifatnya Wajib yang Dapat Dikurangkan dari Penghasilan Bruto', '2010', 'https://peraturan.go.id/files/pp60-2010bt.pdf'),
 ('JDIH Kementerian Keuangan', 'Peraturan Menteri Keuangan Nomor 114 Tahun 2025', '2025', 'https://jdih.kemenkeu.go.id/api/download/956c4a77-a4bb-4c97-a4a9-989bc6a98c79/2025pmkeuangan114.pdf'),
 ('DDTCNews', 'Ada Perombakan Ketentuan Zakat, Perhatikan Kembali 3 Syarat Ini', '9 Januari 2026', 'https://news.ddtc.co.id/review/konsultasi/1816488/ada-perombakan-ketentuan-zakat-perhatikan-kembali-3-syarat-ini'),
 ('DDTCNews', 'Kurangkan Zakat dari Penghasilan Bruto, Ada Kriteria Bukti Bayarnya', '10 Maret 2026', 'https://news.ddtc.co.id/berita/nasional/1817876/kurangkan-zakat-dari-penghasilan-bruto-ada-kriteria-bukti-bayarnya'),
]),

dict(
slug='tugas-nazhir-wakaf', rubrik='pedia', tanggal='2026-10-21', menit=3,
judul='Apa Tugas Nazhir Wakaf?',
og=['Apa tugas', 'nazhir wakaf?'],
dek='Nazhir adalah pihak yang menerima dan mengelola harta wakaf. Undang-undang memberinya empat tugas utama.',
poin=[
 'Nazhir bertugas mengadministrasikan, mengelola dan mengembangkan, serta mengawasi dan melindungi harta wakaf.',
 'Nazhir wajib melaporkan pelaksanaan tugasnya kepada Badan Wakaf Indonesia.',
 'Imbalan nazhir paling banyak 10 persen dari hasil bersih pengelolaan wakaf.',
],
isi="""
<h2>Empat tugas nazhir</h2>
<p>Menurut UU Nomor 41 Tahun 2004 Pasal 11, nazhir bertugas:{1}</p>
<ol>
<li>Melakukan pengadministrasian harta benda wakaf.</li>
<li>Mengelola dan mengembangkan harta benda wakaf sesuai dengan tujuan, fungsi, dan peruntukannya.</li>
<li>Mengawasi dan melindungi harta benda wakaf.</li>
<li>Melaporkan pelaksanaan tugas kepada Badan Wakaf Indonesia.</li>
</ol>

<h2>Imbalan nazhir</h2>
<p>Dalam melaksanakan tugasnya, nazhir dapat menerima imbalan dari hasil bersih pengelolaan dan pengembangan harta benda wakaf, yang besarnya tidak melebihi 10 persen.{1}</p>

<h2>Pertanyaan sebelum berwakaf</h2>
<ul>
<li>Siapa nazhirnya, dan apakah terdaftar?</li>
<li>Untuk apa harta wakaf ini akan dikelola?</li>
<li>Bagaimana laporan pengelolaan dan manfaatnya disampaikan kepada wakif?</li>
</ul>
<p class="cta-in">Baca juga perbedaan <a href="/literasi/wakaf-uang-wakaf-melalui-uang-cwls/">wakaf uang, wakaf melalui uang, dan CWLS</a>.</p>
""",
sumber=[
 ('JDIH Kementerian Keuangan', 'Undang-Undang Nomor 41 Tahun 2004 tentang Wakaf', '2004', 'https://jdih.kemenkeu.go.id/fulltext/2004/41TAHUN2004UU.htm'),
]),

dict(
slug='retensi-donatur-lembaga-zakat', rubrik='talk', tanggal='2026-10-21', menit=4,
judul='Donatur Lama Jangan Dilupakan: Tiga Pertanyaan tentang Retensi',
og=['Donatur lama', 'jangan dilupakan.'],
dek='Untuk pengelola lembaga zakat dan wakaf: sebelum mencari donatur baru, cek dulu berapa banyak donatur lama yang kembali.',
poin=[
 'Retensi donatur adalah porsi donatur tahun lalu yang kembali berdonasi tahun ini.',
 'Tiga hal yang perlu dicek: angka retensi, frekuensi sapaan di luar permintaan donasi, dan laporan dampak.',
 'Retensi dapat dihitung dari angka rekap tahunan, tanpa mengunggah data donatur.',
],
isi="""
<h2>Apa itu retensi donatur</h2>
<p>Retensi donatur adalah persentase donatur tahun lalu yang kembali berdonasi tahun ini. Cara menghitungnya sederhana:</p>
<p class="rumus">Retensi = donatur tahun lalu yang berdonasi lagi tahun ini ÷ seluruh donatur tahun lalu</p>
<p>Contoh: tahun lalu ada 1.000 donatur, dan 400 di antaranya berdonasi lagi tahun ini. Retensinya 40 persen.</p>

<h2>Tiga pertanyaan untuk minggu ini</h2>
<ol>
<li><b>Berapa persen donatur tahun lalu yang kembali berdonasi?</b> Bila belum tahu, itu temuan pertama: data donatur belum dicatat per orang.</li>
<li><b>Kapan terakhir mereka dihubungi selain saat diminta donasi?</b> Ucapan terima kasih, kabar program, atau sapaan hari besar membuat donatur merasa dikenal.</li>
<li><b>Sudahkah mereka menerima laporan dampak?</b> Donatur yang tahu ke mana donasinya pergi punya alasan untuk kembali.</li>
</ol>

<h2>Mengapa retensi penting</h2>
<p>Mendapatkan donatur baru butuh biaya kampanye dan waktu. Donatur lama sudah mengenal lembaga. Menjaga mereka membantu penghimpunan tumbuh lebih stabil, dan membuat biaya per rupiah terhimpun lebih terkendali.</p>
<p class="cta-in">Hitung retensi, CLV, dan CAC lembaga Anda secara gratis di <a href="/analitik/">Kesehatan Penghimpunan</a>. Cukup isi angka rekap tahunan.</p>
""",
sumber=[
 ('Zakademia', 'Kesehatan Penghimpunan: retensi, CLV, CAC, dan biaya per rupiah terhimpun', '—', 'https://zakademia.com/analitik/'),
]),
]
