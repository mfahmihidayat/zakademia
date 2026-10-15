<?php
declare(strict_types=1);
require __DIR__ . '/lib_survei.php';
start_session();
$id = (int)($_GET['id'] ?? 0);
$u = sv_require_verified('/survei/edit.php?id=' . $id);
$s = sv_get_owned($id, $u);
if (!$s) { http_response_code(404); page('Survei tidak ditemukan', '<h1>Survei tidak ditemukan</h1><p class="mute">Survei ini tidak ada atau bukan milik akun Anda.</p><a class="btn" href="/survei/">Ke daftar survei</a>'); }
$q = sv_db()->prepare('SELECT COUNT(*) FROM responses WHERE survey_id = ?');
$q->execute([$id]);
$n = (int)$q->fetchColumn();

sv_head('Sunting: ' . $s['title'], '<a href="/survei/">Survei</a> / Sunting');
?>
<style>
.ed{display:grid;gap:20px;grid-template-columns:minmax(0,1fr)}
@media (min-width:980px){.ed{grid-template-columns:minmax(0,1fr) 320px;align-items:start}.side{position:sticky;top:76px;max-height:calc(100vh - 92px);overflow:auto}}
.bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px}
.bar .sp{flex:1}
.saved{font-size:13px;color:var(--mute);font-weight:600}
.qc{padding:16px 18px;display:flex;flex-direction:column;gap:10px;border-left:5px solid transparent}
.qc.focus{border-left-color:var(--deep)}
.qc.sec{background:var(--mist)}
.qc .row{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.qc .num{min-width:30px;height:30px;padding:0 8px;border-radius:9px;background:var(--mist);display:grid;place-items:center;font-weight:800;font-size:13px;color:var(--deep)}
.qc.sec .num{background:var(--deep);color:#FFFFF0}
.qc .tsel{width:auto;min-height:38px;padding:6px 10px;font-size:14px;font-weight:600}
.qc .tools{margin-left:auto;display:flex;gap:4px}
.qc .tools .icon-btn{width:34px;height:34px;font-size:14px}
.qc input.tt{font-size:16px;font-weight:700;border:0;border-bottom:1.5px solid var(--line);border-radius:0;background:transparent;padding:8px 2px;min-height:auto}
.qc input.tt:focus{border-bottom-color:var(--deep);outline:none}
.qc input.hp{border:0;border-bottom:1px dashed var(--line);border-radius:0;background:transparent;padding:6px 2px;min-height:auto;font-size:14px;color:var(--mute)}
.ol{display:flex;flex-direction:column;gap:6px}
.ol .o{display:flex;gap:6px;align-items:center}
.ol .o .mk{width:18px;height:18px;border:2px solid var(--line);flex:none}
.ol .o .mk.r{border-radius:50%}.ol .o .mk.c{border-radius:5px}
.ol .o input{min-height:38px;padding:6px 10px}
.ol .o .icon-btn{width:32px;height:32px;font-size:13px}
.linkbtn{background:none;border:0;color:var(--deep);font-weight:700;cursor:pointer;padding:6px 0;font-size:14px;text-align:left}
.opts-set{display:flex;gap:6px 16px;flex-wrap:wrap;font-size:14px;color:var(--mute);border-top:1px solid var(--line);padding-top:10px;align-items:center}
.opts-set label{display:inline-flex;gap:6px;align-items:center;cursor:pointer}
.opts-set input[type=checkbox]{width:18px;height:18px;accent-color:var(--deep)}
.opts-set input.f,.opts-set select.f{min-height:34px;padding:4px 8px;width:auto;font-size:14px}
.tswitch{margin-left:auto}
.side .card{padding:16px 18px;display:flex;flex-direction:column;gap:12px}
.pal{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.pal button{display:flex;gap:8px;align-items:center;text-align:left;border:1px solid var(--line);background:var(--paper);border-radius:12px;padding:8px 10px;cursor:pointer;font-size:13px;font-weight:600;min-height:42px}
.pal button:hover{background:var(--mist);border-color:var(--deep)}
.pal i{font-style:normal;width:22px;text-align:center;color:var(--deep);font-size:15px}
.hdcard{padding:20px 22px;border-top:8px solid var(--deep);display:flex;flex-direction:column;gap:8px}
.hdcard input{font-size:clamp(20px,3vw,26px);font-weight:800;border:0;border-bottom:1.5px solid var(--line);border-radius:0;background:transparent;padding:6px 2px;letter-spacing:-.01em}
.hdcard textarea{border:0;border-bottom:1px dashed var(--line);border-radius:0;background:transparent;min-height:60px;padding:6px 2px}
.addbar{display:flex;justify-content:center}
#previewBody{background:var(--bg);padding:20px 14px}
</style>
<main class="wrap" style="padding-top:18px">
  <div class="bar">
    <span class="pill" id="stPill"></span>
    <span class="saved" id="saved" role="status">Tersimpan</span>
    <span class="sp"></span>
    <a class="btn ghost sm" href="hasil.php?id=<?= $id ?>">Hasil · <?= $n ?></a>
    <button class="btn ghost sm" type="button" id="btnPreview">Pratinjau</button>
    <button class="btn sm" type="button" id="btnShare">Bagikan</button>
  </div>
  <div class="ed">
    <div style="display:flex;flex-direction:column;gap:12px" id="main">
      <div class="card hdcard">
        <label class="sr" for="svTitle">Judul survei</label><input class="f" id="svTitle" maxlength="200" placeholder="Judul survei">
        <label class="sr" for="svDesc">Deskripsi</label><textarea class="f" id="svDesc" maxlength="3000" placeholder="Deskripsi atau pengantar untuk responden: tujuan survei, perkiraan waktu, dan jaminan kerahasiaan"></textarea>
      </div>
      <div id="qs" style="display:flex;flex-direction:column;gap:12px"></div>
      <div class="addbar"><button class="btn ghost" type="button" id="addEnd">＋ Tambah pertanyaan</button></div>
    </div>
    <aside class="side" style="display:flex;flex-direction:column;gap:14px">
      <div class="card"><b>Tambah pertanyaan</b><p class="mute small" style="margin:-6px 0 0">Ditambahkan di bawah pertanyaan yang sedang dipilih.</p><div class="pal" id="pal"></div></div>
      <div class="card"><b>Pengaturan</b>
        <label class="opts-set" style="border:0;padding:0"><input type="checkbox" id="oProg"> Tampilkan bilah kemajuan</label>
        <label class="opts-set" style="border:0;padding:0"><input type="checkbox" id="oOnce"> Satu kali isi per perangkat</label>
        <label class="lb">Tutup otomatis setelah tanggal<input class="f" type="date" id="oCloses"></label>
        <label class="lb">Batas jumlah responden<input class="f" type="number" min="0" id="oMax" placeholder="0 = tanpa batas"></label>
        <label class="lb">Pesan setelah mengirim<textarea class="f" id="oThanks" maxlength="1000" placeholder="Terima kasih, jawaban Anda sudah kami terima."></textarea></label>
      </div>
    </aside>
  </div>
</main>

<dialog class="dlg wide" id="dlgPreview"><div class="hd"><h2>Pratinjau</h2><span class="mute small">Tampilan responden; jawaban tidak disimpan.</span><button class="icon-btn" type="button" data-close aria-label="Tutup">✕</button></div><div class="bd" id="previewBody"></div></dialog>
<dialog class="dlg" id="dlgPal"><div class="hd"><h2>Tambah pertanyaan</h2><button class="icon-btn" type="button" data-close aria-label="Tutup">✕</button></div><div class="bd"><div class="pal" id="pal2"></div></div></dialog>
<dialog class="dlg" id="dlgBulk"><div class="hd"><h2>Tempel banyak pilihan</h2><button class="icon-btn" type="button" data-close aria-label="Tutup">✕</button></div><div class="bd">
  <p class="mute small" style="margin:0">Satu pilihan per baris. Pilihan yang ada akan diganti.</p><textarea class="f" id="bulkTa" rows="8"></textarea><div><button class="btn" type="button" id="bulkOk">Pakai pilihan ini</button></div></div></dialog>

<?php include __DIR__ . '/share.inc.php'; ?>
<script src="/survei/assets/form.js?v=3"></script>
<script>
var CSRF=<?= json_encode($_SESSION['csrf']) ?>,SID=<?= $id ?>,DEF=<?= json_encode(json_decode($s['def'], true), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    SV={id:SID,slug:<?= json_encode($s['slug']) ?>,status:<?= json_encode($s['status']) ?>,title:<?= json_encode($s['title'], JSON_HEX_TAG) ?>};
</script>
<script src="/survei/assets/builder.js?v=3"></script>
</body></html>
