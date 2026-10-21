<?php
declare(strict_types=1);
/**
 * Rekomendasi Asesmen: produk digital yang dibayar lewat Lynk.id.
 * Akses dicatat di tabel rk_akses (database asesmen). Sumbernya webhook Lynk.id atau tambahan manual admin.
 */
require_once __DIR__ . '/../asesmen/lib.php';

function rk_db(): PDO
{
    static $done = false;
    $pdo = db();
    if ($done) return $pdo;
    $pdo->exec('CREATE TABLE IF NOT EXISTS rk_akses (
        id INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT NOT NULL COLLATE NOCASE, ref TEXT, produk TEXT, nilai TEXT,
        sumber TEXT NOT NULL, catatan TEXT, dibuat TEXT NOT NULL, user_id INTEGER, diklaim TEXT, aktif INTEGER NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_rk_email ON rk_akses (email)');
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_rk_ref ON rk_akses (ref) WHERE ref IS NOT NULL AND ref <> ""');
    $pdo->exec('CREATE TABLE IF NOT EXISTS rk_webhook (id INTEGER PRIMARY KEY AUTOINCREMENT, diterima TEXT NOT NULL, hasil TEXT NOT NULL, email TEXT, ref TEXT, header TEXT, isi TEXT)');
    $done = true;
    return $pdo;
}

/** Pengaturan disimpan di folder data server (tidak ada di GitHub): kunci webhook, tautan produk, kata kunci produk. */
function rk_conf(): array
{
    $f = data_dir() . '/rekomendasi.json';
    $c = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    $c += ['kunci' => '', 'lynk_url' => '', 'kata_kunci' => 'Rekomendasi', 'merchant_key' => ''];
    if ($c['kunci'] === '') { $c['kunci'] = bin2hex(random_bytes(20)); rk_conf_save($c); }
    return $c;
}
function rk_conf_save(array $c): void
{
    file_put_contents(data_dir() . '/rekomendasi.json', json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    @chmod(data_dir() . '/rekomendasi.json', 0640);
}
function rk_site(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com');
}

/** Akses aktif milik akun ini: sudah diklaim akun ini, atau dibeli dengan email akun ini. */
function rk_access(array $u): ?array
{
    $q = rk_db()->prepare('SELECT * FROM rk_akses WHERE aktif = 1 AND (user_id = ? OR (user_id IS NULL AND email = ?)) ORDER BY id DESC LIMIT 1');
    $q->execute([(int)$u['id'], $u['email']]);
    $r = $q->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($r && $r['user_id'] === null && user_verified($u)) {
        rk_db()->prepare('UPDATE rk_akses SET user_id = ?, diklaim = ? WHERE id = ?')->execute([(int)$u['id'], gmdate('c'), $r['id']]);
        $r['user_id'] = (int)$u['id'];
    }
    return $r;
}
/** Pembelian yang belum diklaim untuk email tertentu. */
function rk_unclaimed(string $email): ?array
{
    $q = rk_db()->prepare('SELECT * FROM rk_akses WHERE aktif = 1 AND user_id IS NULL AND email = ? ORDER BY id DESC LIMIT 1');
    $q->execute([$email]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

/** Kiriman asesmen terbaru milik akun (dari akun atau dari email pengisi). */
function rk_latest_kiriman(array $u): ?array
{
    $q = db()->prepare('SELECT * FROM kiriman WHERE user_id = ? OR (user_id IS NULL AND pEmail = ? COLLATE NOCASE) ORDER BY dikirim DESC');
    $q->execute([(int)$u['id'], $u['email']]);
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rec = row_to_record($r);
        if (($rec['v'] ?? null) === 4) return $rec;
    }
    return null;
}

function rk_grant(string $email, string $ref, string $produk, string $nilai, string $sumber, string $catatan = ''): array
{
    $pdo = rk_db();
    if ($ref !== '') {
        $q = $pdo->prepare('SELECT * FROM rk_akses WHERE ref = ?');
        $q->execute([$ref]);
        if ($row = $q->fetch(PDO::FETCH_ASSOC)) return ['baru' => false, 'row' => $row];
    }
    $pdo->prepare('INSERT INTO rk_akses (email, ref, produk, nilai, sumber, catatan, dibuat) VALUES (?,?,?,?,?,?,?)')
        ->execute([strtolower($email), $ref !== '' ? $ref : null, $produk, $nilai, $sumber, $catatan, gmdate('c')]);
    $id = (int)$pdo->lastInsertId();
    return ['baru' => true, 'row' => ['id' => $id, 'email' => $email]];
}

function rk_mail_buyer(string $email): bool
{
    $url = rk_site() . '/rekomendasi/';
    return send_mail($email, 'Akses Rekomendasi Asesmen Zakademia',
        "Assalamu'alaikum,\n\nTerima kasih, pembayaran Rekomendasi Asesmen sudah kami terima.\n\nBuka rekomendasi lengkap lembaga Anda di:\n$url\n\n"
        . "Masuk dengan email ini ($email). Belum punya akun? Daftar dengan email yang sama, lalu verifikasi.\n"
        . "Rekomendasi disusun dari asesmen yang diisi dari akun atau email ini. Bila belum mengisi, isi dulu di " . rk_site() . "/asesmen/\n\n"
        . "Salam,\nTim Zakademia\ninfo@zakademia.com · +62 856-9209-0699\n");
}

/* ---------- membaca isi webhook Lynk.id (struktur persisnya tidak didokumentasikan publik, jadi dicari dengan longgar) ---------- */
function rk_walk(array $a, string $path, array &$out): void
{
    foreach ($a as $k => $v) {
        $p = $path === '' ? (string)$k : $path . '.' . $k;
        if (is_array($v)) rk_walk($v, $p, $out);
        elseif (is_scalar($v)) $out[] = [strtolower($p), (string)$v];
    }
}
/** Ambil email pembeli, nomor pesanan, nama produk, nilai, dan status dari isi webhook. */
function rk_parse(array $body): array
{
    $flat = [];
    rk_walk($body, '', $flat);
    $pick = function (array $patterns, callable $ok) use ($flat): string {
        foreach ($patterns as $re) foreach ($flat as [$k, $v]) if (preg_match($re, $k) && $ok($v)) return trim($v);
        return '';
    };
    $isMail = fn($v) => valid_email(trim($v));
    $email = $pick(['/(customer|buyer|pembeli|user|payer)[^.]*\.?email$/', '/(customer|buyer|pembeli)\..*email/', '/email/'], $isMail);
    $ref = $pick(['/(^|\.)(refid|ref_id|reference|order_id|orderid|invoice|invoice_id|transaction_id|trx_id|message_id|id_transaksi)$/', '/(ref|order|invoice|transaction)/'], fn($v) => $v !== '' && strlen($v) <= 120);
    $produk = $pick(['/(product|produk|item)s?(\.\d+)?\.(title|name|nama)$/', '/(title|product_name|item_name)$/'], fn($v) => $v !== '');
    $nilai = $pick(['/(grand_total|total|amount|price|harga|nominal)$/'], fn($v) => is_numeric(str_replace(['.', ','], '', $v)));
    $status = $pick(['/(^|\.)(status|event|type|payment_status|transaction_status)$/'], fn($v) => $v !== '');
    return compact('email', 'ref', 'produk', 'nilai', 'status');
}
