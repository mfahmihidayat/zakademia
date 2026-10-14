<?php
/**
 * Pengaturan Asesmen Zakademia.
 * Ubah nilai di bawah sesuai kebutuhan. Kata sandi admin TIDAK diisi di sini:
 * kata sandi dibuat saat pertama kali membuka admin.php.
 */
return [
    // Alamat penerima pemberitahuan kiriman baru.
    'notify_email' => 'info@zakademia.com',
    // Alamat pengirim email hasil. Sebaiknya kotak surat yang ada di hosting yang sama.
    'from_email'   => 'info@zakademia.com',
    'from_name'    => 'Zakademia',
    // Folder data (database dan kata sandi admin). Bawaan: folder "data" di sebelah file ini.
    'data_dir'     => __DIR__ . '/data',
    // Batas kiriman dari satu alamat IP dalam 10 menit (penangkal spam).
    'rate_limit'   => 5,
];
