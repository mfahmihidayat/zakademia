<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
require_once __DIR__ . '/../survei/lib_survei.php';
start_session();
$u = current_user();

/* Status masuk untuk formulir dan situs utama */
if (($_GET['api'] ?? '') === 'me') {
    header('Cache-Control: no-store');
    json_out($u ? ['ok' => true, 'user' => ['name' => $u['name'], 'email' => $u['email'], 'org' => $u['org'], 'type' => $u['org_type'], 'role' => $u['role'], 'id' => (int)$u['id'], 'verified' => user_verified($u)]] : ['ok' => true, 'user' => null]);
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
$q = sv_db()->prepare('SELECT COUNT(*), SUM(status = "aktif"), (SELECT COUNT(*) FROM responses r JOIN surveys x ON x.id = r.survey_id WHERE x.user_id = ?) FROM surveys WHERE user_id = ?');
$q->execute([$u['id'], $u['id']]);
[$nSv, $nAktif, $nJwb] = array_map('intval', $q->fetch(PDO::FETCH_NUM));
$svc = function (string $id, string $tag, string $name, string $info, array $btns) {
    $b = '';
    foreach ($btns as $i => [$label, $href]) $b .= '<a class="btn' . ($i ? ' ghost' : '') . '" href="' . e($href) . '" style="min-height:40px;padding:8px 16px;font-size:14px">' . e($label) . '</a>';
    return '<div id="' . $id . '" style="border:1px solid var(--line);border-radius:18px;padding:16px 18px;display:flex;flex-direction:column;gap:8px;background:var(--bg)">'
        . '<span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--mute);font-weight:700">' . e($tag) . '</span><b style="font-size:17px">' . e($name) . '</b>'
        . '<p class="mute" style="flex:1">' . $info . '</p><div style="display:flex;gap:8px;flex-wrap:wrap">' . $b . '</div></div>';
};
$services = '<h2>Layanan Anda</h2><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px">'
    . $svc('riset', 'Figur yang berlari', 'Riset Kebutuhan', $nSv ? e("$nSv survei ($nAktif aktif), $nJwb jawaban masuk.") : 'Susun kuesioner dengan 15 jenis pertanyaan, bagikan tautan pendek dan kode QR.', [['Buat Survei', '/survei/']])
    . $svc('data', 'Lengkung dasar', 'Data & Analitik', 'Kesehatan Penghimpunan: retensi, CLV, CAC, dan biaya per rupiah dari angka rekap tahunan, tanpa unggah data donatur.', [['Analisis Data', '/analitik/'], ['Hasil survei', '/survei/']])
    . $svc('strategi', 'Daun muda', 'Pendampingan Strategi', 'Pendampingan dimulai dengan Asesmen Kesiapan Penghimpunan (sekitar 15 menit).', [[ 'Mulai Asesmen', '/asesmen/']])
    . $svc('belajar', 'Daun terbuka', 'Ruang Belajar', 'Dua kamar belajar mandiri, Analitik dan Riset: modul singkat, kuis, dan praktik langsung dengan alat Zakademia.', [['Masuk Ruang Belajar', '/belajar/']])
    . '</div>';
$list = '';
foreach ($rows as $r) {
    $done = $r['status'] === 'terkirim';
    $list .= '<tr><td>' . e($fmt($r['dikirim'])) . '</td><td>' . e($r['org']) . '</td><td><span class="pill' . ($done ? ' done' : '') . '">'
        . ($done ? 'Hasil dikirim ' . e($fmt($r['terkirim_pada'])) : 'Sedang ditelaah') . '</span></td></tr>';
}
$rkHas = false;
try {
    $q = db()->prepare('SELECT 1 FROM rk_akses WHERE aktif = 1 AND (user_id = ? OR (user_id IS NULL AND email = ?)) LIMIT 1');
    $q->execute([$u['id'], $u['email']]);
    $rkHas = (bool)$q->fetchColumn();
} catch (Throwable $e) { $rkHas = false; }
$opts = '';
foreach (ORG_TYPES as $t) $opts .= '<option' . ($t === $u['org_type'] ? ' selected' : '') . '>' . e($t) . '</option>';

page('Akun saya', '
<h1>Assalamu\'alaikum, ' . e($u['name']) . '</h1>
<p class="mute">' . e($u['email']) . ($u['org'] ? ' · ' . e($u['org']) : '') . '</p>
' . (user_verified($u) ? '' : '<div class="err" role="status" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap"><span style="flex:1;min-width:200px">Email akun belum diverifikasi.</span><a class="btn" href="verifikasi.php" style="min-height:40px;padding:8px 16px;font-size:14px">Verifikasi sekarang</a></div>') . '
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
' . ($u['role'] === 'admin' ? '<div class="row" style="justify-content:flex-start"><a class="btn gold" href="admin.php">Dashboard admin asesmen</a><a class="btn ghost" href="/rekomendasi/admin.php">Pembeli rekomendasi</a><a class="btn ghost" href="email.php">Pengaturan email</a></div>' : '') . ($rkHas ? '<div class="ok" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap"><span style="flex:1;min-width:200px">Rekomendasi Asesmen lembaga Anda sudah dapat dibuka.</span><a class="btn" href="/rekomendasi/" style="min-height:40px;padding:8px 16px;font-size:14px">Buka rekomendasi</a></div>' : '') . $services . '
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
<p class="mute" style="text-align:center"><a href="keluar.php">Keluar</a></p>', '880px');
