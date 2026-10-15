<?php
declare(strict_types=1);
require_once __DIR__ . '/../asesmen/lib.php';

const SV_TYPES = ['section', 'short', 'long', 'single', 'multi', 'dropdown', 'likert', 'matrix', 'linear', 'nps', 'rating', 'ranking', 'number', 'email', 'phone', 'date'];
const SV_CHOICE = ['single', 'multi', 'dropdown', 'ranking'];
const SV_RESERVED = ['index', 'admin', 'api', 'survei', 'asesmen', 'baru', 'edit', 'hasil'];
/** Batas jawaban per survei untuk akun biasa. Admin dapat mengubahnya per survei. */
const SV_FREE_QUOTA = 10;
const SV_CONSULT_WA = '6285692090699';
const LIKERT5 = ['Sangat tidak setuju', 'Tidak setuju', 'Netral', 'Setuju', 'Sangat setuju'];

function sv_db(): PDO
{
    static $done = false;
    $pdo = db();
    if (!$done) {
        $pdo->exec('CREATE TABLE IF NOT EXISTS surveys (
            id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, slug TEXT NOT NULL UNIQUE COLLATE NOCASE,
            title TEXT NOT NULL, def TEXT NOT NULL, status TEXT NOT NULL DEFAULT "draf", created TEXT NOT NULL, updated TEXT NOT NULL)');
        $pdo->exec('CREATE TABLE IF NOT EXISTS responses (
            id INTEGER PRIMARY KEY AUTOINCREMENT, survey_id INTEGER NOT NULL, created TEXT NOT NULL, answers TEXT NOT NULL,
            duration INTEGER, ip_hash TEXT)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_resp ON responses (survey_id, created)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_surv_user ON surveys (user_id)');
        // Kolom kuota: NULL = bawaan (10 untuk akun biasa, tanpa batas untuk survei milik admin), 0 = tanpa batas.
        $cols = array_column($pdo->query('PRAGMA table_info(surveys)')->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('quota', $cols, true)) $pdo->exec('ALTER TABLE surveys ADD COLUMN quota INTEGER');
        $done = true;
    }
    return $pdo;
}

function sv_require_user(string $next): array
{
    $u = current_user();
    if (!$u) redirect('/asesmen/masuk.php?next=' . urlencode($next));
    return $u;
}
/** Pengguna yang sudah masuk dan emailnya terverifikasi. */
function sv_require_verified(string $next): array
{
    $u = sv_require_user($next);
    if (!user_verified($u)) redirect('/asesmen/verifikasi.php?next=' . urlencode($next));
    return $u;
}

/** Ambil survei bila pengguna berhak menyuntingnya (pemilik atau admin). */
function sv_get_owned(int $id, array $u): ?array
{
    $q = sv_db()->prepare('SELECT * FROM surveys WHERE id = ?');
    $q->execute([$id]);
    $s = $q->fetch(PDO::FETCH_ASSOC);
    if (!$s || ((int)$s['user_id'] !== (int)$u['id'] && $u['role'] !== 'admin')) return null;
    return $s;
}

function sv_slugify(string $t): string
{
    $t = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $t), '-'));
    return substr($t, 0, 40) ?: 'survei';
}
function sv_slug_ok(string $s): bool
{
    return (bool)preg_match('/^[a-z0-9][a-z0-9-]{1,58}[a-z0-9]$/', $s) && !in_array($s, SV_RESERVED, true);
}
function sv_slug_free(string $s, int $exceptId = 0): bool
{
    $q = sv_db()->prepare('SELECT id FROM surveys WHERE slug = ? AND id != ?');
    $q->execute([$s, $exceptId]);
    return !$q->fetchColumn();
}
function sv_unique_slug(string $title): string
{
    $base = sv_slugify($title);
    if (strlen($base) < 3) $base .= '-survei';
    $s = $base;
    while (!sv_slug_ok($s) || !sv_slug_free($s)) $s = $base . '-' . substr(bin2hex(random_bytes(2)), 0, 4);
    return $s;
}

function sv_public_url(string $slug): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com') . '/s/' . $slug;
}

/* ---------- membersihkan definisi survei dari editor ---------- */
function sv_str($v, int $max): string { return clip(is_string($v) || is_numeric($v) ? (string)$v : '', $max); }
function sv_list($v, int $maxN, int $maxLen): array
{
    $out = [];
    foreach (is_array($v) ? $v : [] as $x) {
        $x = sv_str($x, $maxLen);
        if ($x !== '' && !in_array($x, $out, true)) $out[] = $x;
        if (count($out) >= $maxN) break;
    }
    return $out;
}

function sv_clean_def(array $d): array
{
    $o = is_array($d['opt'] ?? null) ? $d['opt'] : [];
    $def = [
        'title' => sv_str($d['title'] ?? '', 200) ?: 'Survei tanpa judul',
        'desc' => sv_str($d['desc'] ?? '', 3000),
        'thanks' => sv_str($d['thanks'] ?? '', 1000),
        'opt' => [
            'progress' => !empty($o['progress']),
            'once' => !empty($o['once']),
            'closes' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($o['closes'] ?? '')) ? (string)$o['closes'] : '',
            'max' => max(0, min(1000000, (int)($o['max'] ?? 0))),
        ],
        'items' => [],
    ];
    $ids = [];
    foreach (array_slice(is_array($d['items'] ?? null) ? $d['items'] : [], 0, 200) as $it) {
        if (!is_array($it) || !in_array($it['t'] ?? '', SV_TYPES, true)) continue;
        $id = preg_match('/^[a-z0-9]{1,12}$/', (string)($it['id'] ?? '')) ? (string)$it['id'] : 'q' . bin2hex(random_bytes(3));
        if (isset($ids[$id])) $id = 'q' . bin2hex(random_bytes(3));
        $ids[$id] = true;
        $t = $it['t'];
        $c = ['id' => $id, 't' => $t, 'title' => sv_str($it['title'] ?? '', 500), 'help' => sv_str($it['help'] ?? '', 1000)];
        if ($t !== 'section') $c['req'] = !empty($it['req']);
        if (in_array($t, SV_CHOICE, true)) {
            $c['opts'] = sv_list($it['opts'] ?? [], 60, 200) ?: ['Pilihan 1'];
            if ($t !== 'ranking') { $c['other'] = !empty($it['other']); $c['shuffle'] = !empty($it['shuffle']); }
            if ($t === 'multi') $c['maxSel'] = max(0, min(60, (int)($it['maxSel'] ?? 0)));
        }
        if ($t === 'likert' || $t === 'matrix') $c['opts'] = sv_list($it['opts'] ?? [], 11, 80) ?: LIKERT5;
        if ($t === 'matrix') $c['rows'] = sv_list($it['rows'] ?? [], 40, 300) ?: ['Pernyataan 1'];
        if ($t === 'linear') {
            $c['min'] = (int)($it['min'] ?? 1) === 0 ? 0 : 1;
            $c['max'] = max(2, min(10, (int)($it['max'] ?? 5)));
            $c['lo'] = sv_str($it['lo'] ?? '', 60);
            $c['hi'] = sv_str($it['hi'] ?? '', 60);
        }
        if ($t === 'rating') $c['max'] = max(3, min(10, (int)($it['max'] ?? 5)));
        if ($t === 'number') {
            foreach (['min', 'max'] as $k) $c[$k] = is_numeric($it[$k] ?? null) ? (float)$it[$k] : null;
        }
        if ($t === 'short' || $t === 'long') $c['ph'] = sv_str($it['ph'] ?? '', 120);
        $cond = $it['cond'] ?? null;
        if (is_array($cond) && isset($ids[(string)($cond['q'] ?? '')]) && sv_str($cond['v'] ?? '', 200) !== '') {
            $c['cond'] = ['q' => (string)$cond['q'], 'v' => sv_str($cond['v'], 200)];
        }
        $def['items'][] = $c;
    }
    return $def;
}

/* ---------- validasi jawaban responden ---------- */
function sv_visible(array $it, array $ans): bool
{
    if (empty($it['cond'])) return true;
    $a = $ans[$it['cond']['q']] ?? null;
    return is_array($a) ? in_array($it['cond']['v'], $a, true) : $a === $it['cond']['v'];
}

/** Kembalikan [jawaban bersih, daftar galat per soal]. */
function sv_clean_answers(array $def, array $in): array
{
    $ans = [];
    $err = [];
    foreach ($def['items'] as $it) {
        $t = $it['t'];
        if ($t === 'section') continue;
        $id = $it['id'];
        if (!sv_visible($it, $ans)) continue;
        $v = $in[$id] ?? null;
        $val = null;
        switch ($t) {
            case 'short': case 'phone': $val = sv_str($v, 500); break;
            case 'long': $val = sv_str($v, 5000); break;
            case 'email':
                $val = sv_str($v, 160);
                if ($val !== '' && !valid_email($val)) $err[$id] = 'Alamat email belum benar.';
                break;
            case 'date':
                $val = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$v) ? (string)$v : '';
                break;
            case 'number':
                if (is_numeric($v)) {
                    $val = (float)$v;
                    if (($it['min'] !== null && $val < $it['min']) || ($it['max'] !== null && $val > $it['max'])) $err[$id] = 'Angka di luar rentang.';
                } else $val = '';
                break;
            case 'single': case 'dropdown':
                $val = sv_str($v, 300);
                if ($val !== '' && !in_array($val, $it['opts'], true) && empty($it['other'])) $val = '';
                break;
            case 'multi':
                $val = [];
                foreach (is_array($v) ? $v : [] as $x) {
                    $x = sv_str($x, 300);
                    if ($x !== '' && (in_array($x, $it['opts'], true) || !empty($it['other'])) && !in_array($x, $val, true)) $val[] = $x;
                }
                $val = array_slice($val, 0, count($it['opts']) + 1);
                if (!empty($it['maxSel']) && count($val) > $it['maxSel']) $err[$id] = 'Pilih paling banyak ' . $it['maxSel'] . '.';
                break;
            case 'ranking':
                $val = [];
                foreach (is_array($v) ? $v : [] as $x) if (in_array($x, $it['opts'], true) && !in_array($x, $val, true)) $val[] = $x;
                if (count($val) !== count($it['opts'])) $val = [];
                break;
            case 'likert':
                $val = (is_numeric($v) && (int)$v >= 1 && (int)$v <= count($it['opts'])) ? (int)$v : null;
                break;
            case 'linear':
                $val = (is_numeric($v) && (int)$v >= $it['min'] && (int)$v <= $it['max']) ? (int)$v : null;
                break;
            case 'nps':
                $val = (is_numeric($v) && (int)$v >= 0 && (int)$v <= 10) ? (int)$v : null;
                break;
            case 'rating':
                $val = (is_numeric($v) && (int)$v >= 1 && (int)$v <= $it['max']) ? (int)$v : null;
                break;
            case 'matrix':
                $val = [];
                $vv = is_array($v) ? $v : [];
                foreach ($it['rows'] as $ri => $_) {
                    $x = $vv[$ri] ?? $vv[(string)$ri] ?? null;
                    if (is_numeric($x) && (int)$x >= 1 && (int)$x <= count($it['opts'])) $val[(string)$ri] = (int)$x;
                }
                if (!empty($it['req']) && count($val) < count($it['rows'])) $err[$id] = 'Jawab setiap baris.';
                break;
        }
        $empty = $val === null || $val === '' || $val === [];
        if (!empty($it['req']) && $empty && !isset($err[$id])) $err[$id] = 'Pertanyaan ini wajib dijawab.';
        if (!$empty) $ans[$id] = $t === 'matrix' ? (object)$val : $val;
    }
    return [$ans, $err];
}

/** Kuota jawaban efektif sebuah survei: 0 = tanpa batas. */
function sv_quota(array $s): int
{
    if (isset($s['quota']) && $s['quota'] !== null && $s['quota'] !== '') return max(0, (int)$s['quota']);
    static $roles = [];
    $uid = (int)$s['user_id'];
    if (!isset($roles[$uid])) {
        $q = db()->prepare('SELECT role FROM users WHERE id = ?');
        $q->execute([$uid]);
        $roles[$uid] = (string)$q->fetchColumn();
    }
    return $roles[$uid] === 'admin' ? 0 : SV_FREE_QUOTA;
}

function sv_count(array $s): int
{
    $q = sv_db()->prepare('SELECT COUNT(*) FROM responses WHERE survey_id = ?');
    $q->execute([$s['id']]);
    return (int)$q->fetchColumn();
}

/** True bila kuota (batas versi gratis atau yang diatur admin) sudah habis. */
function sv_quota_full(array $s): bool
{
    $k = sv_quota($s);
    return $k > 0 && sv_count($s) >= $k;
}

/** Tautan WhatsApp konsultasi untuk membuka kuota. */
function sv_consult_url(array $s): string
{
    $t = 'Halo Zakademia, saya ingin konsultasi untuk melanjutkan survei "' . $s['title'] . '" (zakademia.com/s/' . $s['slug'] . ') yang sudah mencapai batas ' . sv_quota($s) . ' jawaban.';
    return 'https://wa.me/' . SV_CONSULT_WA . '?text=' . rawurlencode($t);
}

/** Status survei untuk responden: null bila terbuka, atau pesan penutupan. */
function sv_closed_reason(array $s, array $def): ?string
{
    if ($s['status'] !== 'aktif') return $s['status'] === 'draf' ? 'Survei ini belum dibuka.' : 'Survei ini sudah ditutup. Terima kasih atas perhatian Anda.';
    if ($def['opt']['closes'] !== '' && (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d') > $def['opt']['closes']) return 'Survei ini sudah ditutup. Terima kasih atas perhatian Anda.';
    $n = null;
    if (sv_quota($s) > 0 && ($n = sv_count($s)) >= sv_quota($s)) return 'Survei ini sudah tidak menerima jawaban. Terima kasih atas perhatian Anda.';
    if ($def['opt']['max'] > 0 && ($n ?? sv_count($s)) >= $def['opt']['max']) return 'Kuota responden survei ini sudah terpenuhi. Terima kasih.';
    return null;
}

/* ---------- template ---------- */
function sv_templates(): array
{
    $sec = fn($title, $help = '') => ['t' => 'section', 'title' => $title, 'help' => $help];
    return [
        'kosong' => ['name' => 'Survei kosong', 'desc' => 'Mulai dari nol.', 'def' => ['title' => 'Survei tanpa judul', 'desc' => '', 'items' => [
            ['t' => 'single', 'title' => 'Pertanyaan pertama', 'opts' => ['Pilihan 1', 'Pilihan 2'], 'req' => true]]]],
        'donatur' => ['name' => 'Kepuasan donatur', 'desc' => 'Kepuasan layanan, kepercayaan, NPS, dan niat berdonasi ulang.', 'def' => [
            'title' => 'Survei Kepuasan Donatur', 'desc' => 'Terima kasih telah menjadi bagian dari kebaikan bersama kami. Survei ini memerlukan sekitar 5 menit. Jawaban Anda membantu kami memperbaiki layanan.',
            'thanks' => 'Jazakumullahu khairan. Jawaban Anda sudah kami terima.',
            'items' => [
                $sec('Tentang Anda'),
                ['t' => 'dropdown', 'title' => 'Sudah berapa lama Anda berdonasi melalui lembaga kami?', 'opts' => ['Kurang dari 1 tahun', '1–2 tahun', '3–5 tahun', 'Lebih dari 5 tahun'], 'req' => true],
                ['t' => 'multi', 'title' => 'Jenis dana yang pernah Anda salurkan', 'opts' => ['Zakat', 'Infak/sedekah', 'Wakaf', 'Fidyah', 'Kurban'], 'other' => true, 'req' => true],
                ['t' => 'single', 'title' => 'Kanal yang paling sering Anda gunakan', 'opts' => ['Transfer bank', 'QRIS/dompet digital', 'Situs/aplikasi lembaga', 'Konter/petugas', 'Potong gaji'], 'other' => true, 'req' => true],
                $sec('Penilaian layanan', 'Nyatakan seberapa setuju Anda dengan setiap pernyataan.'),
                ['t' => 'matrix', 'title' => 'Penilaian layanan donatur', 'rows' => ['Proses berdonasi mudah', 'Bukti setor diterima dengan cepat', 'Laporan penyaluran mudah dipahami', 'Petugas ramah dan responsif', 'Saya percaya dana dikelola amanah'], 'opts' => LIKERT5, 'req' => true],
                ['t' => 'nps', 'title' => 'Seberapa mungkin Anda merekomendasikan lembaga kami kepada keluarga atau teman?', 'req' => true],
                ['t' => 'long', 'title' => 'Apa yang paling perlu kami perbaiki?', 'ph' => 'Tuliskan saran Anda'],
            ]]],
        'mustahik' => ['name' => 'Pemetaan kebutuhan mustahik', 'desc' => 'Profil rumah tangga, kebutuhan utama, dan prioritas bantuan.', 'def' => [
            'title' => 'Pemetaan Kebutuhan Penerima Manfaat', 'desc' => 'Survei ini diisi oleh petugas atau penerima manfaat untuk memetakan kebutuhan bantuan. Data dijaga kerahasiaannya.',
            'items' => [
                $sec('Profil rumah tangga'),
                ['t' => 'short', 'title' => 'Desa/kelurahan', 'req' => true],
                ['t' => 'number', 'title' => 'Jumlah anggota rumah tangga', 'min' => 1, 'max' => 30, 'req' => true],
                ['t' => 'single', 'title' => 'Pekerjaan utama kepala rumah tangga', 'opts' => ['Tidak bekerja', 'Buruh harian', 'Petani/nelayan', 'Pedagang kecil', 'Karyawan'], 'other' => true, 'req' => true],
                ['t' => 'dropdown', 'title' => 'Perkiraan pendapatan rumah tangga per bulan', 'opts' => ['< Rp1 juta', 'Rp1–2 juta', 'Rp2–3 juta', 'Rp3–5 juta', '> Rp5 juta'], 'req' => true],
                $sec('Kebutuhan'),
                ['t' => 'ranking', 'title' => 'Urutkan kebutuhan dari yang paling mendesak', 'opts' => ['Pangan', 'Pendidikan anak', 'Kesehatan', 'Modal usaha', 'Perbaikan rumah'], 'req' => true],
                ['t' => 'single', 'title' => 'Apakah ada anggota keluarga yang sedang sakit menahun?', 'opts' => ['Ya', 'Tidak'], 'req' => true],
                ['t' => 'short', 'title' => 'Jenis penyakit atau kebutuhan perawatannya', 'cond' => ['q' => '', 'v' => 'Ya']],
                ['t' => 'long', 'title' => 'Catatan petugas'],
            ]]],
        'program' => ['name' => 'Evaluasi program/pelatihan', 'desc' => 'Kepuasan, manfaat, dan perubahan setelah program.', 'def' => [
            'title' => 'Evaluasi Program', 'desc' => 'Bantu kami menilai manfaat program yang Anda ikuti.',
            'items' => [
                ['t' => 'short', 'title' => 'Nama program yang diikuti', 'req' => true],
                ['t' => 'rating', 'title' => 'Secara keseluruhan, berapa nilai Anda untuk program ini?', 'max' => 5, 'req' => true],
                ['t' => 'likert', 'title' => 'Materi program sesuai dengan kebutuhan saya', 'opts' => LIKERT5, 'req' => true],
                ['t' => 'linear', 'title' => 'Seberapa besar perubahan yang Anda rasakan setelah program?', 'min' => 1, 'max' => 10, 'lo' => 'Tidak ada perubahan', 'hi' => 'Sangat besar', 'req' => true],
                ['t' => 'multi', 'title' => 'Bagian yang paling bermanfaat', 'opts' => ['Materi', 'Praktik', 'Pendamping', 'Jejaring peserta'], 'other' => true, 'maxSel' => 2],
                ['t' => 'long', 'title' => 'Saran untuk program berikutnya'],
            ]]],
    ];
}

/** Bentuk definisi lengkap dari template (beri id, sambungkan kondisi ke soal sebelumnya). */
function sv_from_template(string $key): array
{
    $tpl = sv_templates()[$key] ?? sv_templates()['kosong'];
    $d = $tpl['def'];
    $prev = '';
    foreach ($d['items'] as $i => &$it) {
        $it['id'] = 'q' . ($i + 1);
        if (isset($it['cond']) && $it['cond']['q'] === '') $it['cond']['q'] = $prev;
        if ($it['t'] !== 'section') $prev = $it['id'];
    }
    unset($it);
    $d['opt'] = ['progress' => true, 'once' => false, 'closes' => '', 'max' => 0];
    return sv_clean_def($d);
}

/** Kepala halaman aplikasi survei (bagian dalam, khusus pengguna yang sudah masuk). */
function sv_head(string $title, string $crumb = ''): void
{
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    $u = current_user();
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">'
        . '<title>' . e($title) . ' · Zakademia</title><link rel="icon" href="/assets/images/favicon.png">'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">'
        . '<link rel="stylesheet" href="/survei/assets/sv.css?v=3"></head><body>'
        . '<header class="top"><div class="in"><a class="brand" href="/"><img src="/assets/images/logo-color.png" alt="">Zakademia</a>'
        . ($crumb ? '<span class="crumb">/ ' . $crumb . '</span>' : '')
        . '<nav><a href="/asesmen/akun.php">Akun saya</a><a href="/survei/">Survei</a><a href="/analitik/">Analitik</a><a href="/belajar/">Belajar</a>' . ($u && $u['role'] === 'admin' ? '<a href="/asesmen/admin.php">Admin</a>' : '') . '<a href="/asesmen/keluar.php">Keluar</a></nav></div></header>';
}

/** Ekspor jawaban satu survei ke CSV (dipakai dashboard admin). */
function sv_csv(array $s, array $def, array $rows): void
{
    $head = ['No', 'Waktu (WIB)', 'Durasi (detik)'];
    $cols = [];
    $no = 0;
    foreach ($def['items'] as $it) {
        if ($it['t'] === 'section') continue;
        $no++;
        if ($it['title'] === '') $it['title'] = 'Pertanyaan ' . $no;
        if ($it['t'] === 'matrix') foreach ($it['rows'] as $ri => $r) { $head[] = $it['title'] . ' [' . $r . ']'; $cols[] = [$it, $ri]; }
        else { $head[] = $it['title']; $cols[] = [$it, null]; }
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $s['slug'] . '-' . gmdate('Ymd') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $safe = fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v;
    fputcsv($out, array_map($safe, $head));
    foreach ($rows as $n => $r) {
        $a = json_decode($r['answers'], true) ?: [];
        $t = (new DateTime($r['created']))->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i');
        $line = [$n + 1, $t, $r['duration']];
        foreach ($cols as [$it, $ri]) {
            $v = $a[$it['id']] ?? '';
            if ($ri !== null) { $x = $v[(string)$ri] ?? ''; $v = $x === '' ? '' : $x . ' (' . ($it['opts'][$x - 1] ?? '') . ')'; }
            elseif ($it['t'] === 'likert' && $v !== '') $v = $v . ' (' . ($it['opts'][$v - 1] ?? '') . ')';
            elseif (is_array($v)) $v = implode($it['t'] === 'ranking' ? ' > ' : '; ', $v);
            $line[] = $safe($v);
        }
        fputcsv($out, $line);
    }
    exit;
}
