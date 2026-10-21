<?php
declare(strict_types=1);
/** Daftar tulisan milik pengguna: status, catatan editor, tautan sunting/pratinjau/lihat. */
require __DIR__ . '/lib_literasi.php';
start_session();
header('X-Frame-Options: DENY');
$u = current_user();
if (!$u) redirect('/asesmen/masuk.php?next=' . urlencode('/literasi/saya/'));
$pdo = lt_db();
$ok = flash(); $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $t = lt_tulisan($id);
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa.';
    elseif (!$t || (int)$t['user_id'] !== (int)$u['id']) $error = 'Tulisan tidak ditemukan.';
    elseif (($_POST['act'] ?? '') === 'hapus' && in_array($t['status'], ['draf', 'revisi', 'ditolak'], true)) { $pdo->prepare('DELETE FROM lt_tulisan WHERE id = ?')->execute([$id]); flash('Draf dihapus.'); redirect('/literasi/saya/'); }
    elseif (($_POST['act'] ?? '') === 'tarik' && $t['status'] === 'ditinjau') { $pdo->prepare('UPDATE lt_tulisan SET status = \'draf\' WHERE id = ?')->execute([$id]); flash('Kiriman ditarik kembali menjadi draf.'); redirect('/literasi/saya/'); }
}
$pen = lt_penulis((int)$u['id']);
$q = $pdo->prepare('SELECT * FROM lt_tulisan WHERE user_id = ? ORDER BY diubah DESC'); $q->execute([(int)$u['id']]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);
$warna = ['draf' => '', 'ditinjau' => '', 'revisi' => '', 'terbit' => ' done', 'ditolak' => ''];
$list = '';
foreach ($rows as $t) {
    $aksi = '';
    if (in_array($t['status'], ['draf', 'revisi'], true)) $aksi .= '<a class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" href="/literasi/tulis/?id=' . $t['id'] . '">Sunting</a> ';
    if ($t['status'] === 'terbit') $aksi .= '<a class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" href="' . e(lt_url($t)) . '">Lihat</a> ';
    else $aksi .= '<a class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" href="/literasi/k.php?id=' . $t['id'] . '&pra=1">Pratinjau</a> ';
    if ($t['status'] === 'ditinjau') $aksi .= '<form method="post" action="/literasi/saya/" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="tarik"><input type="hidden" name="id" value="' . $t['id'] . '"><button class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px">Tarik</button></form> ';
    if (in_array($t['status'], ['draf', 'revisi', 'ditolak'], true)) $aksi .= '<form method="post" action="/literasi/saya/" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="hapus"><input type="hidden" name="id" value="' . $t['id'] . '"><button class="btn ghost" style="min-height:34px;padding:4px 12px;font-size:13px" onclick="return confirm(\'Hapus tulisan ini?\')">Hapus</button></form>';
    $list .= '<div style="border:1px solid var(--line);border-radius:16px;padding:14px 16px;display:flex;flex-direction:column;gap:8px"><div class="row" style="align-items:flex-start"><b>' . e($t['judul'] ?: '(tanpa judul)') . '</b><span class="pill' . $warna[$t['status']] . '">' . e(LT_STATUS[$t['status']]) . '</span></div>'
        . '<span class="mute" style="font-size:13px">' . e(LT_RUBRIK[$t['rubrik']][0] ?? '') . ' · diubah ' . e(lt_tgl($t['diubah'])) . ($t['status'] === 'terbit' ? ' · ' . (int)$t['dilihat'] . ' kali dibaca' : '') . '</span>'
        . ($t['catatan'] && in_array($t['status'], ['revisi', 'ditolak'], true) ? '<div class="err" style="font-size:13px"><b>Catatan editor:</b><br>' . nl2br(e($t['catatan'])) . '</div>' : '')
        . '<div>' . $aksi . '</div></div>';
}
page('Tulisan saya', '<div class="row"><h1>Tulisan saya</h1><a class="btn gold" href="/literasi/tulis/">Tulis baru</a></div>'
    . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '')
    . ($pen ? '<p class="mute">Menulis sebagai <b>' . e($pen['nama']) . '</b>' . ($pen['afiliasi'] ? ' · ' . e($pen['afiliasi']) : '') . '. <a href="/literasi/tulis/">Ubah profil</a></p>' : '<p class="mute">Anda belum punya profil penulis. Profil dibuat saat pertama kali menulis.</p>')
    . '<div style="display:flex;flex-direction:column;gap:12px;margin-top:8px">' . ($list ?: '<p class="mute">Belum ada tulisan. Mulai dari tombol Tulis baru.</p>') . '</div>'
    . '<p class="mute" style="text-align:center;margin-top:8px"><a href="/literasi/">Literasi</a> · <a href="/asesmen/akun.php">Akun saya</a></p>', '760px');
