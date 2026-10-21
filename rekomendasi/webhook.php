<?php
declare(strict_types=1);
/**
 * Webhook transaksi sukses dari Lynk.id.
 * Daftarkan URL lengkapnya (termasuk ?k=...) di Lynk.id: Settings > Integrations > Webhooks.
 * URL lengkap tampil di /rekomendasi/admin.php.
 */
require __DIR__ . '/lib_rekomendasi.php';
header('Cache-Control: no-store');

$conf = rk_conf();
if (!hash_equals($conf['kunci'], (string)($_GET['k'] ?? ''))) json_out(['ok' => false, 'error' => 'kunci'], 403);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') json_out(['ok' => true, 'pesan' => 'Webhook Rekomendasi Asesmen aktif.']);

$raw = (string)file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) { $body = $_POST ?: []; if (!$body) parse_str($raw, $body); }
$hdr = [];
foreach ($_SERVER as $k => $v) if (str_starts_with($k, 'HTTP_') && !in_array($k, ['HTTP_COOKIE', 'HTTP_AUTHORIZATION'], true)) $hdr[substr($k, 5)] = clip($v, 300);

$p = rk_parse(is_array($body) ? $body : []);
$all = strtolower($raw);
if (preg_match('/fail|gagal|pending|expire|cancel|batal|refund/i', $p['status'])) $hasil = 'diabaikan: status ' . $p['status'];
elseif ($conf['kata_kunci'] !== '' && !str_contains($all, strtolower($conf['kata_kunci']))) $hasil = 'diabaikan: produk lain';
elseif ($p['email'] === '') $hasil = 'diabaikan: email pembeli tidak ditemukan';
else {
    $g = rk_grant($p['email'], $p['ref'], $p['produk'], $p['nilai'], 'lynk');
    $hasil = $g['baru'] ? 'akses dibuat' : 'sudah tercatat';
    if ($g['baru']) $hasil .= rk_mail_buyer($p['email']) ? ', email terkirim' : ', email gagal';
}

$pdo = rk_db();
$pdo->prepare('INSERT INTO rk_webhook (diterima, hasil, email, ref, header, isi) VALUES (?,?,?,?,?,?)')
    ->execute([gmdate('c'), $hasil, $p['email'], $p['ref'], json_encode($hdr, JSON_UNESCAPED_SLASHES), mb_substr($raw, 0, 20000)]);
$pdo->exec('DELETE FROM rk_webhook WHERE id NOT IN (SELECT id FROM rk_webhook ORDER BY id DESC LIMIT 100)');
json_out(['ok' => true, 'hasil' => $hasil]);
