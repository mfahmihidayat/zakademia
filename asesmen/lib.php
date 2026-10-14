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
    return @mail($to, $subj, $body, implode("\r\n", $headers), '-f' . $from);
}

/* ---------- sesi admin ---------- */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('zkd_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $secure, 'httponly' => true, 'samesite' => 'Strict']);
    session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function admin_file(): string { return data_dir() . '/admin.json'; }
function admin_hash(): ?string
{
    $f = admin_file();
    if (!is_file($f)) return null;
    $d = json_decode((string)file_get_contents($f), true);
    return $d['hash'] ?? null;
}
function set_admin_password(string $pw): void
{
    file_put_contents(admin_file(), json_encode(['hash' => password_hash($pw, PASSWORD_DEFAULT)]), LOCK_EX);
    @chmod(admin_file(), 0640);
}
function is_admin(): bool { return !empty($_SESSION['admin']); }
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
    ];
}
