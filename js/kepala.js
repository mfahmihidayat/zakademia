/* Kepala halaman Zakademia: menu ponsel, submenu, tombol akun sesuai status masuk, penanda halaman aktif. */
(function(){
  var top=document.getElementById('zkTop'); if(!top) return;
  var burger=document.getElementById('zkBurger'), menu=document.getElementById('zkMenu');
  if(burger) burger.addEventListener('click',function(){ var on=menu.classList.toggle('open'); burger.setAttribute('aria-expanded',on?'true':'false'); });
  top.querySelectorAll('.zk-dd>button').forEach(function(b){ b.addEventListener('click',function(e){ e.stopPropagation(); var d=b.parentNode, on=!d.classList.contains('open'); top.querySelectorAll('.zk-dd.open').forEach(function(x){x.classList.remove('open'); x.querySelector('button').setAttribute('aria-expanded','false');}); if(on){ d.classList.add('open'); b.setAttribute('aria-expanded','true'); } }); });
  document.addEventListener('click',function(){ top.querySelectorAll('.zk-dd.open').forEach(function(x){x.classList.remove('open'); x.querySelector('button').setAttribute('aria-expanded','false');}); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape'){ top.querySelectorAll('.zk-dd.open').forEach(function(x){x.classList.remove('open');}); if(menu&&menu.classList.contains('open')){ menu.classList.remove('open'); burger.setAttribute('aria-expanded','false'); } } });
  // halaman aktif
  var k=document.body.getAttribute('data-zk')||''; var p=location.pathname;
  if(!k){ if(p.indexOf('/literasi')===0||p.indexOf('/berita')===0) k='literasi'; else if(p.indexOf('/belajar')===0||p.indexOf('/buku')===0) k='belajar'; else if(/^\/(asesmen|survei|analitik|rekomendasi|layanan)/.test(p)) k='lembaga'; }
  if(k){ var el=top.querySelector('[data-k="'+k+'"]'); if(el) el.setAttribute('aria-current', el.tagName==='A'?'page':'true'); }
  // tombol akun
  var a=document.getElementById('zkAkun');
  if(a && !a.hasAttribute('data-server')){ fetch('/asesmen/akun.php?api=me',{credentials:'same-origin',cache:'no-store'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.user){ if(j.user.role==='admin'){ a.textContent='Pusat kendali'; a.href='/admin/'; } else { a.textContent='Akun saya'; a.href='/asesmen/akun.php'; } } }).catch(function(){}); }
})();

/* Sapaan WhatsApp: tombol melayang di setiap halaman, kartu pesan dengan pilihan topik, lalu membuka WhatsApp Zakademia. */
(function(){
  if(location.hash==='#admin'||document.body.hasAttribute('data-tanpa-wa')) return;
  var NOMOR='6285692090699', KEY='zk-wa-topik';
  var TOPIK=['Riset Kebutuhan','Data & Analitik','Pendampingan Strategi','Ruang Belajar','Buku digital','Hal lain'];
  var ikon='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.3-.4.8-1.4.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.1 4.9 4.3 1.8.8 2.5.8 3.4.7.6-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2 0-.1-.2-.2-.5-.3z"/></svg>';
  var root=document.createElement('div'); root.className='zk-wa';
  root.innerHTML=
    '<div class="zk-wa-card" id="zkWaCard" role="dialog" aria-labelledby="zkWaT" aria-hidden="true">'+
      '<div class="zk-wa-head"><img src="/assets/images/logo-color.png" alt=""><div><b id="zkWaT">Zakademia</b><small>Biasanya membalas pada jam kerja</small></div><button class="x" type="button" aria-label="Tutup">✕</button></div>'+
      '<div class="zk-wa-body">'+
        '<div class="zk-wa-bubble">Assalamu’alaikum. Ada yang ingin dibincangkan tentang zakat, wakaf, atau kebutuhan lembaga Anda?<small>Tim Zakademia</small></div>'+
        '<div class="zk-wa-topik" role="group" aria-label="Pilih topik"></div>'+
        '<form class="zk-wa-form"><label class="sr-only" for="zkWaPesan" style="position:absolute;left:-9999px">Pesan</label><textarea id="zkWaPesan" placeholder="Tulis pesan Anda…"></textarea>'+
        '<button class="zk-wa-kirim" type="submit">'+ikon+'Lanjutkan di WhatsApp</button></form>'+
        '<p class="n">Pesan dibuka di aplikasi WhatsApp menuju <a href="https://wa.me/'+NOMOR+'" target="_blank" rel="noopener">+62 856-9209-0699</a>.</p>'+
      '</div></div>'+
    '<button class="zk-wa-btn" type="button" aria-expanded="false" aria-controls="zkWaCard">'+ikon+'<span class="t">Hubungi kami</span></button>';
  document.body.appendChild(root);
  var btn=root.querySelector('.zk-wa-btn'), card=root.querySelector('.zk-wa-card'), box=root.querySelector('.zk-wa-topik'), ta=root.querySelector('textarea'), topik='';
  TOPIK.forEach(function(t){ var b=document.createElement('button'); b.type='button'; b.textContent=t; b.setAttribute('aria-pressed','false');
    b.onclick=function(){ var on=b.getAttribute('aria-pressed')!=='true'; box.querySelectorAll('button').forEach(function(x){x.setAttribute('aria-pressed','false');}); b.setAttribute('aria-pressed',on?'true':'false'); topik=on?t:''; ta.focus(); };
    box.appendChild(b); });
  function buka(on){ root.classList.toggle('open',on); btn.setAttribute('aria-expanded',on?'true':'false'); card.setAttribute('aria-hidden',on?'false':'true'); if(on) setTimeout(function(){ ta.focus(); },50); }
  btn.onclick=function(){ buka(!root.classList.contains('open')); };
  root.querySelector('.x').onclick=function(){ buka(false); btn.focus(); };
  document.addEventListener('keydown',function(e){ if(e.key==='Escape'&&root.classList.contains('open')){ buka(false); btn.focus(); } });
  document.addEventListener('click',function(e){ if(root.classList.contains('open')&&!root.contains(e.target)) buka(false); });
  root.querySelector('form').onsubmit=function(e){ e.preventDefault();
    var isi=ta.value.trim(), judul=document.title.replace(/\s*[|·].*$/,'').trim();
    var teks='Assalamu’alaikum, Zakademia.'+(topik?' Saya ingin berbincang tentang '+topik+'.':'')+(isi?'\n\n'+isi:'')+'\n\n(dari halaman '+judul+', '+location.href+')';
    window.open('https://wa.me/'+NOMOR+'?text='+encodeURIComponent(teks),'_blank','noopener'); buka(false); };
})();
