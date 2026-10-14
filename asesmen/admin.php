<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

/* rentang jawaban kinerja (sama dengan formulir) */
const KPQ = [
    'total' => [['< Rp100 juta', 5e7], ['Rp100–500 juta', 3e8], ['Rp500 juta–1 miliar', 7.5e8], ['Rp1–5 miliar', 2.5e9], ['Rp5–20 miliar', 1e10], ['> Rp20 miliar', 3e10]],
    'donor' => [['< 100', 60], ['100–500', 300], ['500–2.000', 1200], ['2.000–10.000', 5000], ['> 10.000', 15000]],
    'avg' => [['< Rp50 ribu', 3e4], ['Rp50–100 ribu', 7.5e4], ['Rp100–250 ribu', 1.75e5], ['Rp250 ribu–1 juta', 5e5], ['> Rp1 juta', 1.5e6]],
    'freq' => [['Satu kali', 1], ['2–3 kali', 2.5], ['4–11 kali', 7], ['Setiap bulan', 12]],
    'ret' => [['0–2 orang', .15], ['3–4 orang', .35], ['5–6 orang', .55], ['7–10 orang', .8]],
    'cac' => [['Hampir tanpa biaya', 1e4], ['< Rp50 ribu', 3e4], ['Rp50–200 ribu', 1.25e5], ['Rp200 ribu–1 juta', 5e5], ['> Rp1 juta', 1.5e6]],
    'cost' => [['< Rp5', .03], ['Rp5–12', .09], ['Rp13–20', .165], ['Rp21–35', .28], ['> Rp35', .45]],
    'grow' => [['Turun', -.1], ['Sama', 0], ['Naik < 25%', .15], ['Naik 25–50%', .375], ['Naik > 50%', .6]],
];
const POLE = ['efisiensi_biaya', 'penghimpunan_tinggi', 'retensi_donatur'];
const DIMK = ['pemahaman_donatur', 'perjalanan_donatur', 'kanal_kampanye', 'data_analitik', 'tata_kelola', 'kapasitas'];

start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

/* ---------- keluar ---------- */
if (isset($_GET['logout'])) redirect('keluar.php');

/* ---------- API (hanya untuk admin yang sudah masuk) ---------- */
if (isset($_GET['api'])) {
    if (!is_admin()) json_out(['ok' => false, 'login' => true, 'error' => 'Silakan masuk kembali.'], 401);
    $api = (string)$_GET['api'];
    try {
        $pdo = db();
        if ($api === 'list') {
            $rows = $pdo->query('SELECT * FROM kiriman ORDER BY dikirim DESC')->fetchAll(PDO::FETCH_ASSOC);
            json_out(['ok' => true, 'data' => array_map('row_to_record', $rows)]);
        }
        if ($api === 'csv') {
            export_csv($pdo);
        }
        if ($api === 'csv_survei') {
            require_once __DIR__ . '/../survei/lib_survei.php';
            $sid = (int)($_GET['id'] ?? 0);
            if ($sid) {
                $q = sv_db()->prepare('SELECT * FROM surveys WHERE id = ?');
                $q->execute([$sid]);
                $sv = $q->fetch(PDO::FETCH_ASSOC);
                if (!$sv) json_out(['ok' => false, 'error' => 'Survei tidak ditemukan.'], 404);
                $q = sv_db()->prepare('SELECT id, created, answers, duration FROM responses WHERE survey_id = ? ORDER BY id');
                $q->execute([$sid]);
                sv_csv($sv, json_decode($sv['def'], true) ?: ['items' => []], $q->fetchAll(PDO::FETCH_ASSOC));
            }
            export_survei_all();
        }
        if ($api === 'csv_analitik') {
            require_once __DIR__ . '/../analitik/lib_analitik.php';
            export_analitik();
        }
        if ($api === 'csv_akun') {
            export_akun($pdo);
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'metode'], 405);
        check_csrf();
        $in = json_decode((string)file_get_contents('php://input'), true) ?: [];
        $id = (string)($in['id'] ?? '');
        $q = $pdo->prepare('SELECT id FROM kiriman WHERE id = ?');
        $q->execute([$id]);
        if (!$q->fetchColumn()) json_out(['ok' => false, 'error' => 'Kiriman tidak ditemukan.'], 404);

        if ($api === 'send') {
            $to = trim((string)($in['to'] ?? ''));
            if (!valid_email($to)) json_out(['ok' => false, 'error' => 'Alamat email tujuan tidak valid.'], 422);
            $sent = send_mail($to, clip($in['subject'] ?? '', 200), (string)($in['body'] ?? ''), (string)cfg('from_email'));
            if (!$sent) json_out(['ok' => false, 'error' => 'Server tidak dapat mengirim email. Periksa pengaturan email di hosting.'], 500);
        }
        if ($api === 'send' || $api === 'mark') {
            $pdo->prepare('UPDATE kiriman SET status = "terkirim", terkirim_pada = ? WHERE id = ?')->execute([gmdate('c'), $id]);
            json_out(['ok' => true, 'data' => true]);
        }
        json_out(['ok' => false, 'error' => 'Perintah tidak dikenal.'], 400);
    } catch (Throwable $e) {
        error_log('asesmen admin: ' . $e->getMessage());
        json_out(['ok' => false, 'error' => 'Terjadi kesalahan di server.'], 500);
    }
}

/* ---------- dashboard, atau pembuatan akun admin pertama ---------- */
if (is_admin()) {
    $html = (string)file_get_contents(__DIR__ . '/views/dashboard.html');
    $html = str_replace('<!--UNDUH-->', unduh_panel(), $html);
    echo str_replace('__CSRF__', htmlspecialchars($_SESSION['csrf'], ENT_QUOTES), $html);
    exit;
}
if (admin_exists()) {
    if (current_user()) page('Akses ditolak', '<h1>Khusus admin</h1><p class="mute">Akun Anda tidak memiliki akses ke dashboard admin.</p><a class="btn ghost" href="akun.php">Ke Akun saya</a>');
    redirect('masuk.php?next=' . urlencode('/asesmen/admin.php'));
}

$f = ['name' => '', 'email' => (string)(cfg('admin_email') ?: '')];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f['name'] = clip($_POST['name'] ?? '', 120);
    $f['email'] = clip($_POST['email'] ?? '', 160);
    $p1 = (string)($_POST['password'] ?? '');
    $codeHash = (string)(cfg('setup_code_hash') ?? '');
    $code = strtoupper(trim((string)($_POST['kode'] ?? '')));
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!throttle_ok('setup|' . ip_key(), 8, 3600)) $error = 'Terlalu banyak percobaan. Coba lagi dalam satu jam.';
    elseif ($codeHash !== '' && !hash_equals($codeHash, hash('sha256', $code))) { throttle_hit('setup|' . ip_key()); $error = 'Kode penyiapan salah.'; }
    elseif ($f['name'] === '') $error = 'Nama wajib diisi.';
    elseif (!valid_email($f['email'])) $error = 'Alamat email belum benar.';
    elseif (mb_strlen($p1) < 10) $error = 'Kata sandi minimal 10 karakter.';
    elseif ($p1 !== (string)($_POST['password2'] ?? '')) $error = 'Kedua kata sandi tidak sama.';
    else {
        $q = db()->prepare('SELECT id FROM users WHERE email = ?');
        $q->execute([$f['email']]);
        if ($id = $q->fetchColumn()) {
            db()->prepare('UPDATE users SET role = "admin", name = ?, pass_hash = ? WHERE id = ?')->execute([$f['name'], password_hash($p1, PASSWORD_DEFAULT), $id]);
        } else {
            db()->prepare('INSERT INTO users (email, name, org, role, pass_hash, created) VALUES (?,?,?,?,?,?)')
                ->execute([$f['email'], $f['name'], 'Zakademia', 'admin', password_hash($p1, PASSWORD_DEFAULT), gmdate('c')]);
            $id = db()->lastInsertId();
        }
        login_user((int)$id);
        redirect('admin.php');
    }
}
page('Buat akun admin', '
<h1>Buat akun admin</h1>
<p class="mute">Halaman ini hanya muncul sekali, selama belum ada akun admin. Isi kode penyiapan yang diberikan, lalu gunakan email yang bisa Anda buka (boleh Gmail pribadi), karena "Lupa kata sandi" mengirim tautan ke email ini.</p>
' . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<form method="post" action="admin.php" style="display:flex;flex-direction:column;gap:14px">
  ' . csrf_field() . '
  ' . (cfg('setup_code_hash') ? '<label>Kode penyiapan<input name="kode" required autocomplete="off" placeholder="XXXX-XXXX-XXXX" style="text-transform:uppercase;letter-spacing:.08em"></label>' : '') . '
  <label>Nama<input name="name" value="' . e($f['name']) . '" required maxlength="120" autocomplete="name"></label>
  <label>Email admin<input type="email" name="email" value="' . e($f['email']) . '" required autocomplete="email"></label>
  <label>Kata sandi (minimal 10 karakter)<input type="password" name="password" required minlength="10" autocomplete="new-password"></label>
  <label>Ulangi kata sandi<input type="password" name="password2" required minlength="10" autocomplete="new-password"></label>
  <button class="btn" type="submit">Buat akun dan masuk</button>
</form>');

/* ---------- perhitungan untuk CSV (sama dengan dashboard) ---------- */

function kp_metrics(array $kp): array
{
    $val = function ($k) use ($kp) { $v = $kp[$k] ?? null; return (is_int($v) && isset(KPQ[$k][$v])) ? KPQ[$k][$v][1] : null; };
    $lab = function ($k) use ($kp) { $v = $kp[$k] ?? null; if ($v === 'x') return 'Tidak tahu'; return (is_int($v) && isset(KPQ[$k][$v])) ? KPQ[$k][$v][0] : ''; };
    $labels = array_map($lab, array_keys(KPQ));
    $per = ($val('avg') !== null && $val('freq') !== null) ? $val('avg') * $val('freq') : (($val('total') !== null && $val('donor')) ? $val('total') / $val('donor') : null);
    $ret = $val('ret'); $cac = $val('cac'); $cost = $val('cost');
    $clv = ($per !== null && $ret !== null) ? $per / (1 - min($ret, 0.9)) : null;
    $ratio = ($clv !== null && $cac) ? $clv / $cac : null;
    $st = [];
    if ($ratio !== null) $st[] = $ratio >= 3 ? 0 : ($ratio >= 1 ? 1 : 2);
    if ($ret !== null) $st[] = $ret >= .45 ? 0 : ($ret >= .30 ? 1 : 2);
    if ($cost !== null) $st[] = $cost <= .125 ? 0 : ($cost <= .35 ? 1 : 2);
    $health = $st ? ['Sehat', 'Waspada', 'Perlu perhatian'][max($st)] : '';
    $r = fn($v, $d = 0) => $v === null ? '' : round($v, $d);
    return array_merge($labels, [$r($ret === null ? null : $ret * 100), $r($cac), $r($clv), $r($ratio, 2), $health]);
}

/* ---------- ekspor CSV ---------- */
function vrio_cat(array $v): string
{
    if (count($v) < 4 || in_array(null, $v, true)) return '';
    if (!$v[0]) return 'Belum menjadi keunggulan';
    if (!$v[1]) return 'Setara lembaga lain';
    if (!$v[2]) return 'Keunggulan sementara';
    if (!$v[3]) return 'Belum dimanfaatkan';
    return 'Keunggulan berkelanjutan';
}

function export_csv(PDO $pdo): void
{
    $rows = $pdo->query('SELECT * FROM kiriman ORDER BY dikirim DESC')->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="asesmen-zakademia-' . gmdate('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $head = ['id', 'dikirim', 'lembaga', 'jenis', 'pengisi', 'jabatan', 'email', 'whatsapp', 'personel_penghimpunan', 'status', 'terkirim_pada'];
    foreach (DIMK as $d) { $head[] = 'skor_' . $d; $head[] = 'target_' . $d; $head[] = 'gap_' . $d; }
    $head = array_merge($head, ['indeks_persen', 'guttman_kesalahan', 'guttman_cr']);
    foreach (['kini', 'arah'] as $t) foreach (POLE as $p) $head[] = $t . '_' . $p;
    for ($k = 1; $k <= 3; $k++) { $head[] = 'keunggulan_' . $k; $head[] = 'vrio_' . $k; $head[] = 'kategori_' . $k; }
    $head = array_merge($head, ['arah_inovasi', 'frekuensi_uji_coba', 'hambatan'], array_map(fn($k) => 'rentang_' . $k, array_keys(KPQ)),
        ['retensi_perkiraan_persen', 'cac_perkiraan', 'clv_perkiraan', 'clv_cac', 'kesehatan']);
    fputcsv($out, $head);
    $TIM = ['Belum ada yang khusus', '1–2 orang', '3–5 orang', '6–10 orang', 'Lebih dari 10'];
    $FREK = ['Belum pernah', 'Sesekali, tanpa rencana', 'Beberapa kali setahun', 'Rutin dan terencana'];
    foreach ($rows as $r) {
        $j = json_decode($r['jawaban'], true) ?: [];
        if (($j['v'] ?? 0) !== 4) continue;
        $line = [$r['id'], $r['dikirim'], $r['org'], $r['type'], $r['pName'], $r['pRole'], $r['pEmail'], $r['pWa'],
            isset($j['tim']) && $j['tim'] !== null ? $TIM[$j['tim']] : '', $r['status'], $r['terkirim_pada']];
        $scores = []; $err = 0;
        foreach (range(0, 5) as $d) {
            $lv = [];
            foreach (explode(',', (string)($j['g'][$d] ?? '')) as $it) {
                $a = array_map('intval', str_split(str_pad($it, 3, '0')));
                $n = array_sum($a);
                $lv[] = 1 + $n;
                $ideal = [$n > 0 ? 1 : 0, $n > 1 ? 1 : 0, $n > 2 ? 1 : 0];
                for ($k = 0; $k < 3; $k++) if ($a[$k] !== $ideal[$k]) $err++;
            }
            $sc = array_sum($lv) / max(1, count($lv));
            $t = $j['imp'][$d] ?? null;
            $scores[] = $sc;
            array_push($line, round($sc, 2), $t ?? '', $t === null ? '' : round(max(0, $t - $sc), 2));
        }
        array_push($line, round((array_sum($scores) / 6 - 1) / 3 * 100), $err, round(1 - $err / 90, 3));
        foreach (['dilema', 'tuju'] as $tk) foreach (($j['tri'][$tk] ?? ['', '', '']) as $w) $line[] = $w === '' ? '' : round((float)$w * 100);
        $ks = $j['ku']['s'] ?? [];
        for ($k = 0; $k < 3; $k++) {
            $key = $ks[$k] ?? '';
            $v = $key !== '' ? ($j['ku']['v'][$key] ?? []) : [];
            array_push($line, $key, $v ? implode('', array_map(fn($b) => $b === null ? '-' : ($b ? 'Y' : 'T'), $v)) : '', $v ? vrio_cat($v) : '');
        }
        $line[] = implode(' + ', $j['ino']['arah'] ?? []);
        $line[] = isset($j['ino']['frek']) && $j['ino']['frek'] !== null ? $FREK[$j['ino']['frek']] : '';
        $line[] = implode(' + ', $j['ino']['hamb'] ?? []);
        $line = array_merge($line, kp_metrics((array)($j['kp'] ?? [])));
        // cegah isian dibaca sebagai rumus oleh aplikasi spreadsheet
        $line = array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $line);
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}

/* ---------- pusat unduhan (khusus admin) ---------- */

function csv_open(string $name)
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $name . '-' . gmdate('Ymd') . '.csv"');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    return $out;
}

/** cegah isian dibaca sebagai rumus oleh aplikasi spreadsheet */
function csv_safe(array $line): array
{
    return array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $line);
}

function wib(?string $iso): string
{
    if (!$iso) return '';
    try { return (new DateTime($iso))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i'); } catch (Throwable $e) { return $iso; }
}

/** Semua jawaban dari semua survei, satu baris per pertanyaan per tanggapan. */
function export_survei_all(): void
{
    $pdo = sv_db();
    $surveys = $pdo->query('SELECT s.*, u.email AS u_email, u.org AS u_org FROM surveys s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.id')->fetchAll(PDO::FETCH_ASSOC);
    $out = csv_open('survei-semua-jawaban');
    fputcsv($out, ['survei_id', 'tautan', 'judul', 'status_survei', 'lembaga_pemilik', 'email_pemilik', 'tanggapan_no', 'waktu_wib', 'durasi_detik', 'pertanyaan_no', 'pertanyaan', 'jawaban']);
    $q = $pdo->prepare('SELECT id, created, answers, duration FROM responses WHERE survey_id = ? ORDER BY id');
    foreach ($surveys as $s) {
        $def = json_decode($s['def'], true) ?: ['items' => []];
        $q->execute([$s['id']]);
        $n = 0;
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
            $n++;
            $a = json_decode($r['answers'], true) ?: [];
            $no = 0;
            foreach ($def['items'] ?? [] as $it) {
                if (($it['t'] ?? '') === 'section') continue;
                $no++;
                $title = ($it['title'] ?? '') !== '' ? $it['title'] : 'Pertanyaan ' . $no;
                $v = $a[$it['id'] ?? ''] ?? '';
                $pairs = [];
                if (($it['t'] ?? '') === 'matrix') {
                    foreach (($it['rows'] ?? []) as $ri => $row) {
                        $x = is_array($v) ? ($v[(string)$ri] ?? '') : '';
                        $pairs[] = [$title . ' [' . $row . ']', $x === '' ? '' : $x . ' (' . ($it['opts'][$x - 1] ?? '') . ')'];
                    }
                } else {
                    if (($it['t'] ?? '') === 'likert' && $v !== '' && !is_array($v)) $v = $v . ' (' . ($it['opts'][$v - 1] ?? '') . ')';
                    elseif (is_array($v)) $v = implode(($it['t'] ?? '') === 'ranking' ? ' > ' : '; ', $v);
                    $pairs[] = [$title, $v];
                }
                foreach ($pairs as [$t, $val]) {
                    fputcsv($out, csv_safe([(int)$s['id'], '/s/' . $s['slug'], $s['title'], $s['status'], (string)$s['u_org'], (string)$s['u_email'],
                        $n, wib($r['created']), $r['duration'], $no, $t, (string)$val]));
                }
            }
        }
    }
    fclose($out);
    exit;
}

/** Semua analisis Kesehatan Penghimpunan yang disimpan pengguna. */
function export_analitik(): void
{
    $rows = an_db()->query('SELECT a.*, u.email AS u_email, u.org AS u_org, u.name AS u_name FROM analitik a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.updated DESC')->fetchAll(PDO::FETCH_ASSOC);
    $out = csv_open('analitik-kesehatan-penghimpunan');
    $lab = ['danaL' => 'dana_tahun_lalu', 'danaN' => 'dana_tahun_ini', 'donL' => 'donatur_tahun_lalu', 'donN' => 'donatur_tahun_ini',
        'trxL' => 'transaksi_tahun_lalu', 'trxN' => 'transaksi_tahun_ini', 'biayaL' => 'biaya_tahun_lalu', 'biayaN' => 'biaya_tahun_ini',
        'baru' => 'donatur_baru', 'tahan' => 'donatur_bertahan', 'akuisisi' => 'biaya_akuisisi', 'top10' => 'dana_10_donatur_terbesar'];
    fputcsv($out, array_merge(['id', 'lembaga', 'pengguna', 'email', 'label', 'tahun', 'dibuat_wib', 'diperbarui_wib'], array_values($lab),
        ['pertumbuhan_dana_persen', 'retensi_persen', 'cac', 'biaya_per_rupiah', 'porsi_10_terbesar_persen']));
    $div = fn($a, $b) => ($a !== null && $b) ? $a / $b : null;
    $r2 = fn($v, $d = 2) => $v === null ? '' : round($v, $d);
    foreach ($rows as $r) {
        $d = json_decode($r['data'], true) ?: [];
        $g = fn($k) => isset($d[$k]) && is_numeric($d[$k]) ? (float)$d[$k] : null;
        $line = [(int)$r['id'], (string)$r['u_org'], (string)$r['u_name'], (string)$r['u_email'], $r['label'], $r['tahun'], wib($r['created']), wib($r['updated'])];
        foreach (array_keys($lab) as $k) $line[] = $g($k) === null ? '' : $g($k);
        $grow = ($g('danaN') !== null && $g('danaL')) ? ($g('danaN') - $g('danaL')) / $g('danaL') * 100 : null;
        $ret = $div($g('tahan'), $g('donL'));
        $top = $div($g('top10'), $g('danaN'));
        array_push($line, $r2($grow, 1), $r2($ret === null ? null : $ret * 100, 1), $r2($div($g('akuisisi'), $g('baru')), 0),
            $r2($div($g('biayaN'), $g('danaN')), 3), $r2($top === null ? null : $top * 100, 1));
        fputcsv($out, csv_safe($line));
    }
    fclose($out);
    exit;
}

/** Daftar akun pengguna (tanpa kata sandi). */
function export_akun(PDO $pdo): void
{
    $rows = $pdo->query('SELECT * FROM users ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    $out = csv_open('akun-pengguna');
    fputcsv($out, ['id', 'nama', 'email', 'lembaga', 'jenis_lembaga', 'peran', 'terdaftar_wib']);
    foreach ($rows as $r) {
        fputcsv($out, csv_safe([(int)$r['id'], (string)($r['name'] ?? ''), (string)($r['email'] ?? ''), (string)($r['org'] ?? ''),
            (string)($r['org_type'] ?? ''), (string)($r['role'] ?? ''), wib($r['created'] ?? null)]));
    }
    fclose($out);
    exit;
}

/** Kartu "Unduh data" di dashboard admin. */
function unduh_panel(): string
{
    $n = function (string $sql): int { try { return (int)db()->query($sql)->fetchColumn(); } catch (Throwable $e) { return 0; } };
    $nAs = $n('SELECT COUNT(*) FROM kiriman');
    $nUs = $n('SELECT COUNT(*) FROM users');
    $surveys = [];
    $nJw = 0; $nAn = 0;
    try {
        require_once __DIR__ . '/../survei/lib_survei.php';
        $surveys = sv_db()->query('SELECT s.id, s.slug, s.title, s.status, u.org AS u_org, (SELECT COUNT(*) FROM responses r WHERE r.survey_id = s.id) AS n
            FROM surveys s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.updated DESC')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($surveys as $s) $nJw += (int)$s['n'];
        require_once __DIR__ . '/../analitik/lib_analitik.php';
        $nAn = (int)an_db()->query('SELECT COUNT(*) FROM analitik')->fetchColumn();
    } catch (Throwable $e) { error_log('unduh panel: ' . $e->getMessage()); }
    $item = fn($t, $d, $href) => '<div class="dl"><div><b>' . e($t) . '</b><span class="note">' . e($d) . '</span></div><a class="btn btn-ghost" href="' . e($href) . '">Unduh CSV</a></div>';
    $h = '<div class="card" id="unduh"><div class="card-head"><div><span class="label">Khusus admin</span><h2>Unduh data</h2>'
        . '<p>Semua unduhan data Zakademia ada di sini saja. File CSV siap dibuka di Excel atau SPSS.</p></div></div><div class="dlgrid">'
        . $item('Asesmen Kesiapan Penghimpunan', $nAs . ' kiriman, lengkap dengan skor, gap, VRIO, dan kesehatan.', 'admin.php?api=csv')
        . $item('Survei — semua jawaban', count($surveys) . ' survei, ' . $nJw . ' tanggapan. Satu baris per pertanyaan.', 'admin.php?api=csv_survei')
        . $item('Analitik Kesehatan Penghimpunan', $nAn . ' analisis tersimpan, beserta retensi, CAC, dan biaya per rupiah.', 'admin.php?api=csv_analitik')
        . $item('Akun pengguna', $nUs . ' akun: nama, email, lembaga, peran (tanpa kata sandi).', 'admin.php?api=csv_akun')
        . '</div>';
    if ($surveys) {
        $h .= '<details class="dlsv"><summary>Unduh per survei (' . count($surveys) . ')</summary><div class="tablewrap"><table><thead><tr><th>Survei</th><th>Pemilik</th><th>Status</th><th>Tanggapan</th><th><span class="sr-only">Unduh</span></th></tr></thead><tbody>';
        foreach ($surveys as $s) {
            $h .= '<tr><td>' . e($s['title']) . '<br><span class="note">/s/' . e($s['slug']) . '</span></td><td>' . e((string)$s['u_org']) . '</td><td>' . e($s['status'])
                . '</td><td>' . (int)$s['n'] . '</td><td><a class="linkbtn" href="admin.php?api=csv_survei&amp;id=' . (int)$s['id'] . '">Unduh CSV</a></td></tr>';
        }
        $h .= '</tbody></table></div></details>';
    }
    return $h . '</div>';
}
