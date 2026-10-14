<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'metode'], 405);

try {
    $raw = file_get_contents('php://input', false, null, 0, 40000);
    $d = json_decode((string)$raw, true);
    if (!is_array($d)) json_out(['ok' => false, 'error' => 'format'], 400);
    if (!empty($d['hp'])) json_out(['ok' => true]);                 // jebakan robot
    $user = current_user();
    if (!$user) json_out(['ok' => false, 'error' => 'perlu_masuk'], 401);

    $email = clip($d['pEmail'] ?? '', 160);
    $jawaban = clean_answers($d);
    if (clip($d['org'] ?? '', 200) === '' || !valid_email($email) || $jawaban === null) json_out(['ok' => false, 'error' => 'isian_tidak_lengkap'], 422);
    $jawaban = json_encode($jawaban);

    $pdo = db();
    $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|zakademia');
    $since = gmdate('c', time() - 600);
    $q = $pdo->prepare('SELECT COUNT(*) FROM kiriman WHERE ip_hash = ? AND dikirim > ?');
    $q->execute([$ipHash, $since]);
    if ((int)$q->fetchColumn() >= (int)cfg('rate_limit')) json_out(['ok' => false, 'error' => 'terlalu_sering'], 429);

    $id = uuid4();
    $ins = $pdo->prepare('INSERT INTO kiriman (id, dikirim, org, type, pName, pRole, pEmail, pWa, jawaban, status, ip_hash, user_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $ins->execute([$id, gmdate('c'), clip($d['org'], 200), clip($d['type'] ?? '', 80), clip($d['pName'] ?? '', 120),
        clip($d['pRole'] ?? '', 120), $email, clip($d['pWa'] ?? '', 40), $jawaban, 'baru', $ipHash, (int)$user['id']]);

    $base = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/asesmen/simpan.php'), '/');
    send_mail((string)cfg('notify_email'), 'Asesmen baru: ' . clip($d['org'], 80),
        'Asesmen baru masuk dari ' . clip($d['org'], 120) . ' (' . clip($d['pName'] ?? '', 80) . ', ' . $email . ', akun ' . $user['email'] . ").\n\n" .
        'Buka dashboard untuk melihat hasilnya: ' . $base . "/admin.php\n");

    json_out(['ok' => true]);
} catch (Throwable $e) {
    error_log('asesmen simpan: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'server'], 500);
}

/** Hanya simpan jawaban dengan bentuk yang diharapkan (formulir versi 4). */
function clean_answers(array $d): ?array
{
    $int = function ($v, int $lo, int $hi): ?int {
        if (!is_numeric($v)) return null;
        $n = (int)$v;
        return ($n < $lo || $n > $hi) ? null : $n;
    };
    $pick = fn($arr, array $allowed, int $max) => array_values(array_slice(array_unique(array_intersect(is_array($arr) ? $arr : [], $allowed)), 0, $max));
    $w3 = function ($x): ?array {
        if (!is_array($x) || count($x) !== 3) return null;
        $w = [];
        foreach (array_values($x) as $v) { if (!is_numeric($v) || $v < 0 || $v > 1) return null; $w[] = round((float)$v, 4); }
        return $w;
    };
    $g = $d['g'] ?? null; $imp = $d['imp'] ?? null; $tri = $d['tri'] ?? null;
    if (!is_array($g) || count($g) !== 6 || !is_array($imp) || count($imp) !== 6 || !is_array($tri)) return null;
    $g = array_map('strval', array_values($g));
    foreach ($g as $s) if (!preg_match('/^[01]{3}(,[01]{3}){4}$/', $s)) return null;
    $imp = array_map(fn($v) => $int($v, 1, 4), array_values($imp));
    if (in_array(null, $imp, true)) return null;
    $tri = ['dilema' => $w3($tri['dilema'] ?? null), 'tuju' => $w3($tri['tuju'] ?? null)];
    if ($tri['dilema'] === null || $tri['tuju'] === null) return null;
    $ku = is_array($d['ku'] ?? null) ? $d['ku'] : [];
    $s = $pick($ku['s'] ?? [], ['reputasi', 'komunitas', 'mitra', 'loyal', 'program', 'tokoh', 'digital', 'data', 'relawan', 'khas'], 3);
    $v = [];
    foreach ($s as $k) {
        $x = array_map(fn($b) => $int($b, 0, 1), array_slice(array_values((array)($ku['v'][$k] ?? [])), 0, 4));
        $v[$k] = (count($x) === 4 && !in_array(null, $x, true)) ? $x : [null, null, null, null];
    }
    $ino = is_array($d['ino'] ?? null) ? $d['ino'] : [];
    $kp = [];
    $kpIn = is_array($d['kp'] ?? null) ? $d['kp'] : [];
    foreach (['total' => 6, 'donor' => 5, 'avg' => 5, 'freq' => 4, 'ret' => 4, 'cac' => 5, 'cost' => 5, 'grow' => 5] as $k => $n) {
        $x = $kpIn[$k] ?? null;
        if ($x === 'x') $kp[$k] = 'x';
        elseif (($i = $int($x, 0, $n - 1)) !== null) $kp[$k] = $i;
    }
    return ['v' => 4, 'g' => $g, 'imp' => $imp, 'tri' => $tri, 'ku' => ['s' => $s, 'v' => (object)$v],
        'ino' => ['arah' => $pick($ino['arah'] ?? [], ['produk', 'kanal', 'layanan', 'mitra', 'usaha', 'data'], 2), 'frek' => $int($ino['frek'] ?? null, 0, 3),
            'hamb' => $pick($ino['hamb'] ?? [], ['dana', 'sdm', 'putusan', 'data', 'teknologi', 'risiko'], 2)],
        'kp' => (object)$kp, 'tim' => $int($d['tim'] ?? null, 0, 4)];
}
