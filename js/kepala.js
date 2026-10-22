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
  if(!k){ if(p.indexOf('/literasi')===0) k='literasi'; else if(p.indexOf('/belajar')===0||p.indexOf('/buku')===0) k='belajar'; else if(/^\/(asesmen|survei|analitik|rekomendasi|layanan)/.test(p)) k='lembaga'; else if(p.indexOf('/berita')===0) k='tentang'; }
  if(k){ var el=top.querySelector('[data-k="'+k+'"]'); if(el) el.setAttribute('aria-current', el.tagName==='A'?'page':'true'); }
  // tombol akun
  var a=document.getElementById('zkAkun');
  if(a && !a.hasAttribute('data-server')){ fetch('/asesmen/akun.php?api=me',{credentials:'same-origin',cache:'no-store'}).then(function(r){return r.json();}).then(function(j){ if(j&&j.user){ if(j.user.role==='admin'){ a.textContent='Pusat kendali'; a.href='/admin/'; } else { a.textContent='Akun saya'; a.href='/asesmen/akun.php'; } } }).catch(function(){}); }
})();
