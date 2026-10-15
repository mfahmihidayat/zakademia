/* Zakademia — Ruang Belajar: daftar kamar dan tampilan modul */
(function(){
'use strict';
function h(t,c,x){var e=document.createElement(t);if(c)e.className=c;if(x!=null)e.textContent=x;return e;}
var app=document.getElementById('app'),K=window.ZK_KAMAR,M=window.ZK_MODUL;
function kamar(id){return K.filter(function(k){return k.id===id;})[0];}
function lulus(id){var p=PROG[id];return p&&p.terbaik>=Math.ceil(p.total*0.6);}

function rooms(){
  var hd=h('div');hd.style.cssText='display:flex;flex-direction:column;gap:6px;margin-bottom:20px';
  hd.appendChild(h('p','mute small','RUANG BELAJAR')).style.cssText='margin:0;font-weight:700;letter-spacing:.08em';
  hd.appendChild(h('h1',null,'Belajar dengan tenang, praktik langsung'));
  var p=h('p','mute','Dua kamar belajar mandiri. Setiap modul sekitar 15 menit, diakhiri kuis singkat dan langkah praktik dengan alat Zakademia. Kemajuan belajar tersimpan di akun Anda.');p.style.cssText='margin:0;max-width:72ch';hd.appendChild(p);
  app.appendChild(hd);
  var g=h('div','rooms');
  K.forEach(function(k){var c=h('section','card room '+k.id);c.setAttribute('aria-labelledby','rt_'+k.id);
    var rh=h('div','rh');var img=h('img');img.src=k.foto;img.alt='';rh.appendChild(img);var tx=h('div');tx.style.flex='1';var t=h('h2',null,k.nama);t.id='rt_'+k.id;tx.appendChild(t);
    tx.appendChild(h('p',null,'Pemandu: '+k.pemandu));
    var done=k.modul.filter(lulus).length;var bar=h('div','bar');bar.setAttribute('role','img');bar.setAttribute('aria-label',done+' dari '+k.modul.length+' modul selesai');var bi=h('i');bi.style.width=(done/k.modul.length*100)+'%';bar.appendChild(bi);tx.appendChild(bar);
    tx.appendChild(h('p',null,done+' dari '+k.modul.length+' modul selesai')).style.fontSize='12.5px';
    rh.appendChild(tx);c.appendChild(rh);
    var ip=h('p','mute small',k.intro);ip.style.cssText='margin:0;padding:16px 22px 0';c.appendChild(ip);
    var ul=h('ul','mods');
    k.modul.forEach(function(id){var m=M[id],li=h('li',lulus(id)?'done':(m.status!=='siap'?'soon':''));var a=h(m.status==='siap'?'a':'div',m.status==='siap'?'':'x');if(m.status==='siap')a.href='?m='+id;
      a.appendChild(h('span','no',lulus(id)?'✓':String(m.no)));var b=h('span');b.appendChild(h('b',null,m.judul));b.appendChild(h('small',null,m.menit+' menit · '+m.tujuan[0]));a.appendChild(b);
      var p=PROG[id];a.appendChild(h('span','tag '+(m.status!=='siap'?'s':lulus(id)?'r':p?'d':'r'),m.status!=='siap'?'Segera':lulus(id)?'Selesai · '+p.terbaik+'/'+p.total:p?'Ulangi kuis':'Mulai'));
      li.appendChild(a);ul.appendChild(li);});
    c.appendChild(ul);g.appendChild(c);});
  app.appendChild(g);
  var foot=h('div','card');foot.style.cssText='margin-top:18px;padding:18px 22px;display:flex;gap:12px;align-items:center;flex-wrap:wrap';
  var fp=h('p',null,'Ingin belajar bersama tim lembaga, kelas berjadwal, atau pendampingan penelitian?');fp.style.cssText='margin:0;flex:1;min-width:220px;font-weight:600';foot.appendChild(fp);
  var fa=h('a','btn sm','Konsultasikan');fa.href='/?topik='+encodeURIComponent('Ruang Belajar')+'#kontak';foot.appendChild(fa);app.appendChild(foot);
}

function modul(id){
  var m=M[id];if(!m||m.status!=='siap'){app.appendChild(h('p','err-box','Modul ini belum tersedia.'));var b=h('a','btn ghost','Kembali ke Ruang Belajar');b.href='/belajar/';app.appendChild(b);return;}
  var k=kamar(m.kamar),v=h('article','mv');app.appendChild(v);
  var hd=h('div','mvh');var img=h('img');img.src=k.foto;img.alt='';hd.appendChild(img);var ht=h('div');ht.appendChild(h('p','mute small',k.nama+' · Modul '+m.no+' · '+m.menit+' menit')).style.margin='0';ht.appendChild(h('h1',null,m.judul));hd.appendChild(ht);v.appendChild(hd);
  var g=h('div','card goal');g.appendChild(h('b',null,'Setelah modul ini, Anda dapat:'));var ul=h('ul');m.tujuan.forEach(function(x){ul.appendChild(h('li',null,x));});g.appendChild(ul);v.appendChild(g);
  m.bagian.forEach(function(b,i){var s=h('section','card sec');s.appendChild(h('span','n','Bagian '+(i+1)));s.appendChild(h('h2',null,b.h));
    (b.p||[]).forEach(function(x){s.appendChild(h('p',null,x));});
    if(b.daftar){var l=h('ul');b.daftar.forEach(function(x){l.appendChild(h('li',null,x));});s.appendChild(l);}
    if(b.rumus)s.appendChild(h('div','rumus',b.rumus));
    if(b.contoh){var c=h('div','contoh');c.appendChild(h('b',null,'Contoh'));c.appendChild(document.createTextNode(b.contoh));s.appendChild(c);}
    (b.p2||[]).forEach(function(x){s.appendChild(h('p',null,x));});v.appendChild(s);});
  /* kuis */
  var qz=h('section','card quiz');qz.setAttribute('aria-labelledby','qzt');var qt=h('h2',null,'Kuis singkat');qt.id='qzt';qz.appendChild(qt);
  qz.appendChild(h('p','mute small','Lima pertanyaan. Modul dianggap selesai bila minimal tiga jawaban benar.')).style.margin='-10px 0 0';
  var picks={};
  m.kuis.forEach(function(q,i){var d=h('fieldset','qq');d.style.cssText='border:0;margin:0;padding:0';var lg=h('legend','qt',(i+1)+'. '+q.q);d.appendChild(lg);
    q.o.forEach(function(o,j){var l=h('label','qo');var r=h('input');r.type='radio';r.name='q'+i;r.value=j;r.onchange=function(){picks[i]=j;};l.appendChild(r);l.appendChild(h('span',null,o));d.appendChild(l);});
    var e=h('div','qe');e.hidden=true;d.appendChild(e);qz.appendChild(d);});
  var row=h('div');row.style.cssText='display:flex;gap:12px;align-items:center;flex-wrap:wrap';var btn=h('button','btn','Periksa jawaban');btn.type='button';var res=h('span',null);res.setAttribute('role','status');res.style.fontWeight='700';row.appendChild(btn);row.appendChild(res);qz.appendChild(row);
  btn.onclick=function(){var n=Object.keys(picks).length;if(n<m.kuis.length){res.textContent='Jawab semua pertanyaan terlebih dahulu ('+n+'/'+m.kuis.length+').';return;}
    var skor=0;[].forEach.call(qz.querySelectorAll('.qq'),function(d,i){var q=m.kuis[i];[].forEach.call(d.querySelectorAll('.qo'),function(l,j){l.classList.toggle('ok',j===q.a);l.classList.toggle('no',j===picks[i]&&j!==q.a);l.querySelector('input').disabled=true;});
      if(picks[i]===q.a)skor++;var e=d.querySelector('.qe');e.hidden=false;e.textContent=(picks[i]===q.a?'Benar. ':'Belum tepat. ')+q.e;});
    var ok=skor>=Math.ceil(m.kuis.length*0.6);res.textContent='Skor '+skor+'/'+m.kuis.length+(ok?'. Modul selesai.':'. Coba pelajari lagi bagian yang belum tepat.');
    btn.textContent='Ulangi kuis';btn.onclick=function(){location.reload();};
    fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:JSON.stringify({modul:id,skor:skor,total:m.kuis.length})}).then(function(r){return r.json();}).then(function(j){if(j.ok)PROG[id]={skor:skor,total:m.kuis.length,terbaik:j.terbaik};}).catch(function(){});};
  v.appendChild(qz);
  if(m.praktik){var pr=h('div','prak');pr.appendChild(h('p',null,'Praktik: '+m.praktik.teks));var a=h('a','btn',m.praktik.label);a.href=m.praktik.href;pr.appendChild(a);v.appendChild(pr);}
  /* navigasi */
  var nav=h('div','nav2');var back=h('a','btn ghost','← Ruang Belajar');back.href='/belajar/';nav.appendChild(back);
  var ids=k.modul,nx=null;for(var i=ids.indexOf(id)+1;i<ids.length;i++)if(M[ids[i]].status==='siap'){nx=ids[i];break;}
  if(nx){var n=h('a','btn ghost','Modul berikutnya →');n.href='?m='+nx;nav.appendChild(n);}v.appendChild(nav);
  document.title=m.judul+' · Ruang Belajar Zakademia';
}
if(MOD)modul(MOD);else rooms();
})();
