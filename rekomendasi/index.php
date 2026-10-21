<?php
declare(strict_types=1);
/**
 * zakademia.com/rekomendasi — halaman pembeli Rekomendasi Asesmen (checkout lewat Lynk.id).
 * Alur: masuk dan verifikasi email → cek akses (webhook Lynk.id atau tambahan admin) → tampilkan hasil asesmen beserta rekomendasi rinci.
 */
require __DIR__ . '/lib_rekomendasi.php';
start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$self = '/rekomendasi/';
$u = current_user();
if (!$u) share_gate('rekomendasi', $self, '/asesmen/masuk.php?next=' . urlencode($self));
if (!user_verified($u)) redirect('/asesmen/verifikasi.php?next=' . urlencode($self));

$conf = rk_conf();
$error = '';
$info = flash();
$tahap = '';
$klaimEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif ($act === 'klaim') {
        $klaimEmail = clip($_POST['email'] ?? '', 160);
        if (!valid_email($klaimEmail)) $error = 'Alamat email belum benar.';
        elseif (!throttle_ok('rkklaim|' . $u['id'], 8, 3600)) $error = 'Terlalu banyak percobaan. Coba lagi dalam satu jam.';
        else {
            throttle_hit('rkklaim|' . $u['id']);
            if (!rk_unclaimed($klaimEmail)) $error = 'Pembelian dengan email ini belum tercatat atau sudah dipakai akun lain. Bila baru membayar, tunggu beberapa menit lalu coba lagi, atau hubungi info@zakademia.com.';
            else {
                $err = otp_send('rk|' . $u['id'], $klaimEmail, 'membuka Rekomendasi Asesmen');
                if ($err) $error = $err;
                else { $_SESSION['rk_klaim'] = strtolower($klaimEmail); $tahap = 'kode'; $info = 'Kode verifikasi sudah dikirim ke ' . $klaimEmail . '.'; }
            }
        }
    } elseif ($act === 'kode') {
        $klaimEmail = (string)($_SESSION['rk_klaim'] ?? '');
        $email = otp_check('rk|' . $u['id'], (string)($_POST['kode'] ?? ''), $err);
        if (!$email || $email !== $klaimEmail) { $error = $err ?: 'Kode tidak cocok.'; $tahap = 'kode'; }
        elseif ($row = rk_unclaimed($email)) {
            rk_db()->prepare('UPDATE rk_akses SET user_id = ?, diklaim = ? WHERE id = ?')->execute([(int)$u['id'], gmdate('c'), $row['id']]);
            unset($_SESSION['rk_klaim']);
            flash('Akses berhasil dibuka untuk akun ini.');
            redirect($self);
        } else $error = 'Pembelian ini sudah dipakai akun lain.';
    }
}

$akses = rk_access($u);
$buy = $conf['lynk_url'] !== '' ? '<a class="btn gold" href="' . e($conf['lynk_url']) . '" target="_blank" rel="noopener">Beli di Lynk.id</a>' : '';

if (!$akses) {
    $form = $tahap === 'kode'
        ? '<form method="post" action="' . $self . '" style="display:flex;flex-direction:column;gap:14px">' . csrf_field() . '<input type="hidden" name="act" value="kode">
            <label>Kode verifikasi dari ' . e($klaimEmail) . '<input name="kode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus></label>
            <button class="btn" type="submit">Buka akses</button></form>'
        : '<form method="post" action="' . $self . '" style="display:flex;flex-direction:column;gap:14px">' . csrf_field() . '<input type="hidden" name="act" value="klaim">
            <label>Email yang dipakai saat membayar di Lynk.id<input type="email" name="email" value="' . e($klaimEmail) . '" required autocomplete="email"></label>
            <button class="btn ghost" type="submit">Kirim kode verifikasi</button></form>';
    page('Rekomendasi Asesmen', '
<h1>Rekomendasi Asesmen</h1>
' . ($info ? '<p class="ok" role="status">' . e($info) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<p class="mute">Akses untuk ' . e($u['email']) . ' belum ditemukan. Akses terbuka otomatis bila pembayaran di Lynk.id memakai email yang sama dengan akun ini.</p>
' . ($buy ? '<div class="row" style="justify-content:flex-start">' . $buy . '</div>' : '') . '
<h2>Sudah membayar dengan email lain?</h2>
<p class="mute">Masukkan email tersebut. Kami kirim kode ke email itu untuk memastikan pembelian milik Anda.</p>
' . $form, '520px');
}

$rec = rk_latest_kiriman($u);
if (!$rec) {
    page('Rekomendasi Asesmen', '
<h1>Satu langkah lagi</h1>
' . ($info ? '<p class="ok" role="status">' . e($info) . '</p>' : '') . '
<p class="mute">Akses Rekomendasi Asesmen sudah aktif. Rekomendasi disusun dari jawaban asesmen, tetapi belum ada asesmen yang dikirim dari akun ' . e($u['email']) . '.</p>
<p class="mute">Isi asesmen sekitar 15 menit. Setelah terkirim, buka kembali halaman ini.</p>
<div class="row" style="justify-content:flex-start"><a class="btn" href="/asesmen/">Isi asesmen</a><a class="btn ghost" href="' . $self . '">Muat ulang</a></div>', '520px');
}

/* ---------- tampilkan hasil asesmen dan rekomendasi rinci (memakai tampilan dashboard admin, mode pembeli) ---------- */
$html = (string)file_get_contents(__DIR__ . '/../asesmen/views/dashboard.html');
$recJson = json_encode($rec, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$swap = [
    ['<title>Dashboard Asesmen Zakademia</title>', '<title>Rekomendasi Asesmen · Zakademia</title>'],
    ['<span class="sub">Dashboard Asesmen Kesiapan Penghimpunan</span>', '<span class="sub">Rekomendasi Asesmen Kesiapan Penghimpunan</span>'],
    ['Bahan sesi diskusi dengan lembaga. Bagian ini tidak ikut dalam email hasil.', 'Langkah prioritas lembaga Anda, disusun dari jawaban asesmen.'],
    ['<span class="pill warn">Khusus admin</span>', ''],
    ["'5. Dikonfirmasi saat diskusi'", "'5. Perlu dicek bersama tim Zakademia'"],
    ["(withReco?'-internal':'')", "'-rekomendasi'"],
    ['</style>', '#viewInbox,#emailCard,#resBack,.pdfopt{display:none!important}' . "\n</style>"],
    ["var BACKEND={list:function(){return api('list');}", 'var BACKEND={list:function(){return Promise.resolve([]);}'],
    ["\nload();\n})();", "\nvar REC=$recJson;document.getElementById('pdfReco').checked=true;openResult(REC);\n})();"],
    ['__CSRF__', ''],
];
$missing = [];
foreach ($swap as [$a, $b]) {
    if (!str_contains($html, $a)) { $missing[] = $a; continue; }
    $html = str_replace($a, $b, $html);
}
$html = preg_replace('#<nav class="adminbar">.*?</nav>#s', '<nav class="adminbar"><a class="btn btn-ghost" href="/asesmen/akun.php">Akun saya</a><a class="btn btn-ghost" href="/asesmen/keluar.php">Keluar</a></nav>', $html, 1);
if ($missing) {
    error_log('rekomendasi: tampilan dashboard berubah, penanda tidak ditemukan: ' . implode(' | ', $missing));
    page('Rekomendasi Asesmen', '<h1>Halaman sedang diperbarui</h1><p class="mute">Rekomendasi belum dapat ditampilkan. Tim Zakademia sudah mendapat catatan galat ini. Hubungi info@zakademia.com bila perlu segera.</p>');
}
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
echo $html;
