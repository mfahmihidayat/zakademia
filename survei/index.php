<?php
declare(strict_types=1);
require __DIR__ . '/lib_survei.php';
start_session();
$u = sv_require_user('/survei/');

$sql = 'SELECT s.id, s.slug, s.title, s.status, s.updated, s.user_id, s.quota, (SELECT COUNT(*) FROM responses r WHERE r.survey_id = s.id) AS n,
        (SELECT MAX(created) FROM responses r WHERE r.survey_id = s.id) AS last FROM surveys s';
if ($u['role'] === 'admin' && isset($_GET['semua'])) {
    $q = sv_db()->query($sql . ' ORDER BY s.updated DESC');
} else {
    $q = sv_db()->prepare($sql . ' WHERE s.user_id = ? ORDER BY s.updated DESC');
    $q->execute([$u['id']]);
}
$list = $q->fetchAll(PDO::FETCH_ASSOC);
foreach ($list as &$r) {
    $r['url'] = sv_public_url($r['slug']); $r['n'] = (int)$r['n']; $r['id'] = (int)$r['id'];
    $r['custom'] = $r['quota'] !== null; $r['quota'] = sv_quota($r);
    $r['full'] = $r['quota'] > 0 && $r['n'] >= $r['quota'];
    $r['consult'] = sv_consult_url($r);
}
unset($r);
$tpls = [];
foreach (sv_templates() as $k => $t) $tpls[] = ['k' => $k, 'name' => $t['name'], 'desc' => $t['desc']];

sv_head('Survei', 'Survei');
?>
<main class="wrap" style="display:flex;flex-direction:column;gap:26px">
  <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap">
    <div style="flex:1;min-width:240px"><p class="mute small" style="margin:0 0 4px;font-weight:700;letter-spacing:.08em;text-transform:uppercase">Riset Kebutuhan</p><h1>Survei <?= e($u['org'] ?: $u['name']) ?></h1>
      <p class="mute" style="margin:6px 0 0">Susun kuesioner, bagikan tautan, dan pantau jawaban yang masuk.</p></div>
    <?php if ($u['role'] === 'admin'): ?><a class="btn ghost sm" href="?<?= isset($_GET['semua']) ? '' : 'semua=1' ?>"><?= isset($_GET['semua']) ? 'Survei saya saja' : 'Lihat semua survei (admin)' ?></a><?php endif; ?>
  </div>

  <section style="display:flex;flex-direction:column;gap:12px">
    <h2>Buat survei baru</h2>
    <div id="tpls" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px"></div>
  </section>

  <section style="display:flex;flex-direction:column;gap:12px">
    <h2>Survei Anda</h2>
    <div id="list" style="display:flex;flex-direction:column;gap:10px"></div>
  </section>
</main>

<?php include __DIR__ . '/share.inc.php'; ?>
<script>
var CSRF=<?= json_encode($_SESSION['csrf']) ?>,ADMIN=<?= $u['role'] === 'admin' ? 'true' : 'false' ?>,LIST=<?= json_encode($list, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>,TPLS=<?= json_encode($tpls, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
function h(t,c,x){var e=document.createElement(t);if(c)e.className=c;if(x!=null)e.textContent=x;return e;}
function toast(m){var t=h('div','toast',m);document.body.appendChild(t);setTimeout(function(){t.remove();},2600);}
function api(a,body){return fetch('api.php?a='+a,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:JSON.stringify(body||{})}).then(function(r){return r.json();}).then(function(j){if(!j.ok)throw new Error(j.error||'Gagal');return j;});}
var ST={draf:'Draf',aktif:'Aktif',tutup:'Ditutup'};
function when(iso){if(!iso)return '–';var d=new Date(iso);return d.toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})+' '+d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});}

TPLS.forEach(function(t){var b=h('button','card');b.type='button';b.style.cssText='text-align:left;padding:16px 18px;cursor:pointer;display:flex;flex-direction:column;gap:6px;border-radius:16px';
  var top=h('b',null,(t.k==='kosong'?'＋ ':'')+t.name);b.appendChild(top);b.appendChild(h('span','mute small',t.desc));
  b.onclick=function(){b.disabled=true;api('buat',{tpl:t.k}).then(function(j){location.href='edit.php?id='+j.id;},function(e){b.disabled=false;toast(e.message);});};
  document.getElementById('tpls').appendChild(b);});

function drawList(){var L=document.getElementById('list');L.textContent='';
  if(!LIST.length){L.appendChild(h('p','mute','Belum ada survei. Pilih template di atas untuk memulai.'));return;}
  LIST.forEach(function(s){var c=h('div','card');c.style.cssText='padding:14px 18px;display:flex;gap:12px 18px;align-items:center;flex-wrap:wrap';
    var info=h('div');info.style.cssText='flex:1;min-width:220px;display:flex;flex-direction:column;gap:4px';
    var tl=h('a',null,s.title);tl.href='edit.php?id='+s.id;tl.style.cssText='font-size:16px;text-decoration:none;color:var(--ink)';info.appendChild(tl);
    var meta=h('div','mute small');meta.appendChild(h('span','pill '+s.status,ST[s.status]));meta.appendChild(document.createTextNode('  /s/'+s.slug+' · diubah '+when(s.updated)));info.appendChild(meta);c.appendChild(info);
    var n=h('a');n.href='hasil.php?id='+s.id;n.style.cssText='text-decoration:none;text-align:center;min-width:76px';var nb=h('b',null,String(s.n));nb.style.cssText='display:block;font-size:22px;color:var(--deep)';n.appendChild(nb);n.appendChild(h('span','mute small',s.quota>0?'dari '+s.quota+' jawaban':'jawaban'));c.appendChild(n);
    var acts=h('div');acts.style.cssText='display:flex;gap:6px;flex-wrap:wrap';
    function a(label,cls,fn){var b=h('button','btn sm '+cls,label);b.type='button';b.onclick=fn;acts.appendChild(b);return b;}
    a('Sunting','ghost',function(){location.href='edit.php?id='+s.id;});
    a('Hasil','ghost',function(){location.href='hasil.php?id='+s.id;});
    a('Bagikan','',function(){openShare(s);});
    var more=h('select','f');more.style.cssText='width:auto;min-height:36px;padding:4px 10px;font-size:13.5px';more.setAttribute('aria-label','Tindakan lain');
    [['','Lainnya…'],['dup','Gandakan'],['del','Hapus']].concat(ADMIN?[['quota','Atur kuota (admin)']]:[]).forEach(function(o){var x=h('option',null,o[1]);x.value=o[0];more.appendChild(x);});
    more.onchange=function(){var v=more.value;more.value='';
      if(v==='quota'){var cur=s.custom?String(s.quota):'';var x=prompt('Kuota jawaban untuk "'+s.title+'"\nAngka = batas jawaban, 0 = tanpa batas, kosongkan = bawaan (10 untuk akun biasa).',cur);
        if(x!==null)api('kuota',{id:s.id,quota:x.trim()}).then(function(j){s.quota=j.quota;s.custom=j.custom;s.full=s.quota>0&&s.n>=s.quota;drawList();toast(j.quota>0?'Kuota diatur: '+j.quota+' jawaban.':'Kuota: tanpa batas.');},function(e){toast(e.message);});}
      if(v==='dup')api('gandakan',{id:s.id}).then(function(j){location.href='edit.php?id='+j.id;},function(e){toast(e.message);});
      if(v==='del'&&confirm('Hapus survei "'+s.title+'" beserta '+s.n+' jawabannya? Tindakan ini tidak dapat dibatalkan.'))api('hapus',{id:s.id}).then(function(){LIST=LIST.filter(function(x){return x!==s;});drawList();toast('Survei dihapus.');},function(e){toast(e.message);});};
    acts.appendChild(more);c.appendChild(acts);
    if(s.full){var nb2=h('div','err-box');nb2.style.cssText='flex-basis:100%;display:flex;gap:8px 14px;align-items:center;flex-wrap:wrap';
      nb2.appendChild(h('span',null,'Batas '+s.quota+' jawaban sudah tercapai. Survei tidak lagi menerima jawaban baru.'));
      var cl=h('a','btn sm','Konsultasi untuk melanjutkan');cl.href=s.consult;cl.target='_blank';cl.rel='noopener';nb2.appendChild(cl);c.appendChild(nb2);}
    L.appendChild(c);});}
drawList();

function openShare(s){ZKShare.open(s,function(){drawList();});}
</script>
</body></html>
