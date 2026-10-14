<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();

$done = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clip($_POST['email'] ?? '', 160);
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!valid_email($email)) $error = 'Alamat email belum benar.';
    else {
        $done = true;   // jawaban selalu sama, agar tidak membocorkan email mana yang terdaftar
        if (throttle_ok('lupa|' . ip_key(), 5, 3600) && throttle_ok('lupa|' . strtolower($email), 3, 3600)) {
            throttle_hit('lupa|' . ip_key());
            throttle_hit('lupa|' . strtolower($email));
            $q = db()->prepare('SELECT id, name FROM users WHERE email = ?');
            $q->execute([$email]);
            if ($u = $q->fetch(PDO::FETCH_ASSOC)) {
                $token = bin2hex(random_bytes(32));
                db()->prepare('DELETE FROM resets WHERE user_id = ? OR expires < ?')->execute([$u['id'], time()]);
                db()->prepare('INSERT INTO resets (token_hash, user_id, expires) VALUES (?,?,?)')->execute([hash('sha256', $token), $u['id'], time() + 3600]);
                send_mail($email, 'Atur ulang kata sandi Zakademia',
                    "Assalamu'alaikum " . $u['name'] . ",\n\nKami menerima permintaan untuk mengatur ulang kata sandi akun Zakademia Anda. Buka tautan berikut dalam 60 menit:\n\n"
                    . base_url() . '/reset.php?t=' . $token . "\n\nJika Anda tidak meminta ini, abaikan email ini; kata sandi Anda tidak berubah.\n\nSalam,\nTim Zakademia\n");
            }
        }
    }
}

page('Lupa kata sandi', $done ? '
<h1>Periksa email Anda</h1>
<p class="ok" role="status">Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi sudah kami kirim. Tautan berlaku 60 menit.</p>
<p class="mute">Belum menerima? Periksa folder Spam atau Promosi, lalu tunggu beberapa menit sebelum meminta lagi.</p>
<a class="btn ghost" href="masuk.php">Kembali ke halaman masuk</a>' : '
<h1>Lupa kata sandi</h1>
<p class="mute">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>
' . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="lupa.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '
  <label>Email<input type="email" name="email" required autocomplete="email" autofocus></label>
  <button class="btn" type="submit">Kirim tautan</button>
</form>
<p class="mute" style="text-align:center"><a href="masuk.php">Kembali ke halaman masuk</a></p>');
