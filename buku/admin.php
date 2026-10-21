<?php
declare(strict_types=1);
/** Pengelolaan buku digital (khusus admin): tautan Lynk.id, harga, pratinjau, akses manual (misalnya pembayaran transfer), catatan webhook. */
require __DIR__ . '/lib_buku.php';
start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/buku/admin.php'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');

$conf = bk_conf();
$pdo = bk_db();
$error = '';
$ok = flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    $slug = bk_slug($_POST['buku'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif ($act === 'atur' && $slug) {
        $url = clip($_POST['lynk_url'] ?? '', 300);
        $prat = (int)($_POST['pratinjau'] ?? 0);
        if ($url !== '' && !preg_match('#^https://#', $url)) $error = 'Tautan Lynk.id harus diawali https://';
        elseif ($prat < 1 || $prat > 200) $error = 'Jumlah halaman pratinjau antara 1 dan 200.';
        else {
            $conf['buku'][$slug]['lynk_url'] = $url;
            $conf['buku'][$slug]['harga'] = clip($_POST['harga'] ?? '', 40);
            $conf['buku'][$slug]['kata_kunci'] = clip($_POST['kata_kunci'] ?? '', 80);
            $conf['buku'][$slug]['pratinjau'] = $prat;
            bk_conf_save($conf);
            flash('Pengaturan ' . BK_BUKU[$slug]['judul'] . ' tersimpan.');
            redirect('admin.php');
        }
    } elseif ($act === 'kunci') {
        $conf['kunci'] = bin2hex(random_bytes(20));
        bk_conf_save($conf);
        flash('Kunci webhook diganti. Perbarui URL webhook di Lynk.id.');
        redirect('admin.php');
    } elseif ($act === 'tambah' && $slug) {
        $email = clip($_POST['email'] ?? '', 160);
        $ref = clip($_POST['ref'] ?? '', 120);
        if (!valid_email($email)) $error = 'Alamat email belum benar.';
        else {
            $g = bk_grant($slug, $email, $ref, BK_BUKU[$slug]['judul'], clip($_POST['nilai'] ?? '', 40), 'manual', clip($_POST['catatan'] ?? '', 200));
            if (!$g['baru']) $error = 'Nomor pesanan ini sudah tercatat untuk ' . $g['row']['email'] . '.';
            else {
                $sent = !empty($_POST['kirim']) ? (bk_mail_buyer($slug, $email) ? ' Email akses terkirim.' : ' Email akses gagal dikirim.') : '';
                flash('Akses ' . BK_BUKU[$slug]['judul'] . ' untuk ' . $email . ' ditambahkan.' . $sent);
                redirect('admin.php');
            }
        }
    } elseif ($act === 'aktif') {
        $pdo->prepare('UPDATE bk_akses SET aktif = ? WHERE id = ?')->execute([(int)!empty($_POST['nilai']), (int)($_POST['id'] ?? 0)]);
        flash('Status akses diperbarui.');
        redirect('admin.php');
    } elseif ($act === 'kirim') {
        $q = $pdo->prepare('SELECT * FROM bk_akses WHERE id = ?'); $q->execute([(int)($_POST['id'] ?? 0)]);
        if ($r = $q->fetch(PDO::FETCH_ASSOC)) flash(bk_mail_buyer($r['buku'], $r['email']) ? 'Email akses dikirim ulang ke ' . $r['email'] . '.' : 'Email gagal dikirim.');
        redirect('admin.php');
    }
}

$fmt = function (?string $iso): string {
    if (!$iso) return '–';
    $b = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = (new DateTime($iso))->setTimezone(new DateTimeZone('Asia/Jakarta'));
    return $t->format('j') . ' ' . $b[(int)$t->format('n') - 1] . ' ' . $t->format('Y, H.i');
};
$hook = rk_site() . '/buku/webhook.php?k=' . $conf['kunci'];
$opsi = '';
foreach (BK_BUKU as $s => $bk) $opsi .= '<option value="' . e($s) . '">' . e($bk['judul']) . '</option>';

$atur = '';
foreach (BK_BUKU as $s => $bk) {
    $c = $conf['buku'][$s]; $meta = bk_meta($s);
    $atur .= '<form method="post" action="admin.php" style="display:flex;flex-direction:column;gap:14px;border:1px solid var(--line);border-radius:16px;padding:16px">' . csrf_field() . '<input type="hidden" name="act" value="atur"><input type="hidden" name="buku" value="' . e($s) . '">
  <div class="row"><b>' . e($bk['judul']) . '</b><span class="mute">' . ($meta ? (int)$meta['jumlah'] . ' halaman · <a href="/buku/' . e($s) . '/" target="_blank">halaman buku</a>' : 'gambar halaman belum diunggah') . '</span></div>
  <label>Tautan produk di Lynk.id (tombol "Beli")<input name="lynk_url" value="' . e($c['lynk_url']) . '" placeholder="https://lynk.id/zakademia/..."></label>
  <label>Harga yang ditampilkan (teks, misalnya Rp149.000)<input name="harga" value="' . e($c['harga']) . '" maxlength="40"></label>
  <label>Kata kunci nama produk di Lynk.id (untuk mengenali transaksi buku ini dari webhook)<input name="kata_kunci" value="' . e($c['kata_kunci']) . '" maxlength="80"></label>
  <label>Jumlah halaman pratinjau yang terbuka untuk semua orang<input name="pratinjau" type="number" min="1" max="200" value="' . (int)$c['pratinjau'] . '"></label>
  <button class="btn ghost" type="submit">Simpan</button></form>';
}

$rows = $pdo->query('SELECT a.*, u.email AS akun FROM bk_akses a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 300')->fetchAll(PDO::FETCH_ASSOC);
$t = '';
foreach ($rows as $r) {
    $t .= '<tr><td>' . e($fmt($r['dibuat'])) . '</td><td>' . e(BK_BUKU[$r['buku']]['judul'] ?? $r['buku']) . '</td><td>' . e($r['email']) . ($r['ref'] ? '<br><small class="mute">' . e($r['ref']) . '</small>' : '') . ($r['catatan'] ? '<br><small class="mute">' . e($r['catatan']) . '</small>' : '') . '</td><td>' . e($r['sumber']) . '</td><td>'
        . ($r['akun'] ? e($r['akun']) . ($r['terakhir'] ? '<br><small class="mute">dibaca ' . e($fmt($r['terakhir'])) . '</small>' : '') : '<span class="pill">Belum diklaim</span>') . '</td><td style="white-space:nowrap">'
        . '<form method="post" action="admin.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="aktif"><input type="hidden" name="id" value="' . (int)$r['id'] . '"><input type="hidden" name="nilai" value="' . ($r['aktif'] ? '' : '1') . '">'
        . '<button class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" type="submit">' . ($r['aktif'] ? 'Cabut' : 'Aktifkan') . '</button></form> '
        . '<form method="post" action="admin.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="kirim"><input type="hidden" name="id" value="' . (int)$r['id'] . '">'
        . '<button class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" type="submit" title="Kirim ulang email akses">Email</button></form></td></tr>';
}
$logs = $pdo->query('SELECT * FROM bk_webhook ORDER BY id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
$l = '';
foreach ($logs as $g) {
    $l .= '<details style="border-bottom:1px solid var(--line);padding:8px 0"><summary style="cursor:pointer">' . e($fmt($g['diterima'])) . ' · ' . e($g['hasil']) . ($g['email'] ? ' · ' . e($g['email']) : '') . '</summary>'
        . '<pre style="white-space:pre-wrap;word-break:break-all;font-size:12px;background:var(--mist);padding:10px;border-radius:10px">' . e($g['isi']) . "\n\nHeader: " . e($g['header']) . '</pre></details>';
}

page('Buku digital · Admin', '
<h1>Buku digital</h1>
<p class="mute">Pembeli membayar di Lynk.id, lalu membuka zakademia.com/buku dan masuk dengan email pembelian. Pembayaran lewat transfer ditambahkan manual di bagian 3.</p>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<h2>1. Webhook Lynk.id</h2>
<p class="mute">Lynk.id hanya menerima satu URL webhook per akun. Kalau URL webhook Rekomendasi Asesmen sudah terdaftar di Lynk.id, <b>tidak perlu menambah apa pun</b>: URL itu juga mengenali produk buku dari kata kuncinya dan membuka akses otomatis. URL di bawah ini cadangan kalau ingin memakai webhook khusus buku (Settings → Integrations → Webhooks).</p>
<input readonly value="' . e($hook) . '" onclick="this.select()" style="font-size:13px">
<form method="post" action="admin.php">' . csrf_field() . '<input type="hidden" name="act" value="kunci"><button class="btn ghost" type="submit" onclick="return confirm(\'Ganti kunci? URL lama di Lynk.id tidak berlaku lagi.\')">Buat kunci baru</button></form>
<h2>2. Pengaturan per buku</h2>
' . $atur . '
<h2>3. Tambah akses manual</h2>
<p class="mute">Untuk pembayaran yang tidak lewat Lynk.id, misalnya transfer bank. Pembeli masuk atau mendaftar dengan email ini, lalu buku langsung terbuka setelah email terverifikasi.</p>
<form method="post" action="admin.php" style="display:flex;flex-direction:column;gap:14px">' . csrf_field() . '<input type="hidden" name="act" value="tambah">
  <label>Buku<select name="buku">' . $opsi . '</select></label>
  <label>Email pembeli<input type="email" name="email" required></label>
  <label>Nomor pesanan atau bukti transfer (opsional)<input name="ref" maxlength="120"></label>
  <label>Nilai pembayaran (opsional)<input name="nilai" maxlength="40" placeholder="Rp149.000"></label>
  <label>Catatan (opsional)<input name="catatan" maxlength="200" placeholder="transfer BSI 21 Okt"></label>
  <label class="check"><input type="checkbox" name="kirim" value="1" checked> Kirim email berisi tautan baca ke pembeli</label>
  <button class="btn" type="submit">Tambah akses</button>
</form>
<h2>4. Daftar akses</h2>
' . ($rows ? '<div style="overflow-x:auto"><table><thead><tr><th>Dibuat</th><th>Buku</th><th>Email pembelian</th><th>Sumber</th><th>Akun</th><th></th></tr></thead><tbody>' . $t . '</tbody></table></div>' : '<p class="mute">Belum ada pembeli.</p>') . '
<h2>5. Catatan webhook terakhir</h2>
' . ($l ?: '<p class="mute">Belum ada kiriman webhook.</p>') . '
<p class="mute" style="text-align:center"><a href="/admin/">Pusat kendali</a> · <a href="/rekomendasi/admin.php">Admin Rekomendasi</a> · <a href="/asesmen/admin.php">Dashboard asesmen</a> · <a href="/asesmen/akun.php">Akun saya</a></p>', '920px');
