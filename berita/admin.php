<?php
declare(strict_types=1);
/** Kelola kabar (admin): perbarui sekarang, sembunyikan judul, tambah pengumuman sendiri, URL cron. */
require __DIR__ . '/lib_berita.php';
start_session();
header('X-Frame-Options: DENY');
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/berita/admin.php'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');
$ok = flash(); $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = br_baca(); $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa.';
    elseif ($act === 'perbarui') { $d = br_perbarui(); flash('Diperbarui: ' . count($d['item']) . ' kabar. ' . $d['log'][0]); redirect('/berita/admin.php'); }
    elseif ($act === 'sembunyi') { $d['sembunyi'] = array_values(array_unique(array_merge($d['sembunyi'], [clip($_POST['id'] ?? '', 20)]))); br_simpan($d); flash('Judul disembunyikan.'); redirect('/berita/admin.php'); }
    elseif ($act === 'tampil') { $d['sembunyi'] = array_values(array_diff($d['sembunyi'], [clip($_POST['id'] ?? '', 20)])); br_simpan($d); flash('Judul ditampilkan lagi.'); redirect('/berita/admin.php'); }
    elseif ($act === 'manual') { $j = clip($_POST['judul'] ?? '', 160); if (mb_strlen($j) < 5) $error = 'Judul terlalu pendek.'; else { $hari = max(0, (int)($_POST['hari'] ?? 7)); array_unshift($d['manual'], ['id' => bin2hex(random_bytes(4)), 'judul' => $j, 'url' => clip($_POST['url'] ?? '', 300), 'ts' => time(), 'sampai' => $hari ? time() + $hari * 86400 : 0]); $d['manual'] = array_slice($d['manual'], 0, 10); br_simpan($d); flash('Pengumuman ditambahkan.'); redirect('/berita/admin.php'); } }
    elseif ($act === 'hapus_manual') { $d['manual'] = array_values(array_filter($d['manual'], fn($m) => $m['id'] !== ($_POST['id'] ?? ''))); br_simpan($d); flash('Pengumuman dihapus.'); redirect('/berita/admin.php'); }
}
$d = br_baca(); $semb = array_flip($d['sembunyi']);
$fmt = fn(int $ts) => (new DateTime('@' . $ts))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('d/m H.i');
$btn = 'style="min-height:30px;padding:2px 10px;font-size:12px"';
$l = '';
foreach ($d['item'] as $i) $l .= '<tr' . (isset($semb[$i['id']]) ? ' style="opacity:.5"' : '') . '><td><a href="' . e($i['url']) . '" target="_blank" rel="noopener">' . e($i['judul']) . '</a><br><small class="mute">' . e($i['asal']) . ' · ' . e($i['sumber']) . ' · ' . $fmt((int)$i['ts']) . '</small></td><td><form method="post" action="/berita/admin.php">' . csrf_field() . '<input type="hidden" name="id" value="' . e($i['id']) . '"><button class="btn ghost" ' . $btn . ' name="act" value="' . (isset($semb[$i['id']]) ? 'tampil' : 'sembunyi') . '">' . (isset($semb[$i['id']]) ? 'Tampilkan' : 'Sembunyikan') . '</button></form></td></tr>';
$m = '';
foreach ($d['manual'] as $x) $m .= '<tr><td>' . e($x['judul']) . '<br><small class="mute">' . ($x['url'] ? e($x['url']) . ' · ' : '') . 'sampai ' . ($x['sampai'] ? $fmt((int)$x['sampai']) : 'dihapus manual') . '</small></td><td><form method="post" action="/berita/admin.php">' . csrf_field() . '<input type="hidden" name="id" value="' . e($x['id']) . '"><button class="btn ghost" ' . $btn . ' name="act" value="hapus_manual">Hapus</button></form></td></tr>';
page('Kabar zakat dan wakaf · Admin', '<div class="row"><h1>Kabar zakat dan wakaf</h1><a class="mute" href="/admin/">Pusat kendali</a></div>'
    . ($ok ? '<p class="ok">' . e($ok) . '</p>' : '') . ($error ? '<p class="err">' . e($error) . '</p>' : '')
    . '<p class="mute">Running text di beranda diisi dari RSS BWI, Dompet Dhuafa, Rumah Zakat, LAZISMU, dan Google News (BAZNAS, Kemenag, zakat, wakaf). Diperbarui otomatis tiap 30 menit saat ada pengunjung' . ($d['diperbarui'] ? '; terakhir ' . $fmt((int)$d['diperbarui']) . ' WIB' : '; belum pernah') . '.</p>'
    . '<form method="post" action="/berita/admin.php">' . csrf_field() . '<button class="btn" name="act" value="perbarui">Perbarui sekarang</button></form>'
    . '<h2>Pengumuman Zakademia sendiri</h2><p class="mute small">Tampil paling depan di running text, misalnya kelas baru atau webinar.</p><form method="post" action="/berita/admin.php" style="display:flex;flex-direction:column;gap:10px">' . csrf_field() . '<input type="hidden" name="act" value="manual"><label>Judul<input name="judul" required maxlength="160"></label><div class="row" style="gap:10px;align-items:flex-start"><label style="flex:2">Tautan (opsional)<input name="url" maxlength="300" placeholder="https://"></label><label style="flex:1">Tampil selama (hari, 0 = sampai dihapus)<input type="number" name="hari" value="7" min="0" max="365"></label></div><div><button class="btn ghost" type="submit">Tambah pengumuman</button></div></form>'
    . ($m ? '<table style="margin-top:10px"><tbody>' . $m . '</tbody></table>' : '')
    . '<h2>Kabar terkumpul (' . count($d['item']) . ')</h2>' . ($l ? '<table><tbody>' . $l . '</tbody></table>' : '<p class="mute">Belum ada. Tekan Perbarui sekarang.</p>')
    . '<h2>Cron (opsional)</h2><p class="mute small">Agar selalu segar meski sepi pengunjung, tambahkan cron di hPanel Hostinger tiap 30 menit yang memanggil URL ini:</p><input readonly value="' . e((( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com') . '/berita/ambil.php?k=' . $d['kunci']) . '" onclick="this.select()" style="font-size:13px">'
    . '<h2>Catatan pengambilan</h2><pre style="font-size:12px;white-space:pre-wrap;background:var(--mist);padding:10px;border-radius:10px">' . e(implode("\n", $d['log'] ?: ['–'])) . '</pre>', '900px');
