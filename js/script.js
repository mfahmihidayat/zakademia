function zkLight(el,parts){var ps=parts.split(' ');el.querySelectorAll('.ly').forEach(function(l){var m=l.className.match(/ly-(\w+)/),n=m?m[1]:'',on=ps.indexOf(n)>-1;l.classList.toggle('dim',!on);l.classList.toggle('lit',on&&n==='gold');});}
document.querySelectorAll('.emb[data-parts]').forEach(function(el){zkLight(el,el.getAttribute('data-parts'));});
(function(){
  var pages=['beranda','layanan','kajian','literasi','tentang','kontak','admin'];
  var menu=document.getElementById('menu'),toggle=document.getElementById('menuToggle');
  function route(){
    var parts=(location.hash||'#beranda').slice(1).split('/'),h=parts[0],sub=parts[1]||''; if(pages.indexOf(h)<0) h='beranda';
    document.querySelectorAll('[data-page]').forEach(function(p){p.hidden=p.getAttribute('data-page')!==h;});
    
    if(menu){menu.classList.remove('open');} if(toggle){toggle.setAttribute('aria-expanded','false');} var zm=document.getElementById('zkMenu'); if(zm) zm.classList.remove('open'); window.scrollTo(0,0);
    var tgt=sub&&document.getElementById(h+'-'+sub);
    if(tgt){setTimeout(function(){tgt.scrollIntoView({behavior:'smooth',block:'start'});tgt.classList.add('zk-sorot');setTimeout(function(){tgt.classList.remove('zk-sorot');},2400);},60);}
    if(h==='admin' && window.zkAdminStart) window.zkAdminStart();
  }
  window.addEventListener('hashchange',route);
  if(toggle) toggle.addEventListener('click',function(){var o=menu.classList.toggle('open');toggle.setAttribute('aria-expanded',o?'true':'false');});
  route();

  // header shadow on scroll
  var head=document.querySelector('header');
  window.addEventListener('scroll',function(){head.classList.toggle('scrolled',window.scrollY>8);},{passive:true});
  var still=window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // gallery
  var gal={frames:document.getElementById('frames'),dots:document.getElementById('dots'),timer:null,idx:0};
  var GAL_DEFAULT=[].slice.call(gal.frames.querySelectorAll('img')).map(function(im){return {src:im.getAttribute('src'),alt:im.alt};});
  function galShow(i){
    var fs=gal.frames.children,ds=gal.dots.children; if(!fs.length) return;
    if(fs[gal.idx]) fs[gal.idx].classList.remove('on'); if(ds[gal.idx]) ds[gal.idx].removeAttribute('aria-current');
    gal.idx=i; fs[i].classList.add('on'); if(ds[i]) ds[i].setAttribute('aria-current','true');
    var img=fs[i].querySelector('img'); if(img){img.style.animation='none';void img.offsetWidth;img.style.animation='';}
  }
  function galRender(items){
    clearInterval(gal.timer); gal.frames.innerHTML=''; gal.dots.innerHTML=''; gal.idx=0;
    items.forEach(function(it,i){
      var f=document.createElement('figure');f.className='frame'+(i===0?' on':'');f.style.margin='0';
      var im=document.createElement('img');im.src=it.src;im.alt=it.alt||'Foto Zakademia';if(i)im.loading='lazy';f.appendChild(im);gal.frames.appendChild(f);
      var d=document.createElement('button');d.type='button';d.setAttribute('aria-label','Foto '+(i+1));if(i===0)d.setAttribute('aria-current','true');
      d.addEventListener('click',function(){clearInterval(gal.timer);galShow(i);});gal.dots.appendChild(d);
    });
    gal.dots.hidden=items.length<2;
    // galeri tidak berputar sendiri; pengunjung memilih lewat titik di bawahnya
  }
  galRender(GAL_DEFAULT);

  // editable text: [word] marks the green emphasis
  var editEls=[].slice.call(document.querySelectorAll('[data-edit]'));
  var TXT_DEFAULT={};
  function toMark(el){var t='';el.childNodes.forEach(function(c){t+=c.nodeType===1&&c.tagName==='EM'?'['+c.textContent+']':c.textContent;});return t.replace(/\s+/g,' ').trim();}
  function paint(el,val){
    el.textContent='';
    val.split(/(\[[^\]]*\])/).forEach(function(part){
      if(!part) return;
      if(part[0]==='['&&part[part.length-1]===']'){var e=document.createElement('em');e.textContent=part.slice(1,-1);el.appendChild(e);}
      else el.appendChild(document.createTextNode(part));
    });
  }
  editEls.forEach(function(el){var k=el.getAttribute('data-edit');if(!(k in TXT_DEFAULT))TXT_DEFAULT[k]=toMark(el);});
  var txtNow={};
  function applyText(map){
    txtNow=map||{};
    editEls.forEach(function(el){var k=el.getAttribute('data-edit');var v=txtNow[k];paint(el,typeof v==='string'&&v.trim()?v.trim():TXT_DEFAULT[k]);});
  }

  // shared content from the site's managers
  var galNow=null;
  (async function(){
    var db=null; try{db=await window.claude.use('db');}catch(e){}
    if(!db) return;
    db.doc('konten/teks').onSnapshot(function(d){var x=d.data();applyText(x&&x.fields||{});if(window.zkAdminText)window.zkAdminText();},function(){});
    db.doc('konten/galeri').onSnapshot(function(d){var x=d.data();galNow=x&&Array.isArray(x.items)&&x.items.length?x.items:null;galRender(galNow||GAL_DEFAULT);if(window.zkAdminGal)window.zkAdminGal();},function(){});
  })();

  // admin room
  (function(){
    var gate=document.getElementById('admGate'),gateT=document.getElementById('admGateText'),main=document.getElementById('admMain');
    var started=false;
    var pageNames={beranda:'Beranda',layanan:'Layanan',kajian:'Kajian',literasi:'Literasi',tentang:'Tentang',kontak:'Mari berbincang',admin:null};
    async function start(){
      if(started) return; started=true;
      var db=null,user=null,can=false,owner=false,assets=null;
      try{db=await window.claude.use('db');user=await window.claude.use('user');}catch(e){}
      if(user){try{can=await user.canEdit();owner=await user.isOwner();}catch(e){}}
      if(!db||!user){gateT.textContent='Silakan masuk dengan akun yang telah diberi akses pengelola situs ini, lalu buka halaman ini kembali.';return;}
      if(!can){gateT.textContent='Akun Anda belum memiliki akses pengelola. Minta pemilik situs memberikan akses edit kepada akun Anda.';return;}
      gate.hidden=true; main.hidden=false;
      try{assets=await window.claude.use('assets');}catch(e){}

      // tabs
      var tabs=[].slice.call(document.querySelectorAll('.adm-tabs [role="tab"]'));
      if(owner) tabs[2].hidden=false;
      function sel(i){tabs.forEach(function(t,k){t.setAttribute('aria-selected',k===i?'true':'false');t.tabIndex=k===i?0:-1;document.getElementById('ap-'+k).hidden=k!==i;});}
      tabs.forEach(function(t,k){t.addEventListener('click',function(){sel(k);if(k===2)loadInbox();});});

      // text fields
      var box=document.getElementById('admFields'),inputs={};
      var groups={};
      editEls.forEach(function(el){
        var k=el.getAttribute('data-edit'); if(inputs[k]) return;
        var pg=el.closest('[data-page]'),pn=pg?pageNames[pg.getAttribute('data-page')]:'Kaki halaman';
        if(pn===null) return;
        if(!groups[pn]){var g=document.createElement('div');g.className='adm-group';var h=document.createElement('h2');h.textContent=pn;g.appendChild(h);box.appendChild(g);groups[pn]=g;}
        var kind=el.tagName==='H1'?'Judul halaman':el.tagName==='H2'?'Judul bagian':'Paragraf';
        var f=document.createElement('div');f.className='adm-field';
        var lb=document.createElement('label');lb.htmlFor='ed-'+k;lb.textContent=kind;
        var ta=document.createElement('textarea');ta.id='ed-'+k;ta.placeholder=TXT_DEFAULT[k];
        var o=document.createElement('span');o.className='orig';o.textContent='Teks asli: '+TXT_DEFAULT[k];
        f.appendChild(lb);f.appendChild(ta);f.appendChild(o);groups[pn].appendChild(f);inputs[k]=ta;
      });
      var dirty=false;
      Object.keys(inputs).forEach(function(k){inputs[k].addEventListener('input',function(){dirty=true;st.textContent='Ada perubahan yang belum disimpan.';});});
      window.zkAdminText=function(){ if(dirty) return; Object.keys(inputs).forEach(function(k){inputs[k].value=typeof txtNow[k]==='string'?txtNow[k]:'';}); };
      window.zkAdminText();
      var st=document.getElementById('admSaveSt'),save=document.getElementById('admSave');
      save.addEventListener('click',async function(){
        var fields={};Object.keys(inputs).forEach(function(k){var v=inputs[k].value.trim();if(v&&v!==TXT_DEFAULT[k])fields[k]=v;});
        save.disabled=true;st.textContent='Menyimpan…';
        try{await db.doc('konten/teks').set({fields:fields,diperbarui:new Date().toISOString()});dirty=false;st.textContent='Tersimpan. Perubahan langsung tampil di situs.';}
        catch(e){st.textContent='Belum tersimpan ('+(e&&e.code||'galat')+'). Coba lagi.';}
        finally{save.disabled=false;}
      });

      // gallery manager
      var mlist=document.getElementById('mgrList'),mst=document.getElementById('mgrStatus'),inp=document.getElementById('photoInput');
      function current(){return (galNow||GAL_DEFAULT).map(function(x){return {src:x.src,alt:x.alt||''};});}
      async function saveGal(items,msg){
        try{await db.doc('konten/galeri').set({items:items,diperbarui:new Date().toISOString()});if(msg)mst.textContent=msg;}
        catch(e){mst.textContent='Galeri belum tersimpan ('+(e&&e.code||'galat')+'). Coba lagi.';}
      }
      window.zkAdminGal=function(){
        var list=current();mlist.innerHTML='';
        list.forEach(function(it,i){
          var li=document.createElement('li');
          var im=document.createElement('img');im.src=it.src;im.alt='';li.appendChild(im);
          var lab=document.createElement('label');lab.textContent='Deskripsi foto';lab.htmlFor='alt-'+i;
          var cap=document.createElement('input');cap.id='alt-'+i;cap.value=it.alt;cap.placeholder='Mis. Diskusi kajian wakaf bersama nazhir';
          cap.addEventListener('change',function(){var l=current();l[i].alt=cap.value.trim();saveGal(l,'Deskripsi tersimpan.');});
          var row=document.createElement('div');row.className='mgr-row';
          var up=document.createElement('button');up.type='button';up.textContent='Naikkan';up.disabled=i===0;
          up.addEventListener('click',function(){var l=current();var t=l[i-1];l[i-1]=l[i];l[i]=t;saveGal(l,'Urutan diperbarui.');});
          var del=document.createElement('button');del.type='button';del.className='del';del.textContent='Hapus';del.disabled=list.length<2;
          del.addEventListener('click',function(){
            if(del.dataset.sure!=='1'){del.dataset.sure='1';del.textContent='Yakin hapus?';setTimeout(function(){del.dataset.sure='';del.textContent='Hapus';},4000);return;}
            var l=current();l.splice(i,1);saveGal(l,'Foto dihapus dari galeri.');
          });
          row.appendChild(up);row.appendChild(del);li.appendChild(lab);li.appendChild(cap);li.appendChild(row);mlist.appendChild(li);
        });
      };
      window.zkAdminGal();
      document.getElementById('galReset').addEventListener('click',function(){
        var b=this; if(b.dataset.sure!=='1'){b.dataset.sure='1';b.textContent='Yakin kembalikan?';setTimeout(function(){b.dataset.sure='';b.textContent='Kembalikan galeri asli';},4000);return;}
        b.dataset.sure='';b.textContent='Kembalikan galeri asli';saveGal([], 'Galeri kembali ke foto asli.');
      });
      if(!assets){document.getElementById('photoLabel').hidden=true;mst.textContent='Unggah foto tidak tersedia di tampilan ini.';}
      inp.addEventListener('change',async function(){
        var files=[].slice.call(inp.files||[]); if(!files.length||!assets) return;
        var l=current(),done=0; mst.textContent='Mengunggah '+files.length+' foto…';
        for(var k=0;k<files.length;k++){
          try{var r=await assets.upload(files[k]);l.push({src:r.url,alt:''});done++;}
          catch(e){mst.textContent='Ada foto yang belum terunggah ('+(e&&e.code||'galat')+'). Gunakan JPG, PNG, atau WebP di bawah 20 MB.';}
        }
        if(done) await saveGal(l,done+' foto ditambahkan ke galeri.');
        inp.value='';
      });

      // inbox (owner only)
      var inboxLoaded=false;
      async function loadInbox(){
        if(inboxLoaded||!owner) return; inboxLoaded=true;
        var ib=document.getElementById('inbox');ib.textContent='Memuat pesan…';
        try{
          var people=await db.collection('permintaan').get(),all=[];
          for(var p of people.docs){var ks=await db.collection('permintaan/'+p.id+'/kiriman').get();ks.docs.forEach(function(d){all.push(d.data());});}
          all.sort(function(a,b){return String(b.dikirim||'').localeCompare(String(a.dikirim||''));});
          ib.textContent='';
          if(!all.length){ib.textContent='Belum ada pesan masuk.';return;}
          all.forEach(function(m){
            var c=document.createElement('article');c.className='msg';
            var b=document.createElement('b');b.textContent=(m.nama||'Tanpa nama')+' · '+(m.lembaga||'');
            var sm=document.createElement('small');var dt=m.dikirim?new Date(m.dikirim):null;
            sm.textContent=(m.kebutuhan||'')+' · '+(m.email||'')+(dt&&!isNaN(dt)?' · '+dt.toLocaleString('id-ID',{dateStyle:'medium',timeStyle:'short'}):'');
            var pp=document.createElement('p');pp.textContent=m.pesan||'';
            c.appendChild(b);c.appendChild(sm);c.appendChild(pp);ib.appendChild(c);
          });
        }catch(e){ib.textContent='Pesan belum dapat dimuat. Coba buka ulang halaman.';inboxLoaded=false;}
      }
    }
    window.zkAdminStart=start;
  })();

  if((location.hash||'')==='#admin') window.zkAdminStart();

  // philosophy explorer: highlight the emblem parts behind each meaning
  (function(){
    var btns=[].slice.call(document.querySelectorAll('.ph-list [role="tab"]')); if(!btns.length) return;
    var em=document.getElementById('phEmblem'),cap=document.getElementById('phCaption'),stage=document.getElementById('phStage'),auto=null,cur=0;
    function sel(i,focus){
      cur=i;
      btns.forEach(function(b,k){b.setAttribute('aria-selected',k===i?'true':'false');b.tabIndex=k===i?0:-1;});
      var parts=btns[i].getAttribute('data-parts').split(' ');
      em.querySelectorAll('.ly').forEach(function(l){
        var name=l.className.match(/ly-(\w+)/)[1],on=parts.indexOf(name)>-1;
        l.classList.toggle('dim',!on); l.classList.toggle('lit',on&&name==='gold');
      });
      cap.textContent=btns[i].querySelector('b').textContent+' · '+btns[i].querySelector('em').textContent;
      stage.setAttribute('aria-labelledby',btns[i].id);
      if(focus) btns[i].focus();
    }
    btns.forEach(function(b,k){
      b.addEventListener('click',function(){clearInterval(auto);sel(k);});
      b.addEventListener('keydown',function(e){var n=null,L=btns.length;if(e.key==='ArrowDown'||e.key==='ArrowRight')n=(k+1)%L;if(e.key==='ArrowUp'||e.key==='ArrowLeft')n=(k+L-1)%L;if(n!==null){e.preventDefault();clearInterval(auto);sel(n,true);}});
    });
    sel(0);
    if(!still){auto=setInterval(function(){ if(document.querySelector('[data-page="tentang"]').hidden) return; sel((cur+1)%btns.length);},5000);}
  })();

  // learning buddies: play quietly when visible, sound on request
  (function(){
    var vids=[].slice.call(document.querySelectorAll('.buddy video'));
    if('IntersectionObserver' in window && !still){
      var io=new IntersectionObserver(function(es){es.forEach(function(e){ if(e.isIntersecting){ var p=e.target.play(); if(p&&p.catch) p.catch(function(){}); } else e.target.pause(); });},{threshold:.4});
      vids.forEach(function(v){io.observe(v);});
    }
    document.querySelectorAll('.sound').forEach(function(b){
      b.addEventListener('click',function(){
        var v=document.getElementById(b.getAttribute('data-v')),on=b.getAttribute('aria-pressed')!=='true';
        vids.forEach(function(o){ if(o!==v){o.muted=true;} });
        document.querySelectorAll('.sound').forEach(function(x){ if(x!==b){x.setAttribute('aria-pressed','false');x.textContent=x.textContent.replace('Matikan suara','Dengarkan '+x.closest('.buddy').querySelector('h3').textContent);} });
        v.muted=!on; if(on){v.currentTime=0;} var p=v.play(); if(p&&p.catch) p.catch(function(){});
        b.setAttribute('aria-pressed',on?'true':'false');
        b.textContent=on?'Matikan suara':'Dengarkan '+b.closest('.buddy').querySelector('h3').textContent;
      });
    });
  })();

  // paper filter
  document.querySelectorAll('[data-t]').forEach(function(b){
    b.addEventListener('click',function(){
      var t=b.getAttribute('data-t');
      document.querySelectorAll('[data-t]').forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false');});
      document.querySelectorAll('#papers .paper').forEach(function(p){p.hidden=!(t==='semua'||p.getAttribute('data-topic')===t);});
    });
  });

  // services switcher
  var svc=[
    {tag:'Figur yang berlari',name:'Riset Kebutuhan',cta:'Buat Survei',href:'/survei/',parts:'gold top leaf band',desc:'Mendengar pertanyaan yang paling penting bagi lembaga Anda, lalu mencarikan jawabannya dengan cermat.',m:'Seperti sosok yang berlari, riset menjaga lembaga terus bergerak maju dengan arah yang jelas.'},
    {tag:'Lengkung dasar',name:'Data & Analitik',cta:'Analisis Data',href:'/analitik/',parts:'base',desc:'Merapikan catatan kebaikan menjadi pijakan yang kokoh untuk melangkah.',m:'Fondasi yang kokoh membuat setiap langkah lebih mantap.'},
    {tag:'Daun muda',name:'Pendampingan Strategi',cta:'Mulai Asesmen',href:'/asesmen/',parts:'leaf',desc:'Berjalan bersama menyusun arah, dari temuan menjadi langkah yang dijalankan.',m:'Seperti daun muda, lembaga ditemani tumbuh dan berbuah secara berkelanjutan.'},
    {tag:'Daun terbuka',name:'Ruang Belajar',cta:'Masuk Ruang Belajar',href:'/belajar/',parts:'top leaf',desc:'Menumbuhkan kemampuan meneliti di tangan para pengelola kebaikan.',m:'Seperti dua daun yang terbuka, ilmu dibuka untuk siapa saja yang ingin belajar.'}
  ];
  var light=zkLight;
  var tabs=[].slice.call(document.querySelectorAll('.svc-tabs [role="tab"]')),mini=document.getElementById('miniEmblem'),txt=document.querySelector('.svc-text'),cycle=null;
  function pick(i,focusTab){
    tabs.forEach(function(t,k){t.setAttribute('aria-selected',k===i?'true':'false');t.tabIndex=k===i?0:-1;});
    document.getElementById('svcPanel').setAttribute('aria-labelledby','tab-'+i);
    light(mini,svc[i].parts);
    document.getElementById('svcTag').textContent=svc[i].tag;document.getElementById('svcName').textContent=svc[i].name;
    var cta=document.getElementById('svcCta');if(cta){cta.textContent=svc[i].cta;cta.href=svc[i].href;}document.getElementById('svcDesc').textContent=svc[i].desc;document.getElementById('svcMeaning').textContent=svc[i].m;
    txt.classList.remove('swap');void txt.offsetWidth;txt.classList.add('swap');
    if(focusTab) tabs[i].focus();
  }
  tabs.forEach(function(t,k){
    t.addEventListener('click',function(){clearInterval(cycle);pick(k);});
    t.addEventListener('keydown',function(e){var n=null;if(e.key==='ArrowDown'||e.key==='ArrowRight')n=(k+1)%4;if(e.key==='ArrowUp'||e.key==='ArrowLeft')n=(k+3)%4;if(n!==null){e.preventDefault();clearInterval(cycle);pick(n,true);}});
  });
  tabs.forEach(function(t,k){t.tabIndex=k===0?0:-1;});
  light(mini,svc[0].parts);
  if(!still){var cur=0;cycle=setInterval(function(){cur=(cur+1)%4;pick(cur);},6000);}

  // rings legend focus
  var rings=document.querySelector('.rings');
  document.querySelectorAll('[data-ring]').forEach(function(d){
    var n=d.getAttribute('data-ring');
    function on(){rings.classList.add('focus');rings.querySelectorAll('.ring').forEach(function(c){c.classList.toggle('on',c.classList.contains('r'+n));});d.classList.add('on');}
    function off(){rings.classList.remove('focus');d.classList.remove('on');}
    d.addEventListener('mouseenter',on);d.addEventListener('mouseleave',off);d.addEventListener('focus',on);d.addEventListener('blur',off);
  });

  // growth line and reveal on scroll
  var growth=document.getElementById('growth'),steps=document.getElementById('steps');
  var reveals=[].slice.call(document.querySelectorAll('[data-reveal]'));
  if(!still){
    reveals.forEach(function(el){ if(el.getBoundingClientRect().top>window.innerHeight) el.classList.add('pre'); });
    var tick=function(){
      var vh=window.innerHeight;
      reveals.forEach(function(el){ if(el.classList.contains('pre') && el.getBoundingClientRect().top<vh*0.9) el.classList.remove('pre'); });
      if(growth && growth.offsetParent){
        var r=steps.getBoundingClientRect();var p=Math.min(1,Math.max(0,(vh*0.85-r.top)/(r.height+vh*0.3)));
        growth.querySelector('i').style.setProperty('--g',(Math.max(.04,p)*100).toFixed(1)+'%');
      }
    };
    window.addEventListener('scroll',function(){requestAnimationFrame(tick);},{passive:true});
    window.addEventListener('hashchange',function(){setTimeout(function(){reveals.forEach(function(el){el.classList.remove('pre');});tick();},50);});
    tick();
  }

  // contact form: stored privately for the Zakademia team
  var store=null, uid=null;
  (async function(){
    try{
      var db=await window.claude?.use?.('db'); var user=await window.claude?.use?.('user');
      var id=user ? await user.id() : null;
      if(db && id){ store=db; uid=id; }
    }catch(e){}
  })();
  var TO='info@zakademia.com';
  var emailRe=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  function check(id,ok,msg){var el=document.getElementById(id),er=document.getElementById(id+'-err');if(ok){el.removeAttribute('aria-invalid');er.textContent='';}else{el.setAttribute('aria-invalid','true');er.textContent=msg;}return ok;}
  ['c-nama','c-lembaga','c-email','c-jenis','c-pesan'].forEach(function(id){var el=document.getElementById(id);el.addEventListener('input',function(){el.removeAttribute('aria-invalid');document.getElementById(id+'-err').textContent='';});});
  var form=document.getElementById('contactForm'),ok=document.getElementById('contactOk'),note=document.getElementById('formNotice');
  var g=function(id){return document.getElementById(id).value.trim();};

  function compose(){
    var subject='['+g('c-jenis')+'] '+g('c-lembaga')+' – Permintaan diskusi';
    var body=[
      "Assalamu'alaikum Tim Zakademia,",
      '',
      g('c-pesan'),
      '',
      '—',
      'Nama: '+g('c-nama'),
      'Lembaga: '+g('c-lembaga'),
      'Email: '+g('c-email'),
      'Yang ingin dibincangkan: '+g('c-jenis'),
      '',
      'Dikirim melalui formulir website Zakademia.'
    ].join('\n');
    return {subject:subject,body:body};
  }
  function gmailUrl(m){return 'https://mail.google.com/mail/?view=cm&fs=1&to='+encodeURIComponent(TO)+'&su='+encodeURIComponent(m.subject)+'&body='+encodeURIComponent(m.body);}
  function mailtoUrl(m){return 'mailto:'+TO+'?subject='+encodeURIComponent(m.subject)+'&body='+encodeURIComponent(m.body);}

  form.addEventListener('submit',function(e){
    e.preventDefault(); note.textContent='';
    var a=check('c-nama',g('c-nama').length>1,'Tulis nama Anda.');
    var b=check('c-lembaga',g('c-lembaga').length>1,'Tulis nama lembaga, atau "Perorangan".');
    var c=check('c-email',emailRe.test(g('c-email')),'Tulis alamat email yang lengkap.');
    var d=check('c-jenis',g('c-jenis')!=='','Pilih yang ingin dibincangkan.');
    var f=check('c-pesan',g('c-pesan').length>=15,'Ceritakan kebutuhan Anda dalam satu atau dua kalimat.');
    if(!(a&&b&&c&&d&&f)){var bad=form.querySelector('[aria-invalid="true"]');if(bad)bad.focus();return;}

    var m=compose(), gu=gmailUrl(m);
    document.getElementById('gmailLink').href=gu;
    document.getElementById('mailtoLink').href=mailtoUrl(m);

    // must run directly inside the click, otherwise browsers block the new tab
    var w=window.open(gu,'_blank');
    if(w){ try{w.opener=null;}catch(_){} }

    document.getElementById('okText').textContent = w
      ? 'Pesan Anda sudah terbuka di Gmail pada tab baru. Periksa sekali lagi, lalu tekan Kirim.'
      : 'Browser Anda menahan tab baru. Tekan tombol Buka Gmail di bawah untuk melanjutkan.';
    form.hidden=true; ok.hidden=false;
  });

  document.getElementById('againBtn').addEventListener('click',function(){ok.hidden=true;form.hidden=false;});
})();

// isi otomatis formulir kontak dari tautan konsultasi (?topik=…&pesan=…#kontak)
(function(){try{var q=new URLSearchParams(location.search),tp=q.get('topik'),ps=q.get('pesan');if(!tp&&!ps)return;
  var j=document.getElementById('c-jenis'),m=document.getElementById('c-pesan');
  if(j&&tp)[].forEach.call(j.options,function(o){if(o.text===tp)j.value=o.value||o.text;});
  if(m&&ps&&!m.value)m.value=ps;}catch(_){}})();

// pop-up ajakan mengikuti Saluran WhatsApp dan Instagram Zakademia
(function(){
  var WA_CHANNEL='';   // isi dengan tautan Saluran WhatsApp, mis. https://whatsapp.com/channel/XXXXXXXX
  var IG='https://instagram.com/zakademia.official';
  var KEY='zk-follow-tutup',DAYS=14;
  try{var t=+localStorage.getItem(KEY);if(t&&Date.now()-t<DAYS*864e5)return;}catch(_){}
  if(location.hash==='#admin')return;
  var shown=false,box;
  function close(remember){if(!box)return;box.classList.remove('on');setTimeout(function(){box&&box.remove();box=null;},350);if(remember)try{localStorage.setItem(KEY,String(Date.now()));}catch(_){}document.removeEventListener('keydown',esc);}
  function esc(e){if(e.key==='Escape')close(true);}
  var waIcon='<svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.1 5.1 0 0 0 1.1 2.7 11.7 11.7 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg>';
  var igIcon='<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>';
  function show(){if(shown)return;shown=true;
    box=document.createElement('aside');box.className='zk-follow';box.setAttribute('role','dialog');box.setAttribute('aria-labelledby','zkFollowT');
    box.innerHTML='<button class="x" type="button" aria-label="Tutup">✕</button><div class="fh"><img src="assets/images/logo-color.png" alt=""><h2 id="zkFollowT">Ikuti kabar Zakademia</h2></div>'+
      '<p>Kajian, rubrik literasi bersama Zaida dan Wafiy, dan kabar layanan terbaru, langsung di genggaman Anda.</p><div class="fa">'+
      (WA_CHANNEL?'<a class="wa" href="'+WA_CHANNEL+'" target="_blank" rel="noopener">'+waIcon+'Ikuti Saluran WhatsApp</a>':'')+
      '<a class="ig" href="'+IG+'" target="_blank" rel="noopener">'+igIcon+'Ikuti Instagram</a></div><button class="later" type="button">Nanti saja</button>';
    document.body.appendChild(box);requestAnimationFrame(function(){box.classList.add('on');});
    box.querySelector('.x').onclick=function(){close(true);};box.querySelector('.later').onclick=function(){close(true);};
    box.querySelectorAll('.fa a').forEach(function(a){a.addEventListener('click',function(){close(true);});});
    document.addEventListener('keydown',esc);}
  // muncul setelah 20 detik, atau setelah pengunjung menggulir lebih dari setengah halaman
  var timer=setTimeout(show,20000);
  window.addEventListener('scroll',function on(){var h=document.documentElement;if(h.scrollTop/(h.scrollHeight-h.clientHeight||1)>.5){clearTimeout(timer);show();window.removeEventListener('scroll',on);}},{passive:true});
})();


/* Sorotan: geser otomatis tiap 6 detik, berhenti saat disentuh/diarahkan kursor, bisa digeser dan dipakai keyboard. */
(function(){
  var root=document.getElementById('sorotan'); if(!root) return;
  var slides=[].slice.call(root.querySelectorAll('.so-slide')), dots=document.getElementById('soDots'), bar=document.getElementById('soBar');
  if(slides.length<2){ root.querySelector('.so-nav').hidden=true; root.querySelector('.so-bar').hidden=true; return; }
  var i=0, timer=null, t0=0, DUR=6000, paused=false, reduce=window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  slides.forEach(function(s,k){ var b=document.createElement('button'); b.type='button'; b.setAttribute('aria-label','Sorotan '+(k+1)); b.onclick=function(){ go(k); }; dots.appendChild(b); });
  function go(k){ i=(k+slides.length)%slides.length; slides.forEach(function(s,j){ s.classList.toggle('on',j===i); s.setAttribute('aria-hidden',j===i?'false':'true'); }); [].forEach.call(dots.children,function(d,j){ d.setAttribute('aria-current',j===i?'true':'false'); }); t0=Date.now(); }
  function tick(){ if(paused||reduce){ return; } var p=Math.min(1,(Date.now()-t0)/DUR); bar.style.width=(p*100)+'%'; if(p>=1) go(i+1); }
  go(0); if(!reduce) timer=setInterval(tick,100); else bar.parentNode.hidden=true;
  document.getElementById('soPrev').onclick=function(){ go(i-1); }; document.getElementById('soNext').onclick=function(){ go(i+1); };
  root.addEventListener('mouseenter',function(){ paused=true; }); root.addEventListener('mouseleave',function(){ paused=false; t0=Date.now(); });
  root.addEventListener('focusin',function(){ paused=true; }); root.addEventListener('focusout',function(){ paused=false; t0=Date.now(); });
  root.addEventListener('keydown',function(e){ if(e.key==='ArrowLeft') go(i-1); else if(e.key==='ArrowRight') go(i+1); });
  var x0=null; root.addEventListener('touchstart',function(e){ x0=e.touches[0].clientX; paused=true; },{passive:true});
  root.addEventListener('touchend',function(e){ if(x0===null) return; var dx=e.changedTouches[0].clientX-x0; if(Math.abs(dx)>40) go(dx<0?i+1:i-1); x0=null; paused=false; t0=Date.now(); });
  document.addEventListener('visibilitychange',function(){ paused=document.hidden||paused; if(!document.hidden){ paused=false; t0=Date.now(); } });
})();

/* Running text kabar zakat dan wakaf: diambil dari /berita/api.php, diperbarui tiap 10 menit. */
(function(){
  var k=document.getElementById('kabar'), t=document.getElementById('kabarT'); if(!k||!t) return;
  function rel(ts){ var s=Date.now()/1000-ts; if(s<3600) return Math.max(1,Math.floor(s/60))+' mnt lalu'; if(s<86400) return Math.floor(s/3600)+' jam lalu'; return Math.floor(s/86400)+' hari lalu'; }
  function muat(){ fetch('/berita/api.php',{cache:'no-cache'}).then(function(r){return r.json();}).then(function(j){ if(!j||!j.item||!j.item.length){ k.hidden=true; return; }
    var h=j.item.slice(0,18).map(function(i){ var a=document.createElement('a'); a.href=i.url||'/berita/'; if(i.url) {a.target='_blank'; a.rel='noopener';} a.textContent=i.judul; var s=document.createElement('small'); s.textContent=i.asal+' · '+rel(i.ts); a.appendChild(s); return a.outerHTML; }).join('');
    t.innerHTML=h+h; k.hidden=false; t.style.setProperty('--d', Math.max(40, Math.round(t.scrollWidth/2/60))+'s'); }).catch(function(){ k.hidden=true; }); }
  muat(); setInterval(muat, 600000);
})();
