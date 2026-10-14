<?php
declare(strict_types=1);
require __DIR__ . '/lib_survei.php';
start_session();
$id = (int)($_GET['id'] ?? 0);
$u = sv_require_user('/survei/hasil.php?id=' . $id);
$s = sv_get_owned($id, $u);
if (!$s) { http_response_code(404); page('Survei tidak ditemukan', '<h1>Survei tidak ditemukan</h1><a class="btn" href="/survei/">Ke daftar survei</a>'); }
sv_head('Hasil: ' . $s['title'], '<a href="/survei/">Survei</a> / Hasil');
?>
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
.kpi{padding:14px 18px}.kpi b{display:block;font-size:26px;color:var(--deep);letter-spacing:-.02em}.kpi span{font-size:13px;color:var(--mute)}
.tabs{display:flex;gap:6px;border-bottom:1px solid var(--line)}
.tabs button{border:0;background:none;padding:10px 14px;font-weight:700;color:var(--mute);cursor:pointer;border-bottom:3px solid transparent;font-size:14.5px}
.tabs button[aria-selected=true]{color:var(--deep);border-bottom-color:var(--deep)}
.rq{padding:18px 20px;display:flex;flex-direction:column;gap:12px}
.rq .qt{font-weight:700}.rq .meta{font-size:13px;color:var(--mute)}
.bars{display:flex;flex-direction:column;gap:7px}
.br{display:grid;grid-template-columns:minmax(90px,38%) 1fr 74px;gap:10px;align-items:center;font-size:14px}
.br .tr{height:22px;background:var(--mist);border-radius:6px;overflow:hidden}.br .tr i{display:block;height:100%;background:var(--deep);border-radius:6px}
.br .v{text-align:right;font-variant-numeric:tabular-nums;color:var(--mute);font-size:13px}
.stack{display:flex;height:26px;border-radius:7px;overflow:hidden;background:var(--mist)}
.stack i{display:block;height:100%}
.legend{display:flex;gap:6px 14px;flex-wrap:wrap;font-size:12.5px;color:var(--mute)}.legend span{display:inline-flex;gap:6px;align-items:center}.legend em{width:11px;height:11px;border-radius:3px;display:inline-block}
.mxr{display:grid;grid-template-columns:minmax(120px,34%) 1fr 54px;gap:10px;align-items:center;font-size:14px}
.big{font-size:34px;font-weight:800;color:var(--deep);letter-spacing:-.02em}
.texts{display:flex;flex-direction:column;gap:6px;max-height:320px;overflow:auto}
.texts p{margin:0;padding:8px 12px;background:var(--bg);border:1px solid var(--line);border-radius:10px;font-size:14px;white-space:pre-line}
table.rs{width:100%;border-collapse:collapse;font-size:13.5px}
table.rs th,table.rs td{text-align:left;padding:8px 10px;border-bottom:1px solid var(--line);vertical-align:top}
table.rs th{font-size:12px;color:var(--mute);background:var(--mist);position:sticky;top:0}
@media (max-width:560px){.br{grid-template-columns:1fr 64px}.br .lb{grid-column:1/-1}.mxr{grid-template-columns:1fr 50px}.mxr .lb{grid-column:1/-1}}
</style>
<main class="wrap" style="display:flex;flex-direction:column;gap:18px;padding-top:20px">
  <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
    <div style="flex:1;min-width:220px"><p class="mute small" style="margin:0 0 4px;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Hasil survei</p><h1 id="ttl"><?= e($s['title']) ?></h1></div>
    <a class="btn ghost sm" href="edit.php?id=<?= $id ?>">Sunting</a>
    <button class="btn ghost sm" type="button" id="btnShare">Bagikan</button>
    <?php if ($u['role'] === 'admin'): ?><a class="btn sm" href="/asesmen/admin.php?api=csv_survei&amp;id=<?= $id ?>">Unduh CSV (admin)</a><?php endif; ?>
  </div>
  <div class="kpis" id="kpis"></div>
  <div class="tabs" role="tablist"><button role="tab" aria-selected="true" data-v="sum" type="button">Ringkasan</button><button role="tab" aria-selected="false" data-v="ind" type="button">Tanggapan</button></div>
  <div id="sum" style="display:flex;flex-direction:column;gap:12px"></div>
  <div id="ind" hidden></div>
</main>
<?php include __DIR__ . '/share.inc.php'; ?>
<script>
var CSRF=<?= json_encode($_SESSION['csrf']) ?>,SID=<?= $id ?>,SV={id:SID,slug:<?= json_encode($s['slug']) ?>,status:<?= json_encode($s['status']) ?>,title:<?= json_encode($s['title'], JSON_HEX_TAG) ?>};
document.getElementById('btnShare').onclick=function(){ZKShare.open(SV,function(x){SV=x;});};
</script>
<script src="/survei/assets/hasil.js?v=3"></script>
</body></html>
