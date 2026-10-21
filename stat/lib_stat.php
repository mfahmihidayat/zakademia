<?php
declare(strict_types=1);
/**
 * Statistik kunjungan Zakademia, dihitung sendiri tanpa layanan pihak ketiga dan tanpa cookie pelacak.
 * Setiap halaman memanggil /stat/hit.php lewat js/stat.js. Yang disimpan: waktu, alamat halaman, situs perujuk,
 * jenis perangkat, kode pengunjung harian (hash dari IP + peramban + garam harian, tidak dapat dibalik), dan
 * id akun bila sedang masuk. Alamat IP tidak disimpan. Data lebih dari 400 hari dihapus.
 */
require_once __DIR__ . '/../asesmen/lib.php';

function st_db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . data_dir() . '/statistik.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE IF NOT EXISTS st_hit (
        id INTEGER PRIMARY KEY AUTOINCREMENT, ts INTEGER NOT NULL, hari TEXT NOT NULL, jam INTEGER NOT NULL,
        path TEXT NOT NULL, ref TEXT NOT NULL DEFAULT "", vis TEXT NOT NULL, alat TEXT NOT NULL,
        user_id INTEGER, admin INTEGER NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_st_hari ON st_hit (hari)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_st_ts ON st_hit (ts)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_st_vis ON st_hit (hari, vis)');
    return $pdo;
}

/** Garam harian untuk kode pengunjung; diganti otomatis tiap hari sehingga pengunjung tidak dapat dilacak lintas hari. */
function st_salt(string $hari): string
{
    $f = data_dir() . '/statistik.json';
    $c = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    if (($c['hari'] ?? '') !== $hari) {
        $c = ['hari' => $hari, 'garam' => bin2hex(random_bytes(16))];
        file_put_contents($f, json_encode($c), LOCK_EX);
    }
    return $c['garam'];
}

function st_bot(string $ua): bool
{
    return $ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|headless|lighthouse|pingdom|uptime|curl|wget|python|java\/|go-http|libwww|monitor|scan/i', $ua) === 1;
}

function st_alat(string $ua): string
{
    if (preg_match('/iPad|Tablet|Android(?!.*Mobile)/i', $ua)) return 'tablet';
    if (preg_match('/Mobi|Android|iPhone/i', $ua)) return 'ponsel';
    return 'komputer';
}

/** Bersihkan path: tanpa query, tanpa index.html, maksimal 160 karakter. */
function st_path(string $p): string
{
    $p = (string)parse_url($p, PHP_URL_PATH);
    if ($p === '' || $p[0] !== '/') $p = '/' . $p;
    $p = preg_replace('#/index\.(html|php)$#', '/', $p);
    return mb_substr($p, 0, 160);
}

function st_ref(string $r): string
{
    if ($r === '') return '';
    $h = strtolower((string)parse_url($r, PHP_URL_HOST));
    if ($h === '' || $h === 'zakademia.com' || $h === 'www.zakademia.com') return '';
    $h = preg_replace('/^(www|m|l|lm)\./', '', $h);
    $peta = ['instagram.com' => 'Instagram', 'facebook.com' => 'Facebook', 'google.com' => 'Google', 'google.co.id' => 'Google', 'bing.com' => 'Bing',
        'youtube.com' => 'YouTube', 'linkedin.com' => 'LinkedIn', 't.co' => 'X / Twitter', 'x.com' => 'X / Twitter', 'twitter.com' => 'X / Twitter',
        'whatsapp.com' => 'WhatsApp', 'web.whatsapp.com' => 'WhatsApp', 'tiktok.com' => 'TikTok', 'threads.net' => 'Threads', 'lynk.id' => 'Lynk.id',
        'duckduckgo.com' => 'DuckDuckGo', 'yahoo.com' => 'Yahoo', 'search.yahoo.com' => 'Yahoo', 'telegram.org' => 'Telegram'];
    foreach ($peta as $d => $n) if ($h === $d || str_ends_with($h, '.' . $d)) return $n;
    return mb_substr($h, 0, 80);
}

/** Catat satu kunjungan. Mengembalikan false bila diabaikan (bot, halaman admin). */
function st_catat(string $path, string $ref): bool
{
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (st_bot($ua)) return false;
    $path = st_path($path);
    if (preg_match('#admin|/stat/|webhook|/hal\.php#', $path)) return false;
    $t = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $hari = $t->format('Y-m-d');
    $vis = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . $ua . '|' . st_salt($hari)), 0, 24);
    $u = current_user();
    $pdo = st_db();
    // Satu pengunjung di satu halaman dalam 1 menit dihitung sekali (muat ulang berulang).
    $q = $pdo->prepare('SELECT 1 FROM st_hit WHERE vis = ? AND path = ? AND ts > ? LIMIT 1');
    $q->execute([$vis, $path, time() - 60]);
    if ($q->fetchColumn()) return true;
    $pdo->prepare('INSERT INTO st_hit (ts, hari, jam, path, ref, vis, alat, user_id, admin) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([time(), $hari, (int)$t->format('G'), $path, st_ref($ref), $vis, st_alat($ua), $u['id'] ?? null, ($u && $u['role'] === 'admin') ? 1 : 0]);
    if (random_int(1, 200) === 1) $pdo->exec('DELETE FROM st_hit WHERE ts < ' . (time() - 400 * 86400));
    return true;
}

/** Nama halaman yang mudah dibaca untuk daftar halaman terpopuler. */
function st_nama(string $path): string
{
    if ($path === '/') return 'Beranda';
    $peta = ['/literasi/' => 'Literasi (daftar)', '/buku/metodologi/' => 'Buku: Metodologi Penelitian', '/buku/metodologi/baca' => 'Buku: pembaca flip',
        '/asesmen/' => 'Asesmen', '/asesmen/masuk.php' => 'Masuk', '/asesmen/daftar.php' => 'Daftar akun', '/asesmen/akun.php' => 'Akun saya',
        '/survei/' => 'Survei', '/analitik/' => 'Analitik', '/belajar/' => 'Belajar', '/layanan/' => 'Layanan', '/rekomendasi/' => 'Rekomendasi Asesmen'];
    if (isset($peta[$path])) return $peta[$path];
    if (preg_match('#^/literasi/([^/]+)/?$#', $path, $m)) return 'Literasi: ' . str_replace('-', ' ', $m[1]);
    if (preg_match('#^/s/#', $path)) return 'Tautan pendek ' . $path;
    return $path;
}
