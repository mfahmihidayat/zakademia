<?php
declare(strict_types=1);
/** Halaman kelas: deskripsi, daftar materi, pendaftaran (gratis / beli di Lynk.id / sudah beli), kemajuan, sertifikat. */
require __DIR__ . '/lib_lms.php';
start_session();
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['k'] ?? '')));
$k = $slug ? lms_kelas($slug) : null;
$u = current_user();
if (!$k || ($k['status'] !== 'terbit' && !is_admin())) { http_response_code(404); readfile(__DIR__ . '/../404.html'); exit; }
$self = '/belajar/' . $k['slug'] . '/';
$pdo = lms_db();
$error = ''; $ok = flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $u) {
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Coba lagi.';
    elseif (($_POST['act'] ?? '') === 'daftar') {
        if (!user_verified($u)) redirect('/asesmen/verifikasi.php?next=' . urlencode($self));
        if (lms_daftarkan($u, $k)) { flash('Anda terdaftar di kelas ini. Selamat belajar.'); redirect($self); }
        $error = 'Kelas ini berbayar. Pembelian atas email ' . $u['email'] . ' belum tercatat. Bila sudah membayar, tunggu beberapa menit atau hubungi kami lewat WhatsApp.';
    }
}
$daftar = $u ? lms_terdaftar((int)$u['id'], (int)$k['id']) : null;
if (!$daftar && $u && lms_berbayar($k)) { if (lms_daftarkan($u, $k)) $daftar = lms_terdaftar((int)$u['id'], (int)$k['id']); }
$isi = lms_isi((int)$k['id']);
$prog = $u ? lms_progres((int)$u['id'], (int)$k['id']) : [];
$ring = $u && $daftar ? lms_ringkas((int)$u['id'], $k) : null;
$no = $u && $daftar ? lms_sertifikat($u, $k) : null;
$img = lms_pemandu($k['pemandu']);
$n = 0; $menit = 0; $lanjut = null;
foreach ($isi as $m) foreach ($m['materi'] as $t) { $n++; $menit += (int)$t['menit']; if (!$lanjut && $daftar && !isset($prog[(int)$t['id']])) $lanjut = $t; }
lms_head($k['judul'], '<a href="/belajar/">Ruang Belajar</a>', (string)($k['ringkas'] ?? ''), 'https://zakademia.com/belajar/' . $k['slug'] . '/',
    ['@context' => 'https://schema.org', '@graph' => [lms_schema_course($k, $menit),
        ['@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Zakademia', 'item' => 'https://zakademia.com/'], ['@type' => 'ListItem', 'position' => 2, 'name' => 'Ruang Belajar', 'item' => 'https://zakademia.com/belajar/'], ['@type' => 'ListItem', 'position' => 3, 'name' => $k['judul'], 'item' => 'https://zakademia.com/belajar/' . $k['slug'] . '/']]]]]);
echo '<main class="wrap">';
if ($ok) echo '<p class="ok" role="status" style="margin-bottom:14px">' . e($ok) . '</p>';
if ($error) echo '<p class="err" role="alert" style="margin-bottom:14px">' . e($error) . '</p>';
echo '<div class="lms-two"><div>';
echo '<section class="card" style="overflow:hidden"><div class="kh" style="display:flex;gap:16px;align-items:center;padding:24px;background:var(--deep);color:#FFFFF0">' . ($img ? '<img src="' . $img . '" alt="" style="width:76px;height:76px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,240,.6)">' : '')
    . '<div><p class="small" style="margin:0 0 4px;opacity:.85;font-weight:600">Kelas' . ($img ? ' bersama ' . LMS_PEMANDU[$k['pemandu']] : '') . '</p><h1 style="margin:0;color:#FFFFF0;font-size:clamp(22px,3.4vw,30px);line-height:1.2">' . e($k['judul']) . '</h1>'
    . '<p style="margin:6px 0 0;font-size:14px;opacity:.9">' . $n . ' materi · ' . ($menit >= 60 ? round($menit / 60, 1) . ' jam' : $menit . ' menit') . ' · ' . (lms_berbayar($k) ? e($k['harga'] ?: 'Berbayar') : 'Gratis') . ($k['sertifikat'] ? ' · sertifikat' : '') . '</p></div></div>'
    . '<div style="padding:22px 24px"><div class="isi" style="font-size:15.5px;line-height:1.7">' . lt_html($k['deskripsi'] ?: $k['ringkas']) . '</div>'
    . ($k['tujuan'] ? '<h2 style="font-size:17px;margin:18px 0 6px">Setelah kelas ini Anda dapat</h2><div class="isi" style="font-size:15px">' . lt_html($k['tujuan']) . '</div>' : '') . '</div></section>';
// sertifikat / kemajuan
if ($no) echo '<div class="sert" style="margin-top:18px"><div style="flex:1;min-width:220px"><b>Kelas tuntas. Sertifikat Anda sudah terbit.</b><p>Nomor ' . e($no) . '. Unduh, bagikan, atau periksa keasliannya kapan saja.</p></div><a class="btn" href="/belajar/sertifikat/' . e($no) . '">Lihat sertifikat</a></div>';
elseif ($ring) echo '<div class="card" style="margin-top:18px;padding:18px 22px;display:flex;gap:16px;align-items:center;flex-wrap:wrap"><div style="flex:1;min-width:200px"><b>' . $ring['persen'] . '% selesai</b> · ' . $ring['selesai'] . ' dari ' . $ring['jumlah'] . ' materi<div class="bar" style="margin-top:8px"><i style="width:' . $ring['persen'] . '%"></i></div></div>' . ($lanjut ? '<a class="btn" href="' . $self . (int)$lanjut['id'] . '/">Lanjutkan: ' . e($lanjut['judul']) . '</a>' : '') . '</div>';
// tanya jawab terbaru? (tahap 2)
echo '</div><aside class="lms-side">';
// pendaftaran
if (!$u) echo '<div class="card" style="padding:18px 20px;margin-bottom:16px"><b>Ikuti kelas ini</b><p class="mute small" style="margin:6px 0 12px">Masuk atau daftar akun gratis untuk mulai belajar dan menyimpan kemajuan.</p><a class="btn" href="/asesmen/masuk.php?next=' . urlencode($self) . '" style="width:100%">Masuk</a><a class="btn ghost" href="/asesmen/daftar.php?next=' . urlencode($self) . '" style="width:100%;margin-top:8px">Daftar akun</a></div>';
elseif (!$daftar) {
    if (lms_berbayar($k)) echo '<div class="card" style="padding:18px 20px;margin-bottom:16px"><b class="harga" style="font-size:22px">' . e($k['harga'] ?: 'Berbayar') . '</b><p class="mute small" style="margin:6px 0 12px">Setelah membayar di Lynk.id dengan email <b>' . e($u['email']) . '</b>, kelas terbuka otomatis dalam beberapa menit. Pembayaran lewat transfer dapat dikonfirmasi lewat WhatsApp.</p>' . ($k['lynk_url'] ? '<a class="btn" href="' . e($k['lynk_url']) . '" target="_blank" rel="noopener" style="width:100%">Beli di Lynk.id</a>' : '') . '<form method="post" action="' . $self . '" style="margin-top:8px">' . csrf_field() . '<input type="hidden" name="act" value="daftar"><button class="btn ghost" type="submit" style="width:100%">Sudah membayar? Buka kelas</button></form></div>';
    else echo '<form class="card" method="post" action="' . $self . '" style="padding:18px 20px;margin-bottom:16px">' . csrf_field() . '<input type="hidden" name="act" value="daftar"><b>Gratis</b><p class="mute small" style="margin:6px 0 12px">Daftar sekali, kemajuan tersimpan di akun Anda.</p><button class="btn" type="submit" style="width:100%">Ikuti kelas</button></form>';
}
// daftar materi
echo '<div class="card" style="overflow:hidden"><ul class="lms-daftar">';
$i = 0;
foreach ($isi as $m) { if (!$m['materi']) continue; echo '<li class="mod">' . e($m['judul']) . '</li>';
    foreach ($m['materi'] as $t) { $i++; $p = $prog[(int)$t['id']] ?? null; $done = $p && ($t['jenis'] !== 'kuis' || $p['lulus']);
        $cls = $done ? 'done' : (!$daftar ? 'lock' : ''); $label = '<b>' . e($t['judul']) . '</b><small>' . LMS_JENIS[$t['jenis']] . ' · ' . (int)$t['menit'] . ' menit' . ($p && $t['jenis'] === 'kuis' ? ' · nilai ' . (int)$p['skor'] . '/' . (int)$p['total'] : '') . '</small>';
        echo $daftar ? '<li class="' . $cls . '"><a href="' . $self . (int)$t['id'] . '/"><span class="no">' . ($done ? '✓' : $i) . '</span><span>' . $label . '</span></a></li>' : '<li class="' . $cls . '"><span class="x"><span class="no">' . $i . '</span><span>' . $label . '</span></span></li>'; } }
echo '</ul></div></aside></div></main>';
lms_foot();
