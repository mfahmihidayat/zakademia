// Render gambar pratinjau Zakademia dari daftar pekerjaan JSON. Pakai: node tools/og_render.js jobs.json
const fs=require('fs'),path=require('path');const {chromium}=require('playwright');const {ART}=require('./og_art.js');
const ROOT=path.resolve(__dirname,'..');const F=path.join(__dirname,'literasi','fonts');
const esc=s=>String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;');
function html(j){const words=[];j.lines.forEach((l,i)=>l.split(' ').forEach(w=>words.push(`<span class="${i===j.lines.length-1?'g':''}">${esc(w)}</span>`)));
 return `<!doctype html><html><head><meta charset="utf-8"><style>
@font-face{font-family:'Plus Jakarta Sans';font-weight:500;src:url('file://${F}/plus-jakarta-sans-latin-500-normal.woff')}
@font-face{font-family:'Plus Jakarta Sans';font-weight:600 800;src:url('file://${F}/plus-jakarta-sans-latin-800-normal.woff')}
*{margin:0;box-sizing:border-box}body{width:1200px;height:630px;overflow:hidden;background:#FFFFF0;font-family:'Plus Jakarta Sans';position:relative}
.glow{position:absolute;left:-300px;top:380px;width:700px;height:520px;border-radius:50%;background:#E2ECD6;filter:blur(90px)}
.leaf{position:absolute;left:640px;top:250px;width:680px;height:680px;border-radius:50%;background:#0B4D33}
.brand{position:absolute;left:80px;top:58px;display:flex;align-items:center;gap:12px;font-weight:800;font-size:28px;color:#0B4D33}.brand img{width:42px}
.txt{position:absolute;left:80px;top:0;bottom:0;width:490px;display:flex;flex-direction:column;justify-content:center;padding-top:30px}
.eb{display:flex;align-items:center;gap:14px;margin-bottom:22px}.eb img{width:60px;height:60px;border-radius:50%}
.eb b{display:block;color:#C99A3B;font-size:21px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.eb i{font-style:normal;color:#4A6355;font-size:19px;font-weight:500}
h1{font-size:${j.size||54}px;line-height:1.13;letter-spacing:-.02em;color:#10291F;font-weight:800}h1 .g{color:#8A6418}
.ft{margin-top:20px;color:#4A6355;font-size:21px;font-weight:500}
.frame{position:absolute;left:612px;top:36px;width:548px;height:536px;border-radius:72px 72px 72px 24px;background:#FFFFF0;box-shadow:0 26px 50px -20px rgba(8,40,26,.55)}
.art{position:absolute;left:8px;top:8px;width:532px;height:520px;border-radius:64px 64px 64px 16px;overflow:hidden}
.badge{position:absolute;left:562px;top:466px;width:116px;height:116px;border-radius:50%;background:#C99A3B;display:grid;place-items:center}.badge div{width:104px;height:104px;border-radius:50%;background:#fff;display:grid;place-items:center}.badge img{width:64px}
</style></head><body><div class="glow"></div><div class="leaf"></div>
<div class="brand"><img src="file://${ROOT}/assets/images/logo-color.png">Zakademia</div>
<div class="txt"><div class="eb">${j.icon?`<img src="file://${j.icon}">`:''}<div><b>${esc(j.eyebrow)}</b><i>${esc(j.sub||'')}</i></div></div><h1>${words.join(' ')}</h1><div class="ft">${esc(j.footer)}</div></div>
<div class="frame"><div class="art"><svg width="532" height="520" viewBox="0 0 532 520" xmlns="http://www.w3.org/2000/svg">${ART[j.art]()}</svg></div></div>
<div class="badge"><div><img src="file://${ROOT}/assets/images/logo-color.png"></div></div></body></html>`;}
(async()=>{const jobs=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));const b=await chromium.launch();const p=await b.newPage({viewport:{width:1200,height:630}});
 for(const j of jobs){let size=54;for(;;){const tmp=path.join(require('os').tmpdir(),'zk-og.html');fs.writeFileSync(tmp,html({...j,size}));await p.goto('file://'+tmp,{waitUntil:'load'});await p.evaluate(()=>document.fonts.ready);
   const h=await p.evaluate(()=>document.querySelector('h1').getBoundingClientRect().height);if(h<=3*size*1.13+4||size<=40)break;size-=2;}
  await p.screenshot({path:j.out,type:'png'});}
 await b.close();console.log('gambar:',jobs.length);})();
