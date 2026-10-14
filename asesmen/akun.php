<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();
$u = current_user();

/* Status masuk untuk formulir dan situs utama */
if (($_GET['api'] ?? '') === 'me') {
    header('Cache-Control: no-store');
    json_out($u ? ['ok' => true, 'user' => ['name' => $u['name'], 'email' => $u['email'], 'org' => $u['org'], 'type' => $u['org_type'], 'role' => $u['role'], 'id' => (int)$u['id']]] : ['ok' => true, 'user' => null]);
}
if (!$u) redirect('masuk.php?next=' . urlencode('/asesmen/akun.php'));

$error = '';
$ok = flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif ($act === 'profil') {
        $name = clip($_POST['name'] ?? '', 120);
        $org = clip($_POST['org'] ?? '', 200);
        $type = clip($_POST['org_type'] ?? '', 80);
        if ($name === '' || $org === '') $error = 'Nama dan nama lembaga wajib diisi.';
        elseif (!in_array($type, ORG_TYPES, true)) $error = 'Pilih jenis lembaga.';
        else {
            db()->prepare('UPDATE users SET name = ?, org = ?, org_type = ? WHERE id = ?')->execute([$name, $org, $type, $u['id']]);
            flash('Profil tersimpan.');
            redirect('akun.php');
        }
    } elseif ($act === 'sandi') {
        $q = db()->prepare('SELECT pass_hash FROM users WHERE id = ?');
        $q->execute([$u['id']]);
        $min = $u['role'] === 'admin' ? 10 : 8;
        $p1 = (string)($_POST['password'] ?? '');
        if (!password_verify((string)($_POST['old'] ?? ''), (string)$q->fetchColumn())) $error = 'Kata sandi lama salah.';
        elseif (mb_strlen($p1) < $min) $error = "Kata sandi baru minimal $min karakter.";
        elseif ($p1 !== (string)($_POST['password2'] ?? '')) $error = 'Kedua kata sandi baru tidak sama.';
        else {
            db()->prepare('UPDATE users SET pass_hash = ? WHERE id = ?')->execute([password_hash($p1, PASSWORD_DEFAULT), $u['id']]);
            login_user((int)$u['id']);
            flash('Kata sandi berhasil diganti.');
            redirect('akun.php');
        }
    }
}

$q = db()->prepare('SELECT dikirim, org, status, terkirim_pada FROM kiriman WHERE user_id = ? ORDER BY dikirim DESC');
$q->execute([$u['id']]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
$fmt = function (?string $iso): string {
    if (!$iso) return '–';
    $b = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = (new DateTime($iso))->setTimezone(new DateTimeZone('Asia/Jakarta'));
    return $t->format('j') . ' ' . $b[(int)$t->format('n') - 1] . ' ' . $t->format('Y, H.i');
};
$list = '';
foreach ($rows as $r) {
    $done = $r['status'] === 'terkirim';
    $list .= '<tr><td>' . e($fmt($r['dikirim'])) . '</td><td>' . e($r['org']) . '</td><td><span class="pill' . ($done ? ' done' : '') . '">'
        . ($done ? 'Hasil dikirim ' . e($fmt($r['terkirim_pada'])) : 'Sedang ditelaah') . '</span></td></tr>';
}
$opts = '';
foreach (ORG_TYPES as $t) $opts .= '<option' . ($t === $u['org_type'] ? ' selected' : '') . '>' . e($t) . '</option>';

page('Akun saya', '
<h1>Assalamu\'alaikum, ' . e($u['name']) . '</h1>
<p class="mute">' . e($u['email']) . ($u['org'] ? ' · ' . e($u['org']) : '') . '</p>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<div class="row" style="justify-content:flex-start">
  <a class="btn" href="/asesmen/">' . ($rows ? 'Isi asesmen lagi' : 'Mulai asesmen') . '</a>'
  . ($u['role'] === 'admin' ? '<a class="btn gold" href="admin.php">Dashboard admin</a>' : '') . '
</div>
<h2>Riwayat asesmen</h2>
' . ($rows ? '<div style="overflow-x:auto"><table><thead><tr><th>Dikirim</th><th>Lembaga</th><th>Status</th></tr></thead><tbody>' . $list . '</tbody></table></div>
<p class="mute">Hasil asesmen ditelaah tim Zakademia dan dikirim ke email Anda.</p>'
  : '<p class="mute">Belum ada asesmen yang dikirim dari akun ini.</p>') . '
<h2>Profil lembaga</h2>
<form method="post" action="akun.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '<input type="hidden" name="act" value="profil">
  <label>Nama lengkap<input name="name" value="' . e($u['name']) . '" required maxlength="120"></label>
  <label>Nama lembaga<input name="org" value="' . e($u['org']) . '" required maxlength="200"></label>
  <label>Jenis lembaga<select name="org_type">' . $opts . '</select></label>
  <button class="btn ghost" type="submit">Simpan profil</button>
</form>
<h2>Ganti kata sandi</h2>
<form method="post" action="akun.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '<input type="hidden" name="act" value="sandi">
  <label>Kata sandi lama<input type="password" name="old" required autocomplete="current-password"></label>
  <label>Kata sandi baru<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
  <label>Ulangi kata sandi baru<input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
  <button class="btn ghost" type="submit">Ganti kata sandi</button>
</form>
<p class="mute" style="text-align:center"><a href="keluar.php">Keluar</a></p>', '640px');
