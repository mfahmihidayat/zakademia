<?php
declare(strict_types=1);
/**
 * Webhook transaksi sukses dari Lynk.id untuk buku digital.
 * Daftarkan URL lengkapnya (termasuk ?k=...) di Lynk.id: Settings > Integrations > Webhooks. URL tampil di /buku/admin.php.
 * Satu URL melayani semua buku; buku dikenali dari kata kunci nama produk.
 */
require __DIR__ . '/lib_buku.php';
header('Cache-Control: no-store');

$conf = bk_conf();
if (!hash_equals($conf['kunci'], (string)($_GET['k'] ?? ''))) json_out(['ok' => false, 'error' => 'kunci'], 403);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_out(['ok' => true, 'pesan' => 'Webhook buku digital Zakademia aktif.']);

$raw = (string)file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) { $body = $_POST ?: []; if (!$body) parse_str($raw, $body); }
$hdr = [];
foreach ($_SERVER as $k => $v) if (str_starts_with($k, 'HTTP_') && !in_array($k, ['HTTP_COOKIE', 'HTTP_AUTHORIZATION'], true)) $hdr[substr($k, 5)] = clip($v, 300);

$p = rk_parse(is_array($body) ? $body : []);
$slug = bk_match($raw, $conf);
if (preg_match('/fail|gagal|pending|expire|cancel|batal|refund/i', $p['status'])) $hasil = 'diabaikan: status ' . $p['status'];
elseif ($slug === '') $hasil = 'diabaikan: bukan produk buku';
elseif ($p['email'] === '') $hasil = 'diabaikan: email pembeli tidak ditemukan';
else {
    $g = bk_grant($slug, $p['email'], $p['ref'], $p['produk'], $p['nilai'], 'lynk');
    $hasil = ($g['baru'] ? 'akses dibuat' : 'sudah tercatat') . ' (' . $slug . ')';
    if ($g['baru']) $hasil .= bk_mail_buyer($slug, $p['email']) ? ', email terkirim' : ', email gagal';
}

$pdo = bk_db();
$pdo->prepare('INSERT INTO bk_webhook (diterima, hasil, email, ref, header, isi) VALUES (?,?,?,?,?,?)')
    ->execute([gmdate('c'), $hasil, $p['email'], $p['ref'], json_encode($hdr, JSON_UNESCAPED_SLASHES), mb_substr($raw, 0, 20000)]);
$pdo->exec('DELETE FROM bk_webhook WHERE id NOT IN (SELECT id FROM bk_webhook ORDER BY id DESC LIMIT 100)');
json_out(['ok' => true, 'hasil' => $hasil]);
