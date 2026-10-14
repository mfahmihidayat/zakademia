# zakademia.com

Website Zakademia dan formulir Asesmen Kesiapan Penghimpunan.

| Bagian | Lokasi | Alamat |
| --- | --- | --- |
| Website | `index.html`, `css/`, `js/`, `assets/` | https://zakademia.com |
| Formulir asesmen lama (Apps Script) | `asesmen.html` | https://zakademia.com/asesmen.html |
| Formulir asesmen | `asesmen/index.html` | https://zakademia.com/asesmen/ |
| Dashboard admin asesmen | `asesmen/admin.php` | https://zakademia.com/asesmen/admin.php |
| Survei (Riset Kebutuhan) | `survei/` | https://zakademia.com/survei/ |
| Tautan survei untuk responden | `s/` | https://zakademia.com/s/nama-survei |
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
