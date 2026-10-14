<?php
declare(strict_types=1);
require __DIR__ . '/lib_survei.php';
start_session();
header('Cache-Control: no-store');

$u = current_user();
if (!$u) json_out(['ok' => false, 'error' => 'Silakan masuk kembali.', 'login' => true], 401);
$act = (string)($_GET['a'] ?? '');

try {
    /* ---------- baca ---------- */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Unduhan CSV hanya untuk admin; pemilik survei melihat hasil di layar.
        if ($act === 'csv' && $u['role'] !== 'admin') json_out(['ok' => false, 'error' => 'Unduhan data hanya tersedia di dashboard admin.'], 403);
        if ($act === 'hasil' || $act === 'csv') {
            $s = sv_get_owned((int)($_GET['id'] ?? 0), $u);
            if (!$s) json_out(['ok' => false, 'error' => 'Survei tidak ditemukan.'], 404);
            $q = sv_db()->prepare('SELECT id, created, answers, duration FROM responses WHERE survey_id = ? ORDER BY id');
            $q->execute([$s['id']]);
            $rows = $q->fetchAll(PDO::FETCH_ASSOC);
            $def = json_decode($s['def'], true);
            if ($act === 'csv') sv_csv($s, $def, $rows);
            json_out(['ok' => true, 'def' => $def, 'rows' => array_map(fn($r) => ['id' => (int)$r['id'], 'at' => $r['created'], 'dur' => $r['duration'] !== null ? (int)$r['duration'] : null, 'a' => json_decode($r['answers'], true)], $rows)]);
        }
        json_out(['ok' => false, 'error' => 'Perintah tidak dikenal.'], 400);
    }

    /* ---------- ubah (POST + CSRF) ---------- */
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'error' => 'metode'], 405);
    check_csrf();
    $in = json_decode((string)file_get_contents('php://input', false, null, 0, 400000), true);
    if (!is_array($in)) $in = $_POST;
    $pdo = sv_db();

    if ($act === 'buat') {
        $q = $pdo->prepare('SELECT COUNT(*) FROM surveys WHERE user_id = ?');
        $q->execute([$u['id']]);
        if ((int)$q->fetchColumn() >= 100) json_out(['ok' => false, 'error' => 'Batas 100 survei per akun tercapai.'], 422);
        $def = sv_from_template((string)($in['tpl'] ?? 'kosong'));
        $slug = sv_unique_slug($def['title']);
        $now = gmdate('c');
        $pdo->prepare('INSERT INTO surveys (user_id, slug, title, def, status, created, updated) VALUES (?,?,?,?,?,?,?)')
            ->execute([$u['id'], $slug, $def['title'], json_encode($def), 'draf', $now, $now]);
        json_out(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    }

    $s = sv_get_owned((int)($in['id'] ?? 0), $u);
    if (!$s) json_out(['ok' => false, 'error' => 'Survei tidak ditemukan.'], 404);

    if ($act === 'simpan') {
        $def = sv_clean_def(is_array($in['def'] ?? null) ? $in['def'] : []);
        $pdo->prepare('UPDATE surveys SET title = ?, def = ?, updated = ? WHERE id = ?')->execute([$def['title'], json_encode($def), gmdate('c'), $s['id']]);
        json_out(['ok' => true, 'def' => $def]);
    }
    if ($act === 'atur') {
        $slug = strtolower(trim((string)($in['slug'] ?? $s['slug'])));
        $status = in_array($in['status'] ?? '', ['draf', 'aktif', 'tutup'], true) ? $in['status'] : $s['status'];
        if (!sv_slug_ok($slug)) json_out(['ok' => false, 'error' => 'Nama tautan 3–60 karakter: huruf kecil, angka, dan tanda hubung, tanpa spasi.'], 422);
        if (!sv_slug_free($slug, (int)$s['id'])) json_out(['ok' => false, 'error' => 'Nama tautan sudah dipakai survei lain.'], 409);
        $pdo->prepare('UPDATE surveys SET slug = ?, status = ?, updated = ? WHERE id = ?')->execute([$slug, $status, gmdate('c'), $s['id']]);
        json_out(['ok' => true, 'slug' => $slug, 'status' => $status, 'url' => sv_public_url($slug)]);
    }
    if ($act === 'gandakan') {
        $def = json_decode($s['def'], true);
        $def['title'] = clip('Salinan ' . $def['title'], 200);
        $now = gmdate('c');
        $pdo->prepare('INSERT INTO surveys (user_id, slug, title, def, status, created, updated) VALUES (?,?,?,?,?,?,?)')
            ->execute([$u['id'], sv_unique_slug($def['title']), $def['title'], json_encode($def), 'draf', $now, $now]);
        json_out(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    }
    if ($act === 'hapus') {
        $pdo->prepare('DELETE FROM responses WHERE survey_id = ?')->execute([$s['id']]);
        $pdo->prepare('DELETE FROM surveys WHERE id = ?')->execute([$s['id']]);
        json_out(['ok' => true]);
    }
    if ($act === 'hapus_jawaban') {
        $pdo->prepare('DELETE FROM responses WHERE survey_id = ? AND id = ?')->execute([$s['id'], (int)($in['rid'] ?? 0)]);
        json_out(['ok' => true]);
    }
    json_out(['ok' => false, 'error' => 'Perintah tidak dikenal.'], 400);
} catch (Throwable $e) {
    error_log('survei api: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Terjadi kesalahan di server.'], 500);
}
