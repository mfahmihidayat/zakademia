<?php
declare(strict_types=1);
/**
 * Kelola kelas (khusus admin): buat/sunting kelas, modul, dan materi (bacaan, video, berkas, kuis, praktik), urutan,
 * status terbit, harga dan kata kunci Lynk.id, peserta dan pembelian manual, tanya jawab, sertifikat.
 */
require __DIR__ . '/lib_lms.php';
start_session();
header('X-Frame-Options: DENY');
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/belajar/admin.php'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');
$pdo = lms_db();
$error = ''; $ok = flash();
$kid = (int)($_GET['k'] ?? $_POST['kelas_id'] ?? 0);
$now = gmdate('c');
$slugify = function (string $s, int $id = 0) use ($pdo): string {
    $x = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s), '-')) ?: 'kelas'; $b = $x; $n = 2;
    while (true) { $q = $pdo->prepare('SELECT id FROM lms_kelas WHERE slug = ? AND id != ?'); $q->execute([$x, $id]); if (!$q->fetchColumn()) return $x; $x = $b . '-' . $n++; }
};
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa.';
    elseif ($act === 'kelas') {
        $id = (int)($_POST['id'] ?? 0); $judul = clip($_POST['judul'] ?? '', 120);
        if (mb_strlen($judul) < 4) $error = 'Judul kelas terlalu pendek.';
        else {
            $d = [$judul, clip($_POST['ringkas'] ?? '', 300), mb_substr((string)($_POST['deskripsi'] ?? ''), 0, 8000), isset(LMS_PEMANDU[$_POST['pemandu'] ?? '']) ? $_POST['pemandu'] : '', mb_substr((string)($_POST['tujuan'] ?? ''), 0, 2000),
                clip($_POST['harga'] ?? '', 40), clip($_POST['lynk_url'] ?? '', 300), clip($_POST['kata_kunci'] ?? '', 80), max(1, min(100, (int)($_POST['lulus'] ?? 70))), (int)!empty($_POST['sertifikat']), ($_POST['status'] ?? '') === 'terbit' ? 'terbit' : 'draf', (int)($_POST['urutan'] ?? 0), $now];
            if ($id) { $pdo->prepare('UPDATE lms_kelas SET judul=?, ringkas=?, deskripsi=?, pemandu=?, tujuan=?, harga=?, lynk_url=?, kata_kunci=?, lulus=?, sertifikat=?, status=?, urutan=?, diubah=? WHERE id=?')->execute(array_merge($d, [$id])); }
            else { $pdo->prepare('INSERT INTO lms_kelas (judul, ringkas, deskripsi, pemandu, tujuan, harga, lynk_url, kata_kunci, lulus, sertifikat, status, urutan, diubah, dibuat, slug) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute(array_merge($d, [$now, $slugify($judul)])); $id = (int)$pdo->lastInsertId(); }
            flash('Kelas tersimpan.'); redirect('/belajar/admin.php?k=' . $id);
        }
    } elseif ($act === 'hapus_kelas') { $id = (int)$_POST['id']; foreach ($pdo->query('SELECT id FROM lms_modul WHERE kelas_id = ' . $id) as $m) $pdo->exec('DELETE FROM lms_materi WHERE modul_id = ' . (int)$m['id']); $pdo->exec("DELETE FROM lms_modul WHERE kelas_id = $id"); $pdo->exec("DELETE FROM lms_kelas WHERE id = $id"); flash('Kelas dihapus.'); redirect('/belajar/admin.php'); }
    elseif ($act === 'modul') { $judul = clip($_POST['judul'] ?? '', 120); if ($judul === '') $error = 'Judul modul kosong.'; else { $id = (int)($_POST['id'] ?? 0); if ($id) $pdo->prepare('UPDATE lms_modul SET judul = ? WHERE id = ?')->execute([$judul, $id]); else { $n = (int)$pdo->query("SELECT COALESCE(MAX(urutan),-1)+1 FROM lms_modul WHERE kelas_id = $kid")->fetchColumn(); $pdo->prepare('INSERT INTO lms_modul (kelas_id, judul, urutan) VALUES (?,?,?)')->execute([$kid, $judul, $n]); } flash('Modul tersimpan.'); redirect('/belajar/admin.php?k=' . $kid); } }
    elseif ($act === 'hapus_modul') { $id = (int)$_POST['id']; $pdo->exec("DELETE FROM lms_materi WHERE modul_id = $id"); $pdo->exec("DELETE FROM lms_modul WHERE id = $id"); flash('Modul dihapus.'); redirect('/belajar/admin.php?k=' . $kid); }
    elseif ($act === 'materi') {
        $id = (int)($_POST['id'] ?? 0); $mid = (int)($_POST['modul_id'] ?? 0); $judul = clip($_POST['judul'] ?? '', 140); $jenis = isset(LMS_JENIS[$_POST['jenis'] ?? '']) ? $_POST['jenis'] : 'teks';
        $kuis = $jenis === 'kuis' ? lms_kuis_parse((string)($_POST['kuis'] ?? '')) : [];
        if ($judul === '') $error = 'Judul materi kosong.';
        elseif ($jenis === 'kuis' && count($kuis) < 1) $error = 'Kuis belum punya pertanyaan yang lengkap (minimal 2 pilihan, satu ditandai * sebagai jawaban benar).';
        else {
            $d = [$judul, $jenis, mb_substr((string)($_POST['isi'] ?? ''), 0, 60000), clip($_POST['tautan'] ?? '', 400), json_encode($kuis, JSON_UNESCAPED_UNICODE), max(1, min(600, (int)($_POST['menit'] ?? 10)))];
            if ($id) $pdo->prepare('UPDATE lms_materi SET judul=?, jenis=?, isi=?, tautan=?, kuis=?, menit=? WHERE id=?')->execute(array_merge($d, [$id]));
            else { $n = (int)$pdo->query("SELECT COALESCE(MAX(urutan),-1)+1 FROM lms_materi WHERE modul_id = $mid")->fetchColumn(); $pdo->prepare('INSERT INTO lms_materi (judul, jenis, isi, tautan, kuis, menit, modul_id, urutan) VALUES (?,?,?,?,?,?,?,?)')->execute(array_merge($d, [$mid, $n])); }
            flash('Materi tersimpan.'); redirect('/belajar/admin.php?k=' . $kid);
        }
    } elseif ($act === 'hapus_materi') { $pdo->prepare('DELETE FROM lms_materi WHERE id = ?')->execute([(int)$_POST['id']]); flash('Materi dihapus.'); redirect('/belajar/admin.php?k=' . $kid); }
    elseif ($act === 'geser') { // naik/turun modul atau materi
        $tbl = ($_POST['tabel'] ?? '') === 'modul' ? 'lms_modul' : 'lms_materi'; $par = $tbl === 'lms_modul' ? 'kelas_id' : 'modul_id'; $id = (int)$_POST['id']; $arah = ($_POST['arah'] ?? '') === 'naik' ? -1 : 1;
        $q = $pdo->prepare("SELECT * FROM $tbl WHERE id = ?"); $q->execute([$id]); $r = $q->fetch(PDO::FETCH_ASSOC);
        if ($r) { $q = $pdo->prepare("SELECT id FROM $tbl WHERE $par = ? ORDER BY urutan, id"); $q->execute([(int)$r[$par]]); $ids = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN)); $i = array_search($id, $ids, true); $j = $i + $arah;
            if ($i !== false && isset($ids[$j])) { [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]]; foreach ($ids as $n => $x) $pdo->prepare("UPDATE $tbl SET urutan = ? WHERE id = ?")->execute([$n, $x]); } }
        redirect('/belajar/admin.php?k=' . $kid);
    } elseif ($act === 'beli') {
        $email = clip($_POST['email'] ?? '', 160); $k = lms_kelas_id($kid);
        if (!valid_email($email) || !$k) $error = 'Email atau kelas tidak valid.';
        else { $g = lms_beli($kid, $email, clip($_POST['ref'] ?? '', 120), clip($_POST['nilai'] ?? '', 40), 'manual', clip($_POST['catatan'] ?? '', 200)); flash($g['baru'] ? 'Akses untuk ' . $email . ' ditambahkan.' : 'Nomor referensi ini sudah tercatat.'); redirect('/belajar/admin.php?k=' . $kid . '#peserta'); }
    } elseif ($act === 'jawab') {
        $id = (int)$_POST['id']; $jawab = clip($_POST['jawab'] ?? '', 3000);
        if ($jawab === '') $error = 'Jawaban kosong.';
        else { $pdo->prepare('UPDATE lms_tanya SET jawab = ?, dijawab = ? WHERE id = ?')->execute([$jawab, $now, $id]);
            $q = $pdo->prepare('SELECT q.*, u.email, u.name, t.judul AS materi, m.kelas_id FROM lms_tanya q JOIN users u ON u.id = q.user_id JOIN lms_materi t ON t.id = q.materi_id JOIN lms_modul m ON m.id = t.modul_id WHERE q.id = ?'); $q->execute([$id]);
            if (($r = $q->fetch(PDO::FETCH_ASSOC)) && ($kls = lms_kelas_id((int)$r['kelas_id']))) send_mail($r['email'], 'Jawaban pertanyaan Anda · ' . $kls['judul'], "Pertanyaan Anda di materi \"{$r['materi']}\":\n{$r['isi']}\n\nJawaban:\n$jawab\n\nBuka: " . LT_BASE . '/belajar/' . $kls['slug'] . '/' . $r['materi_id'] . '/#tanya');
            flash('Jawaban tersimpan dan dikirim ke penanya.'); redirect('/belajar/admin.php?tanya=1'); }
    }
}

$ta = fn(string $n, string $v, int $rows = 4, string $ph = '') => '<textarea name="' . $n . '" rows="' . $rows . '" placeholder="' . e($ph) . '" style="font:inherit;font-size:15px;border:1px solid var(--line);border-radius:12px;padding:10px 14px;resize:vertical">' . e($v) . '</textarea>';
$btnKecil = 'style="min-height:32px;padding:3px 10px;font-size:13px"';
$frmGeser = fn(string $tabel, int $id, string $arah) => '<form method="post" action="/belajar/admin.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="geser"><input type="hidden" name="kelas_id" value="' . $kid . '"><input type="hidden" name="tabel" value="' . $tabel . '"><input type="hidden" name="id" value="' . $id . '"><input type="hidden" name="arah" value="' . $arah . '"><button class="btn ghost" ' . $btnKecil . ' type="submit" aria-label="' . $arah . '">' . ($arah === 'naik' ? '↑' : '↓') . '</button></form>';
$frmHapus = fn(string $act, int $id, string $konfirmasi) => '<form method="post" action="/belajar/admin.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="' . $act . '"><input type="hidden" name="kelas_id" value="' . $kid . '"><input type="hidden" name="id" value="' . $id . '"><button class="btn ghost" ' . $btnKecil . ' type="submit" onclick="return confirm(\'' . e($konfirmasi) . '\')">Hapus</button></form>';
$css = '<style>.kotak{border:1px solid var(--line);border-radius:16px;padding:14px 16px;display:flex;flex-direction:column;gap:10px}.baris{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.baris b{flex:1;min-width:160px}details>summary{cursor:pointer;font-weight:700}.tab{display:flex;gap:6px;flex-wrap:wrap}.tab a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);text-decoration:none;font-size:13px}.tab a.on{background:var(--deep);color:#FFFFF0;border-color:var(--deep)}label{gap:4px}</style>';

/* ---------- tanya jawab ---------- */
if (isset($_GET['tanya'])) {
    $rows = $pdo->query('SELECT q.*, u.name, u.email, t.judul AS materi, k.judul AS kelas FROM lms_tanya q JOIN users u ON u.id = q.user_id JOIN lms_materi t ON t.id = q.materi_id JOIN lms_modul m ON m.id = t.modul_id JOIN lms_kelas k ON k.id = m.kelas_id ORDER BY (q.jawab IS NULL) DESC, q.id DESC LIMIT 100')->fetchAll(PDO::FETCH_ASSOC);
    $l = '';
    foreach ($rows as $r) $l .= '<div class="kotak"><div class="baris"><b>' . e($r['name']) . ' <span class="mute" style="font-weight:400">· ' . e($r['kelas']) . ' › ' . e($r['materi']) . ' · ' . e(lt_tgl($r['dibuat'])) . '</span></b>' . ($r['jawab'] ? '<span class="pill done">dijawab</span>' : '<span class="pill">menunggu</span>') . '</div><p style="font-size:14.5px">' . nl2br(e($r['isi'])) . '</p>'
        . '<form method="post" action="/belajar/admin.php" style="display:flex;flex-direction:column;gap:8px">' . csrf_field() . '<input type="hidden" name="act" value="jawab"><input type="hidden" name="id" value="' . $r['id'] . '">' . $ta('jawab', (string)$r['jawab'], 3, 'Tulis jawaban') . '<div><button class="btn ghost" ' . $btnKecil . ' type="submit">' . ($r['jawab'] ? 'Perbarui jawaban' : 'Kirim jawaban') . '</button></div></form></div>';
    page('Tanya jawab kelas', $css . '<div class="row"><h1>Tanya jawab</h1><a class="mute" href="/belajar/admin.php">Kelola kelas</a></div>' . ($ok ? '<p class="ok">' . e($ok) . '</p>' : '') . ($error ? '<p class="err">' . e($error) . '</p>' : '') . '<div style="display:flex;flex-direction:column;gap:12px">' . ($l ?: '<p class="mute">Belum ada pertanyaan.</p>') . '</div>', '900px');
}

/* ---------- satu kelas ---------- */
if ($kid) {
    $k = lms_kelas_id($kid); if (!$k) redirect('/belajar/admin.php');
    $edit = (int)($_GET['m'] ?? 0); $materiEdit = $edit ? lms_materi($edit) : null;
    $modulBaru = (int)($_GET['baru'] ?? 0);
    $pem = ''; foreach (LMS_PEMANDU as $v => $l) $pem .= '<option value="' . $v . '"' . ($k['pemandu'] === $v ? ' selected' : '') . '>' . $l . '</option>';
    $formKelas = '<form method="post" action="/belajar/admin.php" style="display:flex;flex-direction:column;gap:12px">' . csrf_field() . '<input type="hidden" name="act" value="kelas"><input type="hidden" name="id" value="' . $kid . '"><input type="hidden" name="kelas_id" value="' . $kid . '">'
        . '<label>Judul kelas<input name="judul" required maxlength="120" value="' . e($k['judul']) . '"></label>'
        . '<label>Ringkasan (tampil di kartu)<input name="ringkas" maxlength="300" value="' . e($k['ringkas']) . '"></label>'
        . '<label>Deskripsi (format teks sederhana: ## subjudul, - daftar, **tebal**)' . $ta('deskripsi', $k['deskripsi'], 5) . '</label>'
        . '<label>Tujuan belajar (satu per baris, diawali "- ")' . $ta('tujuan', $k['tujuan'], 3, "- Menghitung retensi donatur\n- Membaca tren penghimpunan") . '</label>'
        . '<div class="row" style="align-items:flex-start;gap:12px"><label style="flex:1">Pemandu<select name="pemandu">' . $pem . '</select></label><label style="flex:1">Batas lulus kuis (%)<input type="number" name="lulus" min="1" max="100" value="' . (int)$k['lulus'] . '"></label><label style="flex:1">Urutan di katalog<input type="number" name="urutan" value="' . (int)$k['urutan'] . '"></label></div>'
        . '<div class="row" style="align-items:flex-start;gap:12px"><label style="flex:1">Harga tampil (kosongkan bila gratis)<input name="harga" maxlength="40" value="' . e($k['harga']) . '" placeholder="Rp149.000"></label><label style="flex:2">Tautan produk Lynk.id<input name="lynk_url" maxlength="300" value="' . e($k['lynk_url']) . '" placeholder="https://lynk.id/zakademia/..."></label><label style="flex:1">Kata kunci nama produk (webhook)<input name="kata_kunci" maxlength="80" value="' . e($k['kata_kunci']) . '"></label></div>'
        . '<div class="row" style="justify-content:flex-start;gap:18px"><label class="check"><input type="checkbox" name="sertifikat" value="1"' . ($k['sertifikat'] ? ' checked' : '') . '> Terbitkan sertifikat saat tuntas</label><label class="check"><input type="radio" name="status" value="draf"' . ($k['status'] !== 'terbit' ? ' checked' : '') . '> Draf</label><label class="check"><input type="radio" name="status" value="terbit"' . ($k['status'] === 'terbit' ? ' checked' : '') . '> Terbit</label></div>'
        . '<div class="row" style="justify-content:flex-start"><button class="btn" type="submit">Simpan kelas</button><a class="btn ghost" href="/belajar/' . e($k['slug']) . '/" target="_blank">Lihat halaman kelas</a></div></form>';
    // modul & materi
    $isi = lms_isi($kid); $struktur = '';
    foreach ($isi as $mi => $m) {
        $struktur .= '<div class="kotak"><div class="baris"><b>' . ($mi + 1) . '. ' . e($m['judul']) . '</b>' . $frmGeser('modul', (int)$m['id'], 'naik') . $frmGeser('modul', (int)$m['id'], 'turun')
            . '<details style="display:inline"><summary class="btn ghost" ' . $btnKecil . '>Ubah nama</summary><form method="post" action="/belajar/admin.php" class="baris" style="margin-top:6px">' . csrf_field() . '<input type="hidden" name="act" value="modul"><input type="hidden" name="kelas_id" value="' . $kid . '"><input type="hidden" name="id" value="' . $m['id'] . '"><input name="judul" value="' . e($m['judul']) . '" maxlength="120" style="flex:1;min-height:36px"><button class="btn ghost" ' . $btnKecil . ' type="submit">Simpan</button></form></details>' . $frmHapus('hapus_modul', (int)$m['id'], 'Hapus modul beserta semua materinya?') . '</div>';
        foreach ($m['materi'] as $ti => $t) $struktur .= '<div class="baris" style="padding-left:14px;border-top:1px solid var(--line);padding-top:8px"><span class="pill" style="min-width:64px;justify-content:center">' . LMS_JENIS[$t['jenis']] . '</span><b style="font-weight:600">' . e($t['judul']) . ' <span class="mute" style="font-weight:400">· ' . (int)$t['menit'] . ' mnt' . ($t['jenis'] === 'kuis' ? ' · ' . count(lms_kuis($t)) . ' soal' : '') . '</span></b>' . $frmGeser('materi', (int)$t['id'], 'naik') . $frmGeser('materi', (int)$t['id'], 'turun') . '<a class="btn ghost" ' . $btnKecil . ' href="/belajar/admin.php?k=' . $kid . '&m=' . $t['id'] . '#materi">Sunting</a>' . $frmHapus('hapus_materi', (int)$t['id'], 'Hapus materi ini? Kemajuan peserta di materi ini ikut hilang.') . '</div>';
        $struktur .= '<div><a class="btn ghost" ' . $btnKecil . ' href="/belajar/admin.php?k=' . $kid . '&baru=' . $m['id'] . '#materi">+ Tambah materi ke modul ini</a></div></div>';
    }
    $struktur .= '<form method="post" action="/belajar/admin.php" class="baris">' . csrf_field() . '<input type="hidden" name="act" value="modul"><input type="hidden" name="kelas_id" value="' . $kid . '"><input name="judul" placeholder="Judul modul baru" maxlength="120" required style="flex:1;min-height:40px"><button class="btn ghost" type="submit">Tambah modul</button></form>';
    // form materi
    $formMateri = '';
    if ($materiEdit || $modulBaru) {
        $t = $materiEdit ?: ['id' => 0, 'modul_id' => $modulBaru, 'judul' => '', 'jenis' => 'teks', 'isi' => '', 'tautan' => '', 'kuis' => '[]', 'menit' => 10];
        $jo = ''; foreach (LMS_JENIS as $v => $l) $jo .= '<option value="' . $v . '"' . ($t['jenis'] === $v ? ' selected' : '') . '>' . $l . '</option>';
        $formMateri = '<h2 id="materi">' . ($materiEdit ? 'Sunting materi' : 'Materi baru') . '</h2><form method="post" action="/belajar/admin.php" class="kotak" style="background:var(--mist)">' . csrf_field() . '<input type="hidden" name="act" value="materi"><input type="hidden" name="kelas_id" value="' . $kid . '"><input type="hidden" name="id" value="' . (int)$t['id'] . '"><input type="hidden" name="modul_id" value="' . (int)$t['modul_id'] . '">'
            . '<div class="row" style="align-items:flex-start;gap:12px"><label style="flex:2">Judul materi<input name="judul" required maxlength="140" value="' . e($t['judul']) . '"></label><label style="flex:1">Jenis<select name="jenis" id="jenis">' . $jo . '</select></label><label style="flex:1">Perkiraan menit<input type="number" name="menit" min="1" max="600" value="' . (int)$t['menit'] . '"></label></div>'
            . '<label data-j="video berkas praktik">Tautan (YouTube untuk video; berkas PDF/Drive; alat untuk praktik)<input name="tautan" maxlength="400" value="' . e($t['tautan']) . '" placeholder="https://"></label>'
            . '<label data-j="teks video berkas praktik">Isi bacaan atau pengantar (format sederhana: ## subjudul, - daftar, 1. bernomor, **tebal**, *miring*, > catatan/contoh, [teks](url))' . $ta('isi', $t['isi'], 14) . '</label>'
            . '<label data-j="kuis">Soal kuis. Format tiap soal: baris "? pertanyaan", pilihan diawali "- " (salah) atau "* " (benar), lalu "> pembahasan" (opsional). Pisahkan soal dengan baris kosong.' . $ta('kuis', lms_kuis_teks(lms_kuis($t)), 14, "? Berapa nisab zakat emas?\n- 50 gram\n* 85 gram\n- 100 gram\n> PMA 52/2014 menetapkan 85 gram.") . '</label>'
            . '<div class="row" style="justify-content:flex-start"><button class="btn" type="submit">Simpan materi</button><a class="btn ghost" href="/belajar/admin.php?k=' . $kid . '">Batal</a></div></form>'
            . '<script>(function(){var s=document.getElementById("jenis");function u(){document.querySelectorAll("[data-j]").forEach(function(l){l.style.display=l.dataset.j.split(" ").indexOf(s.value)>=0?"":"none";});}s.addEventListener("change",u);u();})();</script>';
    }
    // peserta
    $q = $pdo->prepare('SELECT d.*, u.name, u.email FROM lms_daftar d JOIN users u ON u.id = d.user_id WHERE d.kelas_id = ? ORDER BY d.dibuat DESC LIMIT 300'); $q->execute([$kid]); $peserta = $q->fetchAll(PDO::FETCH_ASSOC);
    $tp = ''; $tuntas = 0;
    foreach ($peserta as $p) { $r = lms_ringkas((int)$p['user_id'], $k); if ($r['tuntas']) $tuntas++; $qs = $pdo->prepare('SELECT no FROM lms_sertifikat WHERE user_id = ? AND kelas_id = ?'); $qs->execute([(int)$p['user_id'], $kid]); $no = $qs->fetchColumn();
        $tp .= '<tr><td>' . e($p['name']) . '<br><small class="mute">' . e($p['email']) . '</small></td><td>' . e($p['sumber']) . '</td><td>' . e(lt_tgl($p['dibuat'])) . '</td><td>' . $r['persen'] . '%<br><small class="mute">' . $r['selesai'] . '/' . $r['jumlah'] . ' materi</small></td><td>' . ($no ? '<a href="/belajar/sertifikat/' . e($no) . '" target="_blank">' . e($no) . '</a>' : '–') . '</td></tr>'; }
    $q = $pdo->prepare('SELECT * FROM lms_beli WHERE kelas_id = ? ORDER BY id DESC LIMIT 100'); $q->execute([$kid]); $beli = $q->fetchAll(PDO::FETCH_ASSOC);
    $tb = ''; foreach ($beli as $b) $tb .= '<tr><td>' . e($b['email']) . ($b['ref'] ? '<br><small class="mute">' . e($b['ref']) . '</small>' : '') . '</td><td>' . e($b['sumber']) . '</td><td>' . e(lt_tgl($b['dibuat'])) . '</td><td>' . ($b['user_id'] ? 'terdaftar' : '<span class="pill">belum masuk</span>') . '</td></tr>';
    page('Kelola kelas · ' . $k['judul'], $css . '<div class="row"><h1>' . e($k['judul']) . '</h1><a class="mute" href="/belajar/admin.php">Semua kelas</a></div><p class="mute">Status: <b>' . ($k['status'] === 'terbit' ? 'Terbit' : 'Draf') . '</b> · ' . count($peserta) . ' peserta · ' . $tuntas . ' tuntas · alamat /belajar/' . e($k['slug']) . '/</p>'
        . ($ok ? '<p class="ok">' . e($ok) . '</p>' : '') . ($error ? '<p class="err">' . e($error) . '</p>' : '')
        . '<details' . (!$materiEdit && !$modulBaru ? ' open' : '') . '><summary>Pengaturan kelas</summary><div style="margin-top:12px">' . $formKelas . '</div></details>'
        . '<h2>Modul dan materi</h2><p class="mute small">Peserta belajar berurutan dari atas ke bawah. Kuis dinilai otomatis; sertifikat terbit saat semua materi selesai dan semua kuis lulus.</p><div style="display:flex;flex-direction:column;gap:12px">' . $struktur . '</div>' . $formMateri
        . '<h2 id="peserta">Peserta</h2>' . ($tp ? '<div style="overflow-x:auto"><table><thead><tr><th>Peserta</th><th>Sumber</th><th>Daftar</th><th>Kemajuan</th><th>Sertifikat</th></tr></thead><tbody>' . $tp . '</tbody></table></div>' : '<p class="mute">Belum ada peserta.</p>')
        . (lms_berbayar($k) ? '<h2>Akses berbayar</h2><p class="mute small">Pembelian lewat Lynk.id tercatat otomatis lewat webhook (kata kunci: <b>' . e($k['kata_kunci'] ?: 'belum diisi') . '</b>). Pembayaran transfer ditambahkan di sini; kelas terbuka begitu pembeli masuk dengan email tersebut.</p>'
            . '<form method="post" action="/belajar/admin.php" class="kotak">' . csrf_field() . '<input type="hidden" name="act" value="beli"><input type="hidden" name="kelas_id" value="' . $kid . '"><div class="row" style="align-items:flex-start;gap:10px"><label style="flex:2">Email pembeli<input type="email" name="email" required></label><label style="flex:1">Referensi<input name="ref" maxlength="120"></label><label style="flex:1">Nilai<input name="nilai" maxlength="40"></label></div><label>Catatan<input name="catatan" maxlength="200"></label><div><button class="btn ghost" type="submit">Tambah akses</button></div></form>'
            . ($tb ? '<div style="overflow-x:auto;margin-top:12px"><table><thead><tr><th>Email</th><th>Sumber</th><th>Tanggal</th><th>Status</th></tr></thead><tbody>' . $tb . '</tbody></table></div>' : '') : '')
        . '<p style="margin-top:24px"><form method="post" action="/belajar/admin.php" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="hapus_kelas"><input type="hidden" name="id" value="' . $kid . '"><button class="btn ghost" type="submit" onclick="return confirm(\'Hapus kelas ini beserta semua modul dan materinya? Tidak dapat dibatalkan.\')">Hapus kelas</button></form></p>', '980px');
}

/* ---------- daftar kelas ---------- */
$kelas = $pdo->query('SELECT k.*, (SELECT COUNT(*) FROM lms_daftar d WHERE d.kelas_id = k.id) AS peserta, (SELECT COUNT(*) FROM lms_sertifikat s WHERE s.kelas_id = k.id) AS sert FROM lms_kelas k ORDER BY urutan, id')->fetchAll(PDO::FETCH_ASSOC);
$l = '';
foreach ($kelas as $k) $l .= '<tr><td><a href="/belajar/admin.php?k=' . $k['id'] . '"><b>' . e($k['judul']) . '</b></a><br><small class="mute">/belajar/' . e($k['slug']) . '/' . ($k['pemandu'] ? ' · ' . LMS_PEMANDU[$k['pemandu']] : '') . '</small></td><td>' . ($k['status'] === 'terbit' ? '<span class="pill done">Terbit</span>' : '<span class="pill">Draf</span>') . '</td><td>' . (lms_berbayar($k) ? e($k['harga'] ?: 'Berbayar') : 'Gratis') . '</td><td>' . (int)$k['peserta'] . '</td><td>' . (int)$k['sert'] . '</td></tr>';
$tanyaBaru = (int)$pdo->query('SELECT COUNT(*) FROM lms_tanya WHERE jawab IS NULL')->fetchColumn();
page('Kelola kelas', $css . '<div class="row"><h1>Kelola kelas</h1><a class="mute" href="/admin/">Pusat kendali</a></div>'
    . ($ok ? '<p class="ok">' . e($ok) . '</p>' : '') . ($error ? '<p class="err">' . e($error) . '</p>' : '')
    . '<p class="mute">Kelas → modul → materi. Isi, kuis, harga, dan status diatur di sini tanpa menyentuh kode. <a href="/belajar/admin.php?tanya=1">Tanya jawab peserta' . ($tanyaBaru ? ' <span class="pill">' . $tanyaBaru . ' menunggu</span>' : '') . '</a></p>'
    . ($l ? '<table><thead><tr><th>Kelas</th><th>Status</th><th>Harga</th><th>Peserta</th><th>Sertifikat</th></tr></thead><tbody>' . $l . '</tbody></table>' : '<p class="mute">Belum ada kelas.</p>')
    . '<h2>Kelas baru</h2><form method="post" action="/belajar/admin.php" style="display:flex;flex-direction:column;gap:12px">' . csrf_field() . '<input type="hidden" name="act" value="kelas"><input type="hidden" name="id" value="0"><label>Judul kelas<input name="judul" required maxlength="120"></label><label>Ringkasan<input name="ringkas" maxlength="300"></label><input type="hidden" name="status" value="draf"><input type="hidden" name="sertifikat" value="1"><input type="hidden" name="lulus" value="70"><button class="btn" type="submit">Buat kelas (sebagai draf)</button></form>', '900px');
