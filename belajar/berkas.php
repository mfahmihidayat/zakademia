<?php
declare(strict_types=1);
/** Unduhan template (belajar/berkas/*.xlsx) hanya untuk peserta terdaftar atau admin. */
require __DIR__ . '/lib_lms.php';
start_session();
$slug = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['k'] ?? ''))); $nama = preg_replace('/[^a-z0-9-]/', '', strtolower((string)($_GET['f'] ?? '')));
$k = $slug ? lms_kelas($slug) : null; $u = current_user();
$f = __DIR__ . '/berkas/' . $nama . '.xlsx';
if (!$k || $nama === '' || !is_file($f)) { http_response_code(404); exit('Berkas tidak ditemukan.'); }
if (!$u) redirect('/asesmen/masuk.php?next=' . urlencode('/belajar/' . $slug . '/'));
if (!is_admin() && !lms_terdaftar((int)$u['id'], (int)$k['id'])) { http_response_code(403); exit('Daftar kelas untuk mengunduh berkas ini.'); }
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); header('Content-Disposition: attachment; filename="Zakademia-' . $nama . '.xlsx"'); header('Content-Length: ' . filesize($f)); header('Cache-Control: private');
readfile($f);
