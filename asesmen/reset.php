<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();

$token = (string)($_GET['t'] ?? $_POST['t'] ?? '');
$row = null;
if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $q = db()->prepare('SELECT r.user_id, u.role FROM resets r JOIN users u ON u.id = r.user_id WHERE r.token_hash = ? AND r.used = 0 AND r.expires > ?');
    $q->execute([hash('sha256', $token), time()]);
    $row = $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
if (!$row) page('Tautan tidak berlaku', '
<h1>Tautan tidak berlaku</h1>
<p class="mute">Tautan sudah kedaluwarsa atau sudah dipakai. Silakan minta tautan baru.</p>
<a class="btn" href="lupa.php">Minta tautan baru</a>');

$min = $row['role'] === 'admin' ? 10 : 8;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p1 = (string)($_POST['password'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (mb_strlen($p1) < $min) $error = "Kata sandi minimal $min karakter.";
    elseif ($p1 !== (string)($_POST['password2'] ?? '')) $error = 'Kedua kata sandi tidak sama.';
    else {
        $uid = (int)$row['user_id'];
        db()->prepare('UPDATE users SET pass_hash = ? WHERE id = ?')->execute([password_hash($p1, PASSWORD_DEFAULT), $uid]);
        db()->prepare('DELETE FROM resets WHERE user_id = ?')->execute([$uid]);
        $q = db()->prepare('SELECT email FROM users WHERE id = ?');
        $q->execute([$uid]);
        throttle_clear('login|' . strtolower((string)$q->fetchColumn()));
        login_user($uid);
        flash('Kata sandi berhasil diganti.');
        redirect($row['role'] === 'admin' ? 'admin.php' : 'akun.php');
    }
}

page('Kata sandi baru', '
<h1>Buat kata sandi baru</h1>
<p class="mute">Kata sandi minimal ' . $min . ' karakter. Setelah disimpan, Anda langsung masuk.</p>
' . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="reset.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '<input type="hidden" name="t" value="' . e($token) . '">
  <label>Kata sandi baru<input type="password" name="password" required minlength="' . $min . '" autocomplete="new-password" autofocus></label>
  <label>Ulangi kata sandi baru<input type="password" name="password2" required minlength="' . $min . '" autocomplete="new-password"></label>
  <button class="btn" type="submit">Simpan kata sandi</button>
</form>');
