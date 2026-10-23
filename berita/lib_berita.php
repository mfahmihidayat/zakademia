<?php
declare(strict_types=1);
/**
 * Kabar zakat dan wakaf: diambil dari RSS lembaga (BWI, Dompet Dhuafa, Rumah Zakat, LAZISMU) dan Google News
 * (untuk BAZNAS dan Kemenag/Bimas Islam yang tidak menyediakan RSS), disaring kata kunci, disimpan di folder data
 * (berita.json), dan ditampilkan sebagai running text di beranda. Diperbarui tiap 30 menit saat ada pengunjung,
 * atau lewat cron ambil.php?k=KUNCI.
 */
require_once __DIR__ . '/../asesmen/lib.php';

const BR_SUMBER = [
    ['id' => 'bwi', 'nama' => 'BWI', 'url' => 'https://www.bwi.go.id/feed/', 'saring' => false],
    ['id' => 'dd', 'nama' => 'Dompet Dhuafa', 'url' => 'https://www.dompetdhuafa.org/feed/', 'saring' => true],
    ['id' => 'rz', 'nama' => 'Rumah Zakat', 'url' => 'https://www.rumahzakat.org/feed/', 'saring' => true],
    ['id' => 'lazismu', 'nama' => 'LAZISMU', 'url' => 'https://www.lazismu.org/feed/', 'saring' => true],
    ['id' => 'gn-baznas', 'nama' => 'BAZNAS', 'url' => 'https://news.google.com/rss/search?q=BAZNAS&hl=id&gl=ID&ceid=ID:id', 'saring' => false, 'google' => true],
    ['id' => 'gn-kemenag', 'nama' => 'Kemenag', 'url' => 'https://news.google.com/rss/search?q=%22Bimas+Islam%22+OR+%22Ditjen+Bimas+Islam%22+zakat+OR+wakaf&hl=id&gl=ID&ceid=ID:id', 'saring' => false, 'google' => true],
    ['id' => 'gn-wakaf', 'nama' => 'Berita wakaf', 'url' => 'https://news.google.com/rss/search?q=wakaf+OR+%22Badan+Wakaf%22&hl=id&gl=ID&ceid=ID:id', 'saring' => false, 'google' => true],
    ['id' => 'gn-zakat', 'nama' => 'Berita zakat', 'url' => 'https://news.google.com/rss/search?q=zakat+OR+%22lembaga+amil+zakat%22&hl=id&gl=ID&ceid=ID:id', 'saring' => false, 'google' => true],
];
const BR_KATA = '/\b(zakat|wakaf|waqf|infak|infaq|sedekah|baznas|laz\b|lazis|nazhir|amil|filantropi|muzaki|mustahik|fidyah|kurban|qurban|cwls|bwi\b|kemenag|bimas islam)/iu';

function br_file(): string { return data_dir() . '/berita.json'; }
function br_baca(): array
{
    $f = br_file(); $d = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    $d += ['kunci' => '', 'diperbarui' => 0, 'item' => [], 'sembunyi' => [], 'manual' => [], 'log' => []];
    if ($d['kunci'] === '') { $d['kunci'] = bin2hex(random_bytes(12)); br_simpan($d); }
    return $d;
}
function br_simpan(array $d): void { file_put_contents(br_file(), json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX); }

function br_ambil_url(string $url): string
{
    if (!function_exists('curl_init')) return (string)@file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'Zakademia/1.0 (+https://zakademia.com)']]));
    $c = curl_init($url);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 6, CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Zakademia/1.0; +https://zakademia.com)', CURLOPT_ENCODING => '']);
    $r = (string)curl_exec($c); $code = (int)curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
    return $code >= 200 && $code < 300 ? $r : '';
}
/** Ambil og:image dari halaman artikel (mengikuti pengalihan Google News). Mengembalikan URL https atau ''. */
function br_og_image(string $url): string
{
    if (!function_exists('curl_init')) return '';
    $c = curl_init($url);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 5, CURLOPT_CONNECTTIMEOUT => 4, CURLOPT_TIMEOUT => 6, CURLOPT_RANGE => '0-120000',
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; Zakademia/1.0; +https://zakademia.com)', CURLOPT_ENCODING => '', CURLOPT_HTTPHEADER => ['Accept: text/html']]);
    $h = (string)curl_exec($c); curl_close($c);
    if ($h === '') return '';
    if (preg_match('/<meta[^>]+property=["\']og:image(?::secure_url)?["\'][^>]+content=["\']([^"\']+)["\']/i', $h, $m) || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $h, $m) || preg_match('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/i', $h, $m)) {
        $u = html_entity_decode($m[1]); if (str_starts_with($u, '//')) $u = 'https:' . $u;
        return preg_match('#^https://#', $u) ? mb_substr($u, 0, 400) : '';
    }
    return '';
}
/** Baca RSS 2.0 / Atom menjadi daftar [judul, tautan, waktu, sumber_asli]. */
function br_parse(string $xml, array $s): array
{
    $out = [];
    if ($xml === '' || !($dom = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET))) return $out;
    $items = isset($dom->channel->item) ? $dom->channel->item : (isset($dom->entry) ? $dom->entry : []);
    foreach ($items as $it) {
        $judul = trim(html_entity_decode((string)$it->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $link = isset($it->link['href']) ? (string)$it->link['href'] : trim((string)$it->link);
        $tgl = (string)($it->pubDate ?? $it->published ?? $it->updated ?? ''); $ts = $tgl ? (strtotime($tgl) ?: time()) : time();
        $asal = $s['nama'];
        if (!empty($s['google'])) { if (isset($it->source)) $asal = trim((string)$it->source); $judul = preg_replace('/\s+-\s+[^-]{2,40}$/u', '', $judul); }
        if ($judul === '' || $link === '') continue;
        if (!empty($s['saring']) && !preg_match(BR_KATA, $judul . ' ' . (string)($it->description ?? ''))) continue;
        $img = '';
        $media = $it->children('http://search.yahoo.com/mrss/');
        if (isset($media->content) && isset($media->content->attributes()->url)) $img = (string)$media->content->attributes()->url;
        elseif (isset($media->thumbnail) && isset($media->thumbnail->attributes()->url)) $img = (string)$media->thumbnail->attributes()->url;
        elseif (isset($it->enclosure) && str_starts_with((string)($it->enclosure['type'] ?? ''), 'image/')) $img = (string)$it->enclosure['url'];
        elseif (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string)($it->description ?? '') . (string)($it->children('http://purl.org/rss/1.0/modules/content/')->encoded ?? ''), $m)) $img = $m[1];
        if ($img !== '' && !preg_match('#^https://#', $img)) $img = '';
        $out[] = ['id' => substr(sha1($link), 0, 12), 'judul' => mb_substr($judul, 0, 160), 'url' => $link, 'ts' => $ts, 'sumber' => $s['id'], 'asal' => mb_substr($asal, 0, 40), 'img' => mb_substr($img, 0, 400)];
    }
    return $out;
}
/** Ambil semua sumber, gabungkan dengan yang lama, buang duplikat, simpan 60 terbaru (maks. 14 hari). */
function br_perbarui(): array
{
    $d = br_baca(); $baru = []; $log = [];
    foreach (BR_SUMBER as $s) { $x = br_ambil_url($s['url']); $it = br_parse($x, $s); $log[] = $s['id'] . ':' . count($it); foreach ($it as $i) $baru[$i['id']] = $i; }
    foreach ($d['item'] as $i) if (!isset($baru[$i['id']])) $baru[$i['id']] = $i; elseif (empty($baru[$i['id']]['img']) && !empty($i['img'])) $baru[$i['id']]['img'] = $i['img'];
    $coba = 0;
    foreach ($baru as &$i) {
        if (!empty($i['img']) || !empty($i['img_coba']) || $coba >= 12) continue;
        $i['img_coba'] = 1; $coba++;
        $i['img'] = br_og_image($i['url']);
    }
    unset($i);
    $lihat = []; $item = [];
    foreach ($baru as $i) { $k = mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $i['judul'])); $k = mb_substr(trim($k), 0, 60); if (isset($lihat[$k])) continue; $lihat[$k] = 1; if ($i['ts'] < time() - 14 * 86400) continue; $item[] = $i; }
    usort($item, fn($a, $b) => $b['ts'] <=> $a['ts']);
    $d['item'] = array_slice($item, 0, 60); $d['diperbarui'] = time(); $d['log'] = array_slice(array_merge([date('c') . ' ' . implode(' ', $log)], $d['log']), 0, 20);
    br_simpan($d);
    return $d;
}
/** Data untuk tampilan: perbarui bila lebih dari 30 menit dan tidak sedang diperbarui proses lain. */
function br_basi(array $d): bool { return time() - (int)$d['diperbarui'] > 1800; }
/** Perbarui bila basi dan tidak ada proses lain yang sedang memperbarui. Dipanggil setelah respons dikirim agar pengunjung tidak menunggu. */
function br_perbarui_bila_basi(): void
{
    $d = br_baca(); if (!br_basi($d)) return;
    $lock = data_dir() . '/berita.lock';
    if (is_file($lock) && filemtime($lock) > time() - 120) return;
    touch($lock); try { br_perbarui(); } catch (Throwable $e) { error_log('berita: ' . $e->getMessage()); } @unlink($lock);
}
function br_tampil(int $maks = 25): array
{
    $d = br_baca();
    if ((int)$d['diperbarui'] === 0) { br_perbarui_bila_basi(); $d = br_baca(); }   // pertama kali: ambil langsung
    $semb = array_flip($d['sembunyi']); $out = [];
    foreach ($d['manual'] as $m) if (($m['sampai'] ?? 0) === 0 || $m['sampai'] > time()) $out[] = ['judul' => $m['judul'], 'url' => $m['url'] ?? '', 'ts' => $m['ts'], 'asal' => 'Zakademia', 'manual' => true];
    foreach ($d['item'] as $i) { if (isset($semb[$i['id']])) continue; $out[] = ['judul' => $i['judul'], 'url' => $i['url'], 'ts' => $i['ts'], 'asal' => $i['asal'], 'img' => $i['img'] ?? '']; if (count($out) >= $maks) break; }
    return ['diperbarui' => (int)$d['diperbarui'], 'item' => $out];
}
