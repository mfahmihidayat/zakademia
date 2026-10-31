<?php
declare(strict_types=1);

const COLS = ['id', 'dikirim', 'org', 'type', 'pName', 'pRole', 'pEmail', 'pWa', 'jawaban', 'status', 'terkirim_pada'];

function cfg(string $key)
{
    static $c = null;
    if ($c === null) $c = require __DIR__ . '/config.php';
    return $c[$key] ?? null;
}

/** Folder data dibuat otomatis beserta pelindungnya, karena folder ini tidak ikut diunggah dari GitHub. */
function data_dir(): string
{
    $dir = (string)cfg('data_dir');
    if (!is_dir($dir)) mkdir($dir, 0750, true);
    if (!is_file($dir . '/.htaccess')) {
        file_put_contents($dir . '/.htaccess', "# Folder ini berisi database. Jangan pernah dapat diunduh dari peramban.\n<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Deny from all\n</IfModule>\n");
    }
    if (!is_file($dir . '/index.html')) file_put_contents($dir . '/index.html', '');
    return $dir;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = data_dir();
    $pdo = new PDO('sqlite:' . $dir . '/asesmen.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE IF NOT EXISTS kiriman (
        id TEXT PRIMARY KEY, dikirim TEXT NOT NULL, org TEXT, type TEXT, pName TEXT, pRole TEXT,
        pEmail TEXT, pWa TEXT, jawaban TEXT NOT NULL, status TEXT NOT NULL DEFAULT "baru",
        terkirim_pada TEXT, ip_hash TEXT)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_ip ON kiriman (ip_hash, dikirim)');
    $cols = array_column($pdo->query('PRAGMA table_info(kiriman)')->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('user_id', $cols, true)) $pdo->exec('ALTER TABLE kiriman ADD COLUMN user_id INTEGER');
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL UNIQUE COLLATE NOCASE, name TEXT NOT NULL,
        org TEXT, org_type TEXT, role TEXT NOT NULL DEFAULT "lembaga", pass_hash TEXT NOT NULL,
        created TEXT NOT NULL, last_login TEXT)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS resets (
        token_hash TEXT PRIMARY KEY, user_id INTEGER NOT NULL, expires INTEGER NOT NULL, used INTEGER NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS attempts (k TEXT NOT NULL, ts INTEGER NOT NULL)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_attempts ON attempts (k, ts)');
    $uc = array_column($pdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_ASSOC), 'name');
    if (!in_array('verified', $uc, true)) $pdo->exec('ALTER TABLE users ADD COLUMN verified INTEGER NOT NULL DEFAULT 0');
    $pdo->exec('CREATE TABLE IF NOT EXISTS tautan (token_hash TEXT PRIMARY KEY, user_id INTEGER NOT NULL, expires INTEGER NOT NULL, used INTEGER NOT NULL DEFAULT 0, next TEXT)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS otp (k TEXT PRIMARY KEY, code_hash TEXT NOT NULL, email TEXT NOT NULL, expires INTEGER NOT NULL, tries INTEGER NOT NULL DEFAULT 0, sent INTEGER NOT NULL)');
    migrate_admin_json($pdo);
    apply_admin_reset($pdo);
    return $pdo;
}

function json_out($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function clip($s, int $n): string
{
    $s = trim((string)($s ?? ''));
    return mb_substr($s, 0, $n);
}

function uuid4(): string
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

function valid_email(string $e): bool
{
    return (bool)filter_var($e, FILTER_VALIDATE_EMAIL);
}

/** Kirim email teks UTF-8. Mengembalikan true bila server menerima email untuk dikirim. */
function send_mail(string $to, string $subject, string $body, ?string $replyTo = null): bool
{
    if (!valid_email($to)) return false;
    $from = cfg('from_email');
    $name = '=?UTF-8?B?' . base64_encode((string)cfg('from_name')) . '?=';
    $headers = [
        'From: ' . $name . ' <' . $from . '>',
        'Reply-To: ' . ($replyTo && valid_email($replyTo) ? $replyTo : $from),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    $subj = '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], ' ', $subject)) . '?=';
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $smtp = smtp_conf();
    if ($smtp) return smtp_send($smtp, $to, $subj, $body, $headers);
    return @mail($to, $subj, $body, implode("\r\n", $headers), '-f' . $from);
}

/* ---------- SMTP (kotak surat Hostinger); kata sandi hanya disimpan di folder data server ---------- */
function smtp_file(): string { return data_dir() . '/smtp.json'; }
function smtp_conf(): ?array
{
    $f = smtp_file();
    if (!is_file($f)) return null;
    $c = json_decode((string)file_get_contents($f), true);
    return (is_array($c) && !empty($c['host']) && !empty($c['user']) && isset($c['pass'])) ? $c : null;
}
/** Kirim lewat SMTP (SSL port 465 atau STARTTLS port 587). Galat terakhir disimpan di $GLOBALS['smtp_error']. */
function smtp_send(array $c, string $to, string $subjHeader, string $body, array $headers): bool
{
    $GLOBALS['smtp_error'] = '';
    $port = (int)($c['port'] ?? 465);
    $host = ($port === 465 ? 'ssl://' : 'tcp://') . $c['host'];
    $fp = @stream_socket_client($host . ':' . $port, $en, $es, 15);
    if (!$fp) { $GLOBALS['smtp_error'] = "Tidak dapat terhubung: $es"; return false; }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp): string { $r = ''; while (($l = fgets($fp, 515)) !== false) { $r .= $l; if (strlen($l) < 4 || $l[3] === ' ') break; } return $r; };
    $cmd = function (string $line, array $okCodes) use ($fp, $read): bool {
        if ($line !== '') fwrite($fp, $line . "\r\n");
        $r = $read();
        if (!in_array((int)substr($r, 0, 3), $okCodes, true)) { $GLOBALS['smtp_error'] = trim($r) ?: 'Server tidak merespons.'; return false; }
        return true;
    };
    $ok = $cmd('', [220]) && $cmd('EHLO zakademia.com', [250]);
    if ($ok && $port !== 465) {
        $ok = $cmd('STARTTLS', [220]) && @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) && $cmd('EHLO zakademia.com', [250]);
    }
    $ok = $ok && $cmd('AUTH LOGIN', [334]) && $cmd(base64_encode($c['user']), [334]) && $cmd(base64_encode($c['pass']), [235]);
    $from = (string)cfg('from_email');
    $ok = $ok && $cmd('MAIL FROM:<' . ($c['user'] ?: $from) . '>', [250]) && $cmd('RCPT TO:<' . $to . '>', [250, 251]) && $cmd('DATA', [354]);
    if ($ok) {
        $h = $headers;
        $h[] = 'To: <' . $to . '>';
        $h[] = 'Subject: ' . $subjHeader;
        $h[] = 'Date: ' . date('r');
        $h[] = 'Message-ID: <' . bin2hex(random_bytes(8)) . '@zakademia.com>';
        $data = implode("\r\n", $h) . "\r\n\r\n" . preg_replace('/^\./m', '..', str_replace("\n", "\r\n", $body));
        $ok = $cmd($data . "\r\n.", [250]);
    }
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}

/* ---------- verifikasi email ---------- */
const DISPOSABLE_DOMAINS = ['mailinator.com', 'guerrillamail.com', 'guerrillamail.info', 'guerrillamail.net', 'sharklasers.com', 'grr.la', '10minutemail.com', '10minutemail.net',
    'tempmail.com', 'temp-mail.org', 'temp-mail.io', 'tempmail.net', 'tempmailo.com', 'tempail.com', 'tempr.email', 'yopmail.com', 'yopmail.net', 'trashmail.com', 'trashmail.de',
    'getnada.com', 'nada.email', 'dispostable.com', 'maildrop.cc', 'mailnesia.com', 'throwawaymail.com', 'fakeinbox.com', 'mohmal.com', 'emailondeck.com', 'minuteinbox.com',
    'mintemail.com', 'spamgourmet.com', 'moakt.com', 'emailfake.com', 'burnermail.io', 'inboxkitten.com', '1secmail.com', '1secmail.org', 'mailcatch.com', 'mytemp.email',
    'tmpmail.org', 'tmpmail.net', 'fakemail.net', 'emailtemporanea.net', 'discard.email', 'spambox.us', 'example.com', 'example.org', 'test.com'];

/** Kembalikan pesan bila email tidak dapat diterima, atau null bila layak. */
function email_problem(string $e): ?string
{
    if (!valid_email($e)) return 'Alamat email belum benar.';
    $dom = strtolower(substr(strrchr($e, '@'), 1));
    if (in_array($dom, DISPOSABLE_DOMAINS, true)) return 'Gunakan email yang aktif dan dapat dihubungi, bukan email sementara.';
    static $dnsOk = null;
    if ($dnsOk === null) $dnsOk = function_exists('checkdnsrr') && @checkdnsrr('gmail.com', 'MX');
    if ($dnsOk && !@checkdnsrr($dom, 'MX') && !@checkdnsrr($dom, 'A')) return 'Domain email tidak ditemukan. Periksa kembali penulisannya.';
    return null;
}

/** Kirim kode 6 angka. Mengembalikan null bila berhasil, atau pesan galat. */
function otp_send(string $key, string $email, string $purpose): ?string
{
    $pdo = db();
    $q = $pdo->prepare('SELECT sent FROM otp WHERE k = ?');
    $q->execute([$key]);
    $last = (int)$q->fetchColumn();
    if ($last && time() - $last < 60) return 'Kode baru saja dikirim. Tunggu ' . (60 - (time() - $last)) . ' detik sebelum meminta lagi.';
    if (!throttle_ok('otp|' . strtolower($email), 6, 3600) || !throttle_ok('otp|' . ip_key(), 15, 3600)) return 'Terlalu banyak permintaan kode. Coba lagi dalam satu jam.';
    $code = (string)random_int(100000, 999999);
    $pdo->prepare('INSERT OR REPLACE INTO otp (k, code_hash, email, expires, tries, sent) VALUES (?,?,?,?,0,?)')
        ->execute([$key, hash('sha256', $key . '|' . $code), strtolower($email), time() + 900, time()]);
    throttle_hit('otp|' . strtolower($email));
    throttle_hit('otp|' . ip_key());
    $ok = send_mail($email, 'Kode verifikasi Zakademia: ' . $code,
        "Assalamu'alaikum,\n\nKode verifikasi Anda untuk $purpose:\n\n    $code\n\nKode berlaku 15 menit. Jangan berikan kode ini kepada siapa pun.\nJika Anda tidak merasa meminta kode ini, abaikan email ini.\n\nSalam,\nTim Zakademia\n");
    if (!$ok) { error_log('otp mail gagal: ' . ($GLOBALS['smtp_error'] ?? '')); return 'Email belum dapat dikirim. Coba lagi beberapa saat, atau hubungi info@zakademia.com.'; }
    return null;
}
/** Periksa kode. Mengembalikan email yang terverifikasi, atau null. */
function otp_check(string $key, string $code, ?string &$err = null): ?string
{
    $pdo = db();
    $q = $pdo->prepare('SELECT * FROM otp WHERE k = ?');
    $q->execute([$key]);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    if (!$r || $r['expires'] < time()) { $err = 'Kode sudah kedaluwarsa. Minta kode baru.'; return null; }
    if ($r['tries'] >= 5) { $err = 'Terlalu banyak percobaan. Minta kode baru.'; return null; }
    if (!hash_equals($r['code_hash'], hash('sha256', $key . '|' . preg_replace('/\D/', '', $code)))) {
        $pdo->prepare('UPDATE otp SET tries = tries + 1 WHERE k = ?')->execute([$key]);
        $err = 'Kode tidak cocok. Periksa kembali email Anda.';
        return null;
    }
    $pdo->prepare('DELETE FROM otp WHERE k = ?')->execute([$key]);
    return $r['email'];
}
function user_verified(?array $u): bool { return $u !== null && ($u['role'] === 'admin' || !empty($u['verified'])); }

/* ---------- sesi dan akun ---------- */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('zkd');
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    // Satu cookie sesi khusus host (www sudah dialihkan ke zakademia.com). Cookie berdomain dari versi sebelumnya dihapus
    // agar tidak ada dua cookie bernama sama yang membuat sesi tidak terbaca.
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
    if (preg_match('/(^|\.)zakademia\.com$/', $host) && !headers_sent()) {
        // header() langsung, karena session_start menghapus Set-Cookie bernama sama yang dipasang sebelumnya
        header('Set-Cookie: zkd=; Expires=Thu, 01 Jan 2015 00:00:00 GMT; Max-Age=0; Path=/; Domain=.zakademia.com; HttpOnly; SameSite=Lax' . ($secure ? '; Secure' : ''), false);
    }
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/** Kata sandi admin versi lama (admin.json) dipindahkan menjadi akun admin dengan email notify_email. */
function migrate_admin_json(PDO $pdo): void
{
    $f = data_dir() . '/admin.json';
    if (!is_file($f)) return;
    $d = json_decode((string)file_get_contents($f), true);
    $email = (string)(cfg('admin_email') ?: cfg('notify_email'));
    if (!empty($d['hash']) && valid_email($email)) {
        $q = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $q->execute([$email]);
        if ($q->fetchColumn()) $pdo->prepare('UPDATE users SET role = "admin" WHERE email = ?')->execute([$email]);
        else $pdo->prepare('INSERT INTO users (email, name, org, role, pass_hash, created) VALUES (?,?,?,?,?,?)')
            ->execute([$email, 'Admin Zakademia', 'Zakademia', 'admin', $d['hash'], gmdate('c')]);
    }
    @rename($f, $f . '.lama');
}

/** Hapus semua akun admin satu kali untuk setiap nilai admin_reset baru di config.php. */
function apply_admin_reset(PDO $pdo): void
{
    $tag = (string)(cfg('admin_reset') ?? '');
    $f = data_dir() . '/admin_reset.txt';
    if ($tag === '' || (is_file($f) && trim((string)file_get_contents($f)) === $tag)) return;
    $pdo->exec('DELETE FROM resets WHERE user_id IN (SELECT id FROM users WHERE role = "admin")');
    $pdo->exec('DELETE FROM users WHERE role = "admin"');
    file_put_contents($f, $tag);
}

function current_user(): ?array
{
    static $u = false;
    if ($u !== false) return $u;
    start_session();
    $u = null;
    if (!empty($_SESSION['uid'])) {
        $q = db()->prepare('SELECT id, email, name, org, org_type, role, verified FROM users WHERE id = ?');
        $q->execute([(int)$_SESSION['uid']]);
        $u = $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    return $u;
}
function is_admin(): bool { $u = current_user(); return $u !== null && $u['role'] === 'admin'; }
function admin_exists(): bool { return (bool)db()->query('SELECT 1 FROM users WHERE role = "admin" LIMIT 1')->fetchColumn(); }

function login_user(int $id): void
{
    start_session();
    session_regenerate_id(true);
    $_SESSION['uid'] = $id;
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
    db()->prepare('UPDATE users SET last_login = ? WHERE id = ?')->execute([gmdate('c'), $id]);
}
function logout_user(): void
{
    start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

/** Pembatas percobaan: kembalikan false bila kunci ini sudah mencapai batas dalam jendela waktu. */
function throttle_ok(string $key, int $max, int $window = 900): bool
{
    $pdo = db();
    $pdo->prepare('DELETE FROM attempts WHERE ts < ?')->execute([time() - 86400]);
    $q = $pdo->prepare('SELECT COUNT(*) FROM attempts WHERE k = ? AND ts > ?');
    $q->execute([$key, time() - $window]);
    return (int)$q->fetchColumn() < $max;
}
function throttle_hit(string $key): void { db()->prepare('INSERT INTO attempts (k, ts) VALUES (?, ?)')->execute([$key, time()]); }
function throttle_clear(string $key): void { db()->prepare('DELETE FROM attempts WHERE k = ?')->execute([$key]); }
function ip_key(): string { return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|zakademia'); }

function base_url(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/asesmen/x.php'), '/');
}
/** Hanya izinkan tujuan di dalam situs sendiri. */
function safe_next(?string $n, string $default): string
{
    $n = (string)$n;
    if ($n === '' || !preg_match('#^/[A-Za-z0-9/_\-.?=&]*$#', $n) || str_starts_with($n, '//')) return $default;
    return $n;
}
/** Tautan checkout Rekomendasi Asesmen di Lynk.id (diatur di /rekomendasi/admin.php). */
function rk_lynk_url(): string
{
    $f = data_dir() . '/rekomendasi.json';
    $c = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    $u = (string)($c['lynk_url'] ?? '');
    return $u !== '' ? $u : 'https://lynk.id/zakademia/x497oomm651d';
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf'] ?? '') . '">'; }
function form_csrf_ok(): bool { return hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? '')); }
function redirect(string $to): void { header('Location: ' . $to); exit; }
function flash(?string $msg = null): ?string
{
    start_session();
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m;
}

const ORG_TYPES = ['Lembaga amil zakat', 'BAZNAS daerah', 'Unit pengumpul zakat', 'Nazhir wakaf', 'Lembaga filantropi kampus', 'Lembaga filantropi korporat', 'Kampus', 'Lainnya'];

/** Tampilan halaman akun (masuk, daftar, lupa kata sandi, akun). */
function page(string $title, string $body, string $width = '440px'): void
{
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="robots" content="noindex"><title>' . e($title) . ' · Zakademia</title><link rel="icon" href="/assets/images/favicon.png">'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">'
        . '<style>:root{--bg:#FFFFF0;--paper:#FFFFFF;--mist:#F2F5E6;--ink:#10291F;--mute:#4A6355;--line:#DCE5D7;--deep:#0B4D33;--gold:#C99A3B;--warn:#9A3B1F}'
        . '*{box-sizing:border-box}body{margin:0;min-height:100vh;background:var(--bg);color:var(--ink);font:400 15px/1.6 "Plus Jakarta Sans",system-ui,sans-serif;display:flex;flex-direction:column}'
        . 'header{border-bottom:1px solid var(--line)}header .in{max-width:1100px;margin:0 auto;padding:14px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}'
        . 'header a.brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--deep);font-weight:800;font-size:18px}header img{width:32px;height:32px}'
        . 'header nav{margin-left:auto;display:flex;gap:6px;flex-wrap:wrap}header nav a{padding:8px 14px;border-radius:999px;text-decoration:none;color:var(--mute);font-weight:600;font-size:14px}header nav a:hover{color:var(--ink);background:var(--mist)}'
        . 'main{flex:1;display:flex;justify-content:center;align-items:flex-start;padding:40px 16px 60px}.box{width:100%;max-width:' . $width . ';background:var(--paper);border:1px solid var(--line);border-radius:24px;padding:28px clamp(18px,4vw,30px);display:flex;flex-direction:column;gap:14px;box-shadow:0 18px 40px -28px rgba(11,77,51,.45)}'
        . 'h1{margin:0;font-size:26px;letter-spacing:-.02em;line-height:1.2}h2{margin:8px 0 0;font-size:18px}p{margin:0}.mute{color:var(--mute);font-size:14px}'
        . 'label{font-weight:600;font-size:14px;display:flex;flex-direction:column;gap:6px}input,select{font:inherit;font-size:16px;border:1px solid var(--line);border-radius:12px;padding:11px 14px;min-height:48px;background:var(--bg);color:var(--ink);width:100%}'
        . '.btn{display:inline-flex;align-items:center;justify-content:center;min-height:48px;padding:10px 22px;border:0;border-radius:999px;background:var(--deep);color:#FFFFF0;font:inherit;font-weight:700;font-size:15px;cursor:pointer;text-decoration:none}'
        . '.btn.ghost{background:transparent;color:var(--ink);border:1.5px solid var(--line)}.btn.gold{background:var(--gold);color:#10291F}'
        . ':focus-visible{outline:2px solid var(--gold);outline-offset:2px}a{color:var(--deep);font-weight:600}.row{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;font-size:14px}'
        . '.err{background:#FBEFE6;border:1px solid #EBCBB4;color:#6E2C16;border-radius:12px;padding:10px 14px;font-size:14px}.ok{background:var(--mist);border:1px solid var(--line);color:var(--deep);border-radius:12px;padding:10px 14px;font-size:14px}'
        . '.hp{position:absolute;left:-9999px}.check{flex-direction:row;align-items:flex-start;gap:10px;font-weight:400;color:var(--mute)}.check input{width:20px;min-height:20px;height:20px;flex:none;margin-top:2px;accent-color:var(--deep)}'
        . 'table{width:100%;border-collapse:collapse;font-size:14px}th,td{text-align:left;padding:10px 8px;border-bottom:1px solid var(--line)}th{font-size:12px;color:var(--mute);text-transform:uppercase;letter-spacing:.06em}'
        . '.pill{font-size:12px;font-weight:700;border-radius:999px;padding:3px 10px;background:#FFF1D6;color:#7A5410;white-space:nowrap}.pill.done{background:var(--mist);color:var(--deep)}'
        . '</style>' . zk_kepala_aset() . '<script src="/js/stat.js?v=2" defer></script></head><body>' . zk_kepala() . '<main><div class="box">' . $body . '</div></main></body></html>';
    exit;
}

function check_csrf(): void
{
    $t = $_SERVER['HTTP_X_CSRF'] ?? ($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$t)) json_out(['ok' => false, 'error' => 'Sesi kedaluwarsa. Muat ulang halaman.'], 403);
}

function row_to_record(array $r): array
{
    $j = json_decode($r['jawaban'], true) ?: [];
    return [
        'id' => $r['id'], 'dikirim' => $r['dikirim'], 'org' => $r['org'], 'type' => $r['type'],
        'pName' => $r['pName'], 'pRole' => $r['pRole'], 'pEmail' => $r['pEmail'], 'pWa' => $r['pWa'],
        'v' => $j['v'] ?? null, 'g' => $j['g'] ?? null, 'imp' => $j['imp'] ?? [], 'tri' => $j['tri'] ?? null,
        'ku' => $j['ku'] ?? null, 'ino' => $j['ino'] ?? null, 'kp' => $j['kp'] ?? (object)[], 'tim' => $j['tim'] ?? null,
        'status' => $r['status'] === 'terkirim' ? 'terkirim' : 'baru', 'terkirimPada' => $r['terkirim_pada'] ?? '',
        'akun' => isset($r['user_id']) && $r['user_id'] !== null,
    ];
}

/* ---------- pratinjau tautan (WhatsApp, Instagram, dll.) ---------- */
const SHARE_CARDS = [
    'riset' => ['Buat Survei | Zakademia', 'Riset Kebutuhan: susun kuesioner, bagikan tautan dan kode QR, pantau jawaban yang masuk.', 'og-riset.png'],
    'analitik' => ['Kesehatan Penghimpunan | Zakademia', 'Data & Analitik: hitung retensi donatur, CLV, CAC, dan biaya per rupiah terhimpun dari angka rekap tahunan.', 'og-analitik.png'],
    'pendampingan' => ['Asesmen Kesiapan Penghimpunan | Zakademia', 'Pendampingan Strategi dimulai dengan asesmen kesiapan penghimpunan lembaga.', 'og-asesmen.png'],
    'rekomendasi' => ['Rekomendasi Asesmen | Zakademia', 'Hasil asesmen kesiapan penghimpunan lembaga Anda beserta 30 langkah prioritas dan indikator ukurnya.', 'og-asesmen.png'],
    'belajar' => ['Ruang Belajar | Zakademia', 'Kelas mandiri zakat, wakaf, data, dan riset: materi singkat, kuis, praktik, dan sertifikat.', 'og-belajar.png'],
];
function share_meta(string $key, string $url): string
{
    [$t, $d, $img] = SHARE_CARDS[$key] ?? ['Zakademia | Riset, Data, dan Literasi Filantropi Islam', 'Ruang untuk bertumbuh, dan bermanfaat bagi sesama.', 'og-image.png'];
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com');
    return '<title>' . e($t) . '</title><meta name="description" content="' . e($d) . '">'
        . '<meta property="og:type" content="website"><meta property="og:site_name" content="Zakademia">'
        . '<meta property="og:title" content="' . e($t) . '"><meta property="og:description" content="' . e($d) . '">'
        . '<meta property="og:url" content="' . e($base . $url) . '"><meta property="og:image" content="' . e($base . '/assets/images/' . $img . '?v=3') . '">'
        . '<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta name="twitter:card" content="summary_large_image">';
}
/** Untuk pengunjung yang belum masuk: tampilkan pratinjau tautan, lalu arahkan ke halaman masuk. */
function share_gate(string $key, string $url, string $to): void
{
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' . share_meta($key, $url)
        . '<link rel="icon" href="/assets/images/favicon.png"><meta http-equiv="refresh" content="0;url=' . e($to) . '"><script>location.replace(' . json_encode($to) . ');</script></head>'
        . '<body style="font-family:system-ui,sans-serif;background:#FFFFF0;color:#10291F;padding:40px 20px"><p>Membuka <a href="' . e($to) . '">halaman masuk Zakademia</a>…</p></body></html>';
    exit;
}

/** Kepala halaman yang sama untuk seluruh situs (inc/kepala.html). Tombol akun diisi di server agar tidak berkedip. */
function zk_kepala(): string
{
    static $tpl = null;
    if ($tpl === null) $tpl = (string)@file_get_contents(__DIR__ . '/../inc/kepala.html');
    $u = current_user();
    [$url, $label] = $u ? ($u['role'] === 'admin' ? ['/admin/', 'Pusat kendali'] : ['/asesmen/akun.php', 'Akun saya']) : ['/asesmen/masuk.php?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/'), 'Masuk'];
    return str_replace(['{{AKUN_URL}}', '{{AKUN_LABEL}}', 'id="zkAkun"'], [e($url), $label, 'id="zkAkun" data-server="1"'], $tpl);
}
function zk_kepala_aset(): string
{
    return '<link rel="stylesheet" href="/css/kepala.css?v=2"><link rel="stylesheet" href="/css/cari.css?v=202610190100"><script src="/js/kepala.js?v=3" defer></script><script src="/js/cari.js?v=202610220100" defer></script>';
}

/** Tautan masuk sekali pakai lewat email (tanpa kata sandi). Mengembalikan pesan galat atau null. */
function tautan_kirim(string $email, string $next): ?string
{
    $email = strtolower(trim($email));
    if (!throttle_ok('tautan|' . $email, 5, 3600) || !throttle_ok('tautan|' . ip_key(), 15, 3600)) return 'Terlalu banyak permintaan. Coba lagi dalam satu jam.';
    throttle_hit('tautan|' . $email); throttle_hit('tautan|' . ip_key());
    $q = db()->prepare('SELECT id, name FROM users WHERE email = ?'); $q->execute([$email]);
    $u = $q->fetch(PDO::FETCH_ASSOC);
    if (!$u) return null; // tidak membocorkan apakah email terdaftar
    $tok = bin2hex(random_bytes(24));
    db()->prepare('INSERT INTO tautan (token_hash, user_id, expires, next) VALUES (?,?,?,?)')->execute([hash('sha256', $tok), (int)$u['id'], time() + 1800, $next]);
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $url = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com') . '/asesmen/masuk.php?t=' . $tok;
    $ok = send_mail($email, 'Tautan masuk Zakademia', "Assalamu'alaikum " . $u['name'] . ",\n\nKlik tautan ini untuk masuk ke Zakademia (berlaku 30 menit, sekali pakai):\n\n$url\n\nJika Anda tidak meminta tautan ini, abaikan saja email ini.\n\nSalam,\nTim Zakademia");
    return $ok ? null : 'Email belum dapat dikirim. Coba lagi beberapa saat.';
}
/** Pakai tautan: masuk, tandai email terverifikasi, kembalikan tujuan. Null bila tautan tidak sah. */
function tautan_pakai(string $tok): ?string
{
    $h = hash('sha256', $tok);
    $q = db()->prepare('SELECT * FROM tautan WHERE token_hash = ?'); $q->execute([$h]);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    if (!$r || $r['used'] || (int)$r['expires'] < time()) return null;
    db()->prepare('UPDATE tautan SET used = 1 WHERE token_hash = ?')->execute([$h]);
    db()->prepare('UPDATE users SET verified = 1 WHERE id = ?')->execute([(int)$r['user_id']]);
    db()->exec('DELETE FROM tautan WHERE expires < ' . (time() - 86400));
    login_user((int)$r['user_id']);
    return (string)($r['next'] ?: '');
}
