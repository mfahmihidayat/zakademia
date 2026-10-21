<?php
declare(strict_types=1);
/** Sertifikat: halaman verifikasi publik /belajar/sertifikat/<nomor>, gambar (?g=1), dan PDF (?pdf=1). */
require __DIR__ . '/lib_lms.php';
start_session();
$no = strtoupper(preg_replace('/[^A-Za-z0-9-]/', '', (string)($_GET['no'] ?? '')));
$q = lms_db()->prepare('SELECT s.*, k.slug FROM lms_sertifikat s JOIN lms_kelas k ON k.id = s.kelas_id WHERE s.no = ?'); $q->execute([$no]);
$s = $q->fetch(PDO::FETCH_ASSOC);
if (!$s) { http_response_code(404); lms_head('Sertifikat tidak ditemukan'); echo '<main class="wrap"><div class="card" style="padding:24px;max-width:560px"><h1 style="font-size:24px;margin:0 0 8px">Sertifikat tidak ditemukan</h1><p class="mute">Nomor ' . e($no) . ' tidak tercatat di Zakademia. Periksa kembali nomornya.</p><a class="btn ghost" href="/belajar/">Ruang Belajar</a></div></main>'; lms_foot(); exit; }
if (isset($_GET['g']) || isset($_GET['pdf'])) {
    $im = lms_gambar_sertifikat($s);
    ob_start(); imagejpeg($im, null, 92); $jpg = ob_get_clean(); imagedestroy($im);
    header('Cache-Control: private, max-age=86400');
    if (isset($_GET['pdf'])) { header('Content-Type: application/pdf'); header('Content-Disposition: attachment; filename="Sertifikat-Zakademia-' . $s['no'] . '.pdf"'); echo lms_pdf_dari_jpeg($jpg, 2000, 1414); }
    else { header('Content-Type: image/jpeg'); if (isset($_GET['unduh'])) header('Content-Disposition: attachment; filename="Sertifikat-Zakademia-' . $s['no'] . '.jpg"'); echo $jpg; }
    exit;
}
$u = current_user(); $milik = $u && (int)$u['id'] === (int)$s['user_id'];
lms_head('Sertifikat ' . $s['no'], '<a href="/belajar/">Ruang Belajar</a>');
echo '<main class="wrap" style="max-width:1000px"><div class="lms-head"><p class="mute small" style="margin:0;font-weight:700;letter-spacing:.08em">SERTIFIKAT TERVERIFIKASI</p><h1>' . e($s['nama']) . '</h1><p class="mute">menyelesaikan kelas <b>' . e($s['judul']) . '</b> di Ruang Belajar Zakademia pada ' . e(lt_tgl($s['tanggal'])) . '. Nomor sertifikat <b>' . e($s['no']) . '</b>, ditandatangani ' . LMS_TTD_NAMA . ', ' . LMS_TTD_JABATAN . '.</p></div>'
    . '<div class="card" style="overflow:hidden"><img src="/belajar/sertifikat.php?no=' . e($s['no']) . '&g=1" alt="Sertifikat ' . e($s['nama']) . '" style="display:block;width:100%;height:auto"></div>'
    . '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px"><a class="btn" href="/belajar/sertifikat.php?no=' . e($s['no']) . '&pdf=1">Unduh PDF</a><a class="btn ghost" href="/belajar/sertifikat.php?no=' . e($s['no']) . '&g=1&unduh=1">Unduh gambar</a>'
    . '<a class="btn ghost" href="https://wa.me/?text=' . e(rawurlencode('Alhamdulillah, saya menyelesaikan kelas ' . $s['judul'] . ' di Zakademia. Sertifikat: ' . LT_BASE . '/belajar/sertifikat/' . $s['no'])) . '" target="_blank" rel="noopener">Bagikan ke WhatsApp</a>'
    . '<a class="btn ghost" href="https://www.linkedin.com/sharing/share-offsite/?url=' . e(rawurlencode(LT_BASE . '/belajar/sertifikat/' . $s['no'])) . '" target="_blank" rel="noopener">Bagikan ke LinkedIn</a></div>'
    . '<p class="mute small" style="margin-top:18px">Halaman ini dapat dibuka siapa pun untuk memeriksa keaslian sertifikat. ' . ($milik ? 'Nama di sertifikat mengikuti nama akun Anda saat kelas dituntaskan.' : '') . '</p></main>';
lms_foot();
