<?php
declare(strict_types=1);
require __DIR__ . '/lib_belajar.php';
start_session();
$u = current_user();
if (!$u) json_out(['ok' => false, 'error' => 'Silakan masuk kembali.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'metode'], 405);
check_csrf();
$in = json_decode((string)file_get_contents('php://input', false, null, 0, 2000), true) ?: [];
$m = (string)($in['modul'] ?? '');
$total = (int)($in['total'] ?? 0);
$skor = (int)($in['skor'] ?? -1);
if (!preg_match('/^[a-z][0-9]{1,2}$/', $m) || $total < 1 || $total > 50 || $skor < 0 || $skor > $total) json_out(['ok' => false, 'error' => 'Data tidak valid.'], 422);
try {
    $q = bl_db()->prepare('SELECT terbaik FROM belajar WHERE user_id = ? AND modul = ?');
    $q->execute([$u['id'], $m]);
    $best = max($skor, (int)$q->fetchColumn());
    bl_db()->prepare('INSERT OR REPLACE INTO belajar (user_id, modul, skor, total, terbaik, selesai) VALUES (?,?,?,?,?,?)')
        ->execute([$u['id'], $m, $skor, $total, $best, gmdate('c')]);
    json_out(['ok' => true, 'terbaik' => $best]);
} catch (Throwable $e) {
    error_log('belajar api: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Terjadi kesalahan di server.'], 500);
}
