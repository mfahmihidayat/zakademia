/* Zakademia — Kesehatan Penghimpunan (perhitungan di browser, dari angka rekap) */
(function(){
'use strict';
function $(id){return document.getElementById(id);}
function h(t,c,x){var e=document.createElement(t);if(c)e.className=c;if(x!=null)e.textContent=x;return e;}
var F=document.getElementById('f'),inputs=[].slice.call(F.querySelectorAll('input[data-k]')),cur=null;
var nf=new Intl.NumberFormat('id-ID'),nf1=new Intl.NumberFormat('id-ID',{maximumFractionDigits:1}),nf2=new Intl.NumberFormat('id-ID',{maximumFractionDigits:2});

/* ---------- format angka ---------- */
function parse(s){s=String(s||'').replace(/[^\d]/g,'');return s===''?null:Number(s);}
function rp(x){if(x==null||!isFinite(x))return '–';var a=Math.abs(x),s=x<0?'−':'';
  if(a>=1e12)return s+'Rp'+nf2.format(a/1e12)+' T';if(a>=1e9)return s+'Rp'+nf2.format(a/1e9)+' M';if(a>=1e6)return s+'Rp'+nf1.format(a/1e6)+' jt';if(a>=1e4)return s+'Rp'+nf1.format(a/1e3)+' rb';return s+'Rp'+nf.format(Math.round(a));}
function pc(x,d){return x==null||!isFinite(x)?'–':nf1.format(x*100)+'%';}
inputs.forEach(function(i){i.addEventListener('input',function(){var pos=i.value.length-i.selectionStart;var v=parse(i.value);i.value=v==null?'':nf.format(v);var p=Math.max(0,i.value.length-pos);try{i.setSelectionRange(p,p);}catch(_){}compute();dirty();});});

/* ---------- tahun ---------- */
var Y=new Date().getFullYear();for(var y=Y;y>=Y-8;y--){var o=h('option',null,String(y));o.value=y;$('tahun').appendChild(o);}
$('tahun').value=Y-1;
function years(){var t=+$('tahun').value;$('tahunL').value=String(t-1);document.querySelectorAll('.pair span').forEach(function(s){if(/^Tahun lalu|^\d{4}$/.test(s.textContent)&&s.parentNode.querySelector('input[data-k$="L"]'))s.textContent=String(t-1);if(/^Tahun ini|^\d{4}$/.test(s.textContent)&&s.parentNode.querySelector('input[data-k$="N"]'))s.textContent=String(t);});}
$('tahun').onchange=function(){years();compute();dirty();};years();

function data(){var d={};inputs.forEach(function(i){d[i.dataset.k]=parse(i.value);});return d;}
function load(d){inputs.forEach(function(i){var v=d[i.dataset.k];i.value=v==null?'':nf.format(v);});}

/* ---------- penilaian ---------- */
function M(title,val,level,stTxt,note){return {t:title,v:val,l:level,s:stTxt,n:note};}
function lvl(x,good,mid,higherBetter){if(x==null||!isFinite(x))return 'na';if(higherBetter)return x>=good?'ok':x>=mid?'warn':'bad';return x<=good?'ok':x<=mid?'warn':'bad';}
var ST={ok:'Sehat',warn:'Perlu perhatian',bad:'Perlu perbaikan',na:'Belum dapat dihitung'};

function calc(d){
  var r={},ok=function(){for(var i=0;i<arguments.length;i++)if(d[arguments[i]]==null)return false;return true;};
  r.retensi=ok('tahan','donL')&&d.donL>0?d.tahan/d.donL:null;
  r.hilang=ok('tahan','donL')?Math.max(0,d.donL-d.tahan):null;
  r.kembali=ok('donN','tahan','baru')?d.donN-d.tahan-d.baru:null;
  r.gDana=ok('danaN','danaL')&&d.danaL>0?d.danaN/d.danaL-1:null;
  r.gDon=ok('donN','donL')&&d.donL>0?d.donN/d.donL-1:null;
  r.avg=ok('danaN','trxN')&&d.trxN>0?d.danaN/d.trxN:null;
  r.avgL=ok('danaL','trxL')&&d.trxL>0?d.danaL/d.trxL:null;
  r.frek=ok('trxN','donN')&&d.donN>0?d.trxN/d.donN:null;
  r.vpd=ok('danaN','donN')&&d.donN>0?d.danaN/d.donN:null;
  r.vpdL=ok('danaL','donL')&&d.donL>0?d.danaL/d.donL:null;
  if(r.retensi!=null){var R=Math.min(r.retensi,0.9);r.umur=1/(1-R);r.umurCap=r.retensi>0.9;}
  r.clv=r.vpd!=null&&r.umur?r.vpd*r.umur:null;
  r.cac=ok('akuisisi','baru')&&d.baru>0?d.akuisisi/d.baru:null;
  r.ratio=r.clv!=null&&r.cac?r.clv/r.cac:null;
  r.payback=r.cac!=null&&r.vpd?r.cac/(r.vpd/12):null;
  r.cpr=ok('biayaN','danaN')&&d.danaN>0?d.biayaN/d.danaN:null;
  r.cprL=ok('biayaL','danaL')&&d.danaL>0?d.biayaL/d.danaL:null;
  r.konsen=ok('top10','danaN')&&d.danaN>0?d.top10/d.danaN:null;
  return r;}

function warnings(d){var w=[];
  if(d.tahan!=null&&d.donL!=null&&d.tahan>d.donL)w.push('Donatur yang memberi lagi lebih banyak daripada jumlah donatur tahun lalu. Periksa kembali kedua angka itu.');
  if(d.donN!=null&&d.tahan!=null&&d.baru!=null&&d.tahan+d.baru>d.donN)w.push('Donatur yang memberi lagi ditambah donatur baru melebihi jumlah donatur tahun ini. Periksa kembali angkanya.');
  if(d.top10!=null&&d.danaN!=null&&d.top10>d.danaN)w.push('Dana dari 10 donatur terbesar melebihi total dana tahun ini.');
  if(d.akuisisi!=null&&d.biayaN!=null&&d.akuisisi>d.biayaN)w.push('Biaya untuk donatur baru melebihi biaya penghimpunan total.');
  if(d.trxN!=null&&d.donN!=null&&d.trxN<d.donN)w.push('Jumlah transaksi lebih kecil daripada jumlah donatur; setiap donatur minimal satu kali transaksi.');
  return w;}

/* ---------- tampilan hasil ---------- */
function compute(){
  var d=data(),r=calc(d),res=$('res');res.textContent='';var tN=$('tahun').value,tL=$('tahunL').value;
  var filled=inputs.filter(function(i){return i.value!=='';}).length;
  if(filled<3){var e=h('div','card sec');e.appendChild(h('h2',null,'Hasil akan muncul di sini'));e.appendChild(h('p','mute','Mulai dari total dana, jumlah donatur, dan donatur yang memberi lagi untuk kedua tahun. Setiap angka yang diisi menambah hasil yang dapat dihitung.'));
    var ul=h('ul','reco mute');['Retensi donatur','Donatur diperoleh dan hilang','Nilai per donatur dan frekuensi','CLV, CAC, dan rasio CLV:CAC','Biaya per rupiah terhimpun','Konsentrasi pada donatur besar','Simulasi dampak perbaikan'].forEach(function(x){ul.appendChild(h('li',null,x));});e.appendChild(ul);res.appendChild(e);return;}
  var w=warnings(d);if(w.length){var wb=h('div','warnbox');w.forEach(function(x){wb.appendChild(h('div',null,'⚠ '+x));});res.appendChild(wb);}
  var hd=h('div');hd.appendChild(h('h2',null,'Hasil '+ORG+' · '+tN+' dibanding '+tL));res.appendChild(hd);

  var ms=[];
  ms.push(M('Retensi donatur',pc(r.retensi),lvl(r.retensi,.6,.4,true),null,r.retensi==null?'Isi jumlah donatur tahun lalu dan donatur yang memberi lagi.':nf.format(d.tahan)+' dari '+nf.format(d.donL)+' donatur '+tL+' memberi lagi. Acuan internasional sekitar 40–45%; di atas 60% tergolong kuat.'));
  ms.push(M('Pertumbuhan dana',r.gDana==null?'–':(r.gDana>=0?'+':'')+pc(r.gDana),r.gDana==null?'na':r.gDana>=.1?'ok':r.gDana>=0?'warn':'bad',null,r.gDana==null?'Isi total dana kedua tahun.':rp(d.danaL)+' → '+rp(d.danaN)+(r.gDon!=null?'. Jumlah donatur '+(r.gDon>=0?'naik ':'turun ')+pc(Math.abs(r.gDon))+'.':'.')));
  ms.push(M('Nilai per donatur',rp(r.vpd),r.vpd==null||r.vpdL==null?'na':r.vpd>=r.vpdL?'ok':'warn',r.vpd!=null&&r.vpdL==null?'Tahun ini':null,r.vpd==null?'Isi total dana dan jumlah donatur.':'Rata-rata satu donatur memberi '+rp(r.vpd)+' per tahun'+(r.vpdL!=null?' (tahun lalu '+rp(r.vpdL)+')':'')+(r.avg!=null?'; sekali donasi rata-rata '+rp(r.avg):'')+(r.frek!=null?', '+nf1.format(r.frek)+' kali setahun.':'.')));
  ms.push(M('CLV (nilai donatur)',rp(r.clv),r.ratio==null?'na':lvl(r.ratio,3,1,true),r.clv!=null&&r.ratio==null?'Dinilai bersama CAC':null,r.clv==null?'Perlu nilai per donatur dan retensi.':'Perkiraan total donasi satu donatur selama bertahan, sekitar '+nf1.format(r.umur)+' tahun'+(r.umurCap?' (dibatasi 10 tahun)':'')+'.'));
  ms.push(M('CAC (biaya per donatur baru)',rp(r.cac),r.cac==null||r.vpd==null?'na':(r.cac<=r.vpd*.5?'ok':r.cac<=r.vpd?'warn':'bad'),r.cac!=null&&r.vpd==null?'Dinilai bersama nilai donatur':null,r.cac==null?'Isi biaya untuk donatur baru dan jumlah donatur baru.':'Biaya rata-rata untuk mendapatkan satu donatur baru.'+(r.payback!=null?' Biaya ini kembali dalam sekitar '+nf1.format(r.payback)+' bulan donasi.':'')));
  ms.push(M('Rasio CLV : CAC',r.ratio==null?'–':nf1.format(r.ratio)+' : 1',lvl(r.ratio,3,1,true),null,r.ratio==null?'Perlu CLV dan CAC.':r.ratio>=3?'Setiap rupiah untuk menjaring donatur baru kembali '+nf1.format(r.ratio)+' kali lipat. Patokan sehat: 3 : 1 atau lebih.':r.ratio>=1?'Akuisisi masih menghasilkan, tetapi di bawah patokan sehat 3 : 1.':'Nilai donatur baru belum menutup biaya untuk mendapatkannya.'));
  ms.push(M('Biaya per Rp100 terhimpun',r.cpr==null?'–':'Rp'+nf1.format(r.cpr*100),lvl(r.cpr,.125,.2,false),null,r.cpr==null?'Isi biaya penghimpunan dan total dana.':'Batas hak amil zakat 1/8 (12,5%) dipakai sebagai acuan.'+(r.cprL!=null?' Tahun lalu Rp'+nf1.format(r.cprL*100)+'.':'')));
  ms.push(M('Ketergantungan donatur besar',pc(r.konsen),lvl(r.konsen,.25,.5,false),null,r.konsen==null?'Opsional: isi dana dari 10 donatur terbesar.':'Porsi dana dari 10 donatur terbesar. Di atas 50% berisiko bila satu-dua donatur berhenti.'));
  var kp=h('div','kp');ms.forEach(function(m){var c=h('div','card m '+m.l);c.appendChild(h('span','t',m.t));c.appendChild(h('span','v',m.v));c.appendChild(h('span','st',m.s||ST[m.l]));c.appendChild(h('p',null,m.n));kp.appendChild(c);});res.appendChild(kp);

  /* arus donatur */
  if(d.donL!=null&&d.tahan!=null){var s=h('div','card sec');s.appendChild(h('h2',null,'Donatur diperoleh dan hilang'));
    var max=Math.max(d.donL,d.donN||0),fl=h('div','flow');
    function row(lab,parts){var r0=h('div','r');r0.appendChild(h('span',null,lab));var b=h('div','b');b.style.width=(parts.reduce(function(a,p){return a+Math.max(0,p[0]);},0)/max*100)+'%';
      parts.forEach(function(p){if(p[0]<=0)return;var i=h('i',p[1],nf.format(p[0]));i.style.width=(p[0]/parts.reduce(function(a,q){return a+Math.max(0,q[0]);},0)*100)+'%';i.title=p[2]+': '+nf.format(p[0]);b.appendChild(i);});r0.appendChild(b);fl.appendChild(r0);}
    row(tL,[[d.tahan,'c-tahan','Memberi lagi'],[r.hilang,'c-hilang','Berhenti']]);
    if(d.donN!=null)row(tN,[[d.tahan,'c-tahan','Memberi lagi'],[r.kembali!=null&&r.kembali>0?r.kembali:0,'c-kembali','Kembali setelah absen'],[d.baru||0,'c-baru','Baru']]);
    s.appendChild(fl);var lg=h('div','legend');[['c-tahan','Memberi lagi'],['c-hilang','Berhenti'],['c-baru','Baru'],['c-kembali','Kembali setelah absen']].forEach(function(x){var sp=h('span');var em=h('em',x[0]);sp.appendChild(em);sp.appendChild(document.createTextNode(x[1]));lg.appendChild(sp);});s.appendChild(lg);
    if(d.baru!=null){var net=d.baru+(r.kembali>0?r.kembali:0)-r.hilang;
      s.appendChild(h('p',null,'Tahun '+tN+' lembaga memperoleh '+nf.format(d.baru)+' donatur baru'+(r.kembali>0?' dan '+nf.format(r.kembali)+' donatur lama yang kembali':'')+', sementara '+nf.format(r.hilang)+' donatur tahun '+tL+' berhenti. '+
        (net>=0?'Hasil bersihnya bertambah '+nf.format(net)+' donatur.':'Hasil bersihnya berkurang '+nf.format(-net)+' donatur.')+
        (d.baru>0&&r.hilang>0?' Setiap 10 donatur baru diimbangi '+nf1.format(r.hilang/d.baru*10)+' donatur yang berhenti.':'')));}
    res.appendChild(s);}

  /* simulasi */
  if(r.retensi!=null&&r.vpd!=null&&d.donN!=null&&d.baru!=null){var sm=h('div','card sec');sm.appendChild(h('h2',null,'Simulasi: bagaimana jika'));
    sm.appendChild(h('p','mute small','Perkiraan dana tahun depan bila jumlah donatur baru dan nilai per donatur sama dengan tahun ini, lalu satu faktor diperbaiki.'));
    var S={ret:10,cac:0,vpd:0},out=h('div');
    function slider(key,label,max,unit,dis){var r0=h('div','sl');var l=h('label',null,label);var id='s_'+key;l.htmlFor=id;var i=h('input');i.type='range';i.min=0;i.max=max;i.step=1;i.value=S[key];i.id=id;i.disabled=!!dis;var b=h('b',null,'+'+S[key]+unit);
      i.oninput=function(){S[key]=+i.value;b.textContent=(key==='cac'?'−':'+')+i.value+unit;upd();};r0.appendChild(l);r0.appendChild(i);r0.appendChild(b);sm.appendChild(r0);if(key==='cac')b.textContent='−'+S[key]+unit;}
    slider('ret','Retensi naik',30,' poin');slider('cac','Biaya per donatur baru turun',50,'%',r.cac==null);slider('vpd','Nilai per donatur naik',30,'%');
    sm.appendChild(out);
    function upd(){var R0=r.retensi,R1=Math.min(R0+S.ret/100,.95),newB=d.baru,newS=r.cac!=null&&S.cac?d.baru/(1-S.cac/100):d.baru;
      var base=(d.donN*R0+newB)*r.vpd,scn=(d.donN*R1+newS)*r.vpd*(1+S.vpd/100),gain=scn-base;
      var clv1=r.vpd*(1+S.vpd/100)/(1-Math.min(R1,.9)),cac1=r.cac!=null?r.cac*(1-S.cac/100):null;
      out.textContent='';var row=h('div');row.style.cssText='display:flex;gap:10px 28px;flex-wrap:wrap;align-items:baseline;border-top:1px solid var(--line);padding-top:12px';
      function kv(v,l){var x=h('div');x.appendChild(h('div','big',v));x.appendChild(h('div','mute small',l));row.appendChild(x);}
      kv((gain>=0?'+':'')+rp(gain),'tambahan dana per tahun dibanding kondisi sekarang');kv(rp(clv1),'CLV setelah perbaikan');if(cac1!=null)kv(nf1.format(clv1/cac1)+' : 1','rasio CLV : CAC');out.appendChild(row);
      out.appendChild(h('p','mute small','Perkiraan kasar dari angka rekap, bukan proyeksi keuangan. Untuk proyeksi per segmen donatur, gunakan konsultasi analitik.'));}
    upd();res.appendChild(sm);}

  /* rekomendasi */
  var rec=[];
  if(r.retensi!=null&&r.retensi<.6)rec.push('Prioritaskan retensi: ucapan terima kasih dan bukti setor dalam 48 jam, laporan dampak berkala, program donasi rutin, serta sapaan terencana bagi donatur yang tahun ini belum memberi lagi.');
  if(r.hilang!=null&&d.baru!=null&&r.hilang>d.baru)rec.push('Donatur yang berhenti lebih banyak daripada donatur baru. Menambah belanja akuisisi tidak akan menumbuhkan basis donatur sebelum retensi diperbaiki.');
  if(r.ratio!=null&&r.ratio<3)rec.push('Rasio CLV : CAC di bawah 3 : 1. Hitung biaya per donatur baru untuk setiap kanal, kurangi kanal termahal, dan dorong donasi kedua dalam 90 hari pertama.');
  if(r.frek!=null&&r.frek<2)rec.push('Donatur rata-rata memberi kurang dari dua kali setahun. Tawarkan donasi rutin bulanan (autodebet, potong gaji, pengingat terjadwal).');
  if(r.cpr!=null&&r.cpr>.125)rec.push('Biaya penghimpunan di atas 12,5% dari dana terhimpun. Rinci biaya per kanal dan per jenis dana untuk memastikan hak amil tetap dalam batas.');
  if(r.konsen!=null&&r.konsen>.5)rec.push('Lebih dari separuh dana datang dari 10 donatur. Kelola donatur besar secara khusus, sambil memperluas basis donatur ritel.');
  if(r.gDana!=null&&r.gDana<0)rec.push('Dana turun dibanding tahun lalu. Bandingkan per kanal dan per jenis dana untuk menemukan sumber penurunannya.');
  var rc=h('div','card sec');rc.appendChild(h('h2',null,'Rekomendasi'));
  if(rec.length){var ol=h('ol','reco');rec.forEach(function(x){ol.appendChild(h('li',null,x));});rc.appendChild(ol);}else rc.appendChild(h('p','mute','Indikator yang dapat dihitung berada dalam kondisi sehat. Lengkapi isian lain untuk gambaran yang lebih utuh.'));
  var cta=h('div','noprint');cta.style.cssText='border-top:1px solid var(--line);padding-top:12px;display:flex;gap:12px;align-items:center;flex-wrap:wrap';
  var ct=h('p','mute small','Butuh analisis per donatur: segmentasi RFM, kohort, CLV per segmen, atau prediksi donatur berhenti? Analisis data rinci dikerjakan melalui konsultasi agar data donatur tetap aman.');ct.style.cssText='flex:1;min-width:220px;margin:0';cta.appendChild(ct);
  var a=h('a','btn sm','Konsultasikan');a.href='/#kontak';cta.appendChild(a);rc.appendChild(cta);res.appendChild(rc);
}

/* ---------- simpan & muat ---------- */
var isDirty=false;function dirty(){isDirty=true;$('saveMsg').textContent=cur?'Ada perubahan yang belum disimpan.':'';}
function chips(){var c=$('saved');c.textContent='';if(!SAVED.length)return;c.appendChild(h('span','mute small','Tersimpan:')).style.alignSelf='center';
  SAVED.forEach(function(s){var ch=h('span','chip'+(cur&&cur.id===s.id?' on':''));var b=h('button',null,s.label);b.type='button';b.onclick=function(){open(s);};ch.appendChild(b);
    var x=h('button','x','✕');x.type='button';x.setAttribute('aria-label','Hapus '+s.label);x.onclick=function(){if(!confirm('Hapus analisis "'+s.label+'"?'))return;post('hapus',{id:s.id}).then(function(){SAVED=SAVED.filter(function(z){return z!==s;});if(cur===s)reset();chips();});};ch.appendChild(x);c.appendChild(ch);});}
function open(s){cur=s;load(s.data);$('tahun').value=s.tahun;$('label').value=s.label;years();compute();chips();isDirty=false;$('saveMsg').textContent='';window.scrollTo({top:0,behavior:'smooth'});}
function reset(){cur=null;inputs.forEach(function(i){i.value='';});$('label').value='';$('tahun').value=Y-1;years();compute();chips();isDirty=false;$('saveMsg').textContent='';}
$('btnNew').onclick=function(){if(isDirty&&!confirm('Isian yang belum disimpan akan hilang. Lanjutkan?'))return;reset();};
function post(a,b){return fetch('api.php?a='+a,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:JSON.stringify(b)}).then(function(r){return r.json();}).then(function(j){if(!j.ok)throw new Error(j.error||'Gagal');return j;});}
F.onsubmit=function(e){e.preventDefault();var btn=$('btnSave');btn.disabled=true;$('saveMsg').textContent='Menyimpan…';
  var body={id:cur?cur.id:0,tahun:+$('tahun').value,label:$('label').value.trim()||('Tahun '+$('tahun').value),data:data()};
  post('simpan',body).then(function(j){var rec={id:j.id,label:j.label,tahun:body.tahun,data:body.data};if(cur){Object.assign(cur,rec);}else{SAVED.unshift(rec);cur=rec;}$('label').value=j.label;chips();isDirty=false;$('saveMsg').textContent='Tersimpan.';})
    .catch(function(x){$('saveMsg').textContent=x.message;}).then(function(){btn.disabled=false;});};
$('label').oninput=dirty;
window.addEventListener('beforeunload',function(e){if(isDirty){e.preventDefault();e.returnValue='';}});
if(SAVED.length)open(SAVED[0]);else{chips();compute();}
})();
