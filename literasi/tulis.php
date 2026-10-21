<?php
declare(strict_types=1);
/** Halaman menulis untuk kontributor: profil penulis, editor tulisan, simpan draf, pratinjau, kirim untuk ditinjau. */
require __DIR__ . '/lib_literasi.php';
start_session();
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
$u = current_user();
$self = '/literasi/tulis/';
if (!$u) {
    page('Tulis untuk Zakademia', '<h1>Tulis untuk Zakademia</h1><p class="mute">Siapa pun yang punya akun Zakademia dapat mengirim tulisan seputar zakat, wakaf, dan filantropi Islam. Setiap tulisan ditinjau editor sebelum terbit dan tampil dengan nama Anda.</p>'
        . '<div class="row" style="justify-content:flex-start"><a class="btn" href="/asesmen/masuk.php?next=' . urlencode($self) . '">Masuk</a><a class="btn ghost" href="/asesmen/daftar.php?next=' . urlencode($self) . '">Daftar akun</a></div>'
        . '<p class="mute" style="margin-top:8px"><a href="/literasi/">Kembali ke Literasi</a></p>');
}
if (!user_verified($u)) {
    page('Tulis untuk Zakademia', '<h1>Verifikasi email dulu</h1><p class="mute">Untuk menulis, email akun Anda perlu diverifikasi lebih dulu. Ini melindungi nama penulis dari penyalahgunaan.</p><a class="btn" href="/asesmen/verifikasi.php?next=' . urlencode($self) . '">Verifikasi sekarang</a>');
}
$pdo = lt_db();
$pen = lt_penulis((int)$u['id']);
$error = ''; $ok = flash(); $kurang = [];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$t = $id ? lt_tulisan($id) : null;
if ($t && (int)$t['user_id'] !== (int)$u['id']) { http_response_code(403); page('Tulis', '<h1>Bukan tulisan Anda</h1><a class="btn ghost" href="/literasi/saya/">Tulisan saya</a>'); }
if ($t && !in_array($t['status'], ['draf', 'revisi'], true)) redirect('/literasi/saya/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = (string)($_POST['act'] ?? '');
    $ajax = !empty($_POST['ajax']);
    if (!form_csrf_ok()) { if ($ajax) json_out(['ok' => false, 'error' => 'sesi'], 400); $error = 'Sesi kedaluwarsa. Salin dulu tulisan Anda, lalu muat ulang halaman.'; }
    elseif ($act === 'profil') {
        $nama = clip($_POST['nama'] ?? '', 80); $afil = clip($_POST['afiliasi'] ?? '', 120); $bio = clip($_POST['bio'] ?? '', 300); $taut = clip($_POST['tautan'] ?? '', 200);
        if (mb_strlen($nama) < 3) $error = 'Nama penulis minimal 3 karakter.';
        elseif ($taut !== '' && !preg_match('#^https?://#', $taut)) $error = 'Tautan profil harus diawali https://';
        else {
            $pdo->prepare('INSERT INTO lt_penulis (user_id, nama, afiliasi, bio, tautan, dibuat) VALUES (?,?,?,?,?,?) ON CONFLICT(user_id) DO UPDATE SET nama = excluded.nama, afiliasi = excluded.afiliasi, bio = excluded.bio, tautan = excluded.tautan')
                ->execute([(int)$u['id'], $nama, $afil, $bio, $taut, gmdate('c')]);
            flash('Profil penulis tersimpan.'); redirect($self . ($id ? '?id=' . $id : ''));
        }
    } elseif ($act === 'simpan' || $act === 'kirim') {
        if (!$pen) { $error = 'Lengkapi profil penulis dulu.'; }
        else {
            $sumber = [];
            foreach (($_POST['s_judul'] ?? []) as $k => $j) {
                $j = clip($j, 200); $url = clip($_POST['s_url'][$k] ?? '', 300);
                if ($j === '' && $url === '') continue;
                $sumber[] = ['penerbit' => clip($_POST['s_penerbit'][$k] ?? '', 120), 'judul' => $j, 'tanggal' => clip($_POST['s_tanggal'][$k] ?? '', 40), 'url' => $url];
            }
            $data = ['judul' => clip($_POST['judul'] ?? '', 140), 'rubrik' => isset(LT_RUBRIK[$_POST['rubrik'] ?? '']) ? $_POST['rubrik'] : 'talk',
                'dek' => clip($_POST['dek'] ?? '', 300), 'poin' => mb_substr((string)($_POST['poin'] ?? ''), 0, 1200), 'isi' => mb_substr((string)($_POST['isi'] ?? ''), 0, 40000),
                'sumber' => json_encode(array_slice($sumber, 0, 12), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
            $now = gmdate('c');
            if ($t) {
                $pdo->prepare('UPDATE lt_tulisan SET judul=?, rubrik=?, dek=?, poin=?, isi=?, sumber=?, diubah=? WHERE id=?')
                    ->execute([$data['judul'], $data['rubrik'], $data['dek'], $data['poin'], $data['isi'], $data['sumber'], $now, $id]);
            } else {
                if (!throttle_ok('lttulis|' . $u['id'], 10, 86400)) { if ($ajax) json_out(['ok' => false], 429); $error = 'Batas 10 tulisan baru per hari.'; }
                else {
                    $pdo->prepare('INSERT INTO lt_tulisan (user_id, judul, rubrik, dek, poin, isi, sumber, dibuat, diubah) VALUES (?,?,?,?,?,?,?,?,?)')
                        ->execute([(int)$u['id'], $data['judul'], $data['rubrik'], $data['dek'], $data['poin'], $data['isi'], $data['sumber'], $now, $now]);
                    $id = (int)$pdo->lastInsertId(); throttle_hit('lttulis|' . $u['id']);
                }
            }
            if (!$error) {
                $t = lt_tulisan($id);
                if ($ajax) json_out(['ok' => true, 'id' => $id, 'waktu' => (new DateTime('now', new DateTimeZone('Asia/Jakarta')))->format('H.i')]);
                if ($act === 'kirim') {
                    $kurang = lt_periksa($t);
                    if (empty($_POST['asli'])) $kurang[] = 'Centang pernyataan bahwa tulisan ini karya Anda sendiri.';
                    if (!$kurang) {
                        if (!throttle_ok('ltkirim|' . $u['id'], 3, 7 * 86400)) $error = 'Batas 3 kiriman per pekan. Sementara disimpan sebagai draf.';
                        else {
                            $slug = lt_slug($t['judul'], $t['slug'], $id);
                            $pdo->prepare('UPDATE lt_tulisan SET status = \'ditinjau\', slug = ?, dikirim = ?, catatan = NULL WHERE id = ?')->execute([$slug, $now, $id]);
                            throttle_hit('ltkirim|' . $u['id']);
                            lt_mail_status(lt_tulisan($id), 'ditinjau');
                            flash('Tulisan terkirim dan masuk antrean tinjauan. Kami kabari lewat email setelah ditinjau.'); redirect('/literasi/saya/');
                        }
                    } else $error = 'Belum bisa dikirim. Lengkapi dulu yang berikut.';
                } elseif (($_POST['lalu'] ?? '') === 'pratinjau') redirect('/literasi/k.php?id=' . $id . '&pra=1');
                else { flash('Draf tersimpan.'); redirect($self . '?id=' . $id); }
            }
        }
    }
}

$v = fn(string $k) => e($_POST[$k] ?? ($t[$k] ?? ''));
$sumber = $_POST ? array_map(fn($k) => ['penerbit' => $_POST['s_penerbit'][$k] ?? '', 'judul' => $_POST['s_judul'][$k] ?? '', 'tanggal' => $_POST['s_tanggal'][$k] ?? '', 'url' => $_POST['s_url'][$k] ?? ''], array_keys($_POST['s_judul'] ?? [])) : ($t ? lt_sumber($t) : []);
while (count($sumber) < 3) $sumber[] = ['penerbit' => '', 'judul' => '', 'tanggal' => '', 'url' => ''];
$rubrikOpsi = '';
$rubrikNow = $_POST['rubrik'] ?? ($t['rubrik'] ?? 'talk');
foreach (LT_RUBRIK as $k => [$n, $q]) $rubrikOpsi .= '<option value="' . $k . '"' . ($rubrikNow === $k ? ' selected' : '') . '>' . $n . ' · ' . e($q) . '</option>';
$srcRows = '';
foreach ($sumber as $k => $s) $srcRows .= '<div class="src"><input name="s_penerbit[]" placeholder="Penerbit / lembaga" value="' . e($s['penerbit']) . '" maxlength="120"><input name="s_judul[]" placeholder="Judul sumber" value="' . e($s['judul']) . '" maxlength="200"><input name="s_tanggal[]" placeholder="Tanggal / tahun" value="' . e($s['tanggal']) . '" maxlength="40"><input name="s_url[]" type="url" placeholder="https://..." value="' . e($s['url']) . '" maxlength="300"></div>';

$profil = '<form method="post" action="' . $self . '" style="display:flex;flex-direction:column;gap:14px">' . csrf_field() . '<input type="hidden" name="act" value="profil"><input type="hidden" name="id" value="' . $id . '">'
    . '<label>Nama penulis (tampil di tulisan)<input name="nama" required maxlength="80" value="' . e($pen['nama'] ?? $u['name']) . '"></label>'
    . '<label>Afiliasi (lembaga, kampus, atau profesi)<input name="afiliasi" maxlength="120" value="' . e($pen['afiliasi'] ?? ($u['org'] ?? '')) . '" placeholder="Misalnya: Amil LAZ Harapan, atau Mahasiswa UIN Jakarta"></label>'
    . '<label>Bio singkat (maksimal 300 karakter)<textarea name="bio" maxlength="300" rows="3">' . e($pen['bio'] ?? '') . '</textarea></label>'
    . '<label>Tautan profil (LinkedIn, Instagram, atau situs; opsional)<input name="tautan" maxlength="200" value="' . e($pen['tautan'] ?? '') . '" placeholder="https://"></label>'
    . '<button class="btn' . ($pen ? ' ghost' : '') . '" type="submit">' . ($pen ? 'Simpan profil' : 'Simpan dan mulai menulis') . '</button></form>';

$kurangHtml = $kurang ? '<div class="err"><b>Belum bisa dikirim:</b><ul style="margin:6px 0 0 18px;padding:0">' . implode('', array_map(fn($k) => '<li>' . e($k) . '</li>', $kurang)) . '</ul></div>' : '';

page($t ? 'Sunting tulisan' : 'Tulis untuk Zakademia', '
<style>
textarea{font:inherit;font-size:16px;border:1px solid var(--line);border-radius:12px;padding:11px 14px;min-height:80px;resize:vertical;background:#fff}
textarea.isi{min-height:420px;line-height:1.65}
.src{display:grid;grid-template-columns:1fr 1.4fr .7fr 1.4fr;gap:8px}.src input{min-height:40px;font-size:14px;padding:8px 10px}
@media(max-width:640px){.src{grid-template-columns:1fr 1fr}}
.panduan{background:var(--mist);border:1px solid var(--line);border-radius:14px;padding:14px 16px;font-size:14px}.panduan code{background:#fff;padding:1px 6px;border-radius:6px;font-size:13px}
.panduan summary{cursor:pointer;font-weight:700}
.hitung{font-size:13px;color:var(--mute);text-align:right}
details.profil{border:1px solid var(--line);border-radius:14px;padding:12px 16px}details.profil summary{cursor:pointer;font-weight:700}
.aksi{display:flex;gap:10px;flex-wrap:wrap;align-items:center;position:sticky;bottom:0;background:var(--paper);padding:12px 0;border-top:1px solid var(--line)}
#autosave{font-size:13px;color:var(--mute);margin-left:auto}
</style>
<div class="row"><h1>' . ($t ? 'Sunting tulisan' : 'Tulis untuk Zakademia') . '</h1><a class="mute" href="/literasi/saya/">Tulisan saya</a></div>
' . ($ok ? '<p class="ok" role="status">' . e($ok) . '</p>' : '') . ($error ? '<p class="err" role="alert">' . e($error) . '</p>' : '') . $kurangHtml . '
' . ($t && $t['status'] === 'revisi' && $t['catatan'] ? '<div class="err"><b>Catatan editor:</b><br>' . nl2br(e($t['catatan'])) . '</div>' : '') . '
' . (!$pen ? '<h2>1. Profil penulis</h2><p class="mute">Diisi sekali. Nama dan afiliasi tampil di setiap tulisan Anda.</p>' . $profil :
    '<details class="profil"><summary>Profil penulis: ' . e($pen['nama']) . ($pen['afiliasi'] ? ' · ' . e($pen['afiliasi']) : '') . '</summary><div style="margin-top:12px">' . $profil . '</div></details>') . '
' . ($pen ? '
<details class="panduan"><summary>Panduan menulis dan seleksi</summary>
<ul style="margin:10px 0 0 18px;padding:0;display:flex;flex-direction:column;gap:6px">
<li>Topik: zakat, wakaf, infak, sedekah, dan filantropi Islam, dari pengalaman lapangan, kajian, data, atau kisah.</li>
<li>Panjang 350 sampai 2.500 kata; satu gagasan per paragraf; bahasa Indonesia yang rapi.</li>
<li>Minimal dua sumber yang bisa dibuka (peraturan, laporan lembaga, jurnal, berita tepercaya). Pendapat hukum harus dirujuk ke sumbernya.</li>
<li>Tulisan harus karya sendiri, belum terbit di media lain, dan bukan sepenuhnya hasil mesin.</li>
<li>Format teks: baris kosong memisahkan paragraf; <code>## Subjudul</code>; <code>- </code> untuk daftar; <code>1. </code> daftar bernomor; <code>**tebal**</code>; <code>*miring*</code>; <code>[teks](https://alamat)</code> tautan; <code>{1}</code> rujukan ke sumber nomor 1.</li>
<li>Editor meninjau dalam 3 sampai 7 hari dan dapat menyunting ringan (ejaan, judul) tanpa mengubah substansi.</li>
</ul></details>
<form method="post" action="' . $self . '" id="fTulis" style="display:flex;flex-direction:column;gap:16px;margin-top:8px">' . csrf_field() . '<input type="hidden" name="act" value="simpan" id="act"><input type="hidden" name="lalu" value="" id="lalu"><input type="hidden" name="id" value="' . $id . '" id="idT">
  <label>Judul<input name="judul" maxlength="140" value="' . $v('judul') . '" placeholder="Jelas dan spesifik, misalnya: Tiga Pelajaran Mengelola Wakaf Produktif di Pesantren"></label>
  <label>Rubrik<select name="rubrik">' . $rubrikOpsi . '</select></label>
  <label>Ringkasan (60 sampai 240 karakter; tampil di kartu dan hasil pencarian)<textarea name="dek" maxlength="300" rows="2" data-hitung>' . $v('dek') . '</textarea><span class="hitung"></span></label>
  <label>Poin penting (2 sampai 4 baris, satu poin per baris)<textarea name="poin" rows="4" maxlength="1200">' . $v('poin') . '</textarea></label>
  <label>Isi tulisan<textarea name="isi" class="isi" data-hitung-kata>' . $v('isi') . '</textarea><span class="hitung"></span></label>
  <div><b style="font-size:14px">Sumber (minimal 2)</b><p class="mute" style="font-size:13px;margin:2px 0 8px">Rujuk di teks dengan {1}, {2}, dan seterusnya sesuai urutan di sini.</p><div id="sumber" style="display:flex;flex-direction:column;gap:8px">' . $srcRows . '</div><button type="button" class="btn ghost" style="min-height:36px;font-size:13px;margin-top:8px" id="tambahSrc">Tambah sumber</button></div>
  <label class="check"><input type="checkbox" name="asli" value="1" id="asli"' . (!empty($_POST['asli']) ? ' checked' : '') . '> Tulisan ini karya saya sendiri, belum terbit di tempat lain, dan saya setuju diterbitkan di Zakademia dengan nama saya.</label>
  <div class="aksi"><button class="btn ghost" type="submit" data-act="simpan">Simpan draf</button><button class="btn ghost" type="submit" data-act="simpan" data-lalu="pratinjau">Pratinjau</button><button class="btn gold" type="submit" data-act="kirim">Kirim untuk ditinjau</button><span id="autosave"></span></div>
</form>
<script>
(function(){
  var f=document.getElementById("fTulis");
  f.querySelectorAll("[data-act]").forEach(function(b){ b.addEventListener("click",function(){ document.getElementById("act").value=b.dataset.act; document.getElementById("lalu").value=b.dataset.lalu||""; document.getElementById("asli").required=b.dataset.act==="kirim"; }); });
  f.querySelectorAll("[data-hitung]").forEach(function(t){ var s=t.nextElementSibling; function u(){ s.textContent=t.value.length+" karakter"; } t.addEventListener("input",u); u(); });
  f.querySelectorAll("[data-hitung-kata]").forEach(function(t){ var s=t.nextElementSibling; function u(){ var n=(t.value.trim().match(/\\S+/g)||[]).length; s.textContent=n+" kata"; } t.addEventListener("input",u); u(); });
  document.getElementById("tambahSrc").onclick=function(){ var d=document.createElement("div"); d.className="src"; d.innerHTML=\'<input name="s_penerbit[]" placeholder="Penerbit / lembaga" maxlength="120"><input name="s_judul[]" placeholder="Judul sumber" maxlength="200"><input name="s_tanggal[]" placeholder="Tanggal / tahun" maxlength="40"><input name="s_url[]" type="url" placeholder="https://..." maxlength="300">\'; document.getElementById("sumber").appendChild(d); };
  // simpan otomatis tiap 40 detik bila ada perubahan
  var dirty=false, st=document.getElementById("autosave"); f.addEventListener("input",function(){ dirty=true; });
  setInterval(function(){ if(!dirty) return; dirty=false; var fd=new FormData(f); fd.set("act","simpan"); fd.set("ajax","1");
    fetch(location.pathname,{method:"POST",body:fd,credentials:"same-origin"}).then(function(r){return r.json();}).then(function(j){ if(j&&j.ok){ st.textContent="Tersimpan otomatis "+j.waktu; if(j.id){ document.getElementById("idT").value=j.id; if(!location.search){ history.replaceState(null,"","?id="+j.id); } } } else st.textContent="Gagal menyimpan otomatis"; }).catch(function(){ st.textContent="Gagal menyimpan otomatis"; }); },40000);
  window.addEventListener("beforeunload",function(e){ if(dirty){ e.preventDefault(); e.returnValue=""; } });
  f.addEventListener("submit",function(){ dirty=false; });
})();
</script>' : '') . '
<p class="mute" style="text-align:center;margin-top:8px"><a href="/literasi/">Literasi</a> · <a href="/asesmen/akun.php">Akun saya</a></p>', '820px');
