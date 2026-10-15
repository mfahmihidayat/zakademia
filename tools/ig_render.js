// Render carousel Instagram Zakademia (1080x1350, JPEG). Pakai: node tools/ig_render.js tools/ig/<nama>.json
// JSON: {"out":"assets/images/ig/<nama>","art":"kalkulator","eyebrow":"ZakaTips","slides":[{type:'cover'|'isi'|'penutup', ...}]}
const fs=require('fs'),path=require('path'),os=require('os');const {chromium}=require('playwright');const {ART}=require('./og_art.js');
const ROOT=path.resolve(__dirname,'..');const F=path.join(__dirname,'literasi','fonts');const LOGO=`file://${ROOT}/assets/images/logo-color.png`;
const art=k=>`<svg width="100%" height="100%" viewBox="0 0 532 520" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">${ART[k]()}</svg>`;
const css=`@font-face{font-family:'PJ';font-weight:500;src:url('file://${F}/plus-jakarta-sans-latin-500-normal.woff')}
@font-face{font-family:'PJ';font-weight:600 800;src:url('file://${F}/plus-jakarta-sans-latin-800-normal.woff')}
*{margin:0;box-sizing:border-box}body{width:1080px;height:1350px;overflow:hidden;background:#FFFFF0;font-family:'PJ';color:#10291F;position:relative}
.glow{position:absolute;right:-260px;top:-220px;width:760px;height:620px;border-radius:50%;background:#E2ECD6;filter:blur(90px)}
.top{position:absolute;left:84px;right:84px;top:70px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:center;gap:14px;font-weight:800;font-size:34px;color:#0B4D33}.brand img{width:52px}
.pg{font-weight:800;font-size:26px;color:#4A6355;letter-spacing:.04em}
.eb{color:#C99A3B;font-size:28px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.foot{position:absolute;left:84px;right:84px;bottom:64px;display:flex;justify-content:space-between;align-items:center;font-size:26px;font-weight:600;color:#4A6355}
.foot b{color:#0B4D33}
.cover h1{position:absolute;left:84px;right:84px;top:218px;font-size:92px;line-height:1.06;letter-spacing:-.03em;font-weight:800}
.cover h1 em{font-style:normal;color:#8A6418}.cover .eb{position:absolute;left:84px;top:170px}
.cover .lead{position:absolute;left:84px;right:150px;top:560px;font-size:34px;line-height:1.4;font-weight:500;color:#33503F}
.cover .artbox{position:absolute;left:84px;right:84px;top:720px;height:480px;border-radius:72px 72px 72px 24px;overflow:hidden;box-shadow:0 30px 60px -24px rgba(8,40,26,.5);border:10px solid #fff;background:linear-gradient(135deg,#F2F5E6,#E3EEDC)}
.swipe{display:inline-flex;gap:10px;align-items:center;background:#0B4D33;color:#FFFFF0;padding:14px 26px;border-radius:99px;font-weight:800}
.isi .eb{position:absolute;left:84px;top:200px}
.isi h2{position:absolute;left:84px;right:84px;top:250px;font-size:68px;line-height:1.1;letter-spacing:-.02em;font-weight:800}
.isi .body{position:absolute;left:84px;right:84px;top:520px;display:flex;flex-direction:column;gap:26px}
.big{background:#0B4D33;color:#FFFFF0;border-radius:40px 40px 40px 14px;padding:40px 44px}.big small{display:block;font-size:28px;font-weight:600;color:#B9D9BE;margin-bottom:6px}
.big b{display:block;font-size:76px;letter-spacing:-.02em;line-height:1.05}.big b i{font-style:normal;font-size:36px;color:#F2D79B;font-weight:600}
.card{background:#fff;border:2px solid #DCE7D3;border-radius:32px 32px 32px 12px;padding:30px 36px;font-size:32px;line-height:1.4;font-weight:500}
.card b{font-weight:800;color:#0B4D33}.card .k{display:block;font-size:24px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#C99A3B;margin-bottom:6px}
.card .r{display:block;margin-top:6px;font-size:40px;font-weight:800;color:#0B4D33}
.rumus{background:#F2D79B;border-radius:32px;padding:38px 40px;font-size:54px;font-weight:800;text-align:center;color:#10291F;letter-spacing:-.01em}
.src{font-size:23px;color:#4A6355;font-weight:500;line-height:1.4}
.penutup{background:#0B4D33;color:#FFFFF0}.penutup .glow{background:#156B47;opacity:.7}
.penutup .brand{color:#FFFFF0}.penutup .brand img{background:#FFFFF0;border-radius:50%;padding:6px}.penutup .pg,.penutup .foot{color:#B9D9BE}.penutup .foot b{color:#F2D79B}
.penutup h2{position:absolute;left:84px;right:84px;top:250px;font-size:72px;line-height:1.1;letter-spacing:-.02em}
.penutup .eb{position:absolute;left:84px;top:200px}
.penutup .body{position:absolute;left:84px;right:84px;top:530px;display:flex;flex-direction:column;gap:24px}
.chk{display:flex;gap:22px;font-size:34px;line-height:1.38;font-weight:500}.chk i{flex:none;width:52px;height:52px;border-radius:50%;background:#C99A3B;color:#0B4D33;display:grid;place-items:center;font-style:normal;font-weight:800;font-size:28px}
.cta{margin-top:20px;background:#FFFFF0;color:#0B4D33;border-radius:36px 36px 36px 12px;padding:34px 40px;font-size:34px;line-height:1.35;font-weight:600}.cta b{font-weight:800}`;
function slide(s,i,n,d){const top=`<div class="glow"></div><div class="top"><div class="brand"><img src="${LOGO}">Zakademia</div><div class="pg">${i+1}/${n}</div></div>`;
 const foot=`<div class="foot"><span>zakademia.com</span>${s.type==='cover'?'<span class="swipe">Geser →</span>':s.type==='penutup'?'<b>@zakademia.official</b>':'<span>Geser →</span>'}</div>`;
 if(s.type==='cover')return `<body class="cover">${top}<div class="eb">${d.eyebrow}</div><h1>${s.h}</h1><p class="lead">${s.lead}</p><div class="artbox">${art(d.art)}</div>${foot}</body>`;
 return `<body class="${s.type}">${top}<div class="eb">${s.eb||d.eyebrow}</div><h2>${s.h}</h2><div class="body">${s.body}</div>${foot}</body>`;}
(async()=>{const d=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));const out=path.join(ROOT,d.out);fs.mkdirSync(path.dirname(out),{recursive:true});
 const b=await chromium.launch();const p=await b.newPage({viewport:{width:1080,height:1350}});const tmp=path.join(os.tmpdir(),'zk-ig.html');
 for(let i=0;i<d.slides.length;i++){fs.writeFileSync(tmp,`<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head>${slide(d.slides[i],i,d.slides.length,d)}</html>`);
  await p.goto('file://'+tmp,{waitUntil:'load'});await p.evaluate(()=>document.fonts.ready);
  const over=await p.evaluate(()=>{const f=document.querySelector('.foot').getBoundingClientRect().top;return [...document.querySelectorAll('.body,.lead,h1,h2')].some(e=>e.getBoundingClientRect().bottom>f-20);});
  if(over)console.warn('PERINGATAN: isi slide',i+1,'menabrak footer');
  await p.screenshot({path:`${out}-${i+1}.jpg`,type:'jpeg',quality:92});}
 await b.close();console.log('slide:',d.slides.length);})();
