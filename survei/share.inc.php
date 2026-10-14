<dialog class="dlg" id="share">
  <div class="hd"><h2>Bagikan survei</h2><button class="icon-btn" type="button" data-close aria-label="Tutup">✕</button></div>
  <div class="bd">
    <label class="lb">Status
      <select class="f" id="shStatus"><option value="draf">Draf: hanya Anda yang bisa melihat</option><option value="aktif">Aktif: menerima jawaban</option><option value="tutup">Ditutup: tidak menerima jawaban</option></select></label>
    <label class="lb">Nama tautan
      <span style="display:flex;align-items:center;border:1px solid var(--line);border-radius:12px;background:var(--bg);overflow:hidden"><span class="mute small" id="shBase" style="padding:0 2px 0 13px;white-space:nowrap"></span><input class="f" id="shSlug" style="border:0;border-radius:0;padding-left:0" autocomplete="off" spellcheck="false" aria-label="Nama tautan"></span>
      <span class="mute small" style="font-weight:400">Huruf kecil, angka, dan tanda hubung. Contoh: kepuasan-donatur-2026</span></label>
    <p class="err-box" id="shErr" hidden></p>
    <div style="display:flex;gap:8px;flex-wrap:wrap"><button class="btn" type="button" id="shSave">Simpan</button><button class="btn ghost" type="button" id="shCopy">Salin tautan</button><a class="btn ghost" id="shOpen" target="_blank" rel="noopener">Buka</a><a class="btn ghost" id="shWa" target="_blank" rel="noopener">WhatsApp</a></div>
    <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;border-top:1px solid var(--line);padding-top:14px">
      <div id="shQr" style="background:#fff;padding:7px;border-radius:12px;border:1px solid var(--line);width:156px;height:156px"></div>
      <div style="flex:1;min-width:180px"><b>Kode QR</b><p class="mute small" style="margin:4px 0 10px">Tempel di poster, spanduk, atau konter layanan.</p><button class="btn ghost sm" type="button" id="shQrDl">Unduh PNG</button></div>
    </div>
  </div>
</dialog>
<script src="/survei/assets/qrcode.js"></script>
<script>
var ZKShare=(function(){
  var BASE=<?= json_encode(sv_public_url('')) ?>,dlg=document.getElementById('share'),cur=null,onChange=null;
  function $(id){return document.getElementById(id);}
  function toast(m){var t=document.createElement('div');t.className='toast';t.textContent=m;document.body.appendChild(t);setTimeout(function(){t.remove();},2600);}
  document.querySelectorAll('dialog [data-close]').forEach(function(b){b.onclick=function(){b.closest('dialog').close();};});
  $('shBase').textContent=BASE.replace(/^https?:\/\//,'');
  function fill(){var url=BASE+cur.slug;$('shStatus').value=cur.status;$('shSlug').value=cur.slug;$('shOpen').href=url;
    $('shWa').href='https://wa.me/?text='+encodeURIComponent((cur.title||'Survei')+'\n'+url);
    var box=$('shQr');box.textContent='';if(!window.qrcode)return;var q=qrcode(0,'M');q.addData(url);q.make();
    var n=q.getModuleCount(),sc=Math.max(2,Math.floor(560/(n+8))),cv=document.createElement('canvas');cv.width=cv.height=(n+8)*sc;var g=cv.getContext('2d');g.fillStyle='#fff';g.fillRect(0,0,cv.width,cv.height);g.fillStyle='#0B4D33';
    for(var r=0;r<n;r++)for(var c=0;c<n;c++)if(q.isDark(r,c))g.fillRect((c+4)*sc,(r+4)*sc,sc,sc);cv.style.cssText='width:140px;height:140px;display:block';cv.setAttribute('role','img');cv.setAttribute('aria-label','Kode QR '+url);box.appendChild(cv);}
  $('shSave').onclick=function(){var e=$('shErr');e.hidden=true;
    fetch('api.php?a=atur',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF':CSRF},body:JSON.stringify({id:cur.id,slug:$('shSlug').value,status:$('shStatus').value})})
      .then(function(r){return r.json();}).then(function(j){if(!j.ok)throw new Error(j.error);cur.slug=j.slug;cur.status=j.status;fill();if(onChange)onChange(cur);toast(j.status==='aktif'?'Tersimpan. Survei aktif dan siap dibagikan.':'Tersimpan.');})
      .catch(function(x){e.textContent=x.message||'Gagal menyimpan.';e.hidden=false;});};
  $('shCopy').onclick=function(){var url=BASE+cur.slug;(navigator.clipboard?navigator.clipboard.writeText(url):Promise.reject()).then(function(){toast('Tautan disalin: '+url);},function(){prompt('Salin tautan ini:',url);});};
  $('shQrDl').onclick=function(){var c=document.querySelector('#shQr canvas'),i=document.querySelector('#shQr img');var src=c?c.toDataURL('image/png'):(i&&i.src);if(!src)return;var a=document.createElement('a');a.href=src;a.download='qr-'+cur.slug+'.png';document.body.appendChild(a);a.click();a.remove();};
  return {open:function(s,cb){cur=s;onChange=cb;$('shErr').hidden=true;fill();dlg.showModal();},base:BASE};
})();
</script>
