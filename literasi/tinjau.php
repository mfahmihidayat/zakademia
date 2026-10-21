<?php
declare(strict_types=1);
/** Tinjauan editor (khusus admin): antrean kiriman kontributor, terbitkan, minta revisi, tolak, tarik, sunting ringan. */
require __DIR__ . '/lib_literasi.php';
start_session();
header('X-Frame-Options: DENY');
if (!current_user()) redirect('/asesmen/masuk.php?next=' . urlencode('/literasi/tinjau.php'));
if (!is_admin()) page('Akses ditolak', '<h1>Khusus admin</h1><a class="btn ghost" href="/asesmen/akun.php">Ke Akun saya</a>');
$pdo = lt_db();
$ok = flash(); $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0); $t = lt_tulisan($id); $act = (string)($_POST['act'] ?? ''); $cat = clip($_POST['catatan'] ?? '', 2000);
    if (!form_csrf_ok()) $error = 'Sesi kedaluwarsa.';
    elseif (!$t) $error = 'Tulisan tidak ditemukan.';
    elseif ($act === 'terbit') {
        $kurang = lt_periksa($t);
        if ($kurang) $error = 'Belum lengkap: ' . implode(' ', $kurang);
        else {
            $slug = lt_slug($t['judul'], $t['slug'], $id);
            $pdo->prepare('UPDATE lt_tulisan SET status = \'terbit\', slug = ?, terbit = COALESCE(terbit, ?), catatan = ? WHERE id = ?')->execute([$slug, gmdate('c'), $cat ?: null, $id]);
            lt_mail_status(lt_tulisan($id), 'terbit'); flash('Terbit: ' . LT_BASE . '/literasi/k/' . $slug . '/'); redirect('/literasi/tinjau.php');
        }
    } elseif ($act === 'revisi' || $act === 'ditolak') {
        if ($cat === '') $error = 'Tulis catatan untuk penulis.';
        else { $pdo->prepare('UPDATE lt_tulisan SET status = ?, catatan = ? WHERE id = ?')->execute([$act, $cat, $id]); lt_mail_status($t, $act, $cat); flash($act === 'revisi' ? 'Dikembalikan untuk revisi, penulis diberi tahu.' : 'Ditolak, penulis diberi tahu.'); redirect('/literasi/tinjau.php'); }
    } elseif ($act === 'tarik') { $pdo->prepare('UPDATE lt_tulisan SET status = \'draf\', catatan = ? WHERE id = ?')->execute([$cat ?: 'Ditarik oleh editor.', $id]); flash('Tulisan ditarik dari publikasi.'); redirect('/literasi/tinjau.php?f=terbit'); }
    elseif ($act === 'tepercaya') { $pdo->prepare('UPDATE lt_penulis SET tepercaya = ? WHERE user_id = ?')->execute([(int)!empty($_POST['nilai']), (int)$t['user_id']]); flash('Status penulis diperbarui.'); redirect('/literasi/tinjau.php'); }
    elseif ($act === 'sunting') {
        $pdo->prepare('UPDATE lt_tulisan SET judul = ?, rubrik = ?, dek = ?, diubah = ? WHERE id = ?')->execute([clip($_POST['judul'] ?? '', 140), isset(LT_RUBRIK[$_POST['rubrik'] ?? '']) ? $_POST['rubrik'] : $t['rubrik'], clip($_POST['dek'] ?? '', 300), gmdate('c'), $id]);
        flash('Judul, rubrik, dan ringkasan disunting.'); redirect('/literasi/tinjau.php?f=' . $t['status']);
    }
}
$f = (string)($_GET['f'] ?? 'ditinjau'); if (!isset(LT_STATUS[$f]) && $f !== 'semua') $f = 'ditinjau';
$hit = [];
foreach ($pdo->query('SELECT status, COUNT(*) n FROM lt_tulisan GROUP BY status') as $r) $hit[$r['status']] = (int)$r['n'];
$q = $f === 'semua' ? $pdo->query('SELECT t.*, p.nama AS penulis, p.afiliasi, p.tepercaya, u.email FROM lt_tulisan t LEFT JOIN lt_penulis p ON p.user_id = t.user_id JOIN users u ON u.id = t.user_id ORDER BY t.diubah DESC LIMIT 200')
    : (function () use ($pdo, $f) { $q = $pdo->prepare('SELECT t.*, p.nama AS penulis, p.afiliasi, p.tepercaya, u.email FROM lt_tulisan t LEFT JOIN lt_penulis p ON p.user_id = t.user_id JOIN users u ON u.id = t.user_id WHERE t.status = ? ORDER BY COALESCE(t.dikirim, t.diubah) ASC LIMIT 200'); $q->execute([$f]); return $q; })();
$tabs = '';
foreach (['ditinjau' => 'Menunggu', 'revisi' => 'Revisi', 'terbit' => 'Terbit', 'ditolak' => 'Ditolak', 'draf' => 'Draf penulis', 'semua' => 'Semua'] as $k => $l) $tabs .= '<a class="' . ($f === $k ? 'on' : '') . '" href="?f=' . $k . '">' . $l . (isset($hit[$k]) ? ' <small>' . $hit[$k] . '</small>' : '') . '</a>';
$list = '';
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $kurang = lt_periksa($t);
    $rubrikOpsi = ''; foreach (LT_RUBRIK as $k => [$n]) $rubrikOpsi .= '<option value="' . $k . '"' . ($t['rubrik'] === $k ? ' selected' : '') . '>' . $n . '</option>';
    $frm = fn(string $act, string $label, string $cls = 'ghost', string $confirm = '') => '<button class="btn ' . $cls . '" style="min-height:36px;padding:5px 14px;font-size:13px" type="submit" name="act" value="' . $act . '"' . ($confirm ? ' onclick="return confirm(\'' . e($confirm) . '\')"' : '') . '>' . $label . '</button> ';
    $list .= '<details class="kirim"' . ($f === 'ditinjau' ? ' open' : '') . '><summary><b>' . e($t['judul'] ?: '(tanpa judul)') . '</b><span class="mute" style="font-size:13px"> · ' . e(LT_RUBRIK[$t['rubrik']][0] ?? '') . ' · ' . e($t['penulis'] ?: '?') . ($t['tepercaya'] ? ' <span class="pill done">tepercaya</span>' : '') . ' · ' . e(lt_tgl($t['dikirim'] ?: $t['diubah'])) . ($t['status'] === 'terbit' ? ' · ' . (int)$t['dilihat'] . ' dibaca' : '') . '</span></summary>'
        . '<div style="display:flex;flex-direction:column;gap:12px;margin-top:12px">'
        . '<p class="mute" style="font-size:14px">' . e($t['dek']) . '</p>'
        . '<p style="font-size:13px"><a href="/literasi/k.php?id=' . $t['id'] . '&pra=1" target="_blank">Buka pratinjau tulisan</a>' . ($t['status'] === 'terbit' ? ' · <a href="' . e(lt_url($t)) . '" target="_blank">halaman terbit</a>' : '') . ' · penulis: ' . e($t['email']) . ($t['afiliasi'] ? ' · ' . e($t['afiliasi']) : '') . ' · ' . str_word_count(strip_tags(lt_html($t['isi']))) . ' kata · ' . count(lt_sumber($t)) . ' sumber</p>'
        . ($kurang ? '<div class="err" style="font-size:13px"><b>Belum memenuhi syarat:</b> ' . e(implode(' ', $kurang)) . '</div>' : '')
        . '<form method="post" action="/literasi/tinjau.php" style="display:flex;flex-direction:column;gap:10px">' . csrf_field() . '<input type="hidden" name="id" value="' . $t['id'] . '">'
        . '<details><summary style="cursor:pointer;font-size:13px;font-weight:700">Sunting ringan (judul, rubrik, ringkasan)</summary><div style="display:flex;flex-direction:column;gap:8px;margin-top:8px"><input name="judul" value="' . e($t['judul']) . '" maxlength="140"><select name="rubrik">' . $rubrikOpsi . '</select><textarea name="dek" rows="2" maxlength="300" style="font:inherit;border:1px solid var(--line);border-radius:12px;padding:10px 14px">' . e($t['dek']) . '</textarea>' . $frm('sunting', 'Simpan suntingan') . '</div></details>'
        . '<label>Catatan untuk penulis (wajib untuk revisi atau tolak; opsional saat terbit)<textarea name="catatan" rows="3" maxlength="2000" style="font:inherit;border:1px solid var(--line);border-radius:12px;padding:10px 14px">' . e($t['status'] !== 'terbit' ? (string)$t['catatan'] : '') . '</textarea></label>'
        . '<div>' . ($t['status'] !== 'terbit' ? $frm('terbit', 'Terbitkan', 'gold') . $frm('revisi', 'Minta revisi') . $frm('ditolak', 'Tolak', 'ghost', 'Tolak tulisan ini? Penulis akan menerima catatan Anda.') : $frm('tarik', 'Tarik dari publikasi', 'ghost', 'Tarik tulisan ini dari publikasi?'))
        . '<button class="btn ghost" style="min-height:36px;padding:5px 14px;font-size:13px" type="submit" name="act" value="tepercaya" onclick="this.form.nilai.value=' . ($t['tepercaya'] ? 0 : 1) . '">' . ($t['tepercaya'] ? 'Cabut status tepercaya' : 'Tandai penulis tepercaya') . '</button><input type="hidden" name="nilai" value="1"></div>'
        . '</form></div></details>';
}
$per = (int)$pdo->query('SELECT COUNT(*) FROM lt_penulis')->fetchColumn();
page('Tinjauan tulisan kontributor', '
<style>.tab{display:flex;gap:6px;flex-wrap:wrap}.tab a{padding:6px 12px;border-radius:999px;border:1px solid var(--line);text-decoration:none;font-size:13px}.tab a.on{background:var(--deep);color:#FFFFF0;border-color:var(--deep)}.tab small{opacity:.8}
details.kirim{border:1px solid var(--line);border-radius:16px;padding:12px 16px}details.kirim>summary{cursor:pointer;list-style:none}details.kirim>summary::-webkit-details-marker{display:none}</style>
<div class="row"><h1>Tinjauan tulisan</h1><span class="mute">' . $per . ' penulis terdaftar</span></div>
<p class="mute">Alur: penulis mengirim → Anda meninjau di sini → Terbitkan, Minta revisi, atau Tolak. Penulis diberi tahu lewat email pada setiap keputusan. Tulisan terbit tampil di /literasi/k/, daftar Literasi, pencarian, dan sitemap.</p>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . '
<div class="tab">' . $tabs . '</div>
<div style="display:flex;flex-direction:column;gap:12px;margin-top:14px">' . ($list ?: '<p class="mute">Tidak ada tulisan di bagian ini.</p>') . '</div>
<p class="mute" style="text-align:center;margin-top:8px"><a href="/admin/">Pusat kendali</a> · <a href="/literasi/">Literasi</a></p>', '900px');
