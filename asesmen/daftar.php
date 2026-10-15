<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '', '/asesmen/');
if (current_user()) redirect($next);

$f = ['name' => '', 'org' => '', 'org_type' => ORG_TYPES[0], 'email' => ''];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $_) $f[$k] = clip($_POST[$k] ?? '', $k === 'email' ? 160 : 200);
    $p1 = (string)($_POST['password'] ?? '');
    $p2 = (string)($_POST['password2'] ?? '');
    if (!empty($_POST['website'])) redirect('/');                       // jebakan robot
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!throttle_ok('daftar|' . ip_key(), 5, 3600)) $error = 'Terlalu banyak pendaftaran dari jaringan ini. Coba lagi dalam satu jam.';
    elseif ($f['name'] === '' || $f['org'] === '') $error = 'Nama dan nama lembaga wajib diisi.';
    elseif ($ep = email_problem($f['email'])) $error = $ep;
    elseif (!in_array($f['org_type'], ORG_TYPES, true)) $error = 'Pilih jenis lembaga.';
    elseif (mb_strlen($p1) < 8) $error = 'Kata sandi minimal 8 karakter.';
    elseif ($p1 !== $p2) $error = 'Kedua kata sandi tidak sama.';
    elseif (empty($_POST['setuju'])) $error = 'Centang persetujuan pengolahan data untuk melanjutkan.';
    else {
        $q = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $q->execute([$f['email']]);
        if ($q->fetchColumn()) {
            $error = 'Email ini sudah terdaftar. Silakan masuk, atau gunakan "Lupa kata sandi".';
        } else {
            throttle_hit('daftar|' . ip_key());
            db()->prepare('INSERT INTO users (email, name, org, org_type, role, pass_hash, created) VALUES (?,?,?,?,?,?,?)')
                ->execute([$f['email'], $f['name'], $f['org'], $f['org_type'], 'lembaga', password_hash($p1, PASSWORD_DEFAULT), gmdate('c')]);
            login_user((int)db()->lastInsertId());
            redirect('verifikasi.php?next=' . urlencode($next));
        }
    }
}

$opts = '';
foreach (ORG_TYPES as $t) $opts .= '<option' . ($t === $f['org_type'] ? ' selected' : '') . '>' . e($t) . '</option>';
page('Daftar', '
<h1>Buat akun lembaga</h1>
<p class="mute">Akun dipakai untuk mengisi asesmen dan melihat riwayat kiriman lembaga Anda.</p>
' . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="daftar.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '<input type="hidden" name="next" value="' . e($next) . '">
  <label>Nama lengkap<input name="name" value="' . e($f['name']) . '" required maxlength="120" autocomplete="name"></label>
  <label>Nama lembaga<input name="org" value="' . e($f['org']) . '" required maxlength="200" autocomplete="organization"></label>
  <label>Jenis lembaga<select name="org_type">' . $opts . '</select></label>
  <label>Email<input type="email" name="email" value="' . e($f['email']) . '" required maxlength="160" autocomplete="email"></label>
  <label>Kata sandi (minimal 8 karakter)<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
  <label>Ulangi kata sandi<input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
  <label class="hp" aria-hidden="true">Situs web<input name="website" tabindex="-1" autocomplete="off"></label>
  <label class="check"><input type="checkbox" name="setuju" value="1" required> Saya setuju data akun dan jawaban asesmen diolah oleh Zakademia untuk keperluan pendampingan lembaga.</label>
  <button class="btn" type="submit">Daftar</button>
</form>
<p class="mute" style="text-align:center">Sudah punya akun? <a href="masuk.php?next=' . urlencode($next) . '">Masuk</a></p>', '480px');
