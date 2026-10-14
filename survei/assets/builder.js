/* Zakademia Survei — editor kuesioner */
(function(){
'use strict';
var TYPES=[
 ['short','Jawaban singkat','—'],['long','Paragraf','¶'],['single','Pilihan ganda','◉'],['multi','Kotak centang','☑'],
 ['dropdown','Dropdown','▾'],['likert','Skala Likert','⋯'],['matrix','Matriks Likert','▦'],['linear','Skala linear','↔'],
 ['nps','Net Promoter Score','➓'],['rating','Rating bintang','★'],['ranking','Peringkat','⇅'],['number','Angka','#'],
 ['email','Email','@'],['phone','Telepon/WhatsApp','☎'],['date','Tanggal','▣'],['section','Bagian baru (halaman)','§']];
var TL={};TYPES.forEach(function(t){TL[t[0]]=t[1];});
var SCALES={
 setuju5:['Sangat tidak setuju','Tidak setuju','Netral','Setuju','Sangat setuju'],
 setuju4:['Sangat tidak setuju','Tidak setuju','Setuju','Sangat setuju'],
 puas5:['Sangat tidak puas','Tidak puas','Cukup','Puas','Sangat puas'],
 penting5:['Sangat tidak penting','Tidak penting','Cukup penting','Penting','Sangat penting'],
 sering5:['Tidak pernah','Jarang','Kadang-kadang','Sering','Selalu'],
 baik5:['Sangat buruk','Buruk','Cukup','Baik','Sangat baik']};
var SCALE_N={setuju5:'Setuju (5)',setuju4:'Setuju (4, tanpa netral)',puas5:'Kepuasan (5)',penting5:'Kepentingan (5)',sering5:'Frekuensi (5)',baik5:'Kualitas (5)'};
var CHOICE=['single','multi','dropdown','ranking'];

function h(t,c,x){var e=document.createElement(t);if(c)e.className=c;if(x!=null)e.textContent=x;return e;}
function $(id){return document.getElementById(id);}
function toast(m){var t=h('div','toast',m);document.body.appendChild(t);setTimeout(function(){t.remove();},2600);}
function nid(){var id;do{id='q'+Math.random().toString(36).slice(2,8);}while(DEF.items.some(function(x){return x.id===id;}));return id;}
document.querySelectorAll('dialog [data-close]').forEach(function(b){b.onclick=function(){b.closest('dialog').close();};});

DEF.opt=DEF.opt||{progress:true,once:false,closes:'',max:0};
var focusIdx=-1;

/* ---------- penyimpanan otomatis ---------- */
var timer=null,saving=false,again=false;
function dirty(){$('saved').textContent='Belum tersimpan…';clearTimeout(timer);timer=setTimeout(save,700);}
function save(){if(saving){again=true;return;}saving=true;$('saved').textContent='Menyimpan…';
  fetch('api.php?a=simpan',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:JSON.stringify({id:SID,def:DEF})})
   .then(function(r){return r.json();}).then(function(j){if(!j.ok)throw new Error(j.error);$('saved').textContent='Tersimpan';SV.title=j.def.title;document.title='Sunting: '+j.def.title+' · Zakademia';})
   .catch(function(e){$('saved').textContent='Gagal menyimpan: '+(e.message||'periksa koneksi');})
   .then(function(){saving=false;if(again){again=false;save();}});}
window.addEventListener('beforeunload',function(e){if($('saved').textContent!=='Tersimpan'){e.preventDefault();e.returnValue='';}});

/* ---------- kepala & pengaturan ---------- */
$('svTitle').value=DEF.title||'';$('svDesc').value=DEF.desc||'';
$('svTitle').oninput=function(){DEF.title=this.value;dirty();};$('svDesc').oninput=function(){DEF.desc=this.value;dirty();};
$('oProg').checked=!!DEF.opt.progress;$('oOnce').checked=!!DEF.opt.once;$('oCloses').value=DEF.opt.closes||'';$('oMax').value=DEF.opt.max||'';$('oThanks').value=DEF.thanks||'';
$('oProg').onchange=function(){DEF.opt.progress=this.checked;dirty();};$('oOnce').onchange=function(){DEF.opt.once=this.checked;dirty();};
$('oCloses').onchange=function(){DEF.opt.closes=this.value;dirty();};$('oMax').oninput=function(){DEF.opt.max=parseInt(this.value,10)||0;dirty();};
$('oThanks').oninput=function(){DEF.thanks=this.value;dirty();};
var ST={draf:'Draf',aktif:'Aktif',tutup:'Ditutup'};
function pill(){var p=$('stPill');p.className='pill '+SV.status;p.textContent=ST[SV.status];}pill();
$('btnShare').onclick=function(){ZKShare.open(SV,function(s){SV=s;pill();});};

/* ---------- palet tipe ---------- */
function palette(box,after){TYPES.forEach(function(t){var b=h('button');b.type='button';var i=h('i',null,t[2]);i.setAttribute('aria-hidden','true');b.appendChild(i);b.appendChild(document.createTextNode(t[1]));b.onclick=function(){add(t[0]);if(after)after();};box.appendChild(b);});}
palette($('pal'));palette($('pal2'),function(){$('dlgPal').close();});
$('addEnd').onclick=function(){focusIdx=DEF.items.length-1;if(window.innerWidth<980)$('dlgPal').showModal();else add('single');};

function blank(t){var q={id:nid(),t:t,title:'',help:''};if(t!=='section')q.req=false;
  if(CHOICE.indexOf(t)>=0)q.opts=['Pilihan 1','Pilihan 2'];
  if(t==='likert')q.opts=SCALES.setuju5.slice();
  if(t==='matrix'){q.opts=SCALES.setuju5.slice();q.rows=['Pernyataan 1','Pernyataan 2'];}
  if(t==='linear'){q.min=1;q.max=5;q.lo='';q.hi='';}
  if(t==='rating')q.max=5;
  if(t==='number'){q.min=null;q.max=null;}
  if(t==='nps')q.title='Seberapa mungkin Anda merekomendasikan kami kepada keluarga atau teman?';
  return q;}
function add(t){var at=focusIdx>=0?focusIdx+1:DEF.items.length;DEF.items.splice(at,0,blank(t));focusIdx=at;draw();dirty();
  var c=$('qs').children[at];if(c){c.scrollIntoView({behavior:'smooth',block:'center'});var i=c.querySelector('input.tt');if(i)setTimeout(function(){i.focus();},250);}}

/* ---------- ubah tipe dengan mempertahankan isi ---------- */
function retype(q,t){var n=blank(t);n.id=q.id;n.title=q.title;n.help=q.help;if('req' in n)n.req=!!q.req;if(q.cond)n.cond=q.cond;
  if(q.opts&&CHOICE.indexOf(q.t)>=0&&CHOICE.indexOf(t)>=0)n.opts=q.opts.slice();
  if((q.t==='likert'||q.t==='matrix')&&(t==='likert'||t==='matrix'))n.opts=q.opts.slice();
  if(q.rows&&t==='matrix')n.rows=q.rows.slice();
  if(t==='nps'&&q.title)n.title=q.title;return n;}

/* ---------- daftar soal ---------- */
function choiceSources(idx){var out=[];for(var i=0;i<idx;i++){var q=DEF.items[i];if(q.t==='single'||q.t==='dropdown'||q.t==='multi')out.push(q);}return out;}
function draw(){var box=$('qs');box.textContent='';var num=0;
  DEF.items.forEach(function(q,idx){box.appendChild(card(q,idx,q.t==='section'?'§':String(++num)));});
  if(!DEF.items.length)box.appendChild(h('p','mute','Belum ada pertanyaan. Pilih jenis pertanyaan di panel Tambah pertanyaan.'));}

function card(q,idx,label){
  var c=h('div','card qc'+(q.t==='section'?' sec':'')+(idx===focusIdx?' focus':''));
  c.addEventListener('focusin',function(){if(focusIdx!==idx){focusIdx=idx;[].forEach.call($('qs').children,function(x,k){x.classList.toggle('focus',k===idx);});}});
  var top=h('div','row');var nm=h('span','num',label);top.appendChild(nm);
  var ts=h('select','f tsel');ts.setAttribute('aria-label','Jenis pertanyaan');TYPES.forEach(function(t){var o=h('option',null,t[1]);o.value=t[0];ts.appendChild(o);});ts.value=q.t;
  ts.onchange=function(){DEF.items[idx]=retype(q,ts.value);draw();dirty();};top.appendChild(ts);
  var tools=h('div','tools');
  function tb(sym,lab,fn,dis){var b=h('button','icon-btn',sym);b.type='button';b.setAttribute('aria-label',lab);b.title=lab;b.disabled=!!dis;b.onclick=fn;tools.appendChild(b);}
  tb('↑','Pindah ke atas',function(){move(idx,-1);},idx===0);
  tb('↓','Pindah ke bawah',function(){move(idx,1);},idx===DEF.items.length-1);
  tb('⧉','Gandakan',function(){var cp=JSON.parse(JSON.stringify(q));cp.id=nid();DEF.items.splice(idx+1,0,cp);focusIdx=idx+1;draw();dirty();});
  tb('✕','Hapus',function(){var used=DEF.items.some(function(x){return x.cond&&x.cond.q===q.id;});if(used&&!confirm('Pertanyaan ini dipakai sebagai syarat tampil pertanyaan lain. Syarat itu ikut dihapus. Lanjutkan?'))return;
    DEF.items.splice(idx,1);DEF.items.forEach(function(x){if(x.cond&&x.cond.q===q.id)delete x.cond;});focusIdx=Math.min(idx,DEF.items.length-1);draw();dirty();});
  top.appendChild(tools);c.appendChild(top);

  var tt=h('input','f tt');tt.value=q.title||'';tt.placeholder=q.t==='section'?'Judul bagian':'Tulis pertanyaan';tt.setAttribute('aria-label',q.t==='section'?'Judul bagian':'Teks pertanyaan');tt.maxLength=500;
  tt.oninput=function(){q.title=tt.value;dirty();};c.appendChild(tt);
  var hp=h('input','f hp');hp.value=q.help||'';hp.placeholder=q.t==='section'?'Keterangan bagian (opsional)':'Keterangan atau petunjuk (opsional)';hp.setAttribute('aria-label','Keterangan');hp.maxLength=1000;
  hp.oninput=function(){q.help=hp.value;dirty();};c.appendChild(hp);

  var t=q.t;
  if(CHOICE.indexOf(t)>=0)c.appendChild(listEditor(q,'opts',t==='single'?'r':t==='multi'?'c':'n','Pilihan',60));
  if(t==='likert'||t==='matrix'){
    if(t==='matrix'){c.appendChild(h('b','small','Pernyataan (baris)'));c.appendChild(listEditor(q,'rows','n','Pernyataan',40));}
    var sw=h('div','opts-set');sw.appendChild(h('span',null,'Skala:'));var ss=h('select','f');ss.setAttribute('aria-label','Pilih skala siap pakai');ss.appendChild(h('option',null,'Pilih skala siap pakai…')).value='';
    Object.keys(SCALES).forEach(function(k){var o=h('option',null,SCALE_N[k]);o.value=k;ss.appendChild(o);});
    ss.onchange=function(){if(!ss.value)return;q.opts=SCALES[ss.value].slice();draw();dirty();};sw.appendChild(ss);c.appendChild(sw);
    c.appendChild(h('b','small','Label skala (kolom), dari skor terendah'));c.appendChild(listEditor(q,'opts','n','Label',11));
  }
  if(t==='linear'){var r=h('div','opts-set');r.appendChild(h('span',null,'Dari'));var mn=h('select','f');mn.setAttribute('aria-label','Nilai terendah');[0,1].forEach(function(v){var o=h('option',null,String(v));o.value=v;mn.appendChild(o);});mn.value=q.min;mn.onchange=function(){q.min=+mn.value;dirty();};r.appendChild(mn);
    r.appendChild(h('span',null,'sampai'));var mx=h('select','f');mx.setAttribute('aria-label','Nilai tertinggi');for(var v=2;v<=10;v++){var o=h('option',null,String(v));o.value=v;mx.appendChild(o);}mx.value=q.max;mx.onchange=function(){q.max=+mx.value;dirty();};r.appendChild(mx);c.appendChild(r);
    var l=h('div','row');['lo','hi'].forEach(function(k){var i=h('input','f');i.placeholder=k==='lo'?'Label nilai terendah, mis. Sangat buruk':'Label nilai tertinggi, mis. Sangat baik';i.setAttribute('aria-label',i.placeholder);i.value=q[k]||'';i.maxLength=60;i.style.flex='1 1 200px';i.oninput=function(){q[k]=i.value;dirty();};l.appendChild(i);});c.appendChild(l);}
  if(t==='rating'){var r2=h('div','opts-set');r2.appendChild(h('span',null,'Jumlah bintang'));var rs=h('select','f');rs.setAttribute('aria-label','Jumlah bintang');for(var k=3;k<=10;k++){var o2=h('option',null,String(k));o2.value=k;rs.appendChild(o2);}rs.value=q.max;rs.onchange=function(){q.max=+rs.value;dirty();};r2.appendChild(rs);c.appendChild(r2);}
  if(t==='nps')c.appendChild(h('p','mute small','Skala 0–10. Skor NPS = % promotor (9–10) dikurangi % kritikus (0–6), dihitung otomatis di halaman hasil.'));
  if(t==='number'){var r3=h('div','opts-set');['min','max'].forEach(function(k){r3.appendChild(h('span',null,k==='min'?'Minimal':'Maksimal'));var i=h('input','f');i.type='number';i.setAttribute('aria-label',k==='min'?'Minimal':'Maksimal');i.style.width='110px';i.value=q[k]==null?'':q[k];i.oninput=function(){q[k]=i.value===''?null:Number(i.value);dirty();};r3.appendChild(i);});c.appendChild(r3);}
  if(t==='short'||t==='long'){var ph=h('input','f');ph.placeholder='Teks contoh di kolom jawaban (opsional)';ph.setAttribute('aria-label','Teks contoh');ph.value=q.ph||'';ph.maxLength=120;ph.oninput=function(){q.ph=ph.value;dirty();};c.appendChild(ph);}

  if(t!=='section'){
    var set=h('div','opts-set');
    function chk(label,key,title){var lb=h('label');var i=h('input');i.type='checkbox';i.checked=!!q[key];i.onchange=function(){q[key]=i.checked;dirty();};lb.appendChild(i);lb.appendChild(document.createTextNode(label));if(title)lb.title=title;set.appendChild(lb);}
    if(t==='single'||t==='multi'||t==='dropdown'){chk('Opsi "Lainnya"','other');chk('Acak urutan pilihan','shuffle','Mengurangi bias urutan jawaban');}
    if(t==='multi'){var ml=h('label');ml.appendChild(document.createTextNode('Maks. pilihan'));var mi=h('input','f');mi.type='number';mi.min=0;mi.style.width='70px';mi.value=q.maxSel||'';mi.placeholder='–';mi.oninput=function(){q.maxSel=parseInt(mi.value,10)||0;dirty();};ml.appendChild(mi);set.appendChild(ml);}
    // logika tampil
    var src=choiceSources(idx);
    if(src.length){var lg=h('label');lg.appendChild(document.createTextNode('Tampilkan jika'));
      var qs=h('select','f');qs.setAttribute('aria-label','Pertanyaan syarat');qs.appendChild(h('option',null,'selalu')).value='';
      src.forEach(function(s){var o=h('option',null,(s.title||'(tanpa judul)').slice(0,40));o.value=s.id;qs.appendChild(o);});
      var vs=h('select','f');vs.setAttribute('aria-label','Jawaban syarat');
      function fillV(){vs.textContent='';var s=src.filter(function(x){return x.id===qs.value;})[0];vs.hidden=!s;if(!s)return;s.opts.forEach(function(op){var o=h('option',null,'= '+op);o.value=op;vs.appendChild(o);});}
      qs.value=q.cond?q.cond.q:'';fillV();if(q.cond)vs.value=q.cond.v;
      qs.onchange=function(){fillV();if(qs.value){q.cond={q:qs.value,v:vs.value};}else delete q.cond;dirty();};
      vs.onchange=function(){if(qs.value){q.cond={q:qs.value,v:vs.value};dirty();}};
      lg.appendChild(qs);lg.appendChild(vs);set.appendChild(lg);}
    var rq=h('label','tswitch');var ri=h('input');ri.type='checkbox';ri.checked=!!q.req;ri.onchange=function(){q.req=ri.checked;dirty();};rq.appendChild(ri);rq.appendChild(document.createTextNode('Wajib diisi'));set.appendChild(rq);
    c.appendChild(set);
  }
  return c;
}
function move(i,d){var a=DEF.items,t=a[i];a[i]=a[i+d];a[i+d]=t;focusIdx=i+d;
  // syarat harus merujuk soal sebelumnya
  a.forEach(function(x,k){if(x.cond){var j=a.findIndex(function(y){return y.id===x.cond.q;});if(j<0||j>=k)delete x.cond;}});
  draw();dirty();var c=$('qs').children[i+d];if(c)c.scrollIntoView({block:'nearest'});}

var bulkTarget=null;
$('bulkOk').onclick=function(){var lines=$('bulkTa').value.split(/\r?\n/).map(function(s){return s.trim();}).filter(Boolean);if(!lines.length)return;
  bulkTarget.q[bulkTarget.k]=lines.filter(function(x,i){return lines.indexOf(x)===i;}).slice(0,bulkTarget.max);$('dlgBulk').close();draw();dirty();};

function listEditor(q,key,mark,word,max){
  var box=h('div','ol');
  q[key].forEach(function(v,i){var r=h('div','o');if(mark!=='n'){r.appendChild(h('span','mk '+mark));}else{var n=h('span','mute small',String(i+1)+'.');n.style.minWidth='20px';r.appendChild(n);}
    var inp=h('input','f');inp.value=v;inp.maxLength=key==='rows'?300:200;inp.setAttribute('aria-label',word+' '+(i+1));
    inp.oninput=function(){var old=q[key][i];q[key][i]=inp.value;
      DEF.items.forEach(function(x){if(x.cond&&x.cond.q===q.id&&x.cond.v===old)x.cond.v=inp.value;});dirty();};
    inp.onkeydown=function(e){if(e.key==='Enter'){e.preventDefault();if(q[key].length>=max)return;q[key].splice(i+1,0,'');draw();dirty();var nx=findCard(q).querySelectorAll('.ol')[key==='opts'&&q.t==='matrix'?1:0].querySelectorAll('input')[i+1];if(nx)nx.focus();}};
    r.appendChild(inp);
    var up=h('button','icon-btn','↑');up.type='button';up.setAttribute('aria-label','Naikkan '+word.toLowerCase());up.disabled=i===0;up.onclick=function(){var a=q[key];var t=a[i];a[i]=a[i-1];a[i-1]=t;draw();dirty();};r.appendChild(up);
    var del=h('button','icon-btn','✕');del.type='button';del.setAttribute('aria-label','Hapus '+word.toLowerCase());del.disabled=q[key].length<=1;del.onclick=function(){q[key].splice(i,1);draw();dirty();};r.appendChild(del);
    box.appendChild(r);});
  var acts=h('div','row');
  if(q[key].length<max){var ad=h('button','linkbtn','＋ Tambah '+word.toLowerCase());ad.type='button';ad.onclick=function(){q[key].push(word+' '+(q[key].length+1));draw();dirty();var ins=findCard(q).querySelectorAll('.ol');ins=ins[key==='opts'&&q.t==='matrix'?1:0].querySelectorAll('input');ins[ins.length-1].select();};acts.appendChild(ad);}
  var bk=h('button','linkbtn','Tempel banyak sekaligus');bk.type='button';bk.style.marginLeft='14px';bk.onclick=function(){bulkTarget={q:q,k:key,max:max};$('bulkTa').value=q[key].join('\n');$('dlgBulk').showModal();};acts.appendChild(bk);
  box.appendChild(acts);return box;}
function findCard(q){return $('qs').children[DEF.items.indexOf(q)];}

/* ---------- pratinjau ---------- */
$('btnPreview').onclick=function(){var b=$('previewBody');b.textContent='';ZKSurvey.render(b,JSON.parse(JSON.stringify(DEF)),{preview:true,scroller:b});$('dlgPreview').showModal();};

draw();
})();
