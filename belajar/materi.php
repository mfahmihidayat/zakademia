<?php
declare(strict_types=1);
/** Halaman belajar satu materi: bacaan/video/berkas/praktik dengan tombol selesai, atau kuis yang dinilai. Tanya jawab per materi. */
require __DIR__ . '/lib_lms.php';
start_session();
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['k'] ?? '')));
$k = $slug ? lms_kelas($slug) : null;
$t = $k ? lms_materi((int)($_GET['m'] ?? 0)) : null;
if (!$k || !$t || (int)$t['kelas_id'] !== (int)$k['id'] || ($k['status'] !== 'terbit' && !is_admin())) { http_response_code(404); readfile(__DIR__ . '/../404.html'); exit; }
$self = '/belajar/' . $k['slug'] . '/'; $here = $self . (int)$t['id'] . '/';
$u = current_user();
if (!$u) redirect('/asesmen/masuk.php?next=' . urlencode($here));
$daftar = lms_terdaftar((int)$u['id'], (int)$k['id']);
if (!$daftar && !is_admin()) redirect($self);
$pdo = lms_db();
$semua = lms_semua_materi((int)$k['id']);
$idx = 0; foreach ($semua as $i => $x) if ((int)$x['id'] === (int)$t['id']) $idx = $i;
$prev = $semua[$idx - 1] ?? null; $next = $semua[$idx + 1] ?? null;
$kuis = lms_kuis($t);
$hasil = null; $error = ''; $ok = flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Coba lagi.';
    elseif ($act === 'selesai' && $t['jenis'] !== 'kuis') { lms_selesai((int)$u['id'], (int)$t['id']); $pdo->prepare('UPDATE lms_daftar SET terakhir = ? WHERE user_id = ? AND kelas_id = ?')->execute([gmdate('c'), (int)$u['id'], (int)$k['id']]);
        $no = lms_sertifikat($u, $k); if ($no) { flash('Selamat, kelas tuntas. Sertifikat Anda terbit dengan nomor ' . $no . '.'); redirect($self); } flash('Materi ditandai selesai.'); redirect($next ? $self . (int)$next['id'] . '/' : $self); }
    elseif ($act === 'kuis' && $kuis) {
        $jaw = $_POST['j'] ?? []; $benar = 0; $hasil = [];
        foreach ($kuis as $i => $q) { $j = isset($jaw[$i]) ? (int)$jaw[$i] : -1; $hasil[$i] = $j; if ($j === (int)$q['a']) $benar++; }
        $total = count($kuis); $lulus = $benar >= ceil($total * (int)$k['lulus'] / 100);
        lms_selesai((int)$u['id'], (int)$t['id'], $benar, $total, $lulus);
        $pdo->prepare('UPDATE lms_daftar SET terakhir = ? WHERE user_id = ? AND kelas_id = ?')->execute([gmdate('c'), (int)$u['id'], (int)$k['id']]);
        $no = $lulus ? lms_sertifikat($u, $k) : null;
        $hasil = ['benar' => $benar, 'total' => $total, 'lulus' => $lulus, 'jawab' => $hasil, 'no' => $no];
    } elseif ($act === 'tanya') {
        $isi = clip($_POST['isi'] ?? '', 1000);
        if (mb_strlen($isi) < 10) $error = 'Pertanyaan terlalu pendek.';
        elseif (!throttle_ok('lmstanya|' . $u['id'], 10, 3600)) $error = 'Terlalu banyak pertanyaan dalam satu jam.';
        else { $pdo->prepare('INSERT INTO lms_tanya (materi_id, user_id, isi, dibuat) VALUES (?,?,?,?)')->execute([(int)$t['id'], (int)$u['id'], $isi, gmdate('c')]); throttle_hit('lmstanya|' . $u['id']);
            send_mail((string)cfg('notify_email'), 'Pertanyaan baru di kelas ' . $k['judul'], "Materi: {$t['judul']}\nDari: {$u['name']} <{$u['email']}>\n\n$isi\n\nJawab di " . LT_BASE . '/belajar/admin.php?tanya=1');
            flash('Pertanyaan terkirim. Jawaban akan tampil di sini dan dikirim ke email Anda.'); redirect($here . '#tanya'); }
    }
}
$prog = lms_progres((int)$u['id'], (int)$k['id']); $p = $prog[(int)$t['id']] ?? null;
$ring = lms_ringkas((int)$u['id'], $k);
$q = $pdo->prepare('SELECT q.*, u.name FROM lms_tanya q JOIN users u ON u.id = q.user_id WHERE q.materi_id = ? AND (q.jawab IS NOT NULL OR q.user_id = ?) ORDER BY q.id DESC LIMIT 30'); $q->execute([(int)$t['id'], (int)$u['id']]);
$tanya = $q->fetchAll(PDO::FETCH_ASSOC);
lms_head($t['judul'], '<a href="/belajar/">Ruang Belajar</a> / <a href="' . e($self) . '">' . e($k['judul']) . '</a>');
echo '<main class="wrap"><div class="lms-two"><div>';
if ($ok) echo '<p class="ok" role="status" style="margin-bottom:14px">' . e($ok) . '</p>';
if ($error) echo '<p class="err" role="alert" style="margin-bottom:14px">' . e($error) . '</p>';
echo '<article class="card lms-body"><p class="mute small" style="margin:0 0 6px;font-weight:700;letter-spacing:.06em">' . strtoupper(LMS_JENIS[$t['jenis']]) . ' · ' . e(strtoupper($t['modul'])) . ' · ' . (int)$t['menit'] . ' MENIT</p><h1>' . e($t['judul']) . '</h1>';
if ($t['jenis'] === 'video' && $t['tautan']) echo lms_video_embed($t['tautan']);
if ($t['isi'] && $t['jenis'] !== 'kuis') echo '<div class="isi">' . lt_html($t['isi']) . '</div>';
if ($t['jenis'] === 'berkas' && $t['tautan']) echo '<p style="margin-top:16px"><a class="btn" href="' . e($t['tautan']) . '" target="_blank" rel="noopener">Buka berkas</a></p>';
if ($t['jenis'] === 'praktik' && $t['tautan']) echo '<p style="margin-top:16px"><a class="btn" href="' . e($t['tautan']) . '" target="_blank" rel="noopener">Buka alat praktik</a></p>';
if ($t['jenis'] === 'kuis') {
    if ($hasil) {
        echo '<div class="hasil" style="margin-bottom:18px"><b>' . $hasil['benar'] . ' / ' . $hasil['total'] . '</b><div style="flex:1;min-width:200px">' . ($hasil['lulus'] ? '<b style="font-size:16px">Lulus.</b> Batas lulus ' . (int)$k['lulus'] . '%.' : '<b style="font-size:16px">Belum lulus.</b> Batas lulus ' . (int)$k['lulus'] . '%. Baca pembahasannya, lalu coba lagi.') . '</div>' . ($hasil['no'] ? '<a class="btn" href="/belajar/sertifikat/' . e($hasil['no']) . '">Sertifikat terbit, lihat</a>' : '') . '</div>';
        echo '<div class="quiz">';
        foreach ($kuis as $i => $q) { echo '<div class="qq"><div class="qt">' . ($i + 1) . '. ' . e($q['q']) . '</div>';
            foreach ($q['o'] as $oi => $op) { $cls = $oi === (int)$q['a'] ? 'ok' : (($hasil['jawab'][$i] ?? -1) === $oi ? 'no' : ''); echo '<div class="qo ' . $cls . '">' . e($op) . ($oi === (int)$q['a'] ? ' <b>✓</b>' : '') . '</div>'; }
            if (!empty($q['e'])) echo '<div class="qe">' . e($q['e']) . '</div>'; echo '</div>'; }
        echo '</div><form method="post" action="' . $here . '" style="margin-top:18px">' . csrf_field() . '<input type="hidden" name="act" value="ulang"><a class="btn ghost" href="' . $here . '">Coba lagi</a></form>';
    } else {
        if ($p) echo '<p class="mute">Nilai terbaik Anda ' . (int)$p['skor'] . '/' . (int)$p['total'] . ($p['lulus'] ? ' (lulus)' : ' (belum lulus)') . '. Mengerjakan ulang tidak menurunkan nilai terbaik.</p>';
        echo '<form method="post" action="' . $here . '" class="quiz">' . csrf_field() . '<input type="hidden" name="act" value="kuis">';
        foreach ($kuis as $i => $q) { echo '<fieldset class="qq" style="border:0;padding:0;margin:0"><legend class="qt">' . ($i + 1) . '. ' . e($q['q']) . '</legend>';
            foreach ($q['o'] as $oi => $op) echo '<label class="qo"><input type="radio" name="j[' . $i . ']" value="' . $oi . '" required> ' . e($op) . '</label>'; echo '</fieldset>'; }
        echo '<div><button class="btn" type="submit">Kirim jawaban</button></div></form>';
    }
}
echo '<div class="lms-nav">' . ($prev ? '<a class="btn ghost" href="' . $self . (int)$prev['id'] . '/">‹ ' . e($prev['judul']) . '</a>' : '<span></span>');
if ($t['jenis'] !== 'kuis') echo '<form method="post" action="' . $here . '">' . csrf_field() . '<input type="hidden" name="act" value="selesai"><button class="btn" type="submit">' . ($p ? 'Sudah selesai · ' : 'Tandai selesai') . ($next ? ($p ? 'lanjut ›' : ' dan lanjut ›') : '') . '</button></form>';
elseif ($next) echo '<a class="btn ghost" href="' . $self . (int)$next['id'] . '/">Materi berikutnya ›</a>';
echo '</div>';
// tanya jawab
echo '<section class="tanya" id="tanya"><h2 style="font-size:18px;margin:0">Tanya pemandu</h2><p class="mute small" style="margin:-6px 0 0">Pertanyaan dijawab oleh tim Zakademia dan tampil di sini untuk semua peserta.</p>';
foreach ($tanya as $x) echo '<div class="t"><b>' . e($x['name']) . ' · ' . e(lt_tgl($x['dibuat'])) . '</b><br>' . nl2br(e($x['isi'])) . ($x['jawab'] ? '<div class="j"><b>Zakademia</b><br>' . nl2br(e($x['jawab'])) . '</div>' : '<div class="mute small" style="margin-top:4px">Menunggu jawaban.</div>') . '</div>';
echo '<form method="post" action="' . $here . '" style="display:flex;flex-direction:column;gap:8px">' . csrf_field() . '<input type="hidden" name="act" value="tanya"><textarea name="isi" rows="3" maxlength="1000" placeholder="Tulis pertanyaan tentang materi ini" style="font:inherit;border:1px solid var(--line);border-radius:12px;padding:10px 14px"></textarea><div><button class="btn ghost" type="submit">Kirim pertanyaan</button></div></form></section>';
echo '</article></div><aside class="lms-side"><div class="card" style="padding:14px 18px;margin-bottom:14px"><b>' . $ring['persen'] . '% selesai</b><div class="bar" style="margin-top:8px"><i style="width:' . $ring['persen'] . '%"></i></div></div><div class="card" style="overflow:hidden"><ul class="lms-daftar">';
$i = 0; $modNow = '';
foreach ($semua as $x) { $i++; if ($x['modul'] !== $modNow) { $modNow = $x['modul']; echo '<li class="mod">' . e($modNow) . '</li>'; } $px = $prog[(int)$x['id']] ?? null; $done = $px && ($x['jenis'] !== 'kuis' || $px['lulus']);
    echo '<li class="' . ($done ? 'done' : '') . '"><a class="' . ((int)$x['id'] === (int)$t['id'] ? 'on' : '') . '" href="' . $self . (int)$x['id'] . '/"><span class="no">' . ($done ? '✓' : $i) . '</span><span><b>' . e($x['judul']) . '</b><small>' . LMS_JENIS[$x['jenis']] . ' · ' . (int)$x['menit'] . ' menit</small></span></a></li>'; }
echo '</ul></div></aside></div></main><div class="baca-prog" id="bacaProg" aria-hidden="true"></div><script>(function(){var b=document.getElementById("bacaProg");function u(){var h=document.documentElement;var p=h.scrollHeight-h.clientHeight;b.style.width=(p>0?Math.min(100,window.scrollY/p*100):100)+"%";}window.addEventListener("scroll",u,{passive:true});u();})();</script>';
lms_foot();
