<?php
declare(strict_types=1);
/**
 * zakademia.com/buku/<slug>/ — halaman buku digital.
 * Semua orang dapat melihat keterangan buku dan membaca pratinjau. Pembeli (lewat Lynk.id atau tambahan admin)
 * masuk dengan email pembelian, lalu membaca seluruh buku di /buku/<slug>/baca.
 */
require __DIR__ . '/lib_buku.php';
start_session();
header('Referrer-Policy: same-origin');

$slug = bk_slug($_GET['b'] ?? '');
if ($slug === '') { $slug = array_key_first(BK_BUKU); if (($_GET['b'] ?? '') !== '') { http_response_code(404); } }
$b = BK_BUKU[$slug];
$conf = bk_conf();
$c = $conf['buku'][$slug];
$meta = bk_meta($slug);
$self = '/buku/' . $slug . '/';
$u = current_user();
$akses = $u ? bk_access($u, $slug) : null;
$error = '';
$info = flash();
$tahap = '';
$klaimEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $u) {
    $act = (string)($_POST['act'] ?? '');
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa. Silakan coba lagi.';
    elseif (!user_verified($u)) redirect('/asesmen/verifikasi.php?next=' . urlencode($self));
    elseif ($act === 'klaim') {
        $klaimEmail = clip($_POST['email'] ?? '', 160);
        if (!valid_email($klaimEmail)) $error = 'Alamat email belum benar.';
        elseif (!throttle_ok('bkklaim|' . $u['id'], 8, 3600)) $error = 'Terlalu banyak percobaan. Coba lagi dalam satu jam.';
        else {
            throttle_hit('bkklaim|' . $u['id']);
            if (!bk_unclaimed($slug, $klaimEmail)) $error = 'Pembelian dengan email ini belum tercatat atau sudah dipakai akun lain. Bila baru membayar, tunggu beberapa menit lalu coba lagi, atau hubungi info@zakademia.com.';
            else {
                $err = otp_send('bk|' . $u['id'], $klaimEmail, 'membuka buku digital');
                if ($err) $error = $err;
                else { $_SESSION['bk_klaim'] = strtolower($klaimEmail); $tahap = 'kode'; $info = 'Kode verifikasi sudah dikirim ke ' . $klaimEmail . '.'; }
            }
        }
    } elseif ($act === 'kode') {
        $klaimEmail = (string)($_SESSION['bk_klaim'] ?? '');
        $email = otp_check('bk|' . $u['id'], (string)($_POST['kode'] ?? ''), $err);
        if (!$email || $email !== $klaimEmail) { $error = $err ?: 'Kode tidak cocok.'; $tahap = 'kode'; }
        elseif ($row = bk_unclaimed($slug, $email)) {
            bk_db()->prepare('UPDATE bk_akses SET user_id = ?, diklaim = ? WHERE id = ?')->execute([(int)$u['id'], gmdate('c'), $row['id']]);
            unset($_SESSION['bk_klaim']);
            flash('Akses buku berhasil dibuka untuk akun ini.');
            redirect($self);
        } else $error = 'Pembelian ini sudah dipakai akun lain.';
    }
}

$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$base = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'zakademia.com');
$judul = $b['judul'] . ': ' . $b['sub'];
$jml = $meta ? (int)$meta['jumlah'] : 0;
$prat = (int)$c['pratinjau'];
$harga = $c['harga'] !== '' ? $c['harga'] : '';
$lynk = $c['lynk_url'];
$masuk = '/asesmen/masuk.php?next=' . urlencode($self);
$daftar = '/asesmen/daftar.php?next=' . urlencode($self);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?><!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($b['judul']) ?> · Buku digital Zakademia</title>
<meta name="description" content="<?= e($b['ringkas']) ?>">
<link rel="canonical" href="<?= e($base . $self) ?>">
<meta property="og:type" content="book"><meta property="og:site_name" content="Zakademia">
<meta property="og:title" content="<?= e($judul) ?>"><meta property="og:description" content="<?= e($b['ringkas']) ?>">
<meta property="og:url" content="<?= e($base . $self) ?>"><meta property="og:image" content="<?= e($base . '/assets/images/og-buku-' . $slug . '.png?v=1') ?>">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630"><meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/assets/images/favicon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/survei/assets/sv.css?v=3"><?= zk_kepala_aset() ?><script src="/js/stat.js?v=2" defer></script>
<style>
.bk{max-width:1040px;margin:0 auto;display:grid;grid-template-columns:minmax(220px,320px) 1fr;gap:clamp(22px,4vw,48px);align-items:start}
@media(max-width:720px){.bk{grid-template-columns:1fr}.bk .cov{max-width:280px;margin:0 auto}}
.cov{border-radius:14px;overflow:hidden;box-shadow:0 24px 50px -28px rgba(11,77,51,.6);border:1px solid var(--line);background:#fff}
.cov img{display:block;width:100%;height:auto}
.kicker{font-size:12px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gold)}
h1.judul{font-size:clamp(26px,3.4vw,38px);line-height:1.15;letter-spacing:-.02em;margin:6px 0 4px;color:var(--deep)}
.sub{font-size:18px;color:var(--ink);margin:0 0 10px;font-weight:600}
.au{color:var(--mute);margin:0 0 18px}
.ring{line-height:1.75;margin:0 0 18px}
.cta{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin:14px 0 8px}
.harga{font-size:22px;font-weight:800;color:var(--deep)}
.card2{background:var(--paper);border:1px solid var(--line);border-radius:18px;padding:20px 22px;display:flex;flex-direction:column;gap:12px;margin-top:22px}
.card2 h2{font-size:17px;margin:0}
.card2 label{font-weight:600;font-size:14px;display:flex;flex-direction:column;gap:6px}
.card2 input{font:inherit;font-size:16px;border:1px solid var(--line);border-radius:12px;padding:11px 14px;min-height:48px;background:var(--bg);color:var(--ink);width:100%}
.fitur{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:18px 0 0}
.fitur div{background:var(--mist);border-radius:14px;padding:12px 14px;font-size:13.5px;line-height:1.5}
.fitur b{display:block;color:var(--deep);font-size:14px}
.err{background:#FBEFE6;border:1px solid #EBCBB4;color:#6E2C16;border-radius:12px;padding:10px 14px;font-size:14px}
.ok{background:var(--mist);border:1px solid var(--line);color:var(--deep);border-radius:12px;padding:10px 14px;font-size:14px}
</style></head><body>
<?= zk_kepala() ?>
<main class="wrap" style="padding-top:32px;padding-bottom:70px">
<div class="bk">
  <div>
    <div class="cov"><img src="/buku/hal.php?b=<?= e($slug) ?>&amp;n=1" alt="Sampul <?= e($b['judul']) ?>" width="1100" height="1467"></div>
  </div>
  <div>
    <span class="kicker">Buku digital · <?= e($b['penerbit']) ?></span>
    <h1 class="judul"><?= e($b['judul']) ?></h1>
    <p class="sub"><?= e($b['sub']) ?></p>
    <p class="au"><?= e($b['penulis']) ?><?= $jml ? ' · ' . $jml . ' halaman' : '' ?></p>
    <?= $info ? '<p class="ok" role="status">' . e($info) . '</p>' : '' ?><?= $error ? '<p class="err" role="alert">' . e($error) . '</p>' : '' ?>
    <p class="ring"><?= e($b['ringkas']) ?></p>
<?php if ($akses): ?>
    <div class="cta"><a class="btn" href="<?= e($self) ?>baca">Baca buku</a><span class="mute" style="font-size:14px">Akses aktif untuk <?= e($u['email']) ?><?= ($akses['sumber'] ?? '') === 'admin' ? ' (admin)' : '' ?>.</span></div>
<?php else: ?>
    <div class="cta">
      <?= $harga !== '' ? '<span class="harga">' . e($harga) . '</span>' : '' ?>
      <?= $lynk !== '' ? '<a class="btn gold" href="' . e($lynk) . '" target="_blank" rel="noopener">Beli di Lynk.id</a>' : '<span class="mute" style="font-size:14px">Tautan pembelian segera tersedia.</span>' ?>
      <a class="btn ghost" href="<?= e($self) ?>baca">Baca pratinjau (<?= $prat ?> halaman)</a>
    </div>
    <p class="mute" style="font-size:13.5px">Setelah membayar, buku terbuka otomatis untuk akun dengan email yang sama dengan email pembayaran. Pembayaran lewat transfer? Hubungi <a href="https://wa.me/6285692090699" target="_blank" rel="noopener">WhatsApp Zakademia</a>, akses dibuka manual oleh admin.</p>
<?php endif; ?>
    <div class="fitur">
      <div><b>Dibaca di situs</b>Tampilan buku yang dibalik halaman demi halaman, di ponsel maupun komputer.</div>
      <div><b>Akses seumur akun</b>Terikat pada akun Zakademia Anda, bisa dibuka kapan saja.</div>
      <div><b>10 bab lengkap</b>Studi kasus, kotak Dalam Jurnal, kuis, glosarium, dan indeks.</div>
    </div>
<?php if (!$akses): ?>
    <div class="card2">
<?php if (!$u): ?>
      <h2>Sudah membeli?</h2>
      <p class="mute" style="margin:0">Masuk atau daftar dengan email yang dipakai saat membayar. Akses terbuka otomatis setelah email terverifikasi.</p>
      <div class="cta" style="margin:0"><a class="btn" href="<?= e($masuk) ?>">Masuk</a><a class="btn ghost" href="<?= e($daftar) ?>">Daftar akun</a></div>
<?php elseif (!user_verified($u)): ?>
      <h2>Verifikasi email dulu</h2>
      <p class="mute" style="margin:0">Akses buku dicocokkan dengan email akun, jadi email <?= e($u['email']) ?> perlu diverifikasi.</p>
      <div class="cta" style="margin:0"><a class="btn" href="/asesmen/verifikasi.php?next=<?= e(urlencode($self)) ?>">Verifikasi sekarang</a></div>
<?php else: ?>
      <h2>Sudah membayar dengan email lain?</h2>
      <p class="mute" style="margin:0">Akses untuk <?= e($u['email']) ?> belum ditemukan. Masukkan email yang dipakai saat membayar; kami kirim kode ke email itu untuk memastikan pembelian milik Anda.</p>
<?php if ($tahap === 'kode'): ?>
      <form method="post" action="<?= e($self) ?>" style="display:flex;flex-direction:column;gap:12px"><?= csrf_field() ?><input type="hidden" name="act" value="kode">
        <label>Kode verifikasi dari <?= e($klaimEmail) ?><input name="kode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus></label>
        <button class="btn" type="submit">Buka akses</button></form>
<?php else: ?>
      <form method="post" action="<?= e($self) ?>" style="display:flex;flex-direction:column;gap:12px"><?= csrf_field() ?><input type="hidden" name="act" value="klaim">
        <label>Email yang dipakai saat membayar<input type="email" name="email" value="<?= e($klaimEmail) ?>" required autocomplete="email"></label>
        <button class="btn ghost" type="submit">Kirim kode verifikasi</button></form>
<?php endif; ?>
<?php endif; ?>
    </div>
<?php endif; ?>
  </div>
</div>
</main>
</body></html>
