# zakademia.com

Website Zakademia dan formulir Asesmen Kesiapan Penghimpunan.

| Bagian | Lokasi | Alamat |
| --- | --- | --- |
| Website | `index.html`, `css/`, `js/`, `assets/` | https://zakademia.com |
| Formulir asesmen lama (Apps Script) | `asesmen.html` | https://zakademia.com/asesmen.html |
| Formulir asesmen | `asesmen/index.html` | https://zakademia.com/asesmen/ |
| Dashboard admin asesmen | `asesmen/admin.php` | https://zakademia.com/asesmen/admin.php |
| Survei (Riset Kebutuhan) | `survei/` | https://zakademia.com/survei/ |
| Kesehatan Penghimpunan (Data & Analitik) | `analitik/` | https://zakademia.com/analitik/ |
| Tautan survei untuk responden | `s/` | https://zakademia.com/s/nama-survei |
| Buku digital (baca di situs, flip) | `buku/` | https://zakademia.com/buku/metodologi/ |
| Admin buku digital | `buku/admin.php` | https://zakademia.com/buku/admin.php |
| Masuk / daftar akun | `asesmen/masuk.php`, `asesmen/daftar.php` | https://zakademia.com/asesmen/masuk.php |

## Penerbitan otomatis

Setiap perubahan di cabang `main` otomatis diunggah ke hosting Hostinger melalui GitHub Actions (`.github/workflows/deploy.yml`).

Pengaturan yang diperlukan di **Settings → Secrets and variables → Actions**:

| Nama | Jenis | Isi |
| --- | --- | --- |
| `FTP_SERVER` | Secret | Alamat FTP dari hPanel, misalnya `ftp.zakademia.com` |
| `FTP_USERNAME` | Secret | Nama pengguna FTP |
| `FTP_PASSWORD` | Secret | Kata sandi FTP |
| `FTP_DIR` | Variable (opsional) | Folder tujuan bila bukan `/domains/zakademia.com/public_html/` |

Folder `asesmen/data/` berisi database kiriman dan akun pengguna. Folder ini dibuat otomatis di server, dilindungi `.htaccess`, dan tidak pernah diunggah atau ditimpa oleh penerbitan.

Panduan pemakaian dashboard asesmen ada di `asesmen/PANDUAN.md`.

Alur **Salin situs aktif** (dijalankan manual dari tab Actions) menyimpan isi situs yang sedang tayang ke cabang `situs-aktif`, misalnya sebelum perubahan besar.

## Literasi (artikel)

Isi artikel ada di `tools/literasi/articles.py`. Setelah mengubah atau menambah artikel, jalankan `python3 tools/literasi/build.py` dari akar repositori. Skrip ini membuat halaman `literasi/`, gambar pratinjau `assets/images/og-lit-*.png`, dan kartu "Bacaan terbaru" di `index.html`. Folder `tools/` tidak ikut diunggah ke hosting.

Gambar pratinjau memakai ilustrasi vektor khas Zakademia (`tools/og_art.js`, digambar dengan `tools/og_render.js` lewat Playwright). Pilihan ilustrasi per artikel ada di `ILUSTRASI` dalam `tools/literasi/build.py`. Gambar pratinjau layanan dibuat dengan `python3 tools/og_layanan.py`.

## Pencarian

Ikon kaca pembesar di header (beranda, Literasi, dan halaman aplikasi) membuka jendela pencarian seluruh situs. Pintasan papan ketik: `Ctrl+K` / `⌘K` atau `/`. Kodenya ada di `js/cari.js` dan `css/cari.css`.

Isi yang dapat dicari tersimpan di `js/cari-indeks.json`: artikel Literasi, halaman beranda, layanan, bidang kajian, buku, artikel ilmiah, modul Ruang Belajar, dan halaman alat. Indeks ini diperbarui otomatis saat `tools/literasi/build.py` dijalankan. Bila hanya mengubah `index.html` atau `belajar/modul.js`, jalankan `python3 tools/cari/indeks.py` dari akar repositori. Daftar halaman alat diatur di `ALAT` dalam skrip tersebut.

## Buku digital

Buku dibaca di situs dengan tampilan flip (`buku/baca.php`, pustaka `js/flip/page-flip.browser.js`, MIT). Halaman buku disimpan sebagai gambar WebP di `buku/halaman/<slug>/` yang dilindungi `.htaccess` dan hanya dikirim lewat `buku/hal.php` setelah akses diperiksa. Halaman pratinjau (bawaan 24, diatur admin) terbuka untuk semua pengunjung.

Akses pembeli mengikuti pola Rekomendasi Asesmen: webhook Lynk.id (`buku/webhook.php`, URL lengkap ada di halaman admin) membuka akses otomatis berdasarkan email pembeli, dan admin dapat menambah akses manual untuk pembayaran transfer di `buku/admin.php`. Pembeli masuk atau mendaftar dengan email pembelian; bila memakai email lain, ia dapat mengeklaim lewat kode verifikasi. Daftar buku ada di `BK_BUKU` dalam `buku/lib_buku.php`; tautan Lynk.id, harga, kata kunci produk, dan jumlah halaman pratinjau disimpan di `asesmen/data/buku.json` di server.

Menyiapkan halaman buku dari PDF (sekali, lalu commit hasilnya):

    pip install pymupdf pillow numpy
    python3 tools/buku/render.py metodologi /path/Buku.pdf
