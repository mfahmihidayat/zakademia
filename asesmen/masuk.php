<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '', '');
if (!empty($_GET['t'])) {
    $tujuan = tautan_pakai(preg_replace('/[^a-f0-9]/', '', (string)$_GET['t']));
    if ($tujuan === null) { flash('Tautan masuk sudah dipakai atau kedaluwarsa. Minta tautan baru.'); redirect('masuk.php'); }
    $u = current_user(); redirect(safe_next($tujuan, '') ?: ($u && $u['role'] === 'admin' ? '/admin/' : 'akun.php'));
}
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
    } elseif (($_POST['act'] ?? '') === 'tautan') {
        if (!valid_email($email)) $error = 'Alamat email belum benar.';
        else { $e = tautan_kirim($email, $next); if ($e) $error = $e; else { flash('Bila email itu terdaftar, tautan masuk sudah dikirim. Buka email Anda dan klik tautannya (berlaku 30 menit). Belum punya akun? Daftar dulu.'); redirect('masuk.php' . ($next ? '?next=' . urlencode($next) : '')); } }
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
<p class="mute">Masuk untuk mengikuti kelas, membaca buku, menulis, atau mengisi asesmen.</p>
' . ($msg ? '<p class="ok" role="status">' . e($msg) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="masuk.php" style="display:flex;flex-direction:column;gap:14px" id="fMasuk">
  ' . csrf_field() . '<input type="hidden" name="next" value="' . e($next) . '"><input type="hidden" name="act" value="tautan" id="actMasuk">
  <label>Email<input type="email" name="email" value="' . e($email) . '" required autocomplete="email" autofocus></label>
  <button class="btn" type="submit">Kirim tautan masuk ke email</button>
  <p class="mute" style="font-size:13px;text-align:center">Tanpa kata sandi: kami kirim tautan sekali pakai ke email Anda. <a href="#" id="pakaiSandi">Masuk dengan kata sandi</a></p>
  <div id="kotakSandi" hidden style="display:flex;flex-direction:column;gap:14px">
    <label>Kata sandi<input type="password" name="password" autocomplete="current-password" id="sandi"></label>
    <div class="row"><span></span><a href="lupa.php">Lupa kata sandi?</a></div>
    <button class="btn ghost" type="submit" id="btnSandi">Masuk dengan kata sandi</button>
  </div>
</form>
<script>(function(){var k=document.getElementById("kotakSandi"),a=document.getElementById("actMasuk"),s=document.getElementById("sandi");document.getElementById("pakaiSandi").onclick=function(e){e.preventDefault();k.hidden=!k.hidden;k.style.display=k.hidden?"none":"flex";a.value=k.hidden?"tautan":"sandi";s.required=!k.hidden;if(!k.hidden)s.focus();};document.getElementById("btnSandi").onclick=function(){a.value="sandi";s.required=true;};k.style.display="none";})();</script>
<p class="mute" style="text-align:center">Belum punya akun? <a href="daftar.php' . $nq . '">Daftar sekarang</a></p>');
