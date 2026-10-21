/* Statistik kunjungan Zakademia: satu kiriman kecil per tampilan halaman, tanpa cookie pelacak. */
(function(){
  var sent={};
  function kirim(){ try{
    if(/admin|\/stat\//.test(location.pathname)) return;
    var h=(location.pathname==='/'||/\/index\.html$/.test(location.pathname))&&/^#(layanan|kajian|literasi|tentang|kontak)/.test(location.hash)?'/'+location.hash.slice(1).split('/')[0]+'/':location.pathname;
    var d=JSON.stringify({p:h,r:document.referrer||''});
    if(navigator.sendBeacon){ navigator.sendBeacon('/stat/hit.php',new Blob([d],{type:'application/json'})); }
    else { var x=new XMLHttpRequest(); x.open('POST','/stat/hit.php',true); x.setRequestHeader('Content-Type','application/json'); x.send(d); }
  }catch(e){} }
  kirim();
  window.addEventListener('hashchange',function(){ var k=location.hash.split('/')[0]; if(sent[k]) return; sent[k]=1; kirim(); });
})();
