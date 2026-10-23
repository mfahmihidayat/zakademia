<?php
declare(strict_types=1);
/**
 * Literasi kontributor Zakademia: pengguna terverifikasi menulis, admin menyeleksi, tulisan terbit di /literasi/k/<slug>/.
 * Status tulisan: draf -> ditinjau -> (terbit | revisi | ditolak); terbit dapat ditarik kembali ke draf.
 * Tulisan disimpan di tabel lt_tulisan (database asesmen), profil penulis di lt_penulis.
 */
require_once __DIR__ . '/../asesmen/lib.php';

const LT_RUBRIK = [
    'flash' => ['ZakaFlash', 'Apa yang baru?', 'rb-flash.webp'],
    'pedia' => ['ZakaPedia', 'Apa artinya?', 'rb-pedia.webp'],
    'story' => ['ZakaStory', 'Siapa yang merasakan?', 'rb-story.webp'],
    'tips'  => ['ZakaTips', 'Bagaimana caranya?', 'rb-tips.webp'],
    'stat'  => ['ZakaStat', 'Berapa besarnya?', 'rb-stat.webp'],
    'talk'  => ['ZakaTalk', 'Apa kata kamu?', 'rb-talk.webp'],
];
const LT_STATUS = ['draf' => 'Draf', 'ditinjau' => 'Menunggu tinjauan', 'revisi' => 'Perlu revisi', 'terbit' => 'Terbit', 'ditolak' => 'Tidak diterbitkan'];
const LT_BASE = 'https://zakademia.com';

function lt_db(): PDO
{
    static $ok = false;
    $pdo = db();
    if ($ok) return $pdo;
    $pdo->exec('CREATE TABLE IF NOT EXISTS lt_penulis (
        user_id INTEGER PRIMARY KEY, nama TEXT NOT NULL, afiliasi TEXT, bio TEXT, tautan TEXT, tepercaya INTEGER NOT NULL DEFAULT 0, dibuat TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS lt_tulisan (
        id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, slug TEXT, judul TEXT NOT NULL DEFAULT "", rubrik TEXT NOT NULL DEFAULT "talk",
        dek TEXT NOT NULL DEFAULT "", poin TEXT NOT NULL DEFAULT "", isi TEXT NOT NULL DEFAULT "", sumber TEXT NOT NULL DEFAULT "[]",
        status TEXT NOT NULL DEFAULT "draf", catatan TEXT, dibuat TEXT NOT NULL, diubah TEXT NOT NULL, dikirim TEXT, terbit TEXT, dilihat INTEGER NOT NULL DEFAULT 0)');
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_lt_slug ON lt_tulisan (slug)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_lt_status ON lt_tulisan (status, terbit)');
    $ok = true;
    return $pdo;
}

function lt_penulis(int $uid): ?array
{
    $q = lt_db()->prepare('SELECT p.*, u.email FROM lt_penulis p JOIN users u ON u.id = p.user_id WHERE p.user_id = ?');
    $q->execute([$uid]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

function lt_tulisan(int $id): ?array
{
    $q = lt_db()->prepare('SELECT t.*, p.nama AS penulis, p.afiliasi, u.email FROM lt_tulisan t LEFT JOIN lt_penulis p ON p.user_id = t.user_id JOIN users u ON u.id = t.user_id WHERE t.id = ?');
    $q->execute([$id]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

function lt_tulisan_slug(string $slug): ?array
{
    $q = lt_db()->prepare('SELECT t.*, p.nama AS penulis, p.afiliasi, p.bio, p.tautan, u.email FROM lt_tulisan t LEFT JOIN lt_penulis p ON p.user_id = t.user_id JOIN users u ON u.id = t.user_id WHERE t.slug = ?');
    $q->execute([$slug]);
    return $q->fetch(PDO::FETCH_ASSOC) ?: null;
}

/** Slug unik dari judul; slug lama dipertahankan bila sudah ada supaya tautan tidak berubah. */
function lt_slug(string $judul, ?string $lama, int $id): string
{
    if ($lama) return $lama;
    $s = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $judul) ?: $judul), '-'));
    $s = mb_substr($s ?: 'tulisan', 0, 70);
    $pdo = lt_db(); $base = $s; $n = 2;
    while (true) {
        $q = $pdo->prepare('SELECT id FROM lt_tulisan WHERE slug = ? AND id != ?'); $q->execute([$s, $id]);
        if (!$q->fetchColumn()) return $s;
        $s = $base . '-' . $n++;
    }
}

/* ---------- teks tulisan: format sederhana yang aman ----------
 * Baris kosong memisahkan paragraf. "## " subjudul, "- " daftar, "1. " daftar bernomor, **tebal**, *miring*,
 * [teks](https://alamat) tautan, {1} rujukan ke sumber nomor 1. Semua HTML dari penulis di-escape. */
function lt_inline(string $s): string
{
    $s = e($s);
    $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
    $s = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $s);
    $s = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/', fn($m) => '<a href="' . $m[2] . '" target="_blank" rel="noopener">' . $m[1] . '</a>', $s);
    $s = preg_replace('/\{(\d{1,2})\}/', '<sup class="ref"><a href="#sumber-$1" aria-label="Sumber $1">$1</a></sup>', $s);
    return $s;
}
function lt_html(string $teks): string
{
    $teks = str_replace(["\r\n", "\r"], "\n", $teks);
    $out = ''; $list = null;
    $tutup = function () use (&$out, &$list) { if ($list) { $out .= "</$list>\n"; $list = null; } };
    foreach (preg_split("/\n{2,}/", trim($teks)) as $blok) {
        $baris = explode("\n", trim($blok));
        if (preg_match('/^##\s+(.+)/', $baris[0], $m) && count($baris) === 1) { $tutup(); $out .= '<h2>' . lt_inline($m[1]) . "</h2>\n"; continue; }
        if (preg_match('/^###\s+(.+)/', $baris[0], $m) && count($baris) === 1) { $tutup(); $out .= '<h3>' . lt_inline($m[1]) . "</h3>\n"; continue; }
        $semuaList = true; $jenis = null;
        foreach ($baris as $b) {
            if (preg_match('/^[-*]\s+/', $b)) { if ($jenis === 'ol') $semuaList = false; $jenis = 'ul'; }
            elseif (preg_match('/^\d+[.)]\s+/', $b)) { if ($jenis === 'ul') $semuaList = false; $jenis = 'ol'; }
            else $semuaList = false;
        }
        if ($semuaList && $jenis) {
            $tutup(); $out .= "<$jenis>";
            foreach ($baris as $b) $out .= '<li>' . lt_inline(preg_replace('/^([-*]|\d+[.)])\s+/', '', $b)) . '</li>';
            $out .= "</$jenis>\n"; continue;
        }
        if (preg_match('/^>\s?/', $baris[0])) { $tutup(); $out .= '<blockquote><p>' . lt_inline(preg_replace('/^>\s?/m', '', implode("\n", $baris))) . "</p></blockquote>\n"; continue; }
        $tutup(); $out .= '<p>' . str_replace("\n", '<br>', lt_inline(implode("\n", $baris))) . "</p>\n";
    }
    $tutup();
    return $out;
}

function lt_menit(string $teks): int { return max(1, (int)round(str_word_count(strip_tags($teks)) / 200)); }
function lt_tgl(?string $iso): string
{
    if (!$iso) return '';
    $b = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    try { $t = (new DateTime($iso))->setTimezone(new DateTimeZone('Asia/Jakarta')); } catch (Throwable $x) { return $iso; }
    return $t->format('j') . ' ' . $b[(int)$t->format('n') - 1] . ' ' . $t->format('Y');
}
function lt_poin(array $t): array { return array_values(array_filter(array_map('trim', explode("\n", $t['poin'])), fn($p) => $p !== '')); }
function lt_sumber(array $t): array { $s = json_decode($t['sumber'], true); return is_array($s) ? $s : []; }
function lt_url(array $t): string { return '/literasi/k/' . $t['slug'] . '/'; }
function lt_inisial(string $nama): string
{
    $p = preg_split('/\s+/', trim($nama)); $i = mb_strtoupper(mb_substr($p[0], 0, 1)) . (count($p) > 1 ? mb_strtoupper(mb_substr(end($p), 0, 1)) : '');
    return $i ?: 'Z';
}

/** Periksa kelengkapan sebelum dikirim. Mengembalikan daftar kekurangan (kosong bila lengkap). */
function lt_periksa(array $t): array
{
    $k = [];
    if (mb_strlen(trim($t['judul'])) < 12) $k[] = 'Judul minimal 12 karakter.';
    if (mb_strlen(trim($t['judul'])) > 110) $k[] = 'Judul maksimal 110 karakter.';
    if (!isset(LT_RUBRIK[$t['rubrik']])) $k[] = 'Pilih rubrik.';
    $d = mb_strlen(trim($t['dek'])); if ($d < 60 || $d > 240) $k[] = 'Ringkasan antara 60 dan 240 karakter (sekarang ' . $d . ').';
    $p = lt_poin($t); if (count($p) < 2 || count($p) > 4) $k[] = 'Poin penting 2 sampai 4 baris.';
    $kata = str_word_count(strip_tags(lt_html($t['isi']))); if ($kata < 350) $k[] = 'Isi tulisan minimal 350 kata (sekarang ' . $kata . ').';
    if ($kata > 2500) $k[] = 'Isi tulisan maksimal 2.500 kata (sekarang ' . $kata . ').';
    $s = lt_sumber($t); $n = 0;
    foreach ($s as $x) if (trim($x['judul'] ?? '') !== '' && preg_match('#^https?://#', $x['url'] ?? '')) $n++;
    if ($n < 2) $k[] = 'Minimal 2 sumber dengan judul dan tautan yang valid (sekarang ' . $n . ').';
    return $k;
}

/* ---------- tampilan halaman (meniru template tools/literasi/build.py) ---------- */
function lt_chip(string $r, bool $big = false): string
{
    [$n, $q, $ic] = LT_RUBRIK[$r] ?? LT_RUBRIK['talk'];
    return '<span class="lt-chip' . ($big ? ' big' : '') . '"><img src="/assets/images/' . $ic . '" alt=""><b>' . $n . '</b>' . ($big ? '<i>' . e($q) . '</i>' : '') . '</span>';
}
function lt_card(array $t): string
{
    $ic = LT_RUBRIK[$t['rubrik']][2] ?? 'rb-talk.webp';
    return '<a class="lt-card" href="' . e(lt_url($t)) . '"><span class="lt-thumb rub"><img src="/assets/images/' . $ic . '" alt="" loading="lazy"></span>' . lt_chip($t['rubrik']) . '<h3>' . e($t['judul']) . '</h3><p>' . e($t['dek']) . '</p>'
        . '<span class="lt-meta">' . e(lt_tgl($t['terbit'] ?: $t['diubah'])) . ' · ' . lt_menit($t['isi']) . ' menit baca · ' . e($t['penulis'] ?: 'Kontributor') . '</span></a>';
}
function lt_head(string $title, string $desc, string $canon, string $img, string $extra = '', bool $noindex = false): string
{
    return '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">'
        . '<title>' . e($title) . '</title><meta name="description" content="' . e($desc) . '">' . ($noindex ? '<meta name="robots" content="noindex">' : '<link rel="canonical" href="' . e($canon) . '">')
        . '<meta property="og:type" content="article"><meta property="og:site_name" content="Zakademia"><meta property="og:title" content="' . e($title) . '"><meta property="og:description" content="' . e($desc) . '">'
        . '<meta property="og:url" content="' . e($canon) . '"><meta property="og:image" content="' . e($img) . '"><meta name="twitter:card" content="summary_large_image">'
        . '<link rel="icon" href="/assets/images/favicon.png"><link rel="preconnect" href="https://fonts.googleapis.com">'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">'
        . '<link rel="stylesheet" href="/literasi/literasi.css?v=202610232045">' . zk_kepala_aset() . $extra . '</head><body>'
        . '<a class="skip" href="#isi">Langsung ke isi</a>' . zk_kepala();
}
function lt_foot(): string
{
    return '<footer class="lt-foot"><div class="lt-wrap"><a class="lt-brand" href="/"><img src="/assets/images/logo-color.png" alt=""><span>Zakademia</span></a>'
        . '<p>Riset, Data, dan Literasi Filantropi Islam. <em>Bertumbuh bersama, bermanfaat bagi sesama.</em></p>'
        . '<p class="lt-links"><a href="/#layanan">Layanan</a><a href="/literasi/">Literasi</a><a href="/literasi/tulis/">Tulis untuk Zakademia</a><a href="/#kontak">Kontak</a><a href="https://instagram.com/zakademia.official" target="_blank" rel="noopener">Instagram</a></p>'
        . '</div></footer><script src="/js/stat.js?v=2" defer></script></body></html>';
}

/** Halaman tulisan kontributor. $pratinjau = tampilan untuk penulis/admin sebelum terbit. */
function lt_halaman(array $t, bool $pratinjau = false): string
{
    $rn = LT_RUBRIK[$t['rubrik']][0] ?? 'ZakaTalk';
    $canon = LT_BASE . lt_url($t);
    $poin = implode('', array_map(fn($p) => '<li>' . e($p) . '</li>', lt_poin($t)));
    $src = ''; $i = 0;
    foreach (lt_sumber($t) as $s) {
        if (trim($s['judul'] ?? '') === '') continue; $i++;
        $src .= '<li id="sumber-' . $i . '">' . ($s['penerbit'] ? '<span>' . e($s['penerbit']) . '.</span> ' : '')
            . (preg_match('#^https?://#', $s['url'] ?? '') ? '<a href="' . e($s['url']) . '" target="_blank" rel="noopener">' . e($s['judul']) . '</a>' : e($s['judul']))
            . ($s['tanggal'] ? '. ' . e($s['tanggal']) : '') . '.</li>';
    }
    $q = lt_db()->prepare('SELECT t.*, p.nama AS penulis FROM lt_tulisan t LEFT JOIN lt_penulis p ON p.user_id = t.user_id WHERE t.status = \'terbit\' AND t.id != ? ORDER BY t.terbit DESC LIMIT 3');
    $q->execute([(int)$t['id']]); $lain = $q->fetchAll(PDO::FETCH_ASSOC);
    $nama = $t['penulis'] ?: 'Kontributor Zakademia';
    $ld = ['@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $t['judul'], 'description' => $t['dek'], 'datePublished' => $t['terbit'], 'inLanguage' => 'id',
        'author' => ['@type' => 'Person', 'name' => $nama] + ($t['afiliasi'] ? ['affiliation' => $t['afiliasi']] : []),
        'publisher' => ['@type' => 'Organization', 'name' => 'Zakademia', 'logo' => ['@type' => 'ImageObject', 'url' => LT_BASE . '/assets/images/logo-color.png']], 'mainEntityOfPage' => $canon];
    $tulisanLain = $lain ? '<section class="lt-more lt-wrap"><h2>Tulisan kontributor lain</h2><div class="lt-grid">' . implode('', array_map('lt_card', $lain)) . '</div></section>' : '';
    $out = lt_head($t['judul'] . ' | ' . $rn . ' Zakademia', $t['dek'], $canon, LT_BASE . '/assets/images/og-kontributor.png',
        $pratinjau ? '' : '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>', $pratinjau);
    $out .= ($pratinjau ? '<div class="lt-wrap" style="margin-top:14px"><p class="lt-pre">Pratinjau · status: <b>' . e(LT_STATUS[$t['status']] ?? $t['status']) . '</b>. Beginilah tampilannya bila terbit.</p></div>' : '')
        . '<main id="isi"><article class="lt-art"><header class="lt-head lt-wrap-n">'
        . '<p class="lt-crumb"><a href="/literasi/">Literasi</a> / <a href="/literasi/?rubrik=' . e($t['rubrik']) . '">' . $rn . '</a> / Kontributor</p>' . lt_chip($t['rubrik'], true)
        . '<h1>' . e($t['judul']) . '</h1><p class="lt-dek">' . e($t['dek']) . '</p>'
        . '<p class="lt-byline"><span class="lt-ava" aria-hidden="true">' . e(lt_inisial($nama)) . '</span><span><b>' . e($nama) . '</b>' . ($t['afiliasi'] ? ' · ' . e($t['afiliasi']) : '') . '<br>'
        . e(lt_tgl($t['terbit'] ?: $t['diubah'])) . ' · ' . lt_menit($t['isi']) . ' menit baca · <span class="lt-tag">Tulisan kontributor, ditinjau editor Zakademia</span></span></p></header>'
        . '<div class="lt-wrap-n">' . ($poin ? '<aside class="lt-poin" aria-label="Poin penting"><b>Poin penting</b><ul>' . $poin . '</ul></aside>' : '')
        . '<div class="lt-body">' . lt_html($t['isi']) . '</div>'
        . ($src ? '<section class="lt-src" aria-labelledby="h-sumber"><h2 id="h-sumber">Sumber</h2><ol>' . $src . '</ol><p class="lt-note">Isi tulisan menjadi tanggung jawab penulis dan telah ditinjau editor Zakademia. Bila menemukan kekeliruan, kabari kami di <a href="mailto:info@zakademia.com">info@zakademia.com</a>.</p></section>' : '')
        . (!empty($t['bio']) ? '<section class="lt-penulis"><span class="lt-ava big" aria-hidden="true">' . e(lt_inisial($nama)) . '</span><div><b>' . e($nama) . '</b>' . ($t['afiliasi'] ? '<span class="lt-afil">' . e($t['afiliasi']) . '</span>' : '') . '<p>' . e($t['bio']) . '</p>'
            . (!empty($t['tautan']) && preg_match('#^https?://#', $t['tautan']) ? '<a href="' . e($t['tautan']) . '" target="_blank" rel="noopener">' . e(preg_replace('#^https?://(www\.)?#', '', $t['tautan'])) . '</a>' : '') . '</div></section>' : '')
        . '<div class="lt-share"><span>Bagikan</span><a class="wa" href="https://wa.me/?text=' . e(rawurlencode($t['judul'] . ' ' . $canon)) . '" target="_blank" rel="noopener">WhatsApp</a><button type="button" data-copy="' . e($canon) . '">Salin tautan</button></div>'
        . '</div></article>' . $tulisanLain
        . '<section class="lt-cta lt-wrap"><div><b>Punya pengalaman atau kajian seputar zakat dan wakaf?</b><p>Tulis untuk Zakademia. Setiap tulisan ditinjau editor sebelum terbit dan tampil dengan nama Anda.</p></div><a class="lt-btn gold" href="/literasi/tulis/">Mulai menulis</a></section></main>'
        . '<script>document.querySelectorAll("[data-copy]").forEach(function(b){b.onclick=function(){var u=b.getAttribute("data-copy");(navigator.clipboard?navigator.clipboard.writeText(u):Promise.reject()).then(function(){b.textContent="Tersalin";setTimeout(function(){b.textContent="Salin tautan";},2000);},function(){prompt("Salin tautan ini:",u);});};});</script>';
    return $out . lt_foot();
}

/** Email ke penulis saat status berubah, dan ke admin saat ada kiriman baru. */
function lt_mail_status(array $t, string $status, string $catatan = ''): void
{
    $u = '' . LT_BASE . '/literasi/saya/';
    $j = $t['judul'] ?: 'Tulisan Anda';
    $pesan = ['ditinjau' => "Tulisan \"$j\" sudah kami terima dan masuk antrean tinjauan editor. Kami kabari lagi setelah ditinjau.",
        'terbit' => "Kabar baik. Tulisan \"$j\" sudah terbit di Zakademia:\n" . LT_BASE . lt_url($t) . "\n\nTerima kasih sudah berbagi. Silakan bagikan tautannya.",
        'revisi' => "Tulisan \"$j\" perlu disempurnakan sebelum terbit. Catatan editor:\n\n$catatan\n\nSunting tulisan Anda di $u lalu kirim kembali.",
        'ditolak' => "Terima kasih sudah mengirim \"$j\". Setelah ditinjau, tulisan ini belum dapat kami terbitkan." . ($catatan ? "\n\nCatatan editor:\n$catatan" : '') . "\n\nKami menantikan tulisan Anda berikutnya."];
    if (isset($pesan[$status]) && valid_email($t['email'] ?? '')) send_mail($t['email'], ($status === 'terbit' ? 'Tulisan Anda terbit' : 'Kabar tulisan Anda') . ' · Zakademia', $pesan[$status] . "\n\nSalam,\nTim Zakademia");
    if ($status === 'ditinjau') send_mail((string)cfg('notify_email'), 'Kiriman tulisan baru: ' . $j, "Dari: " . ($t['penulis'] ?? '') . " <" . ($t['email'] ?? '') . ">\nRubrik: " . (LT_RUBRIK[$t['rubrik']][0] ?? '') . "\n\nTinjau di " . LT_BASE . '/literasi/tinjau.php');
}
