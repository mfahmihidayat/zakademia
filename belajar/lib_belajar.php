<?php
declare(strict_types=1);
require_once __DIR__ . '/../survei/lib_survei.php';

function bl_db(): PDO
{
    static $done = false;
    $pdo = db();
    if (!$done) {
        $pdo->exec('CREATE TABLE IF NOT EXISTS belajar (user_id INTEGER NOT NULL, modul TEXT NOT NULL, skor INTEGER NOT NULL,
            total INTEGER NOT NULL, terbaik INTEGER NOT NULL, selesai TEXT NOT NULL, PRIMARY KEY (user_id, modul))');
        $done = true;
    }
    return $pdo;
}
