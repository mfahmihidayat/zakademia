<?php
declare(strict_types=1);
/**
 * Pusat kendali Zakademia (khusus admin): statistik kunjungan, akun pengguna dan pengelolaannya,
 * serta pintu ke semua alat admin lain (asesmen, survei, rekomendasi, buku digital).
 */
require __DIR__ . '/../stat/lib_stat.php';
require_once __DIR__ . '/../buku/lib_buku.php';
start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/admin/'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');

$me = current_user();
$pdo = db();
$st = st_db();
$error = '';
$ok = flash();
$tz = new DateTimeZone('Asia/Jakarta');
$fmt = function (?string $iso, bool $jam = true) use ($tz): string {
    if (!$iso) return '–';
    $b = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    try { $t = (new DateTime($iso))->setTimezone($tz); } catch (Throwable $e) { return $iso; }
    return $t->format('j') . ' ' . $b[(int)$t->format('n') - 1] . ' ' . $t->format('Y') . ($jam ? ', ' . $t->format('H.i') : '');
};
$n = fn($x) => number_format((int)$x, 0, ',', '.');

/* ---------- tindakan pengelolaan akun ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    $q = $pdo->prepare('SELECT * FROM users WHERE id = ?'); $q->execute([$id]);
    $target = $q->fetch(PDO::FETCH_ASSOC);
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!$target) $error = 'Akun tidak ditemukan.';
    elseif ($act === 'verif') {
        $pdo->prepare('UPDATE users SET verified = 1 WHERE id = ?')->execute([$id]);
        flash('Email ' . $target['email'] . ' ditandai terverifikasi.'); redirect('/admin/#akun');
    } elseif ($act === 'peran') {
        if ($id === (int)$me['id']) $error = 'Peran akun sendiri tidak dapat diubah dari sini.';
        else {
            $baru = $target['role'] === 'admin' ? 'lembaga' : 'admin';
            $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$baru, $id]);
            flash($target['email'] . ($baru === 'admin' ? ' dijadikan admin.' : ' bukan admin lagi.')); redirect('/admin/#akun');
        }
    } elseif ($act === 'hapus') {
        if ($id === (int)$me['id']) $error = 'Akun sendiri tidak dapat dihapus.';
        else {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            $pdo->prepare('DELETE FROM resets WHERE user_id = ?')->execute([$id]);
            flash('Akun ' . $target['email'] . ' dihapus. Kiriman asesmen dan catatan pembeliannya tetap tersimpan.'); redirect('/admin/#akun');
        }
    }
}

/* ---------- parameter tampilan ---------- */
$hari = (int)($_GET['hari'] ?? 30); if (!in_array($hari, [7, 30, 90], true)) $hari = 30;
$admin = !empty($_GET['admin']);           // sertakan kunjungan admin sendiri
$cari = clip($_GET['q'] ?? '', 80);
$w = $admin ? '1=1' : 'admin = 0';
$now = new DateTime('now', $tz);
$ini = $now->format('Y-m-d');
$mulai = (clone $now)->modify('-' . ($hari - 1) . ' days')->format('Y-m-d');
$qs = fn(array $o) => '?' . http_build_query(array_filter(['hari' => $hari, 'admin' => $admin ? 1 : null, 'q' => $cari ?: null] + $o, fn($v) => $v !== null && $v !== ''));

$one = fn(string $sql, array $p = []) => (function () use ($st, $sql, $p) { $q = $st->prepare($sql); $q->execute($p); return (int)$q->fetchColumn(); })();
$rows = fn(string $sql, array $p = []) => (function () use ($st, $sql, $p) { $q = $st->prepare($sql); $q->execute($p); return $q->fetchAll(PDO::FETCH_ASSOC); })();

/* ---------- angka ringkas ---------- */
$vHari = $one("SELECT COUNT(DISTINCT vis) FROM st_hit WHERE $w AND hari = ?", [$ini]);
$tHari = $one("SELECT COUNT(*) FROM st_hit WHERE $w AND hari = ?", [$ini]);
$v7 = $one("SELECT COUNT(DISTINCT hari || vis) FROM st_hit WHERE $w AND hari >= ?", [(clone $now)->modify('-6 days')->format('Y-m-d')]);
$vN = $one("SELECT COUNT(DISTINCT hari || vis) FROM st_hit WHERE $w AND hari >= ?", [$mulai]);
$tN = $one("SELECT COUNT(*) FROM st_hit WHERE $w AND hari >= ?", [$mulai]);
$online = $one("SELECT COUNT(DISTINCT vis) FROM st_hit WHERE $w AND ts > ?", [time() - 300]);
$masukN = $one("SELECT COUNT(DISTINCT user_id) FROM st_hit WHERE $w AND user_id IS NOT NULL AND hari >= ?", [$mulai]);
$vKemarin = $one("SELECT COUNT(DISTINCT vis) FROM st_hit WHERE $w AND hari = ?", [(clone $now)->modify('-1 day')->format('Y-m-d')]);

/* ---------- grafik harian ---------- */
$per = [];
for ($i = $hari - 1; $i >= 0; $i--) $per[(clone $now)->modify("-$i days")->format('Y-m-d')] = ['v' => 0, 't' => 0];
foreach ($rows("SELECT hari, COUNT(DISTINCT vis) v, COUNT(*) t FROM st_hit WHERE $w AND hari >= ? GROUP BY hari", [$mulai]) as $r) if (isset($per[$r['hari']])) $per[$r['hari']] = ['v' => (int)$r['v'], 't' => (int)$r['t']];
$maxV = max(1, max(array_column($per, 'v')));
$maxT = max(1, max(array_column($per, 't')));
$W = 900; $H = 220; $pad = 36; $bw = ($W - $pad - 10) / max(1, count($per));
$bars = ''; $line = ''; $lab = ''; $i = 0;
foreach ($per as $d => $x) {
    $bh = round($x['v'] / $maxV * ($H - 40), 1); $xx = round($pad + $i * $bw, 1);
    $bars .= '<rect x="' . ($xx + 1) . '" y="' . ($H - 24 - $bh) . '" width="' . max(1, round($bw - 2, 1)) . '" height="' . $bh . '" rx="2" fill="#0B4D33" opacity=".85"><title>' . $fmt($d, false) . ': ' . $x['v'] . ' pengunjung, ' . $x['t'] . ' tayangan</title></rect>';
    $line .= ($i ? ' L' : 'M') . round($xx + $bw / 2, 1) . ' ' . round($H - 24 - $x['t'] / $maxT * ($H - 40), 1);
    $step = $hari > 30 ? 10 : ($hari > 7 ? 5 : 1);
    if ($i % $step === 0) $lab .= '<text x="' . round($xx + $bw / 2, 1) . '" y="' . ($H - 6) . '" text-anchor="middle" font-size="11" fill="#4A6355">' . (int)substr($d, 8, 2) . '/' . (int)substr($d, 5, 2) . '</text>';
    $i++;
}
$grafik = '<svg viewBox="0 0 ' . $W . ' ' . $H . '" width="100%" role="img" aria-label="Grafik kunjungan harian" style="display:block;font-family:inherit">'
    . '<text x="4" y="14" font-size="11" fill="#4A6355">' . $maxV . '</text><text x="4" y="' . ($H - 26) . '" font-size="11" fill="#4A6355">0</text>'
    . '<line x1="' . $pad . '" y1="' . ($H - 24) . '" x2="' . $W . '" y2="' . ($H - 24) . '" stroke="#DCE5D7"/>' . $bars
    . '<path d="' . $line . '" fill="none" stroke="#C99A3B" stroke-width="2.5" stroke-linejoin="round"/>' . $lab . '</svg>'
    . '<p class="mute" style="font-size:13px"><span style="display:inline-block;width:12px;height:12px;background:#0B4D33;border-radius:3px;vertical-align:-1px"></span> Pengunjung per hari (maks. ' . $n($maxV) . ') &nbsp; <span style="display:inline-block;width:14px;height:3px;background:#C99A3B;vertical-align:3px"></span> Tayangan halaman (maks. ' . $n($maxT) . ')</p>';

/* ---------- daftar-daftar ---------- */
$topHal = $rows("SELECT path, COUNT(*) t, COUNT(DISTINCT hari || vis) v FROM st_hit WHERE $w AND hari >= ? GROUP BY path ORDER BY t DESC LIMIT 15", [$mulai]);
$topRef = $rows("SELECT ref, COUNT(DISTINCT hari || vis) v FROM st_hit WHERE $w AND hari >= ? GROUP BY ref ORDER BY v DESC LIMIT 10", [$mulai]);
$alat = $rows("SELECT alat, COUNT(DISTINCT hari || vis) v FROM st_hit WHERE $w AND hari >= ? GROUP BY alat ORDER BY v DESC", [$mulai]);
$jam = array_fill(0, 24, 0);
foreach ($rows("SELECT jam, COUNT(*) t FROM st_hit WHERE $w AND hari >= ? GROUP BY jam", [$mulai]) as $r) $jam[(int)$r['jam']] = (int)$r['t'];
$maxJ = max(1, max($jam));
$jamSvg = '<svg viewBox="0 0 480 70" width="100%" role="img" aria-label="Tayangan per jam" style="display:block">';
foreach ($jam as $h => $t) { $bh = round($t / $maxJ * 50); $jamSvg .= '<rect x="' . ($h * 20 + 2) . '" y="' . (54 - $bh) . '" width="16" height="' . $bh . '" rx="2" fill="#0B4D33" opacity=".8"><title>Pukul ' . sprintf('%02d', $h) . '.00: ' . $t . ' tayangan</title></rect>' . ($h % 3 === 0 ? '<text x="' . ($h * 20 + 10) . '" y="67" font-size="10" text-anchor="middle" fill="#4A6355">' . $h . '</text>' : ''); }
$jamSvg .= '</svg>';
$totRef = max(1, array_sum(array_column($topRef, 'v')));
$totAlat = max(1, array_sum(array_column($alat, 'v')));
$bar = fn(int $v, int $tot) => '<span style="display:inline-block;height:8px;border-radius:4px;background:#C99A3B;width:' . max(2, round($v / $tot * 100)) . 'px;vertical-align:middle"></span>';

/* ---------- akun ---------- */
$akun = ['total' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'verif' => (int)$pdo->query('SELECT COUNT(*) FROM users WHERE verified = 1 OR role = "admin"')->fetchColumn(),
    'admin' => (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "admin"')->fetchColumn()];
// Kolom created dan last_login disimpan dalam UTC (gmdate), jadi batasnya diubah ke UTC dulu.
$utc = fn(DateTime $t) => (clone $t)->setTimezone(new DateTimeZone('UTC'))->format('c');
$q = $pdo->prepare('SELECT COUNT(*) FROM users WHERE created >= ?'); $q->execute([$utc((clone $now)->modify('-' . ($hari - 1) . ' days')->setTime(0, 0))]); $akun['baru'] = (int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM users WHERE last_login >= ?'); $q->execute([$utc((clone $now)->setTime(0, 0))]); $akun['masukHariIni'] = (int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM users WHERE last_login >= ?'); $q->execute([$utc((clone $now)->modify('-6 days')->setTime(0, 0))]); $akun['masuk7'] = (int)$q->fetchColumn();
$kunj = [];
foreach ($rows("SELECT user_id, COUNT(*) t, MAX(ts) m FROM st_hit WHERE user_id IS NOT NULL AND hari >= ? GROUP BY user_id", [$mulai]) as $r) $kunj[(int)$r['user_id']] = $r;
$onlineAkun = [];
foreach ($rows("SELECT DISTINCT user_id FROM st_hit WHERE user_id IS NOT NULL AND ts > ?", [time() - 300]) as $r) $onlineAkun[(int)$r['user_id']] = true;
if ($cari !== '') { $q = $pdo->prepare('SELECT * FROM users WHERE email LIKE ? OR name LIKE ? OR org LIKE ? ORDER BY last_login DESC, created DESC LIMIT 200'); $like = '%' . $cari . '%'; $q->execute([$like, $like, $like]); }
else { $q = $pdo->query('SELECT * FROM users ORDER BY last_login DESC, created DESC LIMIT 100'); }
$users = $q->fetchAll(PDO::FETCH_ASSOC);
$tAkun = '';
foreach ($users as $u) {
    $uid = (int)$u['id']; $self = $uid === (int)$me['id'];
    $k = $kunj[$uid] ?? null;
    $tAkun .= '<tr><td><b>' . e($u['name']) . '</b>' . (isset($onlineAkun[$uid]) ? ' <span class="pill done">online</span>' : '') . '<br><small class="mute">' . e($u['email']) . '</small>' . ($u['org'] ? '<br><small class="mute">' . e($u['org']) . '</small>' : '') . '</td>'
        . '<td>' . ($u['role'] === 'admin' ? '<span class="pill">admin</span>' : e($u['role'])) . '<br><small class="mute">' . (($u['verified'] || $u['role'] === 'admin') ? 'terverifikasi' : 'belum verifikasi') . '</small></td>'
        . '<td>' . e($fmt($u['created'], false)) . '</td><td>' . e($fmt($u['last_login'])) . '</td>'
        . '<td>' . ($k ? $n($k['t']) . ' tayangan<br><small class="mute">terakhir ' . e($fmt(gmdate('c', (int)$k['m']))) . '</small>' : '<span class="mute">–</span>') . '</td><td style="white-space:nowrap">';
    $f = fn(string $act, string $label, string $confirm = '') => '<form method="post" action="/admin/" style="display:inline">' . csrf_field() . '<input type="hidden" name="act" value="' . $act . '"><input type="hidden" name="id" value="' . $uid . '"><button class="btn ghost" style="min-height:32px;padding:3px 10px;font-size:13px" type="submit"' . ($confirm ? ' onclick="return confirm(\'' . e($confirm) . '\')"' : '') . '>' . $label . '</button></form> ';
    if (!$u['verified'] && $u['role'] !== 'admin') $tAkun .= $f('verif', 'Verifikasi');
    if (!$self) $tAkun .= $f('peran', $u['role'] === 'admin' ? 'Cabut admin' : 'Jadikan admin', $u['role'] === 'admin' ? 'Cabut hak admin ' . $u['email'] . '?' : 'Jadikan ' . $u['email'] . ' admin? Ia akan dapat membuka semua dashboard.')
        . $f('hapus', 'Hapus', 'Hapus akun ' . $u['email'] . '? Tidak dapat dibatalkan.');
    $tAkun .= '</td></tr>';
}

/* ---------- angka alat lain ---------- */
$kir = (int)$pdo->query('SELECT COUNT(*) FROM kiriman')->fetchColumn();
$kirBaru = (int)$pdo->query('SELECT COUNT(*) FROM kiriman WHERE status = "baru"')->fetchColumn();
$bk = bk_db();
$bkAkses = (int)$bk->query('SELECT COUNT(*) FROM bk_akses WHERE aktif = 1')->fetchColumn();
$bkBaca = (int)$bk->query('SELECT COUNT(*) FROM bk_akses WHERE terakhir IS NOT NULL')->fetchColumn();
$rkAkses = 0; try { $rkAkses = (int)rk_db()->query('SELECT COUNT(*) FROM rk_akses WHERE aktif = 1')->fetchColumn(); } catch (Throwable $e) {}
$ltAntre = $ltTerbit = $ltPenulis = 0;
try { require_once __DIR__ . '/../literasi/lib_literasi.php'; $lp = lt_db(); $ltAntre = (int)$lp->query('SELECT COUNT(*) FROM lt_tulisan WHERE status = \'ditinjau\'')->fetchColumn(); $ltTerbit = (int)$lp->query('SELECT COUNT(*) FROM lt_tulisan WHERE status = \'terbit\'')->fetchColumn(); $ltPenulis = (int)$lp->query('SELECT COUNT(*) FROM lt_penulis')->fetchColumn(); } catch (Throwable $e) {}
$lmsKelas = $lmsPeserta = $lmsSert = $lmsTanya = 0;
try { require_once __DIR__ . '/../belajar/lib_lms.php'; $lp = lms_db(); $lmsKelas = (int)$lp->query("SELECT COUNT(*) FROM lms_kelas WHERE status = 'terbit'")->fetchColumn(); $lmsPeserta = (int)$lp->query('SELECT COUNT(DISTINCT user_id) FROM lms_daftar')->fetchColumn(); $lmsSert = (int)$lp->query('SELECT COUNT(*) FROM lms_sertifikat')->fetchColumn(); $lmsTanya = (int)$lp->query('SELECT COUNT(*) FROM lms_tanya WHERE jawab IS NULL')->fetchColumn(); } catch (Throwable $e) {}
$svJml = 0; try { require_once __DIR__ . '/../survei/lib_survei.php'; $svJml = (int)sv_db()->query('SELECT COUNT(*) FROM responses')->fetchColumn(); } catch (Throwable $e) {}

$card = fn(string $v, string $l, string $s = '') => '<div class="kartu"><b>' . $v . '</b><span>' . $l . '</span>' . ($s ? '<small class="mute">' . $s . '</small>' : '') . '</div>';
$selisih = $vKemarin ? round(($vHari - $vKemarin) / $vKemarin * 100) : null;

page('Pusat kendali · Admin', '
<style>
.kartu-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
.kartu{border:1px solid var(--line);border-radius:16px;padding:14px 16px;background:var(--mist);display:flex;flex-direction:column;gap:2px}
.kartu b{font-size:26px;line-height:1.1;color:var(--deep)}.kartu span{font-size:13px;font-weight:600}.kartu small{font-size:12px}
.dua{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
.alat{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.alat a{display:flex;flex-direction:column;gap:4px;border:1px solid var(--line);border-radius:16px;padding:14px 16px;text-decoration:none;background:var(--paper)}
.alat a b{color:var(--deep)}.alat a small{color:var(--mute);font-weight:400}.alat a:hover{border-color:var(--gold)}
.tab{display:flex;gap:6px;flex-wrap:wrap}.tab a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);text-decoration:none;font-size:13px}.tab a.on{background:var(--deep);color:#FFFFF0;border-color:var(--deep)}
h2{margin-top:28px}
</style>
<div class="row"><h1>Pusat kendali</h1><span class="mute">' . e($now->format('d/m/Y H.i')) . ' WIB</span></div>
<p class="mute">Statistik kunjungan dihitung sendiri oleh situs tanpa layanan pihak ketiga dan tanpa cookie pelacak; alamat IP tidak disimpan. Kunjungan bot tidak dihitung.</p>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '

<h2 style="margin-top:8px">Kunjungan</h2>
<div class="row"><div class="tab"><a class="' . ($hari === 7 ? 'on' : '') . '" href="' . e($qs(['hari' => 7])) . '">7 hari</a><a class="' . ($hari === 30 ? 'on' : '') . '" href="' . e($qs(['hari' => 30])) . '">30 hari</a><a class="' . ($hari === 90 ? 'on' : '') . '" href="' . e($qs(['hari' => 90])) . '">90 hari</a></div>
<a class="mute" style="font-size:13px" href="' . e($qs(['admin' => $admin ? null : 1])) . '">' . ($admin ? 'Sembunyikan kunjungan admin' : 'Sertakan kunjungan admin') . '</a></div>
<div class="kartu-grid" style="margin-top:12px">'
    . $card($n($vHari), 'Pengunjung hari ini', $selisih === null ? $n($tHari) . ' tayangan' : ($selisih >= 0 ? '+' : '') . $selisih . '% dari kemarin')
    . $card($n($v7), 'Pengunjung 7 hari')
    . $card($n($vN), 'Pengunjung ' . $hari . ' hari', $n($tN) . ' tayangan')
    . $card($n($online), 'Sedang online', '5 menit terakhir')
    . $card($n($masukN), 'Akun aktif ' . $hari . ' hari', 'pengunjung yang masuk akun') . '
</div>
<div style="margin-top:16px">' . $grafik . '</div>
<div class="dua">
  <div><h2>Halaman terpopuler</h2>' . ($topHal ? '<table><thead><tr><th>Halaman</th><th>Tayangan</th><th>Pengunjung</th></tr></thead><tbody>' . implode('', array_map(fn($r) => '<tr><td><a href="' . e($r['path']) . '" target="_blank" style="font-weight:600">' . e(st_nama($r['path'])) . '</a><br><small class="mute">' . e($r['path']) . '</small></td><td>' . $n($r['t']) . '</td><td>' . $n($r['v']) . '</td></tr>', $topHal)) . '</tbody></table>' : '<p class="mute">Belum ada data. Angka mulai terkumpul setelah pembaruan ini tayang.</p>') . '</div>
  <div><h2>Sumber kunjungan</h2>' . ($topRef ? '<table><tbody>' . implode('', array_map(fn($r) => '<tr><td>' . e($r['ref'] === '' ? 'Langsung / ketik alamat / aplikasi chat' : $r['ref']) . '</td><td style="white-space:nowrap">' . $bar((int)$r['v'], $totRef) . ' ' . $n($r['v']) . '</td></tr>', $topRef)) . '</tbody></table>' : '<p class="mute">Belum ada data.</p>') . '
  <h2>Perangkat</h2>' . ($alat ? '<table><tbody>' . implode('', array_map(fn($r) => '<tr><td>' . e(ucfirst($r['alat'])) . '</td><td style="white-space:nowrap">' . $bar((int)$r['v'], $totAlat) . ' ' . $n($r['v']) . ' <small class="mute">(' . round($r['v'] / $totAlat * 100) . '%)</small></td></tr>', $alat)) . '</tbody></table>' : '<p class="mute">Belum ada data.</p>') . '
  <h2>Jam ramai (WIB)</h2>' . $jamSvg . '</div>
</div>

<h2 id="akun">Akun pengguna</h2>
<div class="kartu-grid">'
    . $card($n($akun['total']), 'Akun terdaftar', $n($akun['verif']) . ' terverifikasi · ' . $n($akun['admin']) . ' admin')
    . $card($n($akun['baru']), 'Akun baru ' . $hari . ' hari')
    . $card($n($akun['masukHariIni']), 'Masuk hari ini')
    . $card($n($akun['masuk7']), 'Masuk 7 hari terakhir')
    . $card($n(count($onlineAkun)), 'Akun online sekarang') . '
</div>
<form method="get" action="/admin/" class="row" style="margin-top:14px"><input type="hidden" name="hari" value="' . $hari . '">' . ($admin ? '<input type="hidden" name="admin" value="1">' : '') . '<input name="q" value="' . e($cari) . '" placeholder="Cari nama, email, atau lembaga" style="flex:1;min-width:220px"><button class="btn ghost" type="submit">Cari</button>' . ($cari !== '' ? '<a class="mute" href="' . e($qs(['q' => null])) . '">Tampilkan semua</a>' : '') . '</form>
<p class="mute" style="font-size:13px">' . ($cari !== '' ? count($users) . ' akun cocok.' : 'Diurutkan dari yang terakhir masuk; 100 akun teratas.') . ' Verifikasi manual berguna bila email kode tidak sampai. Menghapus akun tidak menghapus kiriman asesmen atau catatan pembeliannya.</p>
<div style="overflow-x:auto"><table><thead><tr><th>Pengguna</th><th>Peran</th><th>Daftar</th><th>Terakhir masuk</th><th>Kunjungan ' . $hari . ' hari</th><th></th></tr></thead><tbody>' . ($tAkun ?: '<tr><td colspan="6" class="mute">Tidak ada akun.</td></tr>') . '</tbody></table></div>

<h2>Alat pengelolaan</h2>' . ($ltAntre ? '<p class="err"><b>' . $ltAntre . ' tulisan kontributor</b> menunggu tinjauan Anda. <a href="/literasi/tinjau.php">Tinjau sekarang</a></p>' : '') . '
<div class="alat">
  <a href="/asesmen/admin.php"><b>Dashboard asesmen</b><small>' . $n($kir) . ' kiriman' . ($kirBaru ? ' · <span class="pill">' . $kirBaru . ' baru</span>' : '') . ' · hasil, kirim email, ekspor CSV</small></a>
  <a href="/literasi/tinjau.php"><b>Tinjauan tulisan kontributor</b><small>' . $n($ltAntre) . ' menunggu tinjauan · ' . $n($ltTerbit) . ' terbit · ' . $n($ltPenulis) . ' penulis</small></a>
  <a href="/survei/hasil.php"><b>Hasil survei</b><small>' . $n($svJml) . ' jawaban terkumpul</small></a>
  <a href="/rekomendasi/admin.php"><b>Admin Rekomendasi</b><small>' . $n($rkAkses) . ' akses aktif · webhook Lynk.id, tambah manual</small></a>
  <a href="/buku/admin.php"><b>Admin buku digital</b><small>' . $n($bkAkses) . ' pembeli aktif · ' . $n($bkBaca) . ' sudah membaca · harga, tautan Lynk.id, akses manual</small></a>
  <a href="/asesmen/admin.php?api=csv_akun"><b>Unduh daftar akun (CSV)</b><small>semua akun dengan lembaga dan tanggal daftar</small></a>
  <a href="/berita/admin.php"><b>Kabar zakat dan wakaf</b><small>running text beranda · sembunyikan judul · pengumuman sendiri</small></a>
  <a href="/analitik/"><b>Analitik</b><small>katalog data dan visualisasi</small></a>
  <a href="/belajar/admin.php"><b>Kelola kelas (Ruang Belajar)</b><small>' . $n($lmsKelas) . ' kelas · ' . $n($lmsPeserta) . ' peserta · ' . $n($lmsSert) . ' sertifikat' . ($lmsTanya ? ' · <span class="pill">' . $lmsTanya . ' pertanyaan</span>' : '') . '</small></a>
  <a href="https://github.com/mfahmihidayat/zakademia/actions" target="_blank" rel="noopener"><b>Status penerbitan situs</b><small>GitHub Actions · unggah ke Hostinger</small></a>
</div>
<p class="mute" style="text-align:center;margin-top:24px"><a href="/asesmen/akun.php">Akun saya</a> · <a href="/asesmen/keluar.php">Keluar</a></p>', '1100px');
