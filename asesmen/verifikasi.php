<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '', '/asesmen/akun.php');
$u = current_user();
if (!$u) redirect('masuk.php?next=' . urlencode('/asesmen/verifikasi.php?next=' . $next));
if (user_verified($u)) redirect($next);

$key = 'u:' . $u['id'];
$email = $u['email'];
$error = '';
$info = '';
$act = $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['act'] ?? '') : '';
if ($act !== '' && !form_csrf_ok()) $act = 'csrf';

if ($act === 'csrf') {
    $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
} elseif ($act === 'kode') {
    $got = otp_check($key, (string)($_POST['kode'] ?? ''), $error);
    if ($got !== null && strcasecmp($got, $u['email']) === 0) {
        db()->prepare('UPDATE users SET verified = 1 WHERE id = ?')->execute([$u['id']]);
        flash('Email berhasil diverifikasi.');
        redirect($next);
    }
    if ($got !== null) $error = 'Kode ini untuk email lain. Minta kode baru.';
} elseif ($act === 'kirim') {
    $error = otp_send($key, $email, 'verifikasi akun Zakademia') ?? '';
    if ($error === '') $info = 'Kode baru sudah dikirim ke ' . $email . '.';
} elseif ($act === 'ganti') {
    $new = clip($_POST['email'] ?? '', 160);
    $q = db()->prepare('SELECT 1 FROM users WHERE email = ? AND id != ?');
    $q->execute([$new, $u['id']]);
    if ($p = email_problem($new)) $error = $p;
    elseif ($q->fetchColumn()) $error = 'Email ini sudah dipakai akun lain.';
    else {
        db()->prepare('UPDATE users SET email = ?, verified = 0 WHERE id = ?')->execute([$new, $u['id']]);
        db()->prepare('DELETE FROM otp WHERE k = ?')->execute([$key]);
        $email = $new;
        $error = otp_send($key, $email, 'verifikasi akun Zakademia') ?? '';
        if ($error === '') $info = 'Email diganti. Kode sudah dikirim ke ' . $email . '.';
    }
} else {
    // kunjungan pertama: kirim kode otomatis bila belum ada kode yang berlaku
    $q = db()->prepare('SELECT expires FROM otp WHERE k = ?');
    $q->execute([$key]);
    if ((int)$q->fetchColumn() < time() + 60) {
        $error = otp_send($key, $email, 'verifikasi akun Zakademia') ?? '';
        if ($error === '') $info = 'Kode verifikasi sudah dikirim ke ' . $email . '.';
    } else $info = 'Kode verifikasi sudah dikirim ke ' . $email . '.';
}

$hn = '<input type="hidden" name="next" value="' . e($next) . '">';
page('Verifikasi email', '
<h1>Verifikasi email</h1>
<p class="mute">Masukkan 6 angka kode yang dikirim ke <b>' . e($email) . '</b>.</p>
' . ($info ? '<p class="ok" role="status">' . e($info) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="verifikasi.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . $hn . '<input type="hidden" name="act" value="kode">
  <label>Kode verifikasi<input name="kode" inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]{6,7}" required autofocus style="font-size:22px;letter-spacing:.3em;text-align:center"></label>
  <button class="btn" type="submit">Verifikasi</button>
</form>
<p class="mute small">Belum menerima? Periksa folder Spam atau Promosi. Kode berlaku 15 menit.</p>
<form method="post" action="verifikasi.php">' . csrf_field() . $hn . '<input type="hidden" name="act" value="kirim"><button class="btn ghost" type="submit" style="width:100%">Kirim ulang kode</button></form>
<details><summary class="mute small" style="cursor:pointer">Salah menulis email?</summary>
<form method="post" action="verifikasi.php" style="display:flex;flex-direction:column;gap:10px;margin-top:10px">' . csrf_field() . $hn . '<input type="hidden" name="act" value="ganti">
  <label>Email yang benar<input type="email" name="email" required autocomplete="email"></label><button class="btn ghost" type="submit">Ganti dan kirim kode</button></form></details>');
