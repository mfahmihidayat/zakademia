# Panduan pemasangan Asesmen Zakademia (versi PHP)

Semua berjalan di hosting zakademia.com: formulir, penyimpanan, dashboard admin, dan pengiriman email. Waktu pemasangan sekitar 15 menit.

## Isi paket

| File | Fungsi |
| --- | --- |
| `asesmen/index.html` | Formulir yang diisi lembaga |
| `asesmen/simpan.php` | Menerima kiriman, menyimpannya, dan memberi tahu tim |
| `asesmen/admin.php` | Dashboard admin (khusus akun admin) |
| `asesmen/masuk.php`, `daftar.php`, `lupa.php`, `reset.php`, `keluar.php` | Masuk, daftar, lupa kata sandi, atur ulang kata sandi, keluar |
| `asesmen/akun.php` | Akun saya: riwayat asesmen, profil lembaga, ganti kata sandi |
| `asesmen/lib.php` | Fungsi bersama (database, sesi, email) |
| `asesmen/config.php` | Pengaturan: alamat email dan batas kiriman |
| `asesmen/views/dashboard.html` | Tampilan dashboard (dibuka lewat `admin.php`, tidak bisa diakses langsung) |
| `asesmen/data/` | Tempat database kiriman dan akun (terlindung) |
| `logo-color.png` | Logo; lewati bila sudah ada di folder utama website |

Syarat hosting: PHP 7.4 atau lebih baru dengan ekstensi `pdo_sqlite`. Keduanya tersedia di hampir semua hosting cPanel.

## Langkah 1 — Unggah

1. Masuk cPanel > **File Manager** > folder `public_html`.
2. Unggah file zip ini, lalu klik **Extract**.
3. Pindahkan folder `asesmen` ke dalam `public_html` sehingga alamatnya menjadi `public_html/asesmen/`. Bila `logo-color.png` belum ada di `public_html`, pindahkan juga.
4. Pastikan file tersembunyi `.htaccess` di folder `asesmen/data` dan `asesmen/views` ikut terunggah. Di File Manager, aktifkan **Settings > Show Hidden Files** untuk memeriksanya.

## Langkah 2 — Siapkan email

1. Di cPanel > **Email Accounts**, pastikan kotak surat `info@zakademia.com` sudah ada.
2. Di cPanel > **Email Deliverability**, klik **Repair** bila ada tanda peringatan pada SPF atau DKIM. Langkah ini membuat email hasil tidak masuk ke folder spam.
3. Bila alamatnya berbeda, ubah `notify_email` dan `from_email` di `asesmen/config.php`.

## Langkah 3 — Buat akun admin

1. Buka `https://zakademia.com/asesmen/admin.php`.
2. Halaman **Buat akun admin** muncul sekali saja, selama belum ada akun admin. Isi nama, email admin (yang bisa Anda buka), dan kata sandi minimal 10 karakter.
3. Dashboard terbuka. Berikutnya, admin masuk lewat `asesmen/masuk.php` seperti pengguna lain.

## Langkah 4 — Uji coba

1. Buka `https://zakademia.com/asesmen/`, isi dengan data uji, lalu kirim.
2. Pastikan email pemberitahuan masuk ke info@zakademia.com.
3. Buka dashboard, klik **Lihat hasil**, lalu kirim email hasil ke alamat Anda sendiri.
4. **Cek perlindungan data:** buka `https://zakademia.com/asesmen/data/asesmen.sqlite`. Yang benar adalah muncul **403 Forbidden**. Bila file justru terunduh, hosting Anda tidak membaca `.htaccess` (biasanya server Nginx). Pindahkan folder `data` ke luar `public_html`, lalu ubah `data_dir` di `config.php`, misalnya `'/home/namaakun/asesmen-data'`.
5. Hapus kiriman uji: dari File Manager, hapus `asesmen/data/asesmen.sqlite`. File ini dibuat ulang otomatis pada kiriman berikutnya. Menghapus file ini juga menghapus semua akun.

## Langkah 5 — Tautkan dari website

Tambahkan tombol atau tautan "Ikuti asesmen" ke `https://zakademia.com/asesmen/`, misalnya di halaman Layanan.

## Pemakaian sehari-hari

- Kiriman baru → email pemberitahuan ke info@zakademia.com.
- Buka `zakademia.com/asesmen/admin.php` → **Lihat hasil** → periksa dan sunting email → **Kirim email**. Status berubah menjadi *Terkirim*.
- **Unduh CSV** memberi semua kiriman dalam satu file: skor, target, dan gap per dimensi, indeks, Cr Guttman, porsi segitiga prioritas (saat ini dan ke depan), keunggulan beserta jawaban VRIO dan kategorinya, arah inovasi, frekuensi uji coba, hambatan, rentang kinerja, serta perkiraan retensi, CAC, CLV, CLV : CAC, dan status kesehatan. File ini siap diolah di Excel atau SPSS.
- **Rekomendasi rinci** hanya tampil di dashboard dan tidak ikut dalam email hasil. Gunakan sebagai bahan sesi diskusi.
- **Keluar** setelah selesai, terutama di komputer bersama.

## Bila perlu

- **Memperbarui dari versi sebelumnya:** timpa `index.html`, `simpan.php`, `admin.php`, `lib.php`, dan `views/dashboard.html`. Jangan timpa folder `data`. Kiriman uji dari formulir versi lama tidak ditampilkan di dashboard dan CSV.

- **Akun lembaga:** lembaga wajib daftar dan masuk sebelum mengisi asesmen. Profil lembaga terisi otomatis di formulir, dan riwayat kiriman tampil di **Akun saya**.
- **Lupa kata sandi (admin maupun lembaga):** buka `asesmen/masuk.php` → **Lupa kata sandi?** → isi email. Tautan atur ulang (berlaku 60 menit) dikirim ke email tersebut.
- **Kata sandi admin versi lama (`admin.json`)** otomatis dipindahkan menjadi akun admin dengan email `admin_email` di `config.php` (bawaan: info@zakademia.com). Jika lupa kata sandinya, gunakan **Lupa kata sandi** dengan email itu.
- **Email hasil tidak sampai:** periksa folder spam penerima dan menu Email Deliverability. Sebagian hosting membatasi fungsi `mail()`; tanyakan ke penyedia hosting bila email sama sekali tidak terkirim.
- **Pesan "terlalu sering" saat mengisi:** satu alamat IP dibatasi 5 kiriman per 10 menit. Ubah `rate_limit` di `config.php` bila perlu.
- **Cadangan data:** unduh `asesmen/data/asesmen.sqlite` secara berkala, atau gunakan fitur Backup di cPanel.
