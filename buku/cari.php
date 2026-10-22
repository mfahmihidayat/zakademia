<?php
declare(strict_types=1);
/** Cari teks di dalam buku: ?b=slug&q=kata. Tanpa akses penuh, hasil dibatasi halaman pratinjau (jumlah hasil lain tetap diberitahukan). */
require __DIR__ . '/lib_buku.php';
start_session();
header('Cache-Control: no-store');
$slug = bk_slug($_GET['b'] ?? ''); $q = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
if ($slug === '' || mb_strlen($q) < 3) json_out(['ok' => false, 'error' => 'Ketik minimal 3 huruf.'], 422);
$f = __DIR__ . '/halaman/' . $slug . '/teks.json';
if (!is_file($f)) json_out(['ok' => false, 'error' => 'Pencarian belum tersedia untuk buku ini.'], 404);
if (!throttle_ok('bkcari|' . ip_key(), 120, 600)) json_out(['ok' => false, 'error' => 'Terlalu banyak pencarian. Tunggu sebentar.'], 429);
throttle_hit('bkcari|' . ip_key());
$u = current_user(); $conf = bk_conf(); $prat = (int)$conf['buku'][$slug]['pratinjau'];
$penuh = $u && user_verified($u) && bk_access($u, $slug);
$teks = json_decode((string)file_get_contents($f), true) ?: [];
$qn = mb_strtolower($q); $hasil = []; $terkunci = 0; $total = 0;
foreach ($teks as $n => $t) {
    $n = (int)$n; $tl = mb_strtolower($t); $pos = mb_strpos($tl, $qn);
    if ($pos === false) continue;
    $total++;
    if (!$penuh && $n > $prat) { $terkunci++; continue; }
    $jml = mb_substr_count($tl, $qn);
    $a = max(0, $pos - 70); $cup = mb_substr($t, $a, 70 + mb_strlen($q) + 90);
    $cup = preg_replace('/' . preg_quote($q, '/') . '/iu', '<mark>$0</mark>', e($cup));
    $hasil[] = ['n' => $n, 'k' => $jml, 's' => ($a > 0 ? '…' : '') . $cup . '…'];
    if (count($hasil) >= 60) break;
}
json_out(['ok' => true, 'q' => $q, 'total' => $total, 'hasil' => $hasil, 'terkunci' => $terkunci, 'penuh' => (bool)$penuh]);
