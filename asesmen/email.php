<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
start_session();
$u = current_user();
if (!$u) redirect('masuk.php?next=' . urlencode('/asesmen/email.php'));
if ($u['role'] !== 'admin') page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="akun.php">Ke Akun saya</a>');

$c = smtp_conf() ?? ['host' => 'smtp.hostinger.com', 'port' => 465, 'user' => (string)cfg('from_email'), 'pass' => ''];
$on = smtp_conf() !== null;
$error = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif ($act === 'simpan') {
        $n = ['host' => clip($_POST['host'] ?? '', 120), 'port' => (int)($_POST['port'] ?? 465), 'user' => clip($_POST['user'] ?? '', 160),
              'pass' => (string)($_POST['pass'] ?? '') !== '' ? (string)$_POST['pass'] : (string)($c['pass'] ?? '')];
        if (!preg_match('/^[a-z0-9.-]+$/i', $n['host']) || !in_array($n['port'], [465, 587], true) || !valid_email($n['user']) || $n['pass'] === '') $error = 'Lengkapi server, port (465 atau 587), email, dan kata sandi kotak surat.';
        else {
            $GLOBALS['smtp_error'] = '';
            $test = smtp_send($n, $u['email'], '=?UTF-8?B?' . base64_encode('Uji pengiriman email Zakademia') . '?=',
                "Email uji dari zakademia.com melalui SMTP berhasil terkirim.\n\nKode verifikasi dan tautan atur ulang kata sandi kini dikirim melalui kotak surat ini.\n",
                ['From: Zakademia <' . $n['user'] . '>', 'MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8', 'Content-Transfer-Encoding: 8bit']);
            if ($test) {
                file_put_contents(smtp_file(), json_encode($n));
                @chmod(smtp_file(), 0600);
                $c = $n; $on = true;
                $ok = 'Tersimpan. Email uji dikirim ke ' . $u['email'] . '; periksa kotak masuk atau folder Spam.';
            } else $error = 'Belum tersimpan karena email uji gagal: ' . ($GLOBALS['smtp_error'] ?: 'periksa pengaturan.');
        }
    } elseif ($act === 'hapus') {
        @unlink(smtp_file());
        $on = false; $c['pass'] = '';
        $ok = 'Pengaturan SMTP dihapus. Email kembali dikirim dengan fungsi bawaan hosting.';
    } elseif ($act === 'uji') {
        $sent = send_mail($u['email'], 'Uji pengiriman email Zakademia', "Email uji dari zakademia.com.\nMetode: " . ($on ? 'SMTP' : 'fungsi bawaan hosting') . "\n");
        $sent ? $ok = 'Email uji dikirim ke ' . $u['email'] . '. Bila tidak sampai dalam beberapa menit (periksa Spam), aktifkan SMTP.' : $error = 'Email uji gagal dikirim. ' . ($GLOBALS['smtp_error'] ?? '');
    }
}

page('Pengaturan email', '
<h1>Pengaturan pengiriman email</h1>
<p class="mute">Kode verifikasi dan tautan atur ulang kata sandi harus benar-benar sampai. Pengiriman melalui SMTP kotak surat Hostinger lebih andal daripada fungsi bawaan hosting.</p>
<p class="' . ($on ? 'ok' : 'err') . '">Status: ' . ($on ? 'SMTP aktif (' . e($c['user']) . ')' : 'memakai fungsi bawaan hosting; SMTP belum diatur') . '</p>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="email.php" style="display:flex;flex-direction:column;gap:12px" autocomplete="off">
  ' . csrf_field() . '<input type="hidden" name="act" value="simpan">
  <div class="row" style="align-items:flex-end"><label style="flex:2;min-width:180px">Server SMTP<input name="host" value="' . e($c['host']) . '" required></label>
  <label style="flex:1;min-width:110px">Port<select name="port"><option value="465"' . ((int)$c['port'] === 465 ? ' selected' : '') . '>465 (SSL)</option><option value="587"' . ((int)$c['port'] === 587 ? ' selected' : '') . '>587 (TLS)</option></select></label></div>
  <label>Email kotak surat<input type="email" name="user" value="' . e($c['user']) . '" required></label>
  <label>Kata sandi kotak surat<input type="password" name="pass" autocomplete="new-password" placeholder="' . ($on ? 'Kosongkan bila tidak diubah' : 'Kata sandi email info@zakademia.com') . '"' . ($on ? '' : ' required') . '></label>
  <p class="mute small">Kata sandi hanya disimpan di folder data server yang terlindung, tidak di GitHub. Sebelum disimpan, sistem mengirim email uji ke ' . e($u['email']) . '.</p>
  <button class="btn" type="submit">Simpan dan kirim email uji</button>
</form>
<div class="row"><form method="post" action="email.php">' . csrf_field() . '<input type="hidden" name="act" value="uji"><button class="btn ghost" type="submit">Kirim email uji saja</button></form>'
 . ($on ? '<form method="post" action="email.php">' . csrf_field() . '<input type="hidden" name="act" value="hapus"><button class="btn ghost" type="submit">Hapus pengaturan SMTP</button></form>' : '') . '</div>
<p class="mute small"><a href="akun.php">← Akun saya</a> · <a href="admin.php">Dashboard admin</a></p>', '560px');
