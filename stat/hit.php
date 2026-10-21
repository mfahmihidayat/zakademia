<?php
declare(strict_types=1);
/** Penerima catatan kunjungan dari js/stat.js (POST, sendBeacon). Selalu menjawab 204 tanpa isi. */
require __DIR__ . '/lib_stat.php';
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { http_response_code(204); exit; }
start_session();
$raw = (string)file_get_contents('php://input');
$b = json_decode($raw, true);
if (!is_array($b)) $b = $_POST ?: [];
$path = clip($b['p'] ?? ($_SERVER['HTTP_REFERER'] ?? '/'), 300);
$ref = clip($b['r'] ?? '', 300);
try { st_catat($path, $ref); } catch (Throwable $e) { error_log('stat: ' . $e->getMessage()); }
http_response_code(204);
