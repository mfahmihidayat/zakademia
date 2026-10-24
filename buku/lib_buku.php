<?php
declare(strict_types=1);
/**
 * Buku digital Zakademia: dibaca di situs dengan tampilan flip, dibayar lewat Lynk.id.
 * Akses dicatat di tabel bk_akses (database asesmen). Sumbernya webhook Lynk.id atau tambahan manual admin
 * (misalnya pembayaran lewat transfer). Pola yang sama dengan /rekomendasi/.
 * Halaman buku disimpan sebagai gambar di buku/halaman/<slug>/ dan hanya dapat diambil lewat hal.php.
 */
require_once __DIR__ . '/../rekomendasi/lib_rekomendasi.php';

/** Katalog buku. Harga dan tautan beli diatur admin di buku.json (folder data server). */
const BK_BUKU = [
    'metodologi' => [
        'judul' => 'Metodologi Penelitian Filantropi Islam',
        'sub' => 'Paradigma, Metode, dan Penerapan dalam Studi Zakat-Wakaf',
        'penulis' => 'M. Fahmi Hidayat, Fauziyah Latiefa Salsabila',
        'penerbit' => 'Zakademia · 2026',
        'ringkas' => 'Panduan memilih, memadukan, dan menerapkan desain penelitian zakat dan wakaf. Sepuluh bab yang mencakup metode normatif-syariah, kuantitatif, kualitatif, historis, campuran, terapan, digital, dan lanjutan, dengan contoh kasus, kotak Dalam Jurnal, kuis, dan kamus istilah di tepi halaman.',
        'kata_kunci' => 'Metodologi',
        'harga' => 'Rp79.000', 'harga_rev' => '2026-10-24',
        'pratinjau' => 24,
        'tahun' => '2026',
        'bab' => [
            ['Pengantar Penelitian Filantropi Islam', 'Memahami penelitian, peta penelitian zakat dan wakaf, analisis SWOT metodologi, cara menggunakan buku'],
            ['Metode Normatif-Syariah', 'Analisis prinsip Qur\'ani, analisis fikih, maqasid al-shari\'ah, istinbat hukum muamalah'],
            ['Metode Kuantitatif', 'Desain deskriptif dan korelasional, variabel dan hipotesis, kuesioner, analisis data kuantitatif'],
            ['Metode Kualitatif', 'Desain penelitian kualitatif, wawancara dan observasi, analisis data kualitatif'],
            ['Metode Historis-Dokumenter', 'Desain historis dan dokumenter, pengumpulan dan kritik sumber, analisis data historis'],
            ['Metode Campuran dan Komparatif', 'Mixed methods dan analisis komparatif antarlembaga dan antarnegara'],
            ['Metode Terapan', 'Penelitian tindakan, penelitian evaluasi, penelitian dan pengembangan (R&D)'],
            ['Metode Digital', 'Penelitian berbasis internet, big data, analisis teks otomatis'],
            ['Metode Lanjutan', 'Analisis jaringan sosial, analisis sekuens, analisis sistem, pemodelan formal'],
            ['Tantangan dan Dampak Penelitian Filantropi Islam', 'Tantangan metodologi, jalan ke depan, penelitian yang membawa perubahan'],
        ],
        'faq' => [
            ['Untuk siapa buku Metodologi Penelitian Filantropi Islam?', 'Mahasiswa S1 hingga S3 ekonomi syariah dan studi Islam, dosen pengampu metodologi penelitian, peneliti, serta amil dan nazhir yang ingin meneliti lembaganya sendiri.'],
            ['Metode apa saja yang dibahas?', 'Sepuluh bab: normatif-syariah, kuantitatif, kualitatif, historis-dokumenter, campuran dan komparatif, terapan, digital, dan metode lanjutan, masing-masing dengan contoh penelitian zakat dan wakaf.'],
            ['Bagaimana cara membacanya?', 'Buku digital dibaca langsung di zakademia.com dalam tampilan buku yang dapat dibalik, diperbesar, dan dicari kata demi kata. Pratinjau gratis tersedia; akses penuh terbuka setelah pembelian.'],
        ],
    ],
];

function bk_db(): PDO
{
    static $done = false;
    $pdo = rk_db();
    if ($done) return $pdo;
    $pdo->exec('CREATE TABLE IF NOT EXISTS bk_akses (
        id INTEGER PRIMARY KEY AUTOINCREMENT, buku TEXT NOT NULL, email TEXT NOT NULL COLLATE NOCASE, ref TEXT, produk TEXT, nilai TEXT,
        sumber TEXT NOT NULL, catatan TEXT, dibuat TEXT NOT NULL, user_id INTEGER, diklaim TEXT, aktif INTEGER NOT NULL DEFAULT 1, terakhir TEXT)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_bk_email ON bk_akses (buku, email)');
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_bk_ref ON bk_akses (ref) WHERE ref IS NOT NULL AND ref <> ""');
    $pdo->exec('CREATE TABLE IF NOT EXISTS bk_webhook (id INTEGER PRIMARY KEY AUTOINCREMENT, diterima TEXT NOT NULL, hasil TEXT NOT NULL, email TEXT, ref TEXT, header TEXT, isi TEXT)');
    $done = true;
    return $pdo;
}

/** Pengaturan per buku di folder data server: kunci webhook, tautan Lynk.id, harga, kata kunci produk, jumlah halaman pratinjau. */
function bk_conf(): array
{
    $f = data_dir() . '/buku.json';
    $c = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    $c += ['kunci' => '', 'buku' => []];
    $ubah = false;
    if ($c['kunci'] === '') { $c['kunci'] = bin2hex(random_bytes(20)); $ubah = true; }
    foreach (BK_BUKU as $slug => $b) {
        $c['buku'][$slug] = ($c['buku'][$slug] ?? []) + ['lynk_url' => '', 'harga' => '', 'kata_kunci' => $b['kata_kunci'], 'pratinjau' => $b['pratinjau'], 'aktif' => 1];        /* harga baru dari katalog diterapkan sekali; setelah itu admin tetap bisa mengubahnya */
        if (($b['harga_rev'] ?? '') !== '' && ($c['buku'][$slug]['harga_rev'] ?? '') !== $b['harga_rev']) {
            $c['buku'][$slug]['harga'] = $b['harga'];
            $c['buku'][$slug]['harga_rev'] = $b['harga_rev'];
            $ubah = true;
        }
    }
    if ($ubah) bk_conf_save($c);
    return $c;
}
/** Teks harga selalu berawalan Rp dengan titik ribuan: "79000" atau "Rp 79.000,-" menjadi "Rp79.000". Teks lain dibiarkan. */
function bk_rp(string $h): string
{
    $h = trim($h);
    if ($h === '') return '';
    $angka = preg_replace('/,-?$|,00$/', '', preg_replace('/^rp\.?\s*/i', '', $h));
    if (!preg_match('/^\d{1,3}(\.\d{3})*$|^\d+$/', $angka)) return $h;
    return 'Rp' . number_format((int)str_replace('.', '', $angka), 0, ',', '.');
}
function bk_conf_save(array $c): void
{
    file_put_contents(data_dir() . '/buku.json', json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    @chmod(data_dir() . '/buku.json', 0640);
}
function bk_slug(?string $s): string
{
    $s = strtolower((string)$s);
    return isset(BK_BUKU[$s]) ? $s : '';
}
function bk_meta(string $slug): ?array
{
    $f = __DIR__ . '/halaman/' . $slug . '/meta.json';
    return is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: null) : null;
}

/** Akses aktif milik akun ini untuk satu buku: sudah diklaim akun ini, atau dibeli dengan email akun ini. Admin selalu punya akses. */
function bk_access(array $u, string $slug): ?array
{
    if ($u['role'] === 'admin') return ['id' => 0, 'sumber' => 'admin', 'email' => $u['email']];
    $q = bk_db()->prepare('SELECT * FROM bk_akses WHERE buku = ? AND aktif = 1 AND (user_id = ? OR (user_id IS NULL AND email = ?)) ORDER BY id DESC LIMIT 1');
    $q->execute([$slug, (int)$u['id'], $u['email']]);
    $r = $q->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($r && $r['user_id'] === null && user_verified($u)) {
        bk_db()->prepare('UPDATE bk_akses SET user_id = ?, diklaim = ? WHERE id = ?')->execute([(int)$u['id'], gmdate('c'), $r['id']]);
        $r['user_id'] = (int)$u['id'];
    }
    return $r;
}
function bk_unclaimed(string $slug, string $email): ?array
{
    $q = bk_db()->prepare('SELECT * FROM bk_akses WHERE buku = ? AND aktif = 1 AND user_id IS NULL AND email = ? ORDER BY id DESC LIMIT 1');
    $q->execute([$slug, $email]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}
function bk_grant(string $slug, string $email, string $ref, string $produk, string $nilai, string $sumber, string $catatan = ''): array
{
    $pdo = bk_db();
    if ($ref !== '') {
        $q = $pdo->prepare('SELECT * FROM bk_akses WHERE ref = ?');
        $q->execute([$ref]);
        if ($row = $q->fetch(PDO::FETCH_ASSOC)) return ['baru' => false, 'row' => $row];
    }
    $pdo->prepare('INSERT INTO bk_akses (buku, email, ref, produk, nilai, sumber, catatan, dibuat) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([$slug, strtolower($email), $ref !== '' ? $ref : null, $produk, $nilai, $sumber, $catatan, gmdate('c')]);
    return ['baru' => true, 'row' => ['id' => (int)$pdo->lastInsertId(), 'email' => $email]];
}
function bk_mail_buyer(string $slug, string $email): bool
{
    $b = BK_BUKU[$slug];
    $url = rk_site() . '/buku/' . $slug . '/';
    return send_mail($email, 'Akses buku digital ' . $b['judul'],
        "Assalamu'alaikum,\n\nTerima kasih, pembayaran buku digital \"{$b['judul']}\" sudah kami terima.\n\nBaca bukunya di:\n$url\n\n"
        . "Masuk dengan email ini ($email). Belum punya akun? Daftar dengan email yang sama, lalu verifikasi.\n"
        . "Buku dibaca langsung di situs (tidak diunduh) dan dapat dibuka kapan saja dari akun Anda.\n\n"
        . "Salam,\nTim Zakademia\ninfo@zakademia.com · +62 856-9209-0699\n");
}

/** Tentukan buku mana yang dibeli dari isi webhook, berdasarkan kata kunci nama produk. */
function bk_match(string $raw, array $conf): string
{
    $all = strtolower($raw);
    foreach ($conf['buku'] as $slug => $c) {
        if ($c['kata_kunci'] !== '' && str_contains($all, strtolower($c['kata_kunci']))) return $slug;
    }
    return '';
}
