<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '', '');
$u = current_user();
if ($u) redirect($next ?: ($u['role'] === 'admin' ? '/admin/' : 'akun.php'));

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clip($_POST['email'] ?? '', 160);
    $key = 'login|' . ip_key();
    $keyE = 'login|' . strtolower($email);
    if (!form_csrf_ok()) {
        $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    } elseif (!throttle_ok($key, 10) || !throttle_ok($keyE, 6)) {
        $error = 'Terlalu banyak percobaan masuk. Tunggu 15 menit, atau gunakan "Lupa kata sandi".';
    } else {
        $q = db()->prepare('SELECT id, role, pass_hash, verified FROM users WHERE email = ?');
        $q->execute([$email]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify((string)($_POST['password'] ?? ''), $row['pass_hash'])) {
            if (password_needs_rehash($row['pass_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE users SET pass_hash = ? WHERE id = ?')->execute([password_hash((string)$_POST['password'], PASSWORD_DEFAULT), $row['id']]);
            }
            throttle_clear($keyE);
            login_user((int)$row['id']);
            $dest = $next ?: ($row['role'] === 'admin' ? '/admin/' : 'akun.php');
            if ($row['role'] !== 'admin' && empty($row['verified'])) redirect('verifikasi.php?next=' . urlencode(str_starts_with($dest, '/') ? $dest : '/asesmen/' . $dest));
            redirect($dest);
        }
        throttle_hit($key);
        throttle_hit($keyE);
        $error = 'Email atau kata sandi tidak cocok.';
    }
}

$msg = flash() ?? (isset($_GET['keluar']) ? 'Anda sudah keluar.' : null);
$nq = $next ? '?next=' . urlencode($next) : '';
page('Masuk', '
<h1>Masuk</h1>
<p class="mute">Masuk untuk mengisi asesmen, melihat riwayat kiriman, atau membuka dashboard admin.</p>
' . ($msg ? '<p class="ok" role="status">' . e($msg) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="masuk.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '<input type="hidden" name="next" value="' . e($next) . '">
  <label>Email<input type="email" name="email" value="' . e($email) . '" required autocomplete="email" autofocus></label>
  <label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
  <div class="row"><span></span><a href="lupa.php">Lupa kata sandi?</a></div>
  <button class="btn" type="submit">Masuk</button>
</form>
<p class="mute" style="text-align:center">Belum punya akun? <a href="daftar.php' . $nq . '">Daftar sekarang</a></p>');
