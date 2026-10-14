/* Zakademia Survei — tampilan formulir responden (dipakai di halaman isi dan pratinjau editor). */
(function(){
'use strict';
function h(tag,cls,txt){var e=document.createElement(tag);if(cls)e.className=cls;if(txt!=null)e.textContent=txt;return e;}
function shuffle(a){a=a.slice();for(var i=a.length-1;i>0;i--){var j=Math.floor(Math.random()*(i+1));var t=a[i];a[i]=a[j];a[j]=t;}return a;}
var OTHER='__o';

function render(root,def,o){
  o=o||{};
  var items=def.items||[],pages=[[]],secs=[null];
  items.forEach(function(it){if(it.t==='section'){if(pages[pages.length-1].length||secs[secs.length-1]){pages.push([]);secs.push(it);}else secs[secs.length-1]=it;}else pages[pages.length-1].push(it);});
  var raw={},other={},touched={},page=0,t0=Date.now(),orders={};
  items.forEach(function(it){if(it.opts&&it.shuffle)orders[it.id]=shuffle(it.opts);});
  if(o.draftKey){try{var d=JSON.parse(localStorage.getItem(o.draftKey)||'null');if(d&&d.raw){raw=d.raw;other=d.other||{};page=Math.min(d.page||0,pages.length-1);touched=d.touched||{};}}catch(_){}}
  function saveDraft(){if(!o.draftKey)return;try{localStorage.setItem(o.draftKey,JSON.stringify({raw:raw,other:other,page:page,touched:touched}));}catch(_){}}

  /* nilai akhir untuk satu soal */
  function val(it){var v=raw[it.id];
    if(it.t==='single'||it.t==='dropdown'){if(v===OTHER)return (other[it.id]||'').trim();return v==null?'':v;}
    if(it.t==='multi'){if(!Array.isArray(v))return [];return v.map(function(x){return x===OTHER?(other[it.id]||'').trim():x;}).filter(function(x){return x!=='';});}
    if(it.t==='ranking')return touched[it.id]?(v||it.opts.slice()):[];
    return v==null?'':v;}
  function answers(){var a={};items.forEach(function(it){if(it.t==='section'||!visible(it,a))return;var v=val(it);if(v===''||v==null||(Array.isArray(v)&&!v.length)||(typeof v==='object'&&!Array.isArray(v)&&!Object.keys(v).length))return;a[it.id]=v;});return a;}
  function visible(it,a){if(!it.cond)return true;var x=a[it.cond.q];return Array.isArray(x)?x.indexOf(it.cond.v)>=0:x===it.cond.v;}

  function problem(it){var v=val(it);
    var empty=v===''||v==null||(Array.isArray(v)&&!v.length)||(it.t==='matrix'&&Object.keys(v||{}).length===0);
    if(it.t==='matrix'&&it.req&&Object.keys(v||{}).length<it.rows.length)return 'Jawab setiap baris.';
    if(it.req&&empty)return it.t==='ranking'?'Atur urutan, atau tekan "Urutan ini sudah sesuai".':'Pertanyaan ini wajib dijawab.';
    if(!empty&&it.t==='email'&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))return 'Alamat email belum benar.';
    if(!empty&&it.t==='number'){var n=Number(v);if(isNaN(n))return 'Isi dengan angka.';if(it.min!=null&&n<it.min)return 'Minimal '+it.min+'.';if(it.max!=null&&n>it.max)return 'Maksimal '+it.max+'.';}
    if(it.t==='multi'&&it.maxSel&&v.length>it.maxSel)return 'Pilih paling banyak '+it.maxSel+'.';
    if((it.t==='single'||it.t==='dropdown'||it.t==='multi')&&((raw[it.id]===OTHER)||(Array.isArray(raw[it.id])&&raw[it.id].indexOf(OTHER)>=0))&&!(other[it.id]||'').trim())return 'Tuliskan jawaban untuk "Lainnya".';
    return null;}

  var cards={};
  function draw(){
    root.textContent='';cards={};
    var wrap=h('div','sv');root.appendChild(wrap);
    if(page===0||o.preview){var hd=h('div','card sv-head');hd.appendChild(h('h1',null,def.title||'Survei'));if(def.desc)hd.appendChild(h('p',null,def.desc));wrap.appendChild(hd);}
    if(def.opt&&def.opt.progress&&pages.length>1){var pw=h('div');pw.style.cssText='display:flex;flex-direction:column;gap:6px';var pt=h('div','sv-progt');pt.appendChild(h('span',null,'Bagian '+(page+1)+' dari '+pages.length));pt.appendChild(h('span',null,Math.round(page/pages.length*100)+'%'));var pb=h('div','sv-prog');var bar=h('i');bar.style.width=(page/pages.length*100)+'%';pb.appendChild(bar);pw.appendChild(pt);pw.appendChild(pb);wrap.appendChild(pw);}
    var sec=secs[page];if(sec&&(sec.title||sec.help)){var s=h('div','sv-sec');s.appendChild(h('h2',null,sec.title||''));if(sec.help)s.appendChild(h('p',null,sec.help));wrap.appendChild(s);}
    pages[page].forEach(function(it){var c=question(it);cards[it.id]=c;wrap.appendChild(c);});
    refreshVisibility();
    if(!pages[page].length)wrap.appendChild(h('p','mute','Bagian ini belum memiliki pertanyaan.'));
    var nav=h('div','sv-nav');
    if(page>0){var b=h('button','btn ghost','Kembali');b.type='button';b.onclick=function(){page--;saveDraft();draw();scrollTop();};nav.appendChild(b);}
    nav.appendChild(h('span','sp'));
    var msg=h('span','qe');msg.setAttribute('role','status');nav.appendChild(msg);
    var last=page===pages.length-1;
    var nx=h('button','btn',last?(o.preview?'Kirim (pratinjau)':'Kirim'):'Lanjut');nx.type='button';
    nx.onclick=function(){
      var bad=null;pages[page].forEach(function(it){if(!visible(it,answers()))return;var p=problem(it);mark(it,p);if(p&&!bad)bad=it;});
      if(bad){msg.textContent='Masih ada pertanyaan yang perlu dilengkapi.';cards[bad.id].scrollIntoView({behavior:'smooth',block:'center'});return;}
      msg.textContent='';
      if(!last){page++;saveDraft();draw();scrollTop();return;}
      nx.disabled=true;nx.textContent='Mengirim…';
      Promise.resolve(o.onSubmit?o.onSubmit(answers(),Math.round((Date.now()-t0)/1000)):null).then(function(){
        if(o.draftKey)try{localStorage.removeItem(o.draftKey);}catch(_){}
        done();
      },function(e){nx.disabled=false;nx.textContent='Kirim';msg.textContent=(e&&e.message)||'Belum terkirim. Periksa koneksi lalu coba lagi.';if(e&&e.errors)Object.keys(e.errors).forEach(function(id){var it=items.filter(function(x){return x.id===id;})[0];if(it&&cards[id])mark(it,e.errors[id]);});});
    };
    nav.appendChild(nx);wrap.appendChild(nav);
    if(o.footer)wrap.appendChild(o.footer());
  }
  function scrollTop(){(o.scroller||window).scrollTo(0,0);}
  function done(){root.textContent='';var w=h('div','sv');var c=h('div','card sv-done');c.appendChild(h('div','ok','✓'));c.appendChild(h('h1',null,'Terima kasih'));c.appendChild(h('p','mute',def.thanks||'Jawaban Anda sudah kami terima.'));
    if(o.preview){var b=h('button','btn ghost','Ulangi pratinjau');b.type='button';b.onclick=function(){raw={};other={};touched={};page=0;draw();};c.appendChild(b);}
    w.appendChild(c);if(o.footer)w.appendChild(o.footer());root.appendChild(w);scrollTop();if(o.onDone)o.onDone();}
  function mark(it,p){var c=cards[it.id];if(!c)return;c.classList.toggle('bad',!!p);c.querySelector('.qe').textContent=p||'';}
  function changed(it){saveDraft();if(cards[it.id]&&cards[it.id].classList.contains('bad'))mark(it,problem(it));refreshVisibility();}
  function refreshVisibility(){var a=answers();pages[page].forEach(function(it){if(cards[it.id])cards[it.id].hidden=!visible(it,a);});}

  function question(it){
    var c=h('div','card sv-q');var tid='qt_'+it.id;
    var t=h('div','qt',it.title||'Pertanyaan tanpa judul');t.id=tid;if(it.req){var r=h('span','req','*');r.setAttribute('aria-label','wajib');t.appendChild(r);}c.appendChild(t);
    if(it.help)c.appendChild(h('div','qh',it.help));
    var body=h('div');c.appendChild(body);
    var ty=it.t;
    if(ty==='short'||ty==='email'||ty==='number'||ty==='date'||ty==='phone'){
      var i=h('input','f');i.type={short:'text',email:'email',number:'number',date:'date',phone:'tel'}[ty];i.setAttribute('aria-labelledby',tid);
      if(it.ph)i.placeholder=it.ph;if(ty==='email')i.placeholder='nama@contoh.com';if(ty==='number'){i.inputMode='decimal';if(it.min!=null)i.min=it.min;if(it.max!=null)i.max=it.max;}
      if(ty==='phone')i.placeholder='08xxxxxxxxxx';i.value=raw[it.id]||'';i.oninput=function(){raw[it.id]=i.value;changed(it);};body.appendChild(i);
    }else if(ty==='long'){
      var ta=h('textarea','f');ta.setAttribute('aria-labelledby',tid);ta.rows=4;if(it.ph)ta.placeholder=it.ph;ta.value=raw[it.id]||'';ta.oninput=function(){raw[it.id]=ta.value;changed(it);};body.appendChild(ta);
    }else if(ty==='single'||ty==='multi'){
      var multi=ty==='multi',list=h('div','opts');list.setAttribute('role',multi?'group':'radiogroup');list.setAttribute('aria-labelledby',tid);
      var opts=(orders[it.id]||it.opts).slice();if(it.other)opts.push(OTHER);
      var ot=null;
      opts.forEach(function(op,k){var lab=h('label','opt');var inp=h('input');inp.type=multi?'checkbox':'radio';inp.name='r_'+it.id;
        var cur=raw[it.id];inp.checked=multi?(Array.isArray(cur)&&cur.indexOf(op)>=0):cur===op;lab.classList.toggle('on',inp.checked);
        lab.appendChild(inp);lab.appendChild(h('span',null,op===OTHER?'Lainnya':op));
        inp.onchange=function(){
          if(multi){var a=Array.isArray(raw[it.id])?raw[it.id].slice():[];if(inp.checked){if(a.indexOf(op)<0)a.push(op);}else a=a.filter(function(x){return x!==op;});raw[it.id]=a;}
          else raw[it.id]=op;
          list.querySelectorAll('.opt').forEach(function(l){l.classList.toggle('on',l.querySelector('input').checked);});
          if(ot)ot.hidden=!(multi?(raw[it.id]||[]).indexOf(OTHER)>=0:raw[it.id]===OTHER);if(ot&&!ot.hidden&&op===OTHER&&inp.checked)ot.focus();
          changed(it);};
        list.appendChild(lab);});
      body.appendChild(list);
      if(it.other){ot=h('input','f');ot.placeholder='Tuliskan jawaban lain';ot.setAttribute('aria-label','Jawaban lainnya');ot.value=other[it.id]||'';ot.hidden=!(multi?(raw[it.id]||[]).indexOf(OTHER)>=0:raw[it.id]===OTHER);ot.oninput=function(){other[it.id]=ot.value;changed(it);};ot.style.marginTop='8px';body.appendChild(ot);}
      if(multi&&it.maxSel)body.appendChild(h('div','qh','Pilih paling banyak '+it.maxSel+'.')).style.marginTop='8px';
    }else if(ty==='dropdown'){
      var sel=h('select','f');sel.setAttribute('aria-labelledby',tid);sel.appendChild(h('option',null,'Pilih salah satu')).value='';
      (orders[it.id]||it.opts).forEach(function(op){var x=h('option',null,op);x.value=op;sel.appendChild(x);});
      if(it.other){var xo=h('option',null,'Lainnya');xo.value=OTHER;sel.appendChild(xo);}
      sel.value=raw[it.id]||'';body.appendChild(sel);
      var ot2=null;if(it.other){ot2=h('input','f');ot2.placeholder='Tuliskan jawaban lain';ot2.setAttribute('aria-label','Jawaban lainnya');ot2.style.marginTop='8px';ot2.value=other[it.id]||'';ot2.hidden=raw[it.id]!==OTHER;ot2.oninput=function(){other[it.id]=ot2.value;changed(it);};body.appendChild(ot2);}
      sel.onchange=function(){raw[it.id]=sel.value;if(ot2)ot2.hidden=sel.value!==OTHER;changed(it);};
    }else if(ty==='likert'){
      body.appendChild(scaleRow(it,it.opts.map(function(l,k){return {v:k+1,l:l};}),raw[it.id],function(v){raw[it.id]=v;changed(it);},'likert',tid));
    }else if(ty==='linear'||ty==='nps'){
      var mn=ty==='nps'?0:it.min,mx=ty==='nps'?10:it.max,arr=[];for(var n=mn;n<=mx;n++)arr.push({v:n,l:String(n),c:ty==='nps'?(n<=6?'d':n<=8?'p':''):''});
      body.appendChild(scaleRow(it,arr,raw[it.id],function(v){raw[it.id]=v;changed(it);},ty,tid));
      var lo=ty==='nps'?'Sangat tidak mungkin':it.lo,hi=ty==='nps'?'Sangat mungkin':it.hi;
      if(lo||hi){var sl=h('div','scale-lab');sl.appendChild(h('span',null,lo||''));sl.appendChild(h('span',null,hi||''));body.appendChild(sl);sl.style.marginTop='6px';}
    }else if(ty==='rating'){
      var st=h('div','stars');st.setAttribute('role','radiogroup');st.setAttribute('aria-labelledby',tid);
      for(var k=1;k<=it.max;k++)(function(k){var b=h('button',null,'★');b.type='button';b.setAttribute('aria-label',k+' dari '+it.max);b.setAttribute('role','radio');
        b.onclick=function(){raw[it.id]=k;paint();changed(it);};st.appendChild(b);})(k);
      function paint(){[].forEach.call(st.children,function(b,i){b.classList.toggle('on',i<(raw[it.id]||0));b.setAttribute('aria-checked',String(i+1===raw[it.id]));});}
      paint();body.appendChild(st);
    }else if(ty==='matrix'){
      var mv=raw[it.id]=raw[it.id]&&typeof raw[it.id]==='object'?raw[it.id]:{};
      var compact=window.innerWidth<640;
      if(compact){var lg=h('div','mx-leg');it.opts.forEach(function(l,k){var x=h('span');x.appendChild(h('b',null,String(k+1)));x.appendChild(document.createTextNode(' '+l));lg.appendChild(x);});body.appendChild(lg);}
      it.rows.forEach(function(rw,ri){var row=h('div','mx-row');var rt=h('div','rt',rw);rt.id=tid+'_'+ri;row.appendChild(rt);
        row.appendChild(scaleRow(it,it.opts.map(function(l,k){return {v:k+1,l:compact?String(k+1):l,a:l};}),mv[ri],function(v){mv[ri]=v;changed(it);},compact?'mx':'likert',rt.id));body.appendChild(row);});
    }else if(ty==='ranking'){
      var ul=h('ol','rank');ul.setAttribute('aria-labelledby',tid);if(!Array.isArray(raw[it.id]))raw[it.id]=it.opts.slice();
      function drawRank(){ul.textContent='';raw[it.id].forEach(function(op,i){var li=h('li');li.appendChild(h('b',null,String(i+1)));li.appendChild(h('span',null,op));
        var up=h('button','icon-btn','↑');up.type='button';up.setAttribute('aria-label','Naikkan '+op);up.disabled=i===0;up.onclick=function(){mv2(i,-1);};
        var dn=h('button','icon-btn','↓');dn.type='button';dn.setAttribute('aria-label','Turunkan '+op);dn.disabled=i===raw[it.id].length-1;dn.onclick=function(){mv2(i,1);};
        li.appendChild(up);li.appendChild(dn);ul.appendChild(li);});}
      function mv2(i,d){var a=raw[it.id];var t=a[i];a[i]=a[i+d];a[i+d]=t;touched[it.id]=true;okb.hidden=true;drawRank();changed(it);var bs=ul.children[i+d].querySelectorAll('button');(bs[d<0?0:1].disabled?bs[d<0?1:0]:bs[d<0?0:1]).focus();}
      drawRank();body.appendChild(ul);
      var okb=h('button','btn ghost sm','Urutan ini sudah sesuai');okb.type='button';okb.style.marginTop='10px';okb.hidden=!!touched[it.id];okb.onclick=function(){touched[it.id]=true;okb.hidden=true;changed(it);};body.appendChild(okb);
    }
    var e=h('div','qe');e.setAttribute('role','alert');c.appendChild(e);
    return c;
  }
  function scaleRow(it,arr,cur,onPick,kind,labelId){
    var g=h('div','scale '+kind);g.setAttribute('role','radiogroup');g.setAttribute('aria-labelledby',labelId);
    var cols=kind==='likert'?arr.length:Math.min(arr.length,kind==='nps'?11:arr.length);
    g.style.gridTemplateColumns='repeat('+cols+',minmax(0,1fr))';
    if(kind==='nps'&&window.innerWidth<520)g.style.gridTemplateColumns='repeat(6,minmax(0,1fr))';
    if(kind==='likert'&&window.innerWidth<520&&arr.length>3){g.style.gridTemplateColumns='1fr';}
    arr.forEach(function(a){var b=h('button',a.c||'',a.l);b.type='button';if(a.a&&a.a!==a.l){b.setAttribute('aria-label',a.v+' '+a.a);b.title=a.a;}b.setAttribute('role','radio');b.setAttribute('aria-pressed',String(a.v===cur));b.setAttribute('aria-checked',String(a.v===cur));
      b.onclick=function(){[].forEach.call(g.children,function(x){x.setAttribute('aria-pressed','false');x.setAttribute('aria-checked','false');});b.setAttribute('aria-pressed','true');b.setAttribute('aria-checked','true');onPick(a.v);};g.appendChild(b);});
    return g;
  }
  draw();
  return {answers:answers};
}
window.ZKSurvey={render:render};
})();
