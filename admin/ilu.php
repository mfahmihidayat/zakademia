<?php
declare(strict_types=1);
/** Perakit ilustrasi (khusus admin): coba judul apa pun, lihat enam tata letak, ganti unsur, unduh SVG untuk artikel atau unggahan lain. */
require __DIR__ . '/../asesmen/lib.php';
require __DIR__ . '/../inc/ilu.php';
require_once __DIR__ . '/../literasi/lib_literasi.php';
start_session();
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/admin/ilu.php'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');
$semua = zk_ilu_unsur();
$j = clip($_GET['j'] ?? 'Cara Menghitung Zakat Pertanian', 200); $r = isset(LT_RUBRIK[$_GET['r'] ?? '']) ? $_GET['r'] : 'pedia'; $x = clip($_GET['x'] ?? '', 3000);
$u = array_values(array_filter(array_map('strval', (array)($_GET['u'] ?? [])), fn($k) => isset($semua[$k])));
$oto = zk_ilu_pilih($j, $r, $x); $pakai = count($u) === 3 ? $u : $oto;
$q = fn(int $v) => '/literasi/ilu.php?' . http_build_query(['j' => $j, 'r' => $r, 'x' => $x, 'v' => $v, 'u' => implode(',', $pakai)]);
$sel = ''; for ($i = 0; $i < 3; $i++) { $sel .= '<select name="u[]">'; foreach (array_keys($semua) as $n) $sel .= '<option value="' . $n . '"' . ($pakai[$i] === $n ? ' selected' : '') . '>' . str_replace('_', ' ', $n) . '</option>'; $sel .= '</select>'; }
$rub = ''; foreach (LT_RUBRIK as $k => $v) $rub .= '<option value="' . $k . '"' . ($k === $r ? ' selected' : '') . '>' . e($v[0]) . '</option>';
$grid = ''; for ($v = 0; $v < 6; $v++) $grid .= '<figure style="margin:0"><img src="' . e($q($v)) . '" alt="" style="width:100%;aspect-ratio:600/460;border-radius:14px;display:block;border:1px solid var(--line)"><figcaption style="display:flex;justify-content:space-between;font-size:13px;margin-top:6px"><span class="mute">Tata letak ' . ($v + 1) . '</span><a href="' . e($q($v)) . '&unduh=1">Unduh SVG</a></figcaption></figure>';
page('Perakit ilustrasi', '<p class="mute small" style="margin:0"><a href="/admin/">Pusat kendali</a> / Perakit ilustrasi</p><h1 style="font-size:28px;margin:4px 0 8px">Perakit ilustrasi</h1>
<p class="mute">Gambar dirakit dari perpustakaan ' . count($semua) . ' unsur bergaya Zakademia, dipilih otomatis dari judul, rubrik, dan isi. Tidak memakai layanan luar, hasilnya selalu sama untuk masukan yang sama. Tulisan kontributor sudah mendapat ilustrasi ini otomatis dan dapat diatur dari halaman Tinjauan; halaman ini untuk kebutuhan lain (artikel redaksi, unggahan media sosial).</p>
<form method="get" style="display:grid;gap:10px;grid-template-columns:1fr 180px;align-items:end;background:var(--mist);padding:14px;border-radius:16px">
<label>Judul<input name="j" value="' . e($j) . '" maxlength="200" required></label><label>Rubrik<select name="r">' . $rub . '</select></label>
<label style="grid-column:1/-1">Cuplikan isi (opsional, memperkaya kata kunci)<textarea name="x" rows="2" maxlength="3000">' . e($x) . '</textarea></label>
<div style="grid-column:1/-1"><span class="small mute">Unsur (otomatis: ' . e(implode(', ', $oto)) . ')</span><div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">' . $sel . '</div></div>
<div style="grid-column:1/-1;display:flex;gap:8px"><button class="btn gold" type="submit">Rakit</button><a class="btn ghost" href="/admin/ilu.php?j=' . e(rawurlencode($j)) . '&r=' . $r . '">Pakai unsur otomatis</a></div></form>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin-top:18px">' . $grid . '</div>
<p class="mute small">Unduhan berbentuk SVG (vektor, tajam di ukuran berapa pun). Untuk mengubah ke PNG/WebP, buka di peramban lalu simpan sebagai gambar, atau gunakan alat konversi biasa.</p>
<style>label{display:flex;flex-direction:column;gap:4px;font-size:13px;font-weight:600}label input,label select,label textarea,select{font:inherit;font-weight:400;border:1px solid var(--line);border-radius:12px;padding:9px 12px;background:#fff}div>select{flex:1;min-width:160px;max-width:260px}</style>', '980px');
