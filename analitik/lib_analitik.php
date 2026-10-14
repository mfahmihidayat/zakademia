<?php
declare(strict_types=1);
require_once __DIR__ . '/../survei/lib_survei.php';

const AN_FIELDS = ['danaL', 'danaN', 'donL', 'donN', 'trxL', 'trxN', 'biayaL', 'biayaN', 'baru', 'tahan', 'akuisisi', 'top10'];

function an_db(): PDO
{
    static $done = false;
    $pdo = db();
    if (!$done) {
        $pdo->exec('CREATE TABLE IF NOT EXISTS analitik (
            id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, label TEXT NOT NULL, tahun INTEGER,
            data TEXT NOT NULL, created TEXT NOT NULL, updated TEXT NOT NULL)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_an_user ON analitik (user_id)');
        $done = true;
    }
    return $pdo;
}

/** Hanya angka rekap yang disimpan; nilai kosong tetap kosong. */
function an_clean(array $in): array
{
    $d = [];
    foreach (AN_FIELDS as $k) {
        $v = $in[$k] ?? null;
        $d[$k] = (is_numeric($v) && $v >= 0 && $v < 1e16) ? (float)$v : null;
    }
    return $d;
}
