/* Statistik kunjungan Zakademia: satu kiriman kecil per tampilan halaman, tanpa cookie pelacak. */
(function(){
  try{
    if(/admin|\/stat\//.test(location.pathname)) return;
    var d=JSON.stringify({p:location.pathname,r:document.referrer||''});
    if(navigator.sendBeacon){ navigator.sendBeacon('/stat/hit.php',new Blob([d],{type:'application/json'})); }
    else { var x=new XMLHttpRequest(); x.open('POST','/stat/hit.php',true); x.setRequestHeader('Content-Type','application/json'); x.send(d); }
  }catch(e){}
})();
