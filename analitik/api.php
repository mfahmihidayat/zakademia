<?php
declare(strict_types=1);
require __DIR__ . '/lib_analitik.php';
start_session();
header('Cache-Control: no-store');
$u = current_user();
if (!$u) json_out(['ok' => false, 'error' => 'Silakan masuk kembali.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'metode'], 405);
check_csrf();
$in = json_decode((string)file_get_contents('php://input', false, null, 0, 20000), true) ?: [];
$a = (string)($_GET['a'] ?? '');
try {
    $pdo = an_db();
    if ($a === 'simpan') {
        $tahun = max(2000, min(2100, (int)($in['tahun'] ?? date('Y'))));
        $label = clip($in['label'] ?? '', 120) ?: ('Tahun ' . $tahun);
        $data = json_encode(an_clean(is_array($in['data'] ?? null) ? $in['data'] : []));
        $id = (int)($in['id'] ?? 0);
        if ($id) {
            $q = $pdo->prepare('UPDATE analitik SET label = ?, tahun = ?, data = ?, updated = ? WHERE id = ? AND user_id = ?');
            $q->execute([$label, $tahun, $data, gmdate('c'), $id, $u['id']]);
            if (!$q->rowCount()) json_out(['ok' => false, 'error' => 'Analisis tidak ditemukan.'], 404);
        } else {
            $c = $pdo->prepare('SELECT COUNT(*) FROM analitik WHERE user_id = ?');
            $c->execute([$u['id']]);
            if ((int)$c->fetchColumn() >= 50) json_out(['ok' => false, 'error' => 'Batas 50 analisis per akun tercapai. Hapus yang lama terlebih dahulu.'], 422);
            $pdo->prepare('INSERT INTO analitik (user_id, label, tahun, data, created, updated) VALUES (?,?,?,?,?,?)')->execute([$u['id'], $label, $tahun, $data, gmdate('c'), gmdate('c')]);
            $id = (int)$pdo->lastInsertId();
        }
        json_out(['ok' => true, 'id' => $id, 'label' => $label]);
    }
    if ($a === 'hapus') {
        $pdo->prepare('DELETE FROM analitik WHERE id = ? AND user_id = ?')->execute([(int)($in['id'] ?? 0), $u['id']]);
        json_out(['ok' => true]);
    }
    json_out(['ok' => false, 'error' => 'Perintah tidak dikenal.'], 400);
} catch (Throwable $e) {
    error_log('analitik api: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Terjadi kesalahan di server.'], 500);
}
