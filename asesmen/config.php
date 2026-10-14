<?php
/**
 * Pengaturan Asesmen Zakademia.
 * Ubah nilai di bawah sesuai kebutuhan. Kata sandi TIDAK diisi di sini:
 * akun admin dibuat saat pertama kali membuka admin.php, dan bisa diatur ulang lewat "Lupa kata sandi".
 */
return [
    // Alamat penerima pemberitahuan kiriman baru.
    'notify_email' => 'info@zakademia.com',
    // Email akun admin hasil pemindahan kata sandi admin versi lama.
    'admin_email'  => 'info@zakademia.com',
    // Ganti nilai ini (misalnya 'r2') untuk menghapus semua akun admin satu kali saat deploy berikutnya.
    'admin_reset'  => 'r1',
    // Hash SHA-256 dari kode penyiapan; kode wajib diisi saat membuat akun admin baru.
    'setup_code_hash' => 'b7d2af9969d7f04455390527bf1a12d587224c1069362ea9a26d5a9fced6fdbc',
    // Alamat pengirim email hasil. Sebaiknya kotak surat yang ada di hosting yang sama.
    'from_email'   => 'info@zakademia.com',
    'from_name'    => 'Zakademia',
    // Folder data (database dan akun). Bawaan: folder "data" di sebelah file ini.
    'data_dir'     => __DIR__ . '/data',
    // Batas kiriman dari satu alamat IP dalam 10 menit (penangkal spam).
    'rate_limit'   => 5,
];
