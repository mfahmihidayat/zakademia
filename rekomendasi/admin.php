<?php
declare(strict_types=1);
/** Pengelolaan akses Rekomendasi Asesmen (khusus admin). */
require __DIR__ . '/lib_rekomendasi.php';
start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/rekomendasi/admin.php'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');

$conf = rk_conf();
$pdo = rk_db();
$error = '';
$ok = flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif ($act === 'atur') {
        $url = clip($_POST['lynk_url'] ?? '', 300);
        if ($url !== '' && !preg_match('#^https://#', $url)) $error = 'Tautan Lynk.id harus diawali https://';
        else {
            $conf['lynk_url'] = $url;
            $conf['kata_kunci'] = clip($_POST['kata_kunci'] ?? '', 80);
            rk_conf_save($conf);
            flash('Pengaturan tersimpan.');
            redirect('admin.php');
        }
    } elseif ($act === 'kunci') {
        $conf['kunci'] = bin2hex(random_bytes(20));
        rk_conf_save($conf);
        flash('Kunci webhook diganti. Perbarui URL webhook di Lynk.id.');
        redirect('admin.php');
    } elseif ($act === 'tambah') {
        $email = clip($_POST['email'] ?? '', 160);
        $ref = clip($_POST['ref'] ?? '', 120);
        if (!valid_email($email)) $error = 'Alamat email belum benar.';
        else {
            $g = rk_grant($email, $ref, 'Rekomendasi Asesmen', '', 'manual', clip($_POST['catatan'] ?? '', 200));
            if (!$g['baru']) $error = 'Nomor pesanan ini sudah tercatat untuk ' . $g['row']['email'] . '.';
            else {
                $sent = !empty($_POST['kirim']) ? (rk_mail_buyer($email) ? ' Email akses terkirim.' : ' Email akses gagal dikirim.') : '';
                flash('Akses untuk ' . $email . ' ditambahkan.' . $sent);
                redirect('admin.php');
            }
        }
    } elseif ($act === 'aktif') {
        $pdo->prepare('UPDATE rk_akses SET aktif = ? WHERE id = ?')->execute([(int)!empty($_POST['nilai']), (int)($_POST['id'] ?? 0)]);
        flash('Status akses diperbarui.');
        redirect('admin.php');
    }
}

$fmt = function (?string $iso): string {
    if (!$iso) return '–';
    $b = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = (new DateTime($iso))->setTimezone(new DateTimeZone('Asia/Jakarta'));
    return $t->format('j') . ' ' . $b[(int)$t->format('n') - 1] . ' ' . $t->format('Y, H.i');
};
$hook = rk_site() . '/rekomendasi/webhook.php?k=' . $conf['kunci'];

$rows = $pdo->query('SELECT a.*, u.email AS akun FROM rk_akses a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 300')->fetchAll(PDO::FETCH_ASSOC);
$t = '';
foreach ($rows as $r) {
    $t .= '<tr><td>' . e($fmt($r['dibuat'])) . '</td><td>' . e($r['email']) . ($r['ref'] ? '<br><small class="mute">' . e($r['ref']) . '</small>' : '') . '</td><td>' . e($r['sumber']) . '</td><td>'
        . ($r['akun'] ? e($r['akun']) : '<span class="pill">Belum diklaim</span>') . '</td><td>'
        . '<form method="post" action="admin.php">' . csrf_field() . '<input type="hidden" name="act" value="aktif"><input type="hidden" name="id" value="' . (int)$r['id'] . '"><input type="hidden" name="nilai" value="' . ($r['aktif'] ? '' : '1') . '">'
        . '<button class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" type="submit">' . ($r['aktif'] ? 'Cabut' : 'Aktifkan') . '</button></form></td></tr>';
}
$logs = $pdo->query('SELECT * FROM rk_webhook ORDER BY id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
$l = '';
foreach ($logs as $g) {
    $l .= '<details style="border-bottom:1px solid var(--line);padding:8px 0"><summary style="cursor:pointer">' . e($fmt($g['diterima'])) . ' · ' . e($g['hasil']) . ($g['email'] ? ' · ' . e($g['email']) : '') . '</summary>'
        . '<pre style="white-space:pre-wrap;word-break:break-all;font-size:12px;background:var(--mist);padding:10px;border-radius:10px">' . e($g['isi']) . "\n\nHeader: " . e($g['header']) . '</pre></details>';
}

page('Rekomendasi Asesmen · Admin', '
<h1>Rekomendasi Asesmen</h1>
<p class="mute">Pembeli di Lynk.id membuka zakademia.com/rekomendasi, masuk dengan email pembelian, lalu melihat hasil asesmen beserta rekomendasi rinci.</p>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<h2>1. Webhook Lynk.id</h2>
<p class="mute">Salin URL ini ke Lynk.id: Settings → Integrations → Webhooks → Save URL. Setiap transaksi sukses akan membuka akses otomatis dan mengirim email ke pembeli.</p>
<input readonly value="' . e($hook) . '" onclick="this.select()" style="font-size:13px">
<form method="post" action="admin.php">' . csrf_field() . '<input type="hidden" name="act" value="kunci"><button class="btn ghost" type="submit" onclick="return confirm(\'Ganti kunci? URL lama di Lynk.id tidak berlaku lagi.\')">Buat kunci baru</button></form>
<h2>2. Pengaturan</h2>
<form method="post" action="admin.php" style="display:flex;flex-direction:column;gap:14px">' . csrf_field() . '<input type="hidden" name="act" value="atur">
  <label>Tautan produk di Lynk.id (tombol "Beli" untuk yang belum punya akses)<input name="lynk_url" value="' . e($conf['lynk_url']) . '" placeholder="https://lynk.id/zakademia/..."></label>
  <label>Kata kunci nama produk (transaksi produk lain diabaikan; kosongkan untuk menerima semua)<input name="kata_kunci" value="' . e($conf['kata_kunci']) . '"></label>
  <button class="btn ghost" type="submit">Simpan pengaturan</button>
</form>
<h2>3. Tambah akses manual</h2>
<p class="mute">Untuk pembayaran yang tidak masuk lewat webhook, misalnya transfer langsung.</p>
<form method="post" action="admin.php" style="display:flex;flex-direction:column;gap:14px">' . csrf_field() . '<input type="hidden" name="act" value="tambah">
  <label>Email pembeli<input type="email" name="email" required></label>
  <label>Nomor pesanan (opsional)<input name="ref" maxlength="120"></label>
  <label>Catatan (opsional)<input name="catatan" maxlength="200"></label>
  <label class="check"><input type="checkbox" name="kirim" value="1" checked> Kirim email berisi tautan akses ke pembeli</label>
  <button class="btn" type="submit">Tambah akses</button>
</form>
<h2>4. Daftar akses</h2>
' . ($rows ? '<div style="overflow-x:auto"><table><thead><tr><th>Dibuat</th><th>Email pembelian</th><th>Sumber</th><th>Akun</th><th></th></tr></thead><tbody>' . $t . '</tbody></table></div>' : '<p class="mute">Belum ada pembeli.</p>') . '
<h2>5. Catatan webhook terakhir</h2>
<p class="mute">Isi mentah dari Lynk.id, untuk memeriksa bila ada transaksi yang tidak terbaca.</p>
' . ($l ?: '<p class="mute">Belum ada kiriman webhook.</p>') . '
<p class="mute" style="text-align:center"><a href="/admin/">Pusat kendali</a> · <a href="/asesmen/admin.php">Dashboard asesmen</a> · <a href="/asesmen/akun.php">Akun saya</a></p>', '980px');
