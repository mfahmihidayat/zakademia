/* Zakademia Survei — ringkasan hasil */
(function(){
'use strict';
function h(t,c,x){var e=document.createElement(t);if(c)e.className=c;if(x!=null)e.textContent=x;return e;}
function $(id){return document.getElementById(id);}
var nf=new Intl.NumberFormat('id-ID',{maximumFractionDigits:1}),nf2=new Intl.NumberFormat('id-ID',{maximumFractionDigits:2});
function pct(a,b){return b?nf.format(a/b*100)+'%':'0%';}
var SEQ=['#B4532A','#D9925B','#D8D2B8','#7FB98A','#0B4D33'];
function seq(n){if(n===5)return SEQ;var lo=[180,83,42],mid=[216,210,184],hi=[11,77,51],out=[];for(var i=0;i<n;i++){var t=n===1?1:i/(n-1),a,b,u;if(t<.5){a=lo;b=mid;u=t*2;}else{a=mid;b=hi;u=(t-.5)*2;}out.push('rgb('+a.map(function(x,k){return Math.round(x+(b[k]-x)*u);}).join(',')+')');}return out;}
var DEF,ROWS;
document.querySelectorAll('.tabs button').forEach(function(b){b.onclick=function(){document.querySelectorAll('.tabs button').forEach(function(x){x.setAttribute('aria-selected',String(x===b));});$('sum').hidden=b.dataset.v!=='sum';$('ind').hidden=b.dataset.v!=='ind';};});

fetch('api.php?a=hasil&id='+SID,{cache:'no-store'}).then(function(r){return r.json();}).then(function(j){if(!j.ok)throw new Error(j.error);DEF=j.def;ROWS=j.rows;draw();})
  .catch(function(e){$('sum').appendChild(h('p','err-box','Hasil belum dapat dimuat: '+e.message));});

function median(a){if(!a.length)return null;a=a.slice().sort(function(x,y){return x-y;});var m=Math.floor(a.length/2);return a.length%2?a[m]:(a[m-1]+a[m])/2;}
function mean(a){return a.length?a.reduce(function(s,x){return s+x;},0)/a.length:null;}
function dur(s){if(s==null)return '–';return s<60?s+' dtk':Math.round(s/60)+' mnt';}

function draw(){
  var n=ROWS.length,K=$('kpis');
  var last=n?new Date(ROWS[n-1].at):null,durs=ROWS.map(function(r){return r.dur;}).filter(function(x){return x!=null&&x>0;});
  var today=ROWS.filter(function(r){return new Date(r.at).toDateString()===new Date().toDateString();}).length;
  [[String(n),'jawaban masuk'],[String(today),'hari ini'],[dur(median(durs)),'median waktu isi'],[last?last.toLocaleDateString('id-ID',{day:'numeric',month:'short'})+' '+last.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}):'–','jawaban terakhir']]
   .forEach(function(k){var c=h('div','card kpi');c.appendChild(h('b',null,k[0]));c.appendChild(h('span',null,k[1]));K.appendChild(c);});
  var S=$('sum');
  if(!n){var e=h('div','card rq');e.appendChild(h('b',null,'Belum ada jawaban'));e.appendChild(h('p','mute','Aktifkan survei lalu bagikan tautannya. Ringkasan muncul otomatis begitu jawaban pertama masuk.'));S.appendChild(e);}
  var num=0;
  DEF.items.forEach(function(it){if(it.t==='section'){if(n){var s=h('h2',null,it.title||'Bagian');s.style.marginTop='10px';S.appendChild(s);}return;}num++;if(!n)return;
    var vals=ROWS.map(function(r){return r.a[it.id];}).filter(function(v){return v!==undefined&&v!==null&&v!=='';});
    var c=h('div','card rq');var t=h('div','qt',num+'. '+(it.title||'(tanpa judul)'));c.appendChild(t);
    c.appendChild(h('div','meta',vals.length+' dari '+n+' menjawab'+(it.cond?' · hanya ditampilkan bila syarat terpenuhi':'')));
    var f={single:choice,dropdown:choice,multi:choice,likert:likert,matrix:matrix,linear:scale,rating:scale,nps:nps,ranking:ranking,number:number}[it.t]||texts;
    if(vals.length)f(c,it,vals);else c.appendChild(h('p','mute small','Belum ada jawaban untuk pertanyaan ini.'));S.appendChild(c);});
  individual();
}
function bars(c,pairs,total){var b=h('div','bars');pairs.forEach(function(p){var r=h('div','br');r.appendChild(h('span','lb',p[0]));var tr=h('div','tr');var i=h('i');i.style.width=(total?p[1]/total*100:0)+'%';tr.appendChild(i);r.appendChild(tr);r.appendChild(h('span','v',p[1]+' · '+pct(p[1],total)));b.appendChild(r);});c.appendChild(b);}
function choice(c,it,vals){var cnt={},oth=[];it.opts.forEach(function(o){cnt[o]=0;});
  vals.forEach(function(v){(Array.isArray(v)?v:[v]).forEach(function(x){if(x in cnt)cnt[x]++;else oth.push(x);});});
  var pairs=it.opts.map(function(o){return [o,cnt[o]];});if(it.other||oth.length)pairs.push(['Lainnya',oth.length]);
  if(it.t!=='multi'||true)pairs.sort(function(a,b){return b[1]-a[1];});
  bars(c,pairs,vals.length);
  if(it.t==='multi')c.appendChild(h('div','meta','Persentase dihitung dari jumlah responden; satu responden dapat memilih lebih dari satu.'));
  if(oth.length){var d=h('details');d.appendChild(h('summary','meta','Lihat '+oth.length+' jawaban "Lainnya"'));var tx=h('div','texts');oth.forEach(function(x){tx.appendChild(h('p',null,x));});d.appendChild(tx);c.appendChild(d);}}
function stack(c,counts,labels,total){var col=seq(labels.length),s=h('div','stack');counts.forEach(function(k,i){if(!k)return;var x=h('i');x.style.width=(k/total*100)+'%';x.style.background=col[i];x.title=labels[i]+': '+k+' ('+pct(k,total)+')';s.appendChild(x);});c.appendChild(s);
  var lg=h('div','legend');labels.forEach(function(l,i){var sp=h('span');var em=h('em');em.style.background=col[i];sp.appendChild(em);sp.appendChild(document.createTextNode(l+' '+pct(counts[i],total)));lg.appendChild(sp);});c.appendChild(lg);}
function likert(c,it,vals){var k=it.opts.length,cnt=it.opts.map(function(){return 0;});vals.forEach(function(v){cnt[v-1]++;});
  var m=mean(vals),top=cnt.slice(Math.ceil(k/2)+ (k%2?0:0)).reduce(function(a,b){return a+b;},0);
  var row=h('div','row');row.style.cssText='display:flex;gap:24px;flex-wrap:wrap;align-items:baseline';
  var a=h('div');a.appendChild(h('span','big',nf2.format(m)));a.appendChild(h('span','mute small',' rata-rata dari '+k));row.appendChild(a);
  var tb=cnt.slice(k-Math.floor(k/2)).reduce(function(x,y){return x+y;},0);var b=h('div');b.appendChild(h('span','big',pct(tb,vals.length)));b.appendChild(h('span','mute small',' menjawab dua kategori teratas'));if(k>=4)row.appendChild(b);
  c.appendChild(row);stack(c,cnt,it.opts,vals.length);}
function matrix(c,it,vals){var k=it.opts.length,col=seq(k),box=h('div','bars');
  it.rows.forEach(function(rw,ri){var cnt=it.opts.map(function(){return 0;}),xs=[];vals.forEach(function(v){var x=v[ri]||v[String(ri)];if(x){cnt[x-1]++;xs.push(x);}});
    var r=h('div','mxr');r.appendChild(h('span','lb',rw));var s=h('div','stack');cnt.forEach(function(q,i){if(!q)return;var x=h('i');x.style.width=(q/xs.length*100)+'%';x.style.background=col[i];x.title=it.opts[i]+': '+q+' ('+pct(q,xs.length)+')';s.appendChild(x);});r.appendChild(s);
    var mv=h('b',null,xs.length?nf2.format(mean(xs)):'–');mv.style.cssText='text-align:right;color:var(--deep)';mv.title='Rata-rata (1–'+k+')';r.appendChild(mv);box.appendChild(r);});
  c.appendChild(box);var lg=h('div','legend');it.opts.forEach(function(l,i){var sp=h('span');var em=h('em');em.style.background=col[i];sp.appendChild(em);sp.appendChild(document.createTextNode((i+1)+' '+l));lg.appendChild(sp);});c.appendChild(lg);
  c.appendChild(h('div','meta','Angka di kanan: rata-rata skor 1–'+k+' per pernyataan.'));}
function scale(c,it,vals){var mn=it.t==='rating'?1:it.min,mx=it.max,pairs=[];for(var v=mx;v>=mn;v--)pairs.push([(it.t==='rating'?'★ ':'')+v+(v===mn&&it.lo?' · '+it.lo:v===mx&&it.hi?' · '+it.hi:''),vals.filter(function(x){return x===v;}).length]);
  var a=h('div');a.appendChild(h('span','big',nf2.format(mean(vals))));a.appendChild(h('span','mute small',' rata-rata ('+mn+'–'+mx+') · median '+nf2.format(median(vals))));c.appendChild(a);bars(c,pairs,vals.length);}
function nps(c,it,vals){var p=vals.filter(function(x){return x>=9;}).length,d=vals.filter(function(x){return x<=6;}).length,pa=vals.length-p-d,score=Math.round((p-d)/vals.length*100);
  var a=h('div');a.appendChild(h('span','big',(score>0?'+':'')+score));a.appendChild(h('span','mute small',' skor NPS (−100 sampai +100)'));c.appendChild(a);
  stack(c,[d,pa,p],['Kritikus 0–6','Pasif 7–8','Promotor 9–10'],vals.length);}
function ranking(c,it,vals){var avg=it.opts.map(function(o){var r=vals.map(function(v){return v.indexOf(o)+1;}).filter(function(x){return x>0;});return [o,mean(r)];}).sort(function(a,b){return a[1]-b[1];});
  var b=h('div','bars');avg.forEach(function(p,i){var r=h('div','br');r.appendChild(h('span','lb',(i+1)+'. '+p[0]));var tr=h('div','tr');var x=h('i');x.style.width=((it.opts.length-p[1]+1)/it.opts.length*100)+'%';tr.appendChild(x);r.appendChild(tr);r.appendChild(h('span','v','rerata '+nf2.format(p[1])));b.appendChild(r);});
  c.appendChild(b);c.appendChild(h('div','meta','Diurutkan dari rata-rata peringkat terkecil (paling diprioritaskan).'));}
function number(c,it,vals){vals=vals.map(Number);var r=h('div','legend');r.style.fontSize='14px';[['Rata-rata',mean(vals)],['Median',median(vals)],['Terkecil',Math.min.apply(null,vals)],['Terbesar',Math.max.apply(null,vals)],['Jumlah',vals.reduce(function(a,b){return a+b;},0)]].forEach(function(p){var s=h('span');s.appendChild(h('b',null,nf2.format(p[1])));s.appendChild(document.createTextNode(' '+p[0].toLowerCase()));r.appendChild(s);});c.appendChild(r);}
function texts(c,it,vals){var tx=h('div','texts');vals.slice().reverse().forEach(function(v){tx.appendChild(h('p',null,String(v)));});c.appendChild(tx);}

function fmt(it,v){if(v===undefined||v===null||v==='')return '';if(it.t==='likert')return v+' · '+(it.opts[v-1]||'');if(it.t==='matrix')return it.rows.map(function(r,i){var x=v[i]||v[String(i)];return x?r+': '+x:'';}).filter(Boolean).join('\n');if(Array.isArray(v))return v.join(it.t==='ranking'?' › ':', ');return String(v);}
function individual(){var I=$('ind');I.textContent='';if(!ROWS.length){I.appendChild(h('p','mute','Belum ada jawaban.'));return;}
  var qs=DEF.items.filter(function(x){return x.t!=='section';});
  var wrap=h('div','card');wrap.style.cssText='overflow:auto;max-height:70vh';var t=h('table','rs');var hr=h('tr');['#','Waktu'].concat(qs.map(function(q){return q.title||'(tanpa judul)';})).forEach(function(x){hr.appendChild(h('th',null,x));});hr.appendChild(h('th',null,''));
  var th=h('thead');th.appendChild(hr);t.appendChild(th);var tb=h('tbody');
  ROWS.slice().reverse().forEach(function(r,i){var tr=h('tr');tr.appendChild(h('td',null,String(ROWS.length-i)));var d=new Date(r.at);tr.appendChild(h('td',null,d.toLocaleDateString('id-ID',{day:'numeric',month:'short'})+' '+d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})));
    qs.forEach(function(q){var td=h('td',null,fmt(q,r.a[q.id]));td.style.whiteSpace='pre-line';td.style.minWidth='140px';tr.appendChild(td);});
    var x=h('td');var b=h('button','icon-btn','✕');b.type='button';b.title='Hapus jawaban ini';b.setAttribute('aria-label','Hapus jawaban nomor '+(ROWS.length-i));
    b.onclick=function(){if(!confirm('Hapus jawaban ini? Gunakan untuk jawaban uji atau ganda.'))return;fetch('api.php?a=hapus_jawaban',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:JSON.stringify({id:SID,rid:r.id})}).then(function(r){return r.json();}).then(function(j){if(j.ok){ROWS=ROWS.filter(function(z){return z!==r;});$('kpis').textContent='';$('sum').textContent='';draw();}});};
    x.appendChild(b);tr.appendChild(x);tb.appendChild(tr);});
  t.appendChild(tb);wrap.appendChild(t);I.appendChild(wrap);}
})();
