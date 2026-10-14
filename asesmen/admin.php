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
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: admin.php');
    exit;
}

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

/* ---------- buat kata sandi pertama / masuk ---------- */
$error = '';
$hash = admin_hash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $error = 'Sesi kedaluwarsa. Coba lagi.';
    } elseif ($hash === null) {
        $p1 = (string)($_POST['pw'] ?? '');
        $p2 = (string)($_POST['pw2'] ?? '');
        if (mb_strlen($p1) < 10) $error = 'Kata sandi minimal 10 karakter.';
        elseif ($p1 !== $p2) $error = 'Kedua kata sandi tidak sama.';
        else {
            set_admin_password($p1);
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            header('Location: admin.php');
            exit;
        }
    } else {
        $fails = (int)($_SESSION['fails'] ?? 0);
        if ($fails >= 3) sleep(min(10, $fails));
        if (password_verify((string)($_POST['pw'] ?? ''), $hash)) {
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['fails'] = 0;
            header('Location: admin.php');
            exit;
        }
        $_SESSION['fails'] = $fails + 1;
        $error = 'Kata sandi salah.';
    }
}

if (is_admin()) {
    $html = (string)file_get_contents(__DIR__ . '/views/dashboard.html');
    echo str_replace('__CSRF__', htmlspecialchars($_SESSION['csrf'], ENT_QUOTES), $html);
    exit;
}

$setup = ($hash === null);
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Masuk · Dashboard Asesmen Zakademia</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
<style>
:root{--bg:#FFFFF0;--paper:#FFFFFF;--ink:#10291F;--mute:#4A6355;--line:#DCE5D7;--deep:#0B4D33;--gold:#C99A3B;--warn:#9A3B1F}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--bg);color:var(--ink);font:400 15px/1.6 "Plus Jakarta Sans",system-ui,sans-serif;padding:24px 16px}
.box{width:100%;max-width:400px;background:var(--paper);border:1px solid var(--line);border-radius:24px;padding:28px;display:flex;flex-direction:column;gap:14px;box-shadow:0 18px 40px -28px rgba(11,77,51,.45)}
h1{margin:0;font-size:24px;letter-spacing:-.02em}
p{margin:0;color:var(--mute);font-size:14px}
label{font-weight:600;font-size:14px;display:flex;flex-direction:column;gap:6px}
input{font:inherit;font-size:15px;border:1px solid var(--line);border-radius:12px;padding:11px 14px;min-height:46px;background:var(--bg);color:var(--ink)}
button{min-height:46px;border:0;border-radius:999px;background:var(--deep);color:#FFFFF0;font:inherit;font-weight:600;font-size:15px;cursor:pointer}
button:focus-visible,input:focus-visible{outline:2px solid var(--gold);outline-offset:2px}
.err{color:var(--warn);font-size:14px;font-weight:600}
.brand{font-weight:800;color:var(--deep)}
</style>
</head>
<body>
<form class="box" method="post" action="admin.php" autocomplete="off">
  <span class="brand">Zakademia</span>
  <h1><?= $setup ? 'Buat kata sandi admin' : 'Masuk ke dashboard asesmen' ?></h1>
  <p><?= $setup ? 'Halaman ini muncul sekali saja. Kata sandi disimpan terenkripsi di server dan dipakai untuk membuka dashboard.' : 'Khusus pengelola Zakademia.' ?></p>
  <?php if ($error): ?><p class="err" role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
  <label>Kata sandi<input type="password" name="pw" required minlength="<?= $setup ? 10 : 1 ?>" autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>" autofocus></label>
  <?php if ($setup): ?><label>Ulangi kata sandi<input type="password" name="pw2" required minlength="10" autocomplete="new-password"></label><?php endif; ?>
  <button type="submit"><?= $setup ? 'Simpan dan masuk' : 'Masuk' ?></button>
</form>
</body>
</html>
<?php
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
