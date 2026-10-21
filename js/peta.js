/* Peta interaktif zakat dan wakaf per provinsi (beranda). Data: js/peta-data.js */
(function(){
  var D=window.PETA_ZW, root=document.getElementById('peta'); if(!D||!root) return;
  var svg=root.querySelector('svg.peta-svg'), tip=root.querySelector('.peta-tip'), panel=root.querySelector('.peta-panel'), legend=root.querySelector('.peta-legend'), sel=root.querySelector('select.peta-sel');
  var M={
    zis:{l:'Pengumpulan ZIS 2024',s:'Zakat maal, zakat fitrah, dan infak/sedekah yang dihimpun BAZNAS provinsi dan kabupaten/kota sepanjang 2024.',f:rp,k:'zis'},
    zm:{l:'Zakat maal 2024',s:'Zakat harta yang dihimpun BAZNAS daerah sepanjang 2024.',f:rp,k:'zm'},
    is:{l:'Infak/sedekah 2024',s:'Infak dan sedekah yang dihimpun BAZNAS daerah sepanjang 2024.',f:rp,k:'is'},
    pz:{l:'Pengelola zakat',s:'Jumlah BAZNAS provinsi, BAZNAS kabupaten/kota, dan LAZ yang beroperasi di provinsi ini (2024).',f:function(v){return v+' lembaga';},k:'pz'},
    iwn:{l:'Indeks Wakaf 2023',s:'Indeks Wakaf Nasional 2023 (BWI), skala 0 sampai 1: kelembagaan, regulasi, nazhir, aset, dan dampak.',f:function(v){return v.toFixed(3);},k:'iwn'}
  };
  function rp(v){ if(v>=1e12) return 'Rp'+(v/1e12).toFixed(2).replace('.',',')+' triliun'; if(v>=1e9) return 'Rp'+(v/1e9).toFixed(1).replace('.',',')+' miliar'; if(v>=1e6) return 'Rp'+(v/1e6).toFixed(1).replace('.',',')+' juta'; return 'Rp'+v.toLocaleString('id-ID'); }
  var cur='zis', aktif=null;
  var COL=['rgba(255,255,240,.10)','rgba(226,188,103,.28)','rgba(226,188,103,.5)','rgba(226,188,103,.74)','#E2BC67'];
  var LBL=['Terendah','','','','Tertinggi'];
  // gambar peta
  var ns='http://www.w3.org/2000/svg'; svg.setAttribute('viewBox',D.vb);
  D.prov.forEach(function(p,i){ var el=document.createElementNS(ns,'path'); el.setAttribute('d',p.d); el.setAttribute('tabindex','0'); el.setAttribute('role','button'); el.dataset.i=i; svg.appendChild(el); p.el=el; var o=document.createElement('option'); o.value=i; o.textContent=p.n; sel.appendChild(o); });
  function kelas(){ var k=M[cur].k, vals=D.prov.map(function(p){return p[k];}).sort(function(a,b){return a-b;}); var q=[1,2,3,4].map(function(j){return vals[Math.floor(vals.length*j/5)];});
    D.prov.forEach(function(p){ var v=p[k], c=0; while(c<4&&v>=q[c]) c++; p.el.style.fill=COL[c]; p.el.setAttribute('aria-label',p.n+': '+M[cur].f(v)); }); }
  function rank(p){ var k=M[cur].k; return D.prov.slice().sort(function(a,b){return b[k]-a[k];}).indexOf(p)+1; }
  function tampil(p){ aktif=p; D.prov.forEach(function(x){x.el.classList.toggle('on',x===p);}); sel.value=D.prov.indexOf(p);
    var k=M[cur].k;
    panel.innerHTML='<span class="kicker">'+M[cur].l+' · peringkat '+rank(p)+' dari 34</span><h3>'+p.n+'</h3><b class="peta-big">'+M[cur].f(p[k])+'</b>'
      +'<dl><div><dt>Zakat maal 2024</dt><dd>'+rp(p.zm)+'</dd></div><div><dt>Zakat fitrah 2024</dt><dd>'+rp(p.zf)+'</dd></div><div><dt>Infak/sedekah 2024</dt><dd>'+rp(p.is)+'</dd></div>'
      +'<div><dt>Pengelola zakat</dt><dd>'+p.pz+' lembaga'+(p.laz?' ('+p.laz+' LAZ)':'')+'</dd></div><div><dt>Indeks Wakaf 2023</dt><dd>'+p.iwn.toFixed(3)+' · '+p.ik+'</dd></div></dl>'; }
  function nasional(){ aktif=null; D.prov.forEach(function(x){x.el.classList.remove('on');}); sel.value='';
    var k=M[cur].k, top=D.prov.slice().sort(function(a,b){return b[k]-a[k];}).slice(0,5);
    panel.innerHTML='<span class="kicker">'+M[cur].l+'</span><h3>Lima provinsi teratas</h3><ol class="peta-top">'+top.map(function(p){return '<li><button type="button" data-i="'+D.prov.indexOf(p)+'"><span>'+p.n+'</span><b>'+M[cur].f(p[k])+'</b></button></li>';}).join('')+'</ol>'
      +(cur==='iwn'?'<p class="peta-note">Nasional: 0,301 (Baik). Sentuh provinsi di peta untuk rinciannya.</p>':'<p class="peta-note">Total 34 provinsi: '+M[cur].f(D.total[k])+'. Sentuh provinsi di peta untuk rinciannya.</p>');
    panel.querySelectorAll('button[data-i]').forEach(function(b){ b.onclick=function(){ tampil(D.prov[+b.dataset.i]); }; }); }
  function ganti(m){ cur=m; root.querySelectorAll('.peta-tabs button').forEach(function(b){ var on=b.dataset.m===m; b.classList.toggle('on',on); b.setAttribute('aria-selected',on); }); root.querySelector('.peta-desc').textContent=M[m].s; kelas(); if(aktif) tampil(aktif); else nasional(); }
  legend.innerHTML=COL.map(function(c,i){return '<i style="background:'+c+'"></i>';}).join('')+'<span>Rendah</span><span style="margin-left:auto">Tinggi</span>';
  svg.addEventListener('mousemove',function(e){ var t=e.target; if(t.tagName!=='path'){ tip.hidden=true; return; } var p=D.prov[+t.dataset.i]; tip.hidden=false; tip.innerHTML='<b>'+p.n+'</b><br>'+M[cur].f(p[M[cur].k]); var r=root.querySelector('.peta-map').getBoundingClientRect(); tip.style.left=(e.clientX-r.left+12)+'px'; tip.style.top=(e.clientY-r.top-10)+'px'; });
  svg.addEventListener('mouseleave',function(){ tip.hidden=true; });
  svg.addEventListener('click',function(e){ if(e.target.tagName==='path') tampil(D.prov[+e.target.dataset.i]); });
  svg.addEventListener('keydown',function(e){ if((e.key==='Enter'||e.key===' ')&&e.target.tagName==='path'){ e.preventDefault(); tampil(D.prov[+e.target.dataset.i]); } });
  sel.addEventListener('change',function(){ if(this.value==='') nasional(); else tampil(D.prov[+this.value]); });
  root.querySelectorAll('.peta-tabs button').forEach(function(b){ b.addEventListener('click',function(){ ganti(b.dataset.m); }); });
  root.querySelector('.peta-reset').addEventListener('click',nasional);
  ganti('zis');
})();
