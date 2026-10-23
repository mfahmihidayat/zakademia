<?php
declare(strict_types=1);
/** Ruang Belajar: katalog kelas dan kelas yang sedang diikuti. */
require __DIR__ . '/lib_lms.php';
start_session();
$u = current_user();
$pdo = lms_db();
$kelas = $pdo->query('SELECT * FROM lms_kelas WHERE status = \'terbit\' ORDER BY urutan, id')->fetchAll(PDO::FETCH_ASSOC);
$saya = [];
if ($u) { $q = $pdo->prepare('SELECT k.*, d.dibuat AS daftar, d.selesai AS tuntas FROM lms_daftar d JOIN lms_kelas k ON k.id = d.kelas_id WHERE d.user_id = ? ORDER BY d.terakhir DESC, d.dibuat DESC'); $q->execute([(int)$u['id']]); $saya = $q->fetchAll(PDO::FETCH_ASSOC); }
$ikut = array_column($saya, 'id');
$kartu = function (array $k, ?array $r) use ($pdo) {
    $n = (int)$pdo->query('SELECT COUNT(*) FROM lms_materi t JOIN lms_modul m ON m.id = t.modul_id WHERE m.kelas_id = ' . (int)$k['id'])->fetchColumn();
    $menit = (int)$pdo->query('SELECT COALESCE(SUM(menit),0) FROM lms_materi t JOIN lms_modul m ON m.id = t.modul_id WHERE m.kelas_id = ' . (int)$k['id'])->fetchColumn();
    $img = lms_pemandu($k['pemandu']);
    return '<a class="card lms-kelas" href="/belajar/' . e($k['slug']) . '/"><div class="kh">' . ($img ? '<img src="' . $img . '" alt="">' : '') . '<div><h2>' . e($k['judul']) . '</h2><small>' . ($img ? 'Bersama ' . e(LMS_PEMANDU[$k['pemandu']]) . ' · ' : '') . $n . ' materi · ' . ($menit >= 60 ? round($menit / 60, 1) . ' jam' : $menit . ' menit') . '</small>'
        . ($r ? '<div class="bar"><i style="width:' . $r['persen'] . '%"></i></div>' : '') . '</div></div>'
        . '<div class="kb"><p>' . e($k['ringkas']) . '</p><div class="km"><span>' . ($r ? ($r['tuntas'] ? 'Selesai · sertifikat terbit' : $r['persen'] . '% selesai') : (lms_berbayar($k) ? '<span class="harga">' . e($k['harga'] ?: 'Berbayar') . '</span>' : 'Gratis')) . '</span><span class="btn" style="min-height:36px;padding:6px 14px;font-size:13px">' . ($r ? 'Lanjutkan' : 'Lihat kelas') . '</span></div></div></a>';
};
lms_head('Ruang Belajar');
echo '<main class="wrap"><div class="lms-head"><h1>Belajar dengan tenang, praktik langsung.</h1><p class="mute">Kelas mandiri seputar zakat, wakaf, data, dan riset untuk lembaga. Setiap materi singkat, diakhiri kuis dan praktik dengan alat Zakademia. Kemajuan tersimpan di akun, dan sertifikat terbit otomatis saat kelas tuntas.</p></div>';
if ($saya) {
    echo '<h2 style="margin:0 0 12px">Kelas saya</h2><div class="lms-grid" style="margin-bottom:34px">';
    foreach ($saya as $k) echo $kartu($k, lms_ringkas((int)$u['id'], $k));
    echo '</div>';
}
$lain = array_filter($kelas, fn($k) => !in_array($k['id'], $ikut, true));
echo '<h2 style="margin:0 0 12px">' . ($saya ? 'Kelas lain' : 'Semua kelas') . '</h2>';
echo $lain ? '<div class="lms-grid">' . implode('', array_map(fn($k) => $kartu($k, null), $lain)) . '</div>' : '<p class="mute">Belum ada kelas lain.</p>';
if (!$u) echo '<p class="mute" style="margin-top:28px"><a href="/asesmen/masuk.php?next=%2Fbelajar%2F">Masuk</a> atau <a href="/asesmen/daftar.php?next=%2Fbelajar%2F">daftar akun</a> untuk mengikuti kelas dan menyimpan kemajuan belajar.</p>';
echo '</main>';
lms_foot();
