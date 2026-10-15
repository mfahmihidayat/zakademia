/* Zakademia — isi Ruang Belajar. Teks modul ditulis di sini; status 'siap' berarti modul dapat dibuka. */
window.ZK_KAMAR=[
 {id:'analitik',nama:'Kamar Analitik',pemandu:'Zaida',foto:'/assets/images/zaida.jpg',
  intro:'Membaca angka penghimpunan dengan tenang: dari rekap tahunan hingga segmen donatur. Ditemani Zaida, teman belajar zakat, infak, dan sedekah.',
  modul:['a1','a2','a3','a4']},
 {id:'riset',nama:'Kamar Riset',pemandu:'Wafiy',foto:'/assets/images/wafiy.jpg',
  intro:'Merancang penelitian yang menjawab kebutuhan lembaga: dari pertanyaan, kuesioner, hingga laporan. Ditemani Wafiy, teman belajar wakaf dan filantropi Islam yang senang turun ke lapangan.',
  modul:['r1','r2','r3','r4']}
];

window.ZK_MODUL={
 a1:{kamar:'analitik',no:1,judul:'Membaca kesehatan penghimpunan',menit:15,status:'siap',
  tujuan:['Membedakan pertumbuhan dana dengan kesehatan basis donatur','Menghitung retensi, CLV, CAC, dan biaya per rupiah terhimpun dari angka rekap','Menafsirkan hasilnya untuk keputusan penghimpunan'],
  praktik:{teks:'Hitung kesehatan penghimpunan lembaga Anda',href:'/analitik/',label:'Buka Kesehatan Penghimpunan'},
  bagian:[
   {h:'Mengapa total dana saja tidak cukup',p:['Laporan penghimpunan biasanya dibuka dengan satu angka: total dana tahun ini dibanding tahun lalu. Angka itu penting, tetapi tidak menjelaskan dari mana pertumbuhan datang.','Dana dapat naik karena beberapa donatur besar menambah donasinya, sementara ratusan donatur lama berhenti memberi. Di permukaan lembaga terlihat tumbuh; di dalamnya basis donatur menyusut. Karena itu kesehatan penghimpunan dibaca dari beberapa angka sekaligus, bukan dari satu angka.']},
   {h:'Retensi donatur',p:['Retensi donatur adalah persentase donatur tahun lalu yang memberi lagi tahun ini.'],rumus:'Retensi = donatur tahun lalu yang memberi lagi ÷ jumlah donatur tahun lalu',contoh:'Tahun lalu ada 3.200 donatur. Tahun ini 1.650 di antaranya berdonasi lagi. Retensi = 1.650 ÷ 3.200 = 51,6%. Artinya hampir separuh donatur tahun lalu tidak kembali.',p2:['Retensi adalah indikator yang paling sering terlewat, padahal mempertahankan donatur umumnya jauh lebih murah daripada mencari donatur baru.']},
   {h:'Donatur diperoleh dan hilang',p:['Bandingkan jumlah donatur baru dengan donatur yang berhenti. Bila donatur yang berhenti lebih banyak daripada donatur baru, menambah anggaran iklan tidak akan menumbuhkan basis donatur; yang perlu diperbaiki adalah pengalaman donatur setelah donasi pertama.'],contoh:'1.550 donatur berhenti, 1.400 donatur baru datang, dan 450 donatur lama kembali. Hasil bersihnya bertambah 300 donatur, tetapi setiap 10 donatur baru diimbangi 11 donatur yang berhenti.'},
   {h:'CLV dan CAC',p:['CLV (customer lifetime value, di sini nilai seumur hidup donatur) memperkirakan total donasi satu donatur selama ia bertahan. CAC (customer acquisition cost) adalah biaya rata-rata untuk mendapatkan satu donatur baru.'],rumus:'CLV ≈ nilai donasi per donatur per tahun ÷ (1 − retensi)\nCAC = biaya untuk menjaring donatur baru ÷ jumlah donatur baru',contoh:'Nilai per donatur Rp1,37 juta per tahun dan retensi 51,6%, maka CLV ≈ Rp1,37 juta ÷ 0,484 ≈ Rp2,8 juta. Biaya akuisisi Rp280 juta untuk 1.400 donatur baru, maka CAC = Rp200 ribu. Rasio CLV : CAC ≈ 14 : 1.',p2:['Patokan yang lazim dipakai: rasio 3 : 1 atau lebih tergolong sehat. Di bawah 1 : 1, nilai donatur baru belum menutup biaya untuk mendapatkannya.']},
   {h:'Biaya per rupiah terhimpun',p:['Bagi biaya penghimpunan total dengan dana terhimpun. Untuk dana zakat, batas hak amil seperdelapan (12,5%) dapat dipakai sebagai acuan awal, dengan memperhatikan ketentuan yang berlaku bagi lembaga.'],contoh:'Biaya Rp610 juta untuk dana Rp4,8 miliar berarti Rp12,7 untuk setiap Rp100 yang terhimpun, sedikit di atas acuan 12,5%.'},
   {h:'Membaca semuanya bersama',p:['Tidak ada satu angka yang berdiri sendiri. Dana naik dengan retensi rendah menandakan ketergantungan pada donatur baru. Rasio CLV : CAC yang sangat tinggi bisa berarti lembaga terlalu sedikit berinvestasi pada akuisisi. Biaya per rupiah yang rendah tetapi retensi juga rendah bisa berarti layanan donatur kurang diperhatikan.','Kebiasaan yang baik: hitung angka-angka ini setiap tahun dengan cara yang sama, lalu bandingkan dari tahun ke tahun.']}
  ],
  kuis:[
   {q:'Tahun lalu 2.000 donatur, tahun ini 900 di antaranya berdonasi lagi. Berapa retensinya?',o:['45%','55%','90%','Tidak dapat dihitung tanpa total dana'],a:0,e:'900 ÷ 2.000 = 45%.'},
   {q:'Dana naik 20%, tetapi donatur yang berhenti lebih banyak daripada donatur baru. Kesimpulan yang paling tepat?',o:['Penghimpunan sehat karena dana naik','Pertumbuhan bertumpu pada sebagian donatur; basis donatur menyusut','Biaya akuisisi terlalu rendah','Retensi pasti di atas 60%'],a:1,e:'Dana bisa naik dari donatur besar, sementara basis donatur menyusut. Ini perlu diperbaiki lewat retensi.'},
   {q:'Nilai per donatur Rp1 juta per tahun dan retensi 50%. Berapa perkiraan CLV?',o:['Rp500 ribu','Rp1 juta','Rp2 juta','Rp5 juta'],a:2,e:'CLV ≈ Rp1 juta ÷ (1 − 0,5) = Rp2 juta.'},
   {q:'Rasio CLV : CAC sebesar 0,8 : 1 berarti…',o:['Akuisisi sangat efisien','Nilai donatur baru belum menutup biaya untuk mendapatkannya','Retensi terlalu tinggi','Biaya per rupiah di bawah 12,5%'],a:1,e:'Di bawah 1 : 1, setiap donatur baru “dibeli” lebih mahal daripada nilai yang ia berikan.'},
   {q:'Biaya penghimpunan Rp150 juta untuk dana Rp1 miliar. Berapa biaya per Rp100 terhimpun?',o:['Rp1,5','Rp15','Rp150','Rp6,7'],a:1,e:'Rp150 juta ÷ Rp1 miliar = 15%, atau Rp15 per Rp100.'}
  ]},
 a2:{kamar:'analitik',no:2,judul:'Mencatat data donatur dengan rapi',menit:15,status:'segera',
  tujuan:['Menentukan kolom wajib pencatatan donatur dan transaksi','Menyusun kamus data lembaga','Menerapkan prinsip pelindungan data pribadi dalam pencatatan']},
 a3:{kamar:'analitik',no:3,judul:'Segmentasi donatur: RFM dan piramida donatur',menit:20,status:'segera',
  tujuan:['Memahami kebaruan, frekuensi, dan nilai donasi','Membaca piramida jumlah donatur dan segitiga terbalik kontribusi dana','Merancang perlakuan berbeda untuk setiap segmen']},
 a4:{kamar:'analitik',no:4,judul:'Membaca tren dan musim penghimpunan',menit:15,status:'segera',
  tujuan:['Mengenali pola Ramadan, Zulhijah, dan akhir tahun','Membandingkan bulan secara adil dari tahun ke tahun','Menyusun kalender kampanye berbasis data']},
 r1:{kamar:'riset',no:1,judul:'Merumuskan pertanyaan riset lembaga',menit:15,status:'segera',
  tujuan:['Berangkat dari keputusan yang akan diambil','Mengubah kebutuhan informasi menjadi pertanyaan yang dapat dijawab','Memilih pendekatan: survei, wawancara, atau data administrasi']},
 r2:{kamar:'riset',no:2,judul:'Menyusun kuesioner yang baik',menit:15,status:'siap',
  tujuan:['Menyusun pertanyaan yang jelas, netral, dan satu makna','Memilih jenis pertanyaan dan skala yang tepat','Mengatur urutan dan menguji kuesioner sebelum disebar'],
  praktik:{teks:'Susun kuesioner pertama Anda',href:'/survei/',label:'Buat Survei'},
  bagian:[
   {h:'Mulai dari keputusan, bukan dari pertanyaan',p:['Kuesioner yang baik berangkat dari keputusan yang akan diambil lembaga. Tuliskan dulu: keputusan apa yang menunggu hasil survei ini? Misalnya memilih kanal donasi yang akan dikembangkan, atau memperbaiki laporan kepada donatur.','Setiap pertanyaan lalu diuji dengan satu pertanyaan sederhana: bila jawabannya sudah diketahui, apakah keputusan menjadi lebih jelas? Bila tidak, pertanyaan itu sebaiknya dihapus.']},
   {h:'Satu pertanyaan, satu hal',p:['Hindari pertanyaan ganda yang menanyakan dua hal sekaligus, karena responden tidak dapat menjawab keduanya dengan satu jawaban.'],contoh:'Kurang tepat: “Apakah laporan kami cepat dan mudah dipahami?”\nLebih tepat: dua pertanyaan terpisah, satu tentang kecepatan dan satu tentang kemudahan dipahami.',p2:['Gunakan bahasa netral. Pertanyaan menggiring seperti “Bukankah program kami sangat bermanfaat?” mendorong jawaban tertentu dan membuat hasil tidak dapat dipercaya.']},
   {h:'Memilih jenis pertanyaan',p:['Pilih jenis pertanyaan sesuai informasi yang dibutuhkan:'],daftar:['Pilihan ganda: satu jawaban dari pilihan yang saling lepas, misalnya kanal yang paling sering digunakan.','Kotak centang: boleh lebih dari satu jawaban, misalnya jenis dana yang pernah disalurkan.','Skala Likert: tingkat persetujuan atau kepuasan terhadap pernyataan.','Net Promoter Score (0–10): kesediaan merekomendasikan lembaga.','Pertanyaan terbuka: alasan dan saran; batasi jumlahnya karena melelahkan responden dan lebih sulit diolah.']},
   {h:'Skala Likert yang konsisten',p:['Beri label pada setiap titik skala, bukan hanya ujungnya. Skala lima poin menyediakan pilihan netral; skala empat poin meniadakan pilihan netral sehingga responden harus condong ke salah satu arah. Pilih salah satu dan gunakan secara konsisten dalam satu kuesioner.','Jaga arah skala tetap sama, misalnya selalu dari “sangat tidak setuju” ke “sangat setuju”, agar responden tidak keliru.']},
   {h:'Urutan, panjang, dan uji coba',p:['Mulailah dengan pertanyaan yang mudah dan tidak sensitif. Letakkan pertanyaan pribadi, seperti pendapatan, di bagian akhir. Usahakan kuesioner dapat diselesaikan dalam 10 menit.','Sebelum disebar, minta 3–5 orang dari kelompok sasaran mengisinya sambil menyampaikan bagian yang membingungkan. Uji coba kecil ini mencegah kesalahan yang mahal setelah ratusan jawaban terkumpul.']}
  ],
  kuis:[
   {q:'Langkah pertama yang paling tepat sebelum menulis pertanyaan kuesioner adalah…',o:['Menentukan jumlah responden','Menuliskan keputusan yang akan diambil dari hasil survei','Memilih warna formulir','Menyusun pertanyaan terbuka'],a:1,e:'Kuesioner disusun untuk membantu keputusan; dari situ pertanyaan yang relevan diturunkan.'},
   {q:'“Apakah petugas kami ramah dan cepat?” termasuk…',o:['Pertanyaan ganda','Pertanyaan terbuka','Skala Likert yang baik','Pertanyaan penyaring'],a:0,e:'Pertanyaan ini menanyakan dua hal sekaligus: keramahan dan kecepatan.'},
   {q:'Jenis pertanyaan yang tepat untuk “jenis dana yang pernah Anda salurkan” adalah…',o:['Pilihan ganda (satu jawaban)','Kotak centang (boleh lebih dari satu)','Net Promoter Score','Tanggal'],a:1,e:'Seorang donatur bisa menyalurkan lebih dari satu jenis dana.'},
   {q:'Pertanyaan tentang pendapatan sebaiknya diletakkan…',o:['Di awal kuesioner','Di tengah, di antara pertanyaan Likert','Di bagian akhir','Tidak boleh ditanyakan sama sekali'],a:2,e:'Pertanyaan sensitif di akhir mengurangi responden yang berhenti di tengah jalan.'},
   {q:'Tujuan uji coba kuesioner kepada 3–5 orang adalah…',o:['Menambah jumlah responden','Menemukan bagian yang membingungkan sebelum disebar luas','Menghitung skor NPS','Menentukan nama tautan survei'],a:1,e:'Uji coba kecil menemukan masalah lebih awal, sebelum ratusan jawaban terkumpul.'}
  ]},
 r3:{kamar:'riset',no:3,judul:'Sampel dan pengumpulan data lapangan',menit:20,status:'segera',
  tujuan:['Menentukan populasi dan ukuran sampel yang memadai','Memilih cara pengambilan sampel','Menjaga mutu data saat survei lapangan']},
 r4:{kamar:'riset',no:4,judul:'Mengolah dan melaporkan hasil survei',menit:20,status:'segera',
  tujuan:['Membaca distribusi jawaban dan rata-rata skala','Memetakan prioritas perbaikan dengan IPA','Menyusun ringkasan hasil untuk pimpinan']}
};
