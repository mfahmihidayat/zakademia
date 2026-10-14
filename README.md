# zakademia.com

Website Zakademia dan formulir Asesmen Kesiapan Penghimpunan.

| Bagian | Lokasi | Alamat |
| --- | --- | --- |
| Website | `index.html` dan berkas gambar di folder utama | https://zakademia.com |
| Formulir asesmen | `asesmen/index.html` | https://zakademia.com/asesmen/ |
| Dashboard admin asesmen | `asesmen/admin.php` | https://zakademia.com/asesmen/admin.php |

## Penerbitan otomatis

Setiap perubahan di cabang `main` otomatis diunggah ke hosting Hostinger melalui GitHub Actions (`.github/workflows/deploy.yml`).

Pengaturan yang diperlukan di **Settings → Secrets and variables → Actions**:

| Nama | Jenis | Isi |
| --- | --- | --- |
| `FTP_SERVER` | Secret | Alamat FTP dari hPanel, misalnya `ftp.zakademia.com` |
| `FTP_USERNAME` | Secret | Nama pengguna FTP |
| `FTP_PASSWORD` | Secret | Kata sandi FTP |
| `FTP_DIR` | Variable (opsional) | Folder tujuan bila bukan `public_html/` |

Folder `asesmen/data/` berisi database kiriman dan kata sandi admin. Folder ini dibuat otomatis di server, dilindungi `.htaccess`, dan tidak pernah diunggah atau ditimpa oleh penerbitan.

Panduan pemakaian dashboard asesmen ada di `asesmen/PANDUAN.md`.
