<?php
declare(strict_types=1);
/**
 * Ruang Belajar Zakademia (LMS ringan).
 * Struktur: kelas -> modul -> materi (teks, video, berkas, kuis, praktik). Peserta mendaftar (gratis, beli lewat Lynk.id,
 * atau ditambahkan admin), kemajuan per materi tersimpan, kuis dinilai, dan sertifikat terbit otomatis saat semua
 * materi selesai dan semua kuis lulus. Isi kelas dikelola admin di /belajar/admin.php, tanpa menyentuh kode.
 */
require_once __DIR__ . '/../survei/lib_survei.php';
require_once __DIR__ . '/../literasi/lib_literasi.php';

const LMS_JENIS = ['teks' => 'Bacaan', 'video' => 'Video', 'berkas' => 'Berkas', 'kuis' => 'Kuis', 'praktik' => 'Praktik'];
const LMS_PEMANDU = ['' => 'Tanpa pemandu', 'zaida' => 'Zaida', 'wafiy' => 'Wafiy'];
const LMS_TTD_NAMA = 'M. Fahmi Hidayat';
const LMS_TTD_JABATAN = 'Penanggung Jawab Zakademia';

function lms_db(): PDO
{
    static $ok = false;
    $pdo = db();
    if ($ok) return $pdo;
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_kelas (id INTEGER PRIMARY KEY AUTOINCREMENT, slug TEXT NOT NULL UNIQUE, judul TEXT NOT NULL, ringkas TEXT NOT NULL DEFAULT "",
        deskripsi TEXT NOT NULL DEFAULT "", pemandu TEXT NOT NULL DEFAULT "", tujuan TEXT NOT NULL DEFAULT "", harga TEXT NOT NULL DEFAULT "", lynk_url TEXT NOT NULL DEFAULT "",
        kata_kunci TEXT NOT NULL DEFAULT "", lulus INTEGER NOT NULL DEFAULT 70, sertifikat INTEGER NOT NULL DEFAULT 1, status TEXT NOT NULL DEFAULT "draf",
        urutan INTEGER NOT NULL DEFAULT 0, dibuat TEXT NOT NULL, diubah TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_modul (id INTEGER PRIMARY KEY AUTOINCREMENT, kelas_id INTEGER NOT NULL, judul TEXT NOT NULL, urutan INTEGER NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_materi (id INTEGER PRIMARY KEY AUTOINCREMENT, modul_id INTEGER NOT NULL, judul TEXT NOT NULL, jenis TEXT NOT NULL DEFAULT "teks",
        isi TEXT NOT NULL DEFAULT "", tautan TEXT NOT NULL DEFAULT "", kuis TEXT NOT NULL DEFAULT "[]", menit INTEGER NOT NULL DEFAULT 10, urutan INTEGER NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_lms_materi ON lms_materi (modul_id, urutan)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_daftar (user_id INTEGER NOT NULL, kelas_id INTEGER NOT NULL, sumber TEXT NOT NULL DEFAULT "gratis", dibuat TEXT NOT NULL,
        selesai TEXT, terakhir TEXT, PRIMARY KEY (user_id, kelas_id))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_progres (user_id INTEGER NOT NULL, materi_id INTEGER NOT NULL, selesai TEXT NOT NULL, skor INTEGER, total INTEGER, lulus INTEGER NOT NULL DEFAULT 1,
        PRIMARY KEY (user_id, materi_id))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_beli (id INTEGER PRIMARY KEY AUTOINCREMENT, kelas_id INTEGER NOT NULL, email TEXT NOT NULL COLLATE NOCASE, ref TEXT, nilai TEXT, sumber TEXT NOT NULL,
        catatan TEXT, dibuat TEXT NOT NULL, user_id INTEGER, aktif INTEGER NOT NULL DEFAULT 1)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_lms_beli ON lms_beli (email, kelas_id)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_sertifikat (no TEXT PRIMARY KEY, user_id INTEGER NOT NULL, kelas_id INTEGER NOT NULL, nama TEXT NOT NULL, judul TEXT NOT NULL, tanggal TEXT NOT NULL,
        UNIQUE (user_id, kelas_id))');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lms_tanya (id INTEGER PRIMARY KEY AUTOINCREMENT, materi_id INTEGER NOT NULL, user_id INTEGER NOT NULL, isi TEXT NOT NULL, jawab TEXT, dibuat TEXT NOT NULL, dijawab TEXT)');
    $ok = true;
    lms_seed($pdo);
    lms_seed_tambahan($pdo);
    return $pdo;
}

/** Isi awal: dua kelas dari Ruang Belajar lama (belajar/seed.json) dan pemindahan kemajuan peserta lama. Berjalan sekali. */
function lms_seed(PDO $pdo): void
{
    if ((int)$pdo->query('SELECT COUNT(*) FROM lms_kelas')->fetchColumn() > 0) return;
    $f = __DIR__ . '/seed.json';
    if (!is_file($f)) return;
    $seed = json_decode((string)file_get_contents($f), true);
    if (!is_array($seed)) return;
    $now = gmdate('c'); $peta = [];
    foreach ($seed as $ki => $k) {
        $pdo->prepare('INSERT INTO lms_kelas (slug, judul, ringkas, deskripsi, pemandu, tujuan, lulus, status, urutan, dibuat, diubah) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$k['slug'], $k['judul'], $k['ringkas'], $k['deskripsi'] ?? '', $k['pemandu'], $k['tujuan'] ?? '', 60, 'terbit', $ki, $now, $now]);
        $kid = (int)$pdo->lastInsertId();
        foreach ($k['modul'] as $mi => $m) {
            $pdo->prepare('INSERT INTO lms_modul (kelas_id, judul, urutan) VALUES (?,?,?)')->execute([$kid, $m['judul'], $mi]);
            $mid = (int)$pdo->lastInsertId();
            foreach ($m['materi'] as $ti => $t) {
                $pdo->prepare('INSERT INTO lms_materi (modul_id, judul, jenis, isi, tautan, kuis, menit, urutan) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$mid, $t['judul'], $t['jenis'], $t['isi'] ?? '', $t['tautan'] ?? '', json_encode($t['kuis'] ?? [], JSON_UNESCAPED_UNICODE), (int)($t['menit'] ?? 10), $ti]);
                if (!empty($t['kode'])) $peta[$t['kode']] = ['materi' => (int)$pdo->lastInsertId(), 'kelas' => $kid];
            }
        }
    }
    // kemajuan dari tabel belajar lama (user_id, modul, skor, total, terbaik, selesai)
    try {
        foreach ($pdo->query('SELECT * FROM belajar') as $r) {
            if (!isset($peta[$r['modul']])) continue;
            $p = $peta[$r['modul']];
            $pdo->prepare('INSERT OR IGNORE INTO lms_daftar (user_id, kelas_id, sumber, dibuat) VALUES (?,?,?,?)')->execute([(int)$r['user_id'], $p['kelas'], 'gratis', $r['selesai']]);
            $pdo->prepare('INSERT OR IGNORE INTO lms_progres (user_id, materi_id, selesai, skor, total, lulus) VALUES (?,?,?,?,?,?)')
                ->execute([(int)$r['user_id'], $p['materi'], $r['selesai'], (int)$r['terbaik'], (int)$r['total'], (int)((int)$r['terbaik'] >= ceil((int)$r['total'] * .6))]);
        }
    } catch (Throwable $e) { /* tabel lama tidak ada */ }
}

/** Kelas tambahan dari belajar/seed/*.json: dimasukkan sekali (bila slug belum ada), sebagai draf agar admin memeriksa dulu. */
function lms_seed_tambahan(PDO $pdo): void
{
    foreach (glob(__DIR__ . '/seed/*.json') ?: [] as $f) {
        $k = json_decode((string)file_get_contents($f), true);
        if (!is_array($k) || empty($k['slug'])) continue;
        $q = $pdo->prepare('SELECT id FROM lms_kelas WHERE slug = ?'); $q->execute([$k['slug']]);
        if ($q->fetchColumn()) continue;
        $now = gmdate('c');
        $pdo->prepare('INSERT INTO lms_kelas (slug, judul, ringkas, deskripsi, pemandu, tujuan, harga, lynk_url, kata_kunci, lulus, sertifikat, status, urutan, dibuat, diubah) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$k['slug'], $k['judul'], $k['ringkas'] ?? '', $k['deskripsi'] ?? '', $k['pemandu'] ?? '', $k['tujuan'] ?? '', $k['harga'] ?? '', $k['lynk_url'] ?? '', $k['kata_kunci'] ?? '', (int)($k['lulus'] ?? 70), 1, $k['status'] ?? 'draf', (int)($k['urutan'] ?? 9), $now, $now]);
        $kid = (int)$pdo->lastInsertId();
        foreach ($k['modul'] as $mi => $m) {
            $pdo->prepare('INSERT INTO lms_modul (kelas_id, judul, urutan) VALUES (?,?,?)')->execute([$kid, $m['judul'], $mi]); $mid = (int)$pdo->lastInsertId();
            foreach ($m['materi'] as $ti => $t) $pdo->prepare('INSERT INTO lms_materi (modul_id, judul, jenis, isi, tautan, kuis, menit, urutan) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$mid, $t['judul'], $t['jenis'], $t['isi'] ?? '', $t['tautan'] ?? '', json_encode($t['kuis'] ?? [], JSON_UNESCAPED_UNICODE), (int)($t['menit'] ?? 10), $ti]);
        }
    }
}

/* ---------- pengambilan data ---------- */
function lms_kelas(string $slug): ?array
{
    $q = lms_db()->prepare('SELECT * FROM lms_kelas WHERE slug = ?'); $q->execute([$slug]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
function lms_kelas_id(int $id): ?array
{
    $q = lms_db()->prepare('SELECT * FROM lms_kelas WHERE id = ?'); $q->execute([$id]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
/** Modul beserta materinya, berurutan. */
function lms_isi(int $kelasId): array
{
    $pdo = lms_db();
    $q = $pdo->prepare('SELECT * FROM lms_modul WHERE kelas_id = ? ORDER BY urutan, id'); $q->execute([$kelasId]);
    $mods = $q->fetchAll(PDO::FETCH_ASSOC);
    $qm = $pdo->prepare('SELECT * FROM lms_materi WHERE modul_id = ? ORDER BY urutan, id');
    foreach ($mods as &$m) { $qm->execute([(int)$m['id']]); $m['materi'] = $qm->fetchAll(PDO::FETCH_ASSOC); }
    return $mods;
}
function lms_materi(int $id): ?array
{
    $q = lms_db()->prepare('SELECT t.*, m.kelas_id, m.judul AS modul FROM lms_materi t JOIN lms_modul m ON m.id = t.modul_id WHERE t.id = ?'); $q->execute([$id]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
function lms_semua_materi(int $kelasId): array
{
    $out = [];
    foreach (lms_isi($kelasId) as $m) foreach ($m['materi'] as $t) { $t['modul'] = $m['judul']; $out[] = $t; }
    return $out;
}
function lms_progres(int $uid, int $kelasId): array
{
    $q = lms_db()->prepare('SELECT p.* FROM lms_progres p JOIN lms_materi t ON t.id = p.materi_id JOIN lms_modul m ON m.id = t.modul_id WHERE p.user_id = ? AND m.kelas_id = ?');
    $q->execute([$uid, $kelasId]);
    $out = []; foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) $out[(int)$r['materi_id']] = $r;
    return $out;
}
function lms_terdaftar(int $uid, int $kelasId): ?array
{
    $q = lms_db()->prepare('SELECT * FROM lms_daftar WHERE user_id = ? AND kelas_id = ?'); $q->execute([$uid, $kelasId]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
function lms_berbayar(array $k): bool { return trim($k['harga']) !== '' || trim($k['lynk_url']) !== ''; }

/** Daftarkan peserta; untuk kelas berbayar hanya bila ada pembelian aktif atas emailnya (diklaim otomatis) atau admin. */
function lms_daftarkan(array $u, array $k, string $sumber = 'gratis'): bool
{
    $pdo = lms_db();
    if (lms_terdaftar((int)$u['id'], (int)$k['id'])) return true;
    if (lms_berbayar($k) && $sumber === 'gratis' && $u['role'] !== 'admin') {
        $q = $pdo->prepare('SELECT id FROM lms_beli WHERE kelas_id = ? AND email = ? AND aktif = 1'); $q->execute([(int)$k['id'], $u['email']]);
        $b = $q->fetchColumn();
        if (!$b || !user_verified($u)) return false;
        $pdo->prepare('UPDATE lms_beli SET user_id = ? WHERE id = ?')->execute([(int)$u['id'], (int)$b]);
        $sumber = 'beli';
    }
    $pdo->prepare('INSERT OR IGNORE INTO lms_daftar (user_id, kelas_id, sumber, dibuat) VALUES (?,?,?,?)')->execute([(int)$u['id'], (int)$k['id'], $sumber, gmdate('c')]);
    return true;
}

/** Catat pembelian (webhook Lynk.id atau manual admin). */
function lms_beli(int $kelasId, string $email, string $ref, string $nilai, string $sumber, string $catatan = ''): array
{
    $pdo = lms_db();
    if ($ref !== '') { $q = $pdo->prepare('SELECT * FROM lms_beli WHERE ref = ? AND kelas_id = ?'); $q->execute([$ref, $kelasId]); if ($r = $q->fetch(PDO::FETCH_ASSOC)) return ['baru' => false, 'row' => $r]; }
    $pdo->prepare('INSERT INTO lms_beli (kelas_id, email, ref, nilai, sumber, catatan, dibuat) VALUES (?,?,?,?,?,?,?)')->execute([$kelasId, $email, $ref ?: null, $nilai ?: null, $sumber, $catatan ?: null, gmdate('c')]);
    $id = (int)$pdo->lastInsertId();
    // bila sudah punya akun terverifikasi dengan email itu, langsung terdaftar
    $q = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $q->execute([$email]);
    if (($u = $q->fetch(PDO::FETCH_ASSOC)) && user_verified($u)) { $k = lms_kelas_id($kelasId); if ($k) { $pdo->prepare('UPDATE lms_beli SET user_id = ? WHERE id = ?')->execute([(int)$u['id'], $id]); lms_daftarkan($u, $k, 'beli'); } }
    return ['baru' => true, 'row' => ['id' => $id]];
}
function lms_match(string $raw, array $kelasList): ?array
{
    $t = strtolower($raw);
    foreach ($kelasList as $k) if ($k['kata_kunci'] !== '' && str_contains($t, strtolower($k['kata_kunci']))) return $k;
    return null;
}

/* ---------- kuis ---------- */
/** Format teks kuis admin -> array. Baris: "? pertanyaan", "- opsi", "* opsi benar", "> pembahasan". */
function lms_kuis_parse(string $teks): array
{
    $out = []; $cur = null;
    foreach (preg_split("/\r?\n/", $teks) as $b) {
        $b = rtrim($b); if ($b === '') continue;
        if (str_starts_with($b, '? ')) { if ($cur) $out[] = $cur; $cur = ['q' => trim(substr($b, 2)), 'o' => [], 'a' => 0, 'e' => '']; }
        elseif ($cur && (str_starts_with($b, '- ') || str_starts_with($b, '* '))) { if ($b[0] === '*') $cur['a'] = count($cur['o']); $cur['o'][] = trim(substr($b, 2)); }
        elseif ($cur && str_starts_with($b, '> ')) $cur['e'] = trim(substr($b, 2));
        elseif ($cur) $cur['q'] .= ' ' . trim($b);
    }
    if ($cur) $out[] = $cur;
    return array_values(array_filter($out, fn($q) => count($q['o']) >= 2));
}
function lms_kuis_teks(array $kuis): string
{
    $o = [];
    foreach ($kuis as $q) { $s = '? ' . $q['q'] . "\n"; foreach ($q['o'] as $i => $op) $s .= ($i === (int)$q['a'] ? '* ' : '- ') . $op . "\n"; if (!empty($q['e'])) $s .= '> ' . $q['e'] . "\n"; $o[] = $s; }
    return implode("\n", $o);
}
function lms_kuis(array $t): array { $k = json_decode($t['kuis'], true); return is_array($k) ? $k : []; }

/* ---------- kemajuan dan sertifikat ---------- */
function lms_selesai(int $uid, int $materiId, ?int $skor = null, ?int $total = null, bool $lulus = true): void
{
    lms_db()->prepare('INSERT INTO lms_progres (user_id, materi_id, selesai, skor, total, lulus) VALUES (?,?,?,?,?,?) ON CONFLICT(user_id, materi_id) DO UPDATE SET selesai = excluded.selesai, skor = MAX(COALESCE(lms_progres.skor, 0), excluded.skor), total = excluded.total, lulus = MAX(lms_progres.lulus, excluded.lulus)')
        ->execute([$uid, $materiId, gmdate('c'), $skor, $total, (int)$lulus]);
}
/** Ringkasan kemajuan kelas: jumlah materi, selesai, persen, dan apakah tuntas (semua selesai dan semua kuis lulus). */
function lms_ringkas(int $uid, array $k): array
{
    $materi = lms_semua_materi((int)$k['id']); $prog = lms_progres($uid, (int)$k['id']);
    $n = count($materi); $done = 0; $tuntas = $n > 0;
    foreach ($materi as $t) { $p = $prog[(int)$t['id']] ?? null; if ($p && ($t['jenis'] !== 'kuis' || $p['lulus'])) $done++; else $tuntas = false; }
    return ['jumlah' => $n, 'selesai' => $done, 'persen' => $n ? (int)round($done / $n * 100) : 0, 'tuntas' => $tuntas];
}
/** Terbitkan sertifikat bila tuntas; mengembalikan nomor (atau null). */
function lms_sertifikat(array $u, array $k): ?string
{
    $pdo = lms_db();
    $q = $pdo->prepare('SELECT no FROM lms_sertifikat WHERE user_id = ? AND kelas_id = ?'); $q->execute([(int)$u['id'], (int)$k['id']]);
    if ($no = $q->fetchColumn()) return (string)$no;
    if (!$k['sertifikat'] || !lms_ringkas((int)$u['id'], $k)['tuntas']) return null;
    $th = (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y');
    $n = (int)$pdo->query("SELECT COUNT(*) FROM lms_sertifikat WHERE no LIKE 'ZK-$th-%'")->fetchColumn() + 1;
    do { $no = sprintf('ZK-%s-%04d', $th, $n); $q = $pdo->prepare('SELECT 1 FROM lms_sertifikat WHERE no = ?'); $q->execute([$no]); $n++; } while ($q->fetchColumn());
    $pdo->prepare('INSERT INTO lms_sertifikat (no, user_id, kelas_id, nama, judul, tanggal) VALUES (?,?,?,?,?,?)')->execute([$no, (int)$u['id'], (int)$k['id'], $u['name'], $k['judul'], gmdate('c')]);
    $pdo->prepare('UPDATE lms_daftar SET selesai = ? WHERE user_id = ? AND kelas_id = ?')->execute([gmdate('c'), (int)$u['id'], (int)$k['id']]);
    return $no;
}

/** Gambar sertifikat (GD), A4 lanskap 2000x1414 px. */
function lms_gambar_sertifikat(array $s)
{
    $W = 2000; $H = 1414; $im = imagecreatetruecolor($W, $H); imageantialias($im, true);
    $krem = imagecolorallocate($im, 255, 255, 240); $hijau = imagecolorallocate($im, 11, 77, 51); $hijau2 = imagecolorallocate($im, 20, 97, 63);
    $emas = imagecolorallocate($im, 201, 154, 59); $tinta = imagecolorallocate($im, 16, 41, 31); $abu = imagecolorallocate($im, 74, 99, 85); $mist = imagecolorallocate($im, 242, 245, 230);
    imagefilledrectangle($im, 0, 0, $W, $H, $krem);
    imagefilledrectangle($im, 0, 0, 120, $H, $hijau); imagefilledrectangle($im, 120, 0, 132, $H, $emas);
    imagefilledrectangle($im, 60, 0, 120, $H, $hijau2);
    imagesetthickness($im, 3); imagerectangle($im, 190, 70, $W - 70, $H - 70, $emas);
    $F = __DIR__ . '/fonts/'; $R = $F . 'Inter-Regular.otf'; $B = $F . 'Inter-Bold.otf'; $X = $F . 'Inter-ExtraBold.otf'; $S = $F . 'GreatVibes-Regular.ttf';
    $logo = @imagecreatefrompng(__DIR__ . '/../assets/images/logo-color.png');
    if ($logo) { $lh = 150; $lw = (int)(imagesx($logo) * $lh / imagesy($logo)); imagecopyresampled($im, $logo, 260, 130, 0, 0, $lw, $lh, imagesx($logo), imagesy($logo)); imagedestroy($logo); }
    imagettftext($im, 46, 0, 420, 215, $hijau, $X, 'Zakademia');
    imagettftext($im, 22, 0, 420, 258, $abu, $R, 'Riset, Data, dan Literasi Filantropi Islam');
    $tengah = function (string $teks, int $ukuran, string $font, int $y, int $warna) use ($im, $W) { $b = imagettfbbox($ukuran, 0, $font, $teks); $w = $b[2] - $b[0]; imagettftext($im, $ukuran, 0, (int)(((($W + 190) / 2)) - $w / 2), $y, $warna, $font, $teks); };
    $tengah('SERTIFIKAT PENYELESAIAN', 30, $B, 420, $emas);
    $tengah('diberikan kepada', 24, $R, 500, $abu);
    $nama = $s['nama']; $uk = 76; while ($uk > 40) { $b = imagettfbbox($uk, 0, $X, $nama); if ($b[2] - $b[0] < 1300) break; $uk -= 4; }
    $tengah($nama, $uk, $X, 610, $tinta);
    imagesetthickness($im, 2); imageline($im, 560, 650, $W - 370, 650, $emas);
    $tengah('atas penyelesaian kelas', 24, $R, 720, $abu);
    $judul = $s['judul']; $uk = 44; while ($uk > 26) { $b = imagettfbbox($uk, 0, $B, $judul); if ($b[2] - $b[0] < 1400) break; $uk -= 3; }
    $tengah($judul, $uk, $B, 795, $hijau);
    $tgl = lt_tgl($s['tanggal']);
    $tengah('di Ruang Belajar Zakademia, ' . $tgl, 22, $R, 860, $abu);
    // tanda tangan
    $sx = 1330; $lw = 520; $uk = 80;
    while ($uk > 30) { $b = imagettfbbox($uk, 0, $S, LMS_TTD_NAMA); if ($b[2] - $b[0] <= $lw) break; $uk -= 4; }
    $b = imagettfbbox($uk, 0, $S, LMS_TTD_NAMA); imagettftext($im, $uk, 0, (int)($sx + ($lw - ($b[2] - $b[0])) / 2), 1125, $hijau, $S, LMS_TTD_NAMA);
    imageline($im, $sx, 1150, $sx + $lw, 1150, $tinta);
    imagettftext($im, 24, 0, $sx, 1195, $tinta, $B, LMS_TTD_NAMA);
    imagettftext($im, 20, 0, $sx, 1232, $abu, $R, LMS_TTD_JABATAN);
    // nomor dan verifikasi
    imagettftext($im, 20, 0, 260, 1195, $tinta, $B, 'Nomor ' . $s['no']);
    imagettftext($im, 18, 0, 260, 1232, $abu, $R, 'Periksa keaslian: zakademia.com/belajar/sertifikat/' . $s['no']);
    imagettftext($im, 18, 0, 260, 1268, $abu, $R, 'Sertifikat ini diterbitkan otomatis setelah seluruh materi dan kuis kelas dituntaskan.');
    return $im;
}
/** PDF A4 lanskap berisi satu gambar JPEG (tanpa pustaka luar). */
function lms_pdf_dari_jpeg(string $jpeg, int $w, int $h): string
{
    $pw = 841.89; $ph = 595.28;
    $obj = [];
    $obj[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $obj[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $obj[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pw $ph] /Resources << /XObject << /Im1 4 0 R >> >> /Contents 5 0 R >>";
    $obj[] = "<< /Type /XObject /Subtype /Image /Width $w /Height $h /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpeg) . " >>\nstream\n" . $jpeg . "\nendstream";
    $c = "q $pw 0 0 $ph 0 0 cm /Im1 Do Q";
    $obj[] = "<< /Length " . strlen($c) . " >>\nstream\n" . $c . "\nendstream";
    $out = "%PDF-1.4\n"; $off = [];
    foreach ($obj as $i => $o) { $off[] = strlen($out); $out .= ($i + 1) . " 0 obj\n" . $o . "\nendobj\n"; }
    $x = strlen($out);
    $out .= "xref\n0 " . (count($obj) + 1) . "\n0000000000 65535 f \n";
    foreach ($off as $o) $out .= sprintf("%010d 00000 n \n", $o);
    $out .= "trailer\n<< /Size " . (count($obj) + 1) . " /Root 1 0 R >>\nstartxref\n$x\n%%EOF";
    return $out;
}

/* ---------- tampilan ---------- */
function lms_head(string $title, string $crumb = ''): void
{
    header('Content-Type: text/html; charset=utf-8'); header('Cache-Control: no-store'); header('X-Frame-Options: DENY'); header('Referrer-Policy: same-origin');
    $u = current_user();
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>' . e($title) . ' · Zakademia</title><link rel="icon" href="/assets/images/favicon.png">'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">'
        . '<link rel="stylesheet" href="/survei/assets/sv.css?v=3"><link rel="stylesheet" href="/belajar/lms.css?v=1"><link rel="stylesheet" href="/css/cari.css?v=202610190100"><script src="/js/cari.js?v=202610220100" defer></script></head><body>'
        . '<header class="top"><div class="in"><a class="brand" href="/"><img src="/assets/images/logo-color.png" alt="">Zakademia</a>' . ($crumb ? '<span class="crumb">/ ' . $crumb . '</span>' : '')
        . '<nav><a href="/belajar/">Ruang Belajar</a><a href="/literasi/">Literasi</a>' . ($u ? '<a href="/asesmen/akun.php">Akun saya</a>' . ($u['role'] === 'admin' ? '<a href="/belajar/admin.php">Kelola kelas</a>' : '') . '<a href="/asesmen/keluar.php">Keluar</a>' : '<a href="/asesmen/masuk.php?next=' . e(urlencode($_SERVER['REQUEST_URI'] ?? '/belajar/')) . '">Masuk</a>') . '</nav>'
        . '<button class="zk-cari-btn" type="button" data-cari aria-label="Cari di Zakademia" title="Cari (Ctrl+K)"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg></button></div></header>';
}
function lms_foot(): void { echo '<script src="/js/stat.js?v=2" defer></script></body></html>'; }
function lms_pemandu(string $p): string { return $p === 'zaida' ? '/assets/images/zaida.webp' : ($p === 'wafiy' ? '/assets/images/wafiy.webp' : ''); }
function lms_video_embed(string $url): string
{
    if (str_starts_with($url, '/belajar/video.php')) return '<div class="lms-video"><video controls controlsList="nodownload" playsinline preload="metadata" src="' . e($url) . '" style="position:absolute;inset:0;width:100%;height:100%;background:#000"></video></div>';
    if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})#', $url, $m)) return '<div class="lms-video"><iframe src="https://www.youtube-nocookie.com/embed/' . e($m[1]) . '" title="Video materi" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>';
    return '<p><a class="btn" href="' . e($url) . '" target="_blank" rel="noopener">Buka video</a></p>';
}
