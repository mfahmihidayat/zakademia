<?php
declare(strict_types=1);
/**
 * Ringkasan per bab buku Metodologi Penelitian Filantropi Islam untuk halaman /buku/metodologi/bab-N/.
 * Disusun dari Inti Bab, Konsep Kunci, dan daftar isi buku; bukan salinan teks buku.
 * Bagian: I Landasan dan Metode Dasar (Bab 1–3), II Metode Kualitatif, Historis, dan Terapan (Bab 4–7), III Metode Digital dan Lanjutan (Bab 8–10).
 */
const BK_ISI = [
    'metodologi' => [
        'prakata' => [
            'Penulis sering ditanya kolega yang sedang menempuh studi filantropi Islam tentang metode apa yang paling tepat untuk penelitian mereka. Pertanyaan itu biasanya muncul setelah lama bingung menentukan topik, dan metode kerap dipilih karena paling mudah dikerjakan. Akibatnya muncul kemalasan metodologis, yaitu meniru desain skripsi terdahulu tanpa memeriksa kecocokannya; kebiasaan hanya mengutip data yang mendukung; dan pemangkasan langkah yang dianggap merepotkan, seperti uji coba kuesioner atau pemeriksaan sumber primer. Di balik ketiganya sering ada sikap apologetik: kesimpulan sudah ditetapkan sebelum data dikumpulkan, sehingga penelitian berubah dari memeriksa menjadi membela.',
            'Jalan keluarnya bukan mencari satu metode yang paling ilmiah, tetapi bersikap eklektik, yaitu memilih dan memadukan metode secara sengaja berdasarkan pertanyaan yang hendak dijawab. Sah atau tidaknya suatu praktik dijawab dengan dalil, sedangkan berhasil atau tidaknya dijawab dengan data. Kekhasan penelitian zakat dan wakaf terletak pada dua sumber pengetahuan yang dipakai sekaligus, yaitu nas syariat dan data empiris tentang praktik di lapangan, dan keduanya tidak perlu dipertentangkan.',
            'Buku ini membawa sikap eklektik ke arah integrasi, yaitu menjadikan temuan satu metode sebagai bahan untuk menjelaskan atau menguji temuan metode lain. Ia tidak menggantikan literatur metodologi yang ada, tetapi memilih dan menyesuaikannya untuk penelitian filantropi Islam, dengan zakat dan wakaf sebagai dua pranata utamanya, pada tiga lapisan: normatif, empiris, dan praktik.',
        ],
        'bagian' => [1 => 'Bagian I · Landasan dan Metode Dasar', 2 => 'Bagian II · Metode Kualitatif, Historis, dan Terapan', 3 => 'Bagian III · Metode Digital dan Lanjutan'],
        'bab' => [
            [
                'judul' => 'Pengantar Penelitian Filantropi Islam', 'bagian' => 1,
                'ringkas' => 'Apa itu penelitian filantropi Islam, bagaimana peta penelitian zakat dan wakaf di Indonesia, dan bagaimana memilih metode dari pertanyaan penelitian. Bab 1 buku Metodologi Penelitian Filantropi Islam.',
                'isi' => [
                    'Bab pembuka ini menempatkan penelitian sebagai upaya sistematis menjawab pertanyaan melalui prosedur yang dapat dipertanggungjawabkan dan diuji ulang. Prosesnya bergerak dari menemukan masalah, mengkaji pustaka, memilih metode, mengumpulkan dan menganalisis data, hingga melaporkan temuan; dan karena temuan sering memunculkan pertanyaan baru, penelitian bersifat siklis. Berdasarkan tujuannya penelitian dibedakan menjadi penelitian dasar dan terapan, sedangkan berdasarkan pendekatannya penelitian zakat dan wakaf dapat bersifat normatif, kuantitatif, kualitatif, atau campuran.',
                    'Bagian kedua memetakan ekosistem zakat dan wakaf Indonesia: pelaku (muzaki, mustahik, amil, wakif, nazhir, dan mauquf ‘alaih), kelembagaan (BAZNAS, LAZ, UPZ, BWI, Kementerian Agama, dan LKS-PWU), serta landasan hukum UU 23/2011 dan UU 41/2004. Penelitian di bidang ini tersebar dalam enam bidang kajian, yaitu fikih dan hukum, ekonomi, tata kelola, akuntansi, perilaku, dan teknologi. Analisis SWOT atas metodologi penelitian zakat dan wakaf menunjukkan landasan normatif yang kuat dan data digital sebagai modal, sementara dominasi riset deskriptif, keterbatasan data, dan pengulangan topik masih perlu dibenahi.',
                    'Bab ini juga menjadi peta jalan buku. Pemilihan metode dimulai dari pertanyaan penelitian, bukan dari metode yang ingin dipakai; sifat pertanyaan menentukan bab mana yang perlu dibaca, dari Bab 2 tentang metode normatif-syariah hingga Bab 9 tentang metode lanjutan. Kata tanya dalam rumusan masalah tidak selalu mencerminkan sifat pertanyaan, sehingga yang perlu diperiksa adalah jenis hasil yang ingin diperoleh.',
                ],
                'subbab' => [
                    ['1-1', 'Memahami Penelitian', ['Proses Penelitian', 'Jenis-Jenis Penelitian']],
                    ['1-2', 'Peta Penelitian Zakat dan Wakaf', ['Ekosistem Zakat dan Wakaf Indonesia', 'Bidang Kajian Penelitian Zakat dan Wakaf', 'Analisis SWOT Metodologi Penelitian Zakat dan Wakaf']],
                    ['1-3', 'Cara Menggunakan Buku Ini', []],
                ],
                'konsep' => ['filantropi Islam', 'proses penelitian', 'pertanyaan penelitian', 'penelitian dasar', 'penelitian terapan', 'pendekatan normatif', 'pendekatan kuantitatif', 'pendekatan kualitatif', 'pendekatan campuran', 'muzaki', 'mustahik', 'amil', 'wakif', 'nazhir', 'mauquf ‘alaih', 'nisab', 'zakat produktif', 'zakat konsumtif', 'wakaf uang', 'istibdal', 'istitsmar', 'Cash Waqf Linked Sukuk', 'analisis SWOT', 'off balance sheet', 'tabayyun'],
                'tanya' => ['Apa perbedaan penelitian dasar dan penelitian terapan, dan mengapa model CIBEST dapat disebut penelitian dasar yang diilhami kebutuhan penerapan?', 'Mengapa angka pengumpulan ZIS-DSKL nasional dan angka on balance sheet memberi gambaran yang berbeda tentang perkembangan zakat di Indonesia?', 'Rumusan “Bagaimana pengaruh literasi wakaf terhadap minat berwakaf uang?” diawali kata bagaimana; metode apa yang sebenarnya sesuai?'],
            ],
            [
                'judul' => 'Metode Normatif-Syariah', 'bagian' => 1,
                'ringkas' => 'Cara meneliti apa yang seharusnya menurut syariah dalam zakat dan wakaf: analisis prinsip Qur’ani, analisis fikih, maqasid al-shari‘ah, dan istinbat hukum atas instrumen baru seperti wakaf uang dan zakat aset digital.',
                'isi' => [
                    'Penelitian normatif menjawab pertanyaan tentang apa yang seharusnya menurut syariah dengan menelusuri dan menalar sumber hukum: Al-Qur’an, hadis, pendapat fuqaha, fatwa, dan peraturan perundang-undangan. Bab ini memulai dari metodologi standar penelitian ekonomi dan keuangan Islam, lalu menguraikan tiga jalan analisis. Analisis prinsip Qur’ani menggali nilai dasar melalui tafsir tematik (maudhu‘i); analisis fikih membandingkan pendapat mazhab, menelaah fatwa, dan menelusuri penyerapannya ke dalam UU 23/2011 dan UU 41/2004; analisis maqasid al-shari‘ah menilai apakah ketentuan dan praktik zakat dan wakaf mencapai tujuan syariah, sekaligus menjadi dasar penyusunan indikator kesejahteraan.',
                    'Pergeseran kekayaan ke aset finansial dan digital menuntut istinbat hukum atas instrumen baru, seperti wakaf uang, wakaf saham, Cash Waqf Linked Sukuk, zakat saham, dan zakat aset kripto. Bab ini menyajikan langkah istinbat muamalah: memahami objek, menetapkan takyif fiqhi, menalar dengan qiyas, maslahah mursalah, istihsan, atau sadd al-dhari‘ah, lalu menguji hasilnya dengan maqasid.',
                    'Bagian akhir menunjukkan bahwa temuan normatif dapat dipadukan dengan teknik penelitian arus utama, baik dengan menjadikan fikih sebagai dasar definisi operasional dalam survei maupun dengan menyusun indeks berbasis maqasid yang diuji secara empiris, misalnya dengan Analytic Hierarchy Process.',
                ],
                'subbab' => [
                    ['2-1', 'Desain Penelitian Normatif', ['Metodologi Standar Penelitian Ekonomi dan Keuangan Islam', 'Analisis Prinsip-Prinsip Qur’ani', 'Analisis Fikih', 'Analisis Maqasid al-Shari‘ah']],
                    ['2-2', 'Pendekatan Normatif Lanjutan', ['Istinbat Hukum Muamalah', 'Harmonisasi Teknik Arus Utama dengan Metodologi Maqasid']],
                ],
                'konsep' => ['penelitian kepustakaan', 'fatwa', 'tafsir maudhu‘i', 'mazhab', 'al-mal al-mustafad', 'haul', '‘urud al-tijarah', 'maqasid al-shari‘ah', 'daruriyyat, hajiyyat, tahsiniyyat', 'indikator', 'istinbat', 'takyif fiqhi', 'qiyas', 'maslahah mursalah', 'istihsan', 'sadd al-dhari‘ah', 'ijtihad jama‘i', 'had kifayah', 'wakaf berjangka', 'ta’bid', 'takhayyur', 'taqnin', 'Analytic Hierarchy Process'],
                'tanya' => ['Apa perbedaan analisis prinsip Qur’ani, analisis fikih, dan analisis maqasid al-shari‘ah bila diterapkan pada persoalan zakat penghasilan?', 'Mengapa hakikat suatu objek harus ditetapkan lebih dahulu sebelum menetapkan hukum zakatnya, misalnya pada aset kripto?', 'Bagaimana fikih berperan sebagai sumber definisi operasional dalam penelitian kepatuhan zakat?'],
            ],
            [
                'judul' => 'Metode Kuantitatif', 'bagian' => 1,
                'ringkas' => 'Desain deskriptif, korelasional, survei, longitudinal, eksperimen, dan meta-analisis; variabel, hipotesis, kuesioner, dan analisis data dengan SPSS dalam penelitian zakat dan wakaf.',
                'isi' => [
                    'Desain penelitian menghubungkan pertanyaan dengan data dan cara analisisnya, sehingga pilihannya ditentukan oleh bentuk pertanyaan, bukan kebiasaan. Desain deskriptif dan korelasional menjawab seperti apa keadaan dan apakah dua hal berhubungan; survei potong lintang, studi longitudinal, dan studi tren membedakan dimensi waktu pengamatan; penelitian ex post facto menelusuri sebab dari peristiwa yang telah terjadi; dan desain eksperimen, dari pra-eksperimen hingga kuasi-eksperimen dan uji acak, diperlukan untuk menilai apakah suatu program zakat atau wakaf menyebabkan perubahan. Meta-analisis menggabungkan hasil banyak penelitian menjadi kesimpulan yang lebih kokoh.',
                    'Penelitian kuantitatif menerjemahkan konsep abstrak seperti literasi zakat, kepercayaan kepada nazhir, atau kesejahteraan mustahik menjadi variabel yang terukur, lalu merumuskan hubungan antarvariabel sebagai hipotesis. Kualitas temuan bergantung pada definisi operasional, skala pengukuran, serta alat ukur yang valid dan reliabel. Klaim sebab-akibat menuntut lebih dari korelasi: urutan waktu yang jelas, penyingkiran penjelasan alternatif, dan perbandingan dengan kontrafaktual. Kuesioner, alat pengumpul data yang paling banyak dipakai dalam kajian zakat dan wakaf, perlu disusun dengan bahasa yang dipahami mustahik, menangani pertanyaan sensitif seperti pendapatan dengan hati-hati, dan diuji coba sebelum pengumpulan data utama.',
                    'Analisis dimulai dari pengolahan data yang teliti di SPSS, yaitu buku kode, pembersihan, transformasi variabel, dan statistik deskriptif yang memilih ukuran sesuai skala data. Uji statistik dipilih berdasarkan tujuan analisis, skala variabel, dan struktur kelompok; regresi memperkirakan pengaruh beberapa variabel sekaligus, baik pada tingkat individu seperti minat berwakaf uang maupun tingkat daerah seperti penghimpunan ZIS kabupaten/kota. Hasil uji dibaca bersama ukuran efek dan interval kepercayaan, bukan hanya nilai p, dan teknik lanjutan seperti deret waktu, analisis faktor, dan inferensi Bayesian tersedia untuk pertanyaan yang tidak terjawab oleh uji dasar.',
                ],
                'subbab' => [
                    ['3-1', 'Desain Penelitian Kuantitatif', ['Desain Deskriptif dan Korelasional', 'Survei, Studi Longitudinal, Cross Section, dan Tren', 'Penelitian Ex Post Facto', 'Desain Eksperimen Dasar', 'Desain Eksperimen Lanjutan', 'Meta-Analisis']],
                    ['3-2', 'Variabel dan Hipotesis', ['Hipotesis', 'Variabel dan Operasionalisasi', 'Karakteristik Alat Ukur yang Baik', 'Kausalitas']],
                    ['3-3', 'Pengumpulan Data Kuantitatif: Kuesioner', []],
                    ['3-4', 'Analisis Data Kuantitatif', ['Dasar Analisis Kuantitatif dan SPSS', 'Memilih Uji Statistik', 'Kovarians dan Analisis Regresi', 'Analisis Kuantitatif Lanjutan']],
                ],
                'konsep' => ['desain penelitian', 'survei potong lintang', 'studi longitudinal', 'ex post facto', 'kuasi-eksperimen', 'selisih-dalam-selisih', 'meta-analisis', 'hipotesis nol', 'operasionalisasi', 'skala nominal, ordinal, interval, rasio', 'validitas', 'reliabilitas', 'alfa Cronbach', 'kausalitas', 'kontrafaktual', 'kuesioner', 'uji coba kuesioner', 'bias keinginan sosial', 'statistik deskriptif', 'uji beda', 'regresi linear berganda', 'ukuran efek', 'analisis deret waktu', 'analisis faktor', 'inferensi Bayesian', 'model CIBEST', 'literasi zakat'],
                'tanya' => ['Kapan survei potong lintang cukup, dan kapan penelitian zakat memerlukan studi longitudinal atau desain eksperimen?', 'Mengapa korelasi antara literasi zakat dan kepatuhan membayar zakat belum membuktikan sebab-akibat?', 'Mengapa hasil uji statistik perlu dibaca bersama ukuran efek dan interval kepercayaan, bukan hanya nilai p?'],
            ],
            [
                'judul' => 'Metode Kualitatif', 'bagian' => 2,
                'ringkas' => 'Etnografi, fenomenologi, studi kasus, dan analisis tekstual; wawancara, kelompok diskusi terarah, observasi; pengodean, analisis isi, grounded theory, dan analisis tematik dalam kajian zakat dan wakaf.',
                'isi' => [
                    'Penelitian kualitatif berusaha memahami makna zakat dan wakaf dari sudut pandang pelakunya, dengan peneliti sebagai instrumen utama, dan tepat dipakai ketika pertanyaan berbentuk “bagaimana” dan “mengapa”. Perencanaannya dimulai dari penentuan fokus (tata kelola, pengalaman, atau budaya) dan pemilihan informan kunci secara purposive atau snowball. Empat desain utamanya adalah etnografi untuk memahami budaya seperti tradisi zakat fitrah dan wakaf tanah, fenomenologi untuk memahami pengalaman mustahik, wakif, dan amil, studi kasus untuk menelaah lembaga, program, atau proses secara mendalam, serta analisis tekstual untuk menelaah laporan, kampanye, dan khutbah.',
                    'Data dikumpulkan melalui wawancara mendalam dengan pedoman semiterstruktur, kelompok diskusi terarah yang memanfaatkan interaksi antarpeserta, observasi yang memperlihatkan apa yang benar-benar dilakukan, dan catatan pengalaman responden seperti kisah hidup. Setiap teknik menuntut strategi yang berbeda bagi amil, nazhir, dan mustahik, termasuk perlindungan khusus bagi informan yang rentan. Teknik-teknik ini paling kuat bila dipadukan, karena apa yang dikatakan pelaku belum tentu sama dengan apa yang mereka lakukan.',
                    'Analisis kualitatif berjalan bersamaan dengan pengumpulan data dan bertumpu pada pengodean: pemberian label bermakna pada potongan data yang dirangkai menjadi kategori dan tema. Teknik yang dipilih bergantung pada pertanyaan: model interaktif untuk menemukan pola, analisis isi untuk menelaah teks secara sistematis, grounded theory untuk membangun model baru, analisis tematik untuk menemukan pola makna bersama, serta analisis wacana dan hermeneutika untuk menelaah bahasa kebijakan dan penafsiran teks. Laporan yang baik memperlihatkan jejak analisisnya dan melaporkan temuan yang menyimpang dari pola utama.',
                ],
                'subbab' => [
                    ['4-1', 'Desain Penelitian Kualitatif', ['Hakikat Penelitian Kualitatif', 'Merencanakan Studi Kualitatif', 'Etnografi', 'Fenomenologi', 'Studi Kasus', 'Analisis Tekstual']],
                    ['4-2', 'Pengumpulan Data Kualitatif', ['Wawancara', 'Kelompok Diskusi Terarah', 'Observasi', 'Catatan Pengalaman Responden']],
                    ['4-3', 'Analisis Data Kualitatif', ['Pendekatan Analisis Kualitatif', 'Analisis Isi dan Grounded Theory', 'Analisis Tematik', 'Analisis Kualitatif Lanjutan']],
                ],
                'konsep' => ['penelitian kualitatif', 'etnografi', 'fenomenologi', 'studi kasus', 'analisis tekstual', 'informan kunci', 'purposive sampling', 'teknik snowball', 'saturasi', 'wawancara kualitatif', 'pedoman wawancara', 'kelompok diskusi terarah', 'observasi partisipan', 'catatan lapangan', 'photovoice', 'pengodean', 'kode in vivo', 'analisis isi', 'grounded theory', 'analisis tematik', 'analisis wacana', 'hermeneutika', 'deskripsi tebal', 'refleksivitas', 'sudut pandang emik dan etik', 'tadabbur'],
                'tanya' => ['Pertanyaan “Bagaimana amil masjid memaknai tugas menyalurkan zakat fitrah?” paling tepat dijawab dengan desain apa?', 'Mengapa wawancara perlu dipadukan dengan observasi dalam meneliti praktik nazhir?', 'Apa yang membedakan analisis tematik dari grounded theory, dan kapan masing-masing dipilih?'],
            ],
            [
                'judul' => 'Metode Historis-Dokumenter', 'bagian' => 2,
                'ringkas' => 'Merancang penelitian sejarah zakat dan wakaf: penelusuran arsip dan dokumen lembaga, sejarah lisan, prosopografi, kritik sumber eksternal dan internal, serta penyusunan narasi sejarah.',
                'isi' => [
                    'Data sejarah sudah ada sebelum peneliti datang, dibuat oleh orang lain untuk keperluan lain, dan tidak dapat diminta ulang. Merancang penelitian sejarah karena itu berarti menyesuaikan pertanyaan dengan sumber yang benar-benar dapat dijangkau, yang persebarannya berbeda antara masa kerajaan Islam, masa kolonial, dan masa kemerdekaan. Sumber primer memberi keterangan dari masa peristiwa, sedangkan sumber sekunder berguna untuk memahami konteks dan menemukan jalan ke sumber primer, asalkan peneliti tidak terjebak pada rantai kutipan.',
                    'Pengumpulan data historis memerlukan tiga cara kerja. Penelusuran arsip menemukan dan mencatat dokumen di lembaga resmi maupun di tangan pesantren, masjid, dan keluarga, dengan kartu sumber dan inventaris sebagai alat utamanya. Sejarah lisan merekam kesaksian pelaku dan saksi, sehingga dapat menjelaskan alasan, suasana, dan hubungan antarorang yang tidak pernah tercatat, meskipun jangkauannya terbatas pada kira-kira satu abad terakhir. Prosopografi menghimpun keterangan yang sama tentang banyak orang, misalnya para pendiri lembaga zakat atau para nazhir wakaf besar, lalu mencari pola kolektifnya.',
                    'Sumber yang terkumpul harus diuji sebelum dipakai. Kritik eksternal menguji keaslian dan keutuhan dokumen; kritik internal menguji kebenaran isinya dengan menimbang kedudukan pembuat, kepentingannya, dan konsistensinya. Keterangan yang lolos uji ditafsirkan menjadi penjelasan kronologis, kausal, kontekstual, atau komparatif dengan menghindari anakronisme dan presentisme, lalu disusun menjadi narasi sejarah yang berpijak pada sumber dan tegas tentang tingkat keyakinannya. Ketika dokumen dan kesaksian lisan berbeda, perbedaan itu bukan hambatan, melainkan bahan analisis yang kerap menjelaskan jarak antara praktik masyarakat dan administrasi negara.',
                ],
                'subbab' => [
                    ['5-1', 'Desain Penelitian Historis dan Dokumenter', []],
                    ['5-2', 'Pengumpulan Data Historis', ['Penelusuran Arsip dan Dokumen Lembaga', 'Sejarah Lisan dan Sejarah Hidup', 'Prosopografi']],
                    ['5-3', 'Analisis Data Historis', ['Kritik Sumber', 'Penafsiran Sumber dan Penjelasan Sejarah', 'Penyusunan Narasi Sejarah']],
                ],
                'konsep' => ['sumber primer', 'sumber sekunder', 'rantai kutipan', 'penelusuran arsip', 'heuristik', 'akta ikrar wakaf', 'kartu sumber', 'inventaris sumber', 'sejarah lisan', 'sejarah hidup', 'prosopografi', 'kritik sumber', 'kritik eksternal', 'kritik internal', 'anakronisme', 'presentisme', 'historiografi'],
                'tanya' => ['Mengapa akta ikrar wakaf di kantor urusan agama dan kesaksian ahli waris wakif dapat saling bertentangan, dan bagaimana peneliti menyikapinya?', 'Apa beda kritik eksternal dan kritik internal atas sebuah laporan tahunan lembaga zakat masa kolonial?', 'Bagaimana prosopografi dapat dipakai untuk memahami para pendiri lembaga zakat di Indonesia?'],
            ],
            [
                'judul' => 'Metode Campuran dan Komparatif', 'bagian' => 2,
                'ringkas' => 'Desain mixed methods eksploratori, eksplanatori, dan konvergen; analisis komparatif antarlembaga dan antarnegara; tipologi dan analisis komparatif kualitatif (QCA) dalam kajian zakat dan wakaf.',
                'isi' => [
                    'Banyak persoalan zakat dan wakaf tidak dapat dijawab oleh satu jenis data saja. Metode campuran memadukan data kuantitatif dan kualitatif melalui desain eksploratori, eksplanatori, atau konvergen, dengan integrasi yang jelas di antara keduanya, misalnya lewat tampilan bersama. Analisis komparatif membandingkan lembaga, wilayah, atau negara melalui pemilihan kasus yang paling mirip atau paling berbeda, dengan syarat konsep dan ukuran yang dibandingkan setara agar tidak terjadi peregangan konsep.',
                    'Perbandingan diperkaya oleh analisis kontekstual, yang membaca pengaruh budaya lokal dan kelembagaan daerah, serta pendekatan interdisipliner, yang memadukan fikih, ekonomi, manajemen, dan hukum dalam satu pertanyaan penelitian.',
                    'Ketika jumlah kasus banyak dan beragam, tipologi membantu menyederhanakan keragaman dengan menyilangkan dua dimensi atau lebih, menghasilkan jenis-jenis yang bermakna seperti tipologi nazhir, model pengelolaan wakaf produktif, dan strategi penghimpunan zakat. Analisis komparatif kualitatif (QCA) melangkah lebih jauh dengan aljabar Boolean dan tabel kebenaran untuk menemukan kombinasi kondisi yang cukup bagi suatu capaian. Pendekatan ini memperlihatkan bahwa keberhasilan zakat dan wakaf sering bersifat konjungtural, ekuifinal, dan asimetris, sehingga satu faktor jarang cukup menjelaskan hasil sendirian.',
                ],
                'subbab' => [
                    ['6-1', 'Desain Campuran dan Komparatif', ['Metode Campuran', 'Analisis Komparatif', 'Analisis Kontekstual', 'Pendekatan Interdisipliner']],
                    ['6-2', 'Analisis Komparatif', ['Tipologi', 'Analisis Komparatif Kualitatif']],
                ],
                'konsep' => ['metode campuran', 'desain eksploratori', 'desain eksplanatori', 'desain konvergen', 'tampilan bersama', 'analisis komparatif', 'desain kasus paling mirip', 'desain kasus paling berbeda', 'peregangan konsep', 'isomorfisme', 'analisis kontekstual', 'pendekatan interdisipliner', 'tipologi', 'ruang sifat', 'aljabar Boolean', 'crisp-set QCA', 'fuzzy-set QCA', 'kalibrasi'],
                'tanya' => ['Kapan desain eksplanatori lebih tepat daripada desain konvergen dalam meneliti kepercayaan muzaki?', 'Apa syarat agar perbandingan tata kelola zakat Indonesia dan Malaysia tidak menyesatkan?', 'Bagaimana QCA menunjukkan bahwa keberhasilan wakaf produktif jarang ditentukan satu faktor?'],
            ],
            [
                'judul' => 'Metode Terapan', 'bagian' => 2,
                'ringkas' => 'Penelitian tindakan bersama lembaga dan masyarakat, penelitian evaluasi program zakat dan wakaf dengan kerangka masukan–keluaran–hasil–dampak, serta penelitian dan pengembangan (R&D) untuk modul, instrumen, dan aplikasi.',
                'isi' => [
                    'Penelitian tindakan tidak berhenti pada rekomendasi, melainkan ikut merancang perbaikan bersama pelaku di lapangan, mencobanya, lalu menilai hasilnya melalui siklus perencanaan, tindakan, pengamatan, dan refleksi. Yang membedakannya dari pendampingan biasa adalah keharusan menjalankan lebih dari satu siklus. Masyarakat di sini bukan responden, melainkan mitra yang ikut menentukan masalah, tindakan, dan penilaian, sehingga peneliti perlu menjaga kesetaraan peran di tengah hubungan kuasa yang timpang antara lembaga, pengurus, dan mustahik.',
                    'Penelitian evaluasi menilai rancangan, pelaksanaan, atau hasil suatu program untuk mendukung keputusan lembaga, dan dapat dilakukan sebelum, selama, maupun sesudah program berjalan. Kerangka masukan, kegiatan, keluaran, hasil, dan dampak membantu memisahkan hal yang sepenuhnya dikendalikan lembaga dari hal yang hanya disumbangnya, sehingga klaim keberhasilan tidak melampaui bukti. Indikator seperti perubahan pendapatan, kemandirian, dan peralihan status dari mustahik menjadi muzaki perlu didefinisikan secara terbuka dan, untuk menilai dampak, memerlukan kelompok pembanding.',
                    'Penelitian dan pengembangan menghasilkan produk, seperti modul, instrumen, prosedur, atau aplikasi, lalu menguji kelayakan dan keefektifannya melalui validasi ahli, uji coba bertahap, dan revisi dengan model seperti ADDIE atau 4D. Kelemahan yang paling sering ditemui adalah berhenti pada validasi ahli tanpa uji coba pada pengguna.',
                ],
                'subbab' => [
                    ['7-1', 'Penelitian Tindakan', []],
                    ['7-2', 'Penelitian Evaluasi', []],
                    ['7-3', 'Penelitian dan Pengembangan', []],
                ],
                'konsep' => ['penelitian tindakan', 'siklus penelitian tindakan', 'penelitian evaluasi', 'evaluasi formatif', 'evaluasi sumatif', 'rantai hasil', 'zakat produktif', 'wakaf produktif', 'Unit Pengumpul Zakat (UPZ)', 'penelitian dan pengembangan', 'penelitian berbasis desain', 'model ADDIE', 'model 4D', 'validasi ahli', 'uji coba terbatas'],
                'tanya' => ['Apa yang membedakan penelitian tindakan dari pendampingan program biasa?', 'Mengapa klaim “mustahik menjadi muzaki” memerlukan kelompok pembanding?', 'Mengapa validasi ahli saja tidak cukup untuk menyatakan sebuah modul pelatihan amil efektif?'],
            ],
            [
                'judul' => 'Metode Digital', 'bagian' => 3,
                'ringkas' => 'Penelitian berbasis internet, survei daring, big data transaksi dan tren pencarian, serta analisis teks otomatis (sentimen, penambangan teks, pemetaan topik) dalam kajian zakat dan wakaf.',
                'isi' => [
                    'Internet memungkinkan peneliti menyebarkan survei, menelaah platform dan media sosial, serta mewawancarai pengelola lembaga tanpa biaya perjalanan. Kemudahan itu menyimpan jebakan keterwakilan, sebab yang terjangkau hanyalah mereka yang memakai kanal digital, sedangkan penyetor zakat lewat panitia masjid dan wakif tanah di desa tidak meninggalkan jejak. Peneliti wajib menyatakan siapa yang sebenarnya terwakili dalam datanya dan membatasi kesimpulan pada kelompok tersebut.',
                    'Big data dalam kajian zakat dan wakaf umumnya berupa data hasil samping, yaitu jejak dari kegiatan lain seperti transaksi pembayaran dan pencarian di mesin pencari, bukan data yang dikumpulkan untuk penelitian. Kekuatannya terletak pada kelengkapan catatan perilaku, kelemahannya pada ketiadaan alasan di balik perilaku itu. Hambatan terbesarnya bukan analisis, melainkan akses dan kerahasiaan, sehingga peneliti perlu perjanjian penggunaan data yang jelas dan menjaga kerahasiaan muzaki meskipun lembaga telah memberi izin.',
                    'Analisis sentimen, penambangan teks, dan pemetaan topik memungkinkan peneliti mengolah ribuan teks dalam waktu singkat, tetapi ketiganya hanya menghitung kata dan tidak memahami makna. Awan kata hanya menunjukkan frekuensi, sedangkan peta kemunculan bersama lebih informatif karena memperlihatkan keterkaitan antartema. Penelitian yang dapat dipercaya selalu menjelaskan cara pembersihan teks, menguji hasil mesin dengan penilaian manual, dan menyertakan contoh teks asli.',
                ],
                'subbab' => [
                    ['8-1', 'Penelitian Berbasis Internet dan Penggunaan Komputer', []],
                    ['8-2', 'Big Data', []],
                    ['8-3', 'Analisis Teks Otomatis', []],
                ],
                'konsep' => ['survei daring', 'platform zakat digital', 'urun dana wakaf', 'big data', 'data tren pencarian', 'indeks pengungkapan', 'analisis teks otomatis', 'korpus', 'analisis sentimen', 'penambangan teks', 'pemetaan topik', 'Latent Dirichlet Allocation', 'awan kata'],
                'tanya' => ['Siapa yang tidak terwakili dalam survei daring tentang zakat, dan bagaimana membatasi kesimpulannya?', 'Apa kekuatan dan kelemahan data transaksi platform zakat sebagai big data?', 'Mengapa peta kemunculan bersama kata lebih informatif daripada awan kata?'],
            ],
            [
                'judul' => 'Metode Lanjutan', 'bagian' => 3,
                'ringkas' => 'Analisis jaringan sosial, analisis sekuens, analisis sistem dinamis, dan pemodelan formal (teori permainan, pemrograman linear, optimasi) untuk pertanyaan zakat dan wakaf yang tidak terjawab metode dasar.',
                'isi' => [
                    'Analisis jaringan sosial memperlakukan lembaga dan orang sebagai simpul dan hubungan di antara mereka sebagai garis, lalu menghitung ukuran seperti sentralitas dan kepadatan. Metode ini menjawab siapa yang menjadi penghubung dalam kolaborasi lembaga zakat, perusahaan, dan pemerintah, serta bagaimana relasi nazhir, wakif, dan penerima manfaat tersusun. Hasilnya hanya sebaik batas jaringan dan kelengkapan data yang dihimpun.',
                    'Analisis sekuens memperlakukan urutan keadaan dari waktu ke waktu sebagai satu kesatuan data, lalu mengelompokkan satuan yang jalurnya serupa. Metode ini cocok untuk menelusuri perubahan status mustahik dari tahun ke tahun dan perjalanan aset wakaf dari ikrar hingga produktif; kuncinya adalah satuan waktu yang seragam dan kode keadaan yang jelas batasnya. Analisis sistem memandang pengelolaan zakat sebagai rangkaian penghimpunan, pengelolaan, pendistribusian, dan pelaporan yang saling memberi umpan balik; model sistem dinamis menyatakan keterkaitan itu dalam persamaan stok dan aliran untuk memproyeksikan dampak kebijakan sebelum dijalankan.',
                    'Pemodelan formal menuliskan pelaku, pilihan, kendala, dan tujuan secara matematis. Teori permainan menjelaskan kepatuhan muzaki sebagai hasil saling ketergantungan dengan keterbukaan lembaga, model alokasi merumuskan pembagian dana antar-asnaf sebagai pemrograman linear, dan model optimasi mencari pemanfaatan aset wakaf terbaik dalam batas syarat wakif dan risiko. Ketegasan asumsi adalah kekuatan sekaligus batasnya, dan model harus diuji terhadap data masa lalu agar tidak sekadar mengulang asumsi penyusunnya.',
                ],
                'subbab' => [
                    ['9-1', 'Analisis Jaringan Sosial', []],
                    ['9-2', 'Analisis Sekuens', []],
                    ['9-3', 'Analisis Sistem', []],
                    ['9-4', 'Pemodelan Formal', []],
                ],
                'konsep' => ['analisis jaringan sosial', 'simpul dan garis', 'sentralitas', 'kepadatan jaringan', 'sosiogram', 'analisis sekuens', 'analisis sistem', 'sistem dinamis', 'stok dan aliran', 'lingkar umpan balik', 'pemodelan formal', 'teori permainan', 'pemrograman linear', 'model optimasi'],
                'tanya' => ['Pertanyaan apa yang dijawab analisis jaringan sosial dalam kolaborasi lembaga zakat dan perusahaan?', 'Bagaimana perjalanan aset wakaf dari ikrar hingga produktif dapat dianalisis sebagai sekuens?', 'Bagaimana teori permainan menjelaskan hubungan kepatuhan muzaki dan keterbukaan lembaga?'],
            ],
            [
                'judul' => 'Tantangan dan Dampak Penelitian Filantropi Islam', 'bagian' => 3,
                'ringkas' => 'Tantangan data, pendekatan, topik, dan kualitas penelitian zakat dan wakaf; agenda penelitian ke depan; serta cara penelitian membawa dampak bagi lembaga, kebijakan, dan masyarakat.',
                'isi' => [
                    'Penelitian zakat dan wakaf menghadapi data yang tersebar dan tidak seragam, pendekatan yang terbelah antara normatif dan empiris serta bergantung pada teori pinjaman, dan topik yang berulang dengan desain deskriptif bersampel kecil. Jalan keluarnya adalah pemanfaatan data resmi dan kemitraan dengan lembaga, harmonisasi metode dan pengembangan alat ukur berbasis maqasid, serta pemilihan topik dan desain yang lebih kuat.',
                    'Agenda ke depan mencakup dampak jangka panjang zakat, produktivitas dan valuasi aset wakaf, digitalisasi dan tata kelola, serta kolaborasi dengan kebijakan publik, dengan standar pelaporan seperti PSAK 109 dan literasi zakat dan wakaf sebagai bahan kajian yang terus berkembang.',
                    'Penelitian berdampak bagi lembaga jika rekomendasinya praktis dan dapat segera dijalankan; bagi kebijakan jika hadir pada perumusan persoalan, pemilihan kebijakan, dan penilaian, serta menjadi dasar standar dan indeks nasional; dan bagi masyarakat jika menaikkan literasi dan kepercayaan serta menetapkan ukuran yang jujur bagi perubahan mustahik menjadi muzaki. Peneliti adalah bagian dari gerakan zakat dan wakaf, dan penelitiannya adalah amal ilmiah yang bernilai karena ketelitian dan manfaat yang sampai.',
                ],
                'subbab' => [
                    ['10-1', 'Tantangan Metodologi dan Jalan ke Depan', ['Tantangan Data', 'Tantangan Pendekatan', 'Tantangan Topik dan Kualitas', 'Agenda Penelitian ke Depan']],
                    ['10-2', 'Penelitian yang Membawa Perubahan', ['Dampak bagi Lembaga', 'Dampak bagi Kebijakan', 'Dampak bagi Masyarakat', 'Peneliti sebagai Bagian dari Gerakan Zakat dan Wakaf']],
                ],
                'konsep' => ['harmonisasi metode', 'alat ukur berbasis maqasid', 'kejenuhan topik', 'agenda penelitian', 'PSAK 109', 'standar pelaporan', 'literasi zakat dan wakaf', 'siklus kebijakan', 'diseminasi'],
                'tanya' => ['Mengapa penelitian zakat dan wakaf cenderung berulang pada topik dan desain yang sama, dan bagaimana menghindarinya?', 'Pada tahap siklus kebijakan mana hasil penelitian paling mungkin dipakai pemerintah?', 'Apa ukuran jujur bagi klaim perubahan mustahik menjadi muzaki?'],
            ],
        ],
    ],
];
