// Render carousel Instagram Zakademia (1080x1350, JPEG). Pakai: node tools/ig_render.js tools/ig/<nama>.json
// JSON: {"out":"assets/images/ig/<nama>","art":"<kunci sampul>","eyebrow":"ZakaTips","slides":[{type:'cover'|'isi'|'penutup', art:'<kunci>', ...}]}
// Huruf: Fraunces (judul) + Instrument Sans (isi), berkas di tools/ig/fonts. Ilustrasi: og_art.js + ig_art.js.
const fs=require('fs'),path=require('path'),os=require('os');const {chromium}=require('playwright');
const {setFont}=require('./og_art.js');const {ART}=require('./ig_art.js');setFont('Instrument Sans');
const ROOT=path.resolve(__dirname,'..');const F=path.join(__dirname,'ig','fonts');const LOGO=`file://${ROOT}/assets/images/logo-color.png`;
const art=k=>`<svg width="100%" height="100%" viewBox="0 0 532 520" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg">${ART[k]()}</svg>`;
const ff=(fam,w,file)=>`@font-face{font-family:'${fam}';font-weight:${w};src:url('file://${F}/${file}')}`;
const css=ff('Fraunces',600,'fraunces-latin-600-normal.woff2')+ff('Fraunces',700,'fraunces-latin-700-normal.woff2')+
 ['400','500','600','700'].map(w=>ff('Instrument Sans',w,`instrument-sans-latin-${w}-normal.woff2`)).join('')+`
*{margin:0;box-sizing:border-box}body{width:1080px;height:1350px;overflow:hidden;background:#FFFFF0;font-family:'Instrument Sans';color:#10291F;position:relative}
h1,h2,.big b,.card .r,.rumus{font-family:'Fraunces';font-weight:700;letter-spacing:-.015em}
.glow{position:absolute;right:-260px;top:-220px;width:760px;height:620px;border-radius:50%;background:#E2ECD6;filter:blur(90px)}
.top{position:absolute;left:84px;right:84px;top:70px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:center;gap:14px;font-weight:700;font-size:34px;color:#0B4D33;letter-spacing:-.01em}.brand img{width:52px}
.pg{font-weight:600;font-size:26px;color:#4A6355;letter-spacing:.06em}
.eb{color:#B0852C;font-size:26px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
.foot{position:absolute;left:84px;right:84px;bottom:64px;display:flex;justify-content:space-between;align-items:center;font-size:26px;font-weight:600;color:#4A6355}
.foot b{color:#0B4D33}
.cover h1{position:absolute;left:84px;right:84px;top:218px;font-size:96px;line-height:1.02}
.cover h1 em{font-style:italic;color:#8A6418;font-weight:600}.cover .eb{position:absolute;left:84px;top:170px}
.cover .lead{position:absolute;left:84px;right:150px;top:560px;font-size:34px;line-height:1.4;font-weight:500;color:#33503F}
.artbox{border-radius:72px 72px 72px 24px;overflow:hidden;box-shadow:0 30px 60px -24px rgba(8,40,26,.5);border:10px solid #fff;background:linear-gradient(135deg,#F2F5E6,#E3EEDC)}
.cover .artbox{position:absolute;left:84px;right:84px;top:720px;height:480px}
.swipe{display:inline-flex;gap:10px;align-items:center;background:#0B4D33;color:#FFFFF0;padding:14px 26px;border-radius:99px;font-weight:700}
.isi .eb,.penutup .eb{position:absolute;left:84px;top:190px}
.isi h2,.penutup h2{position:absolute;left:84px;width:560px;top:240px;font-size:66px;line-height:1.08}
.ill{position:absolute;right:84px;top:160px;width:330px;height:330px;border-radius:56px 56px 56px 18px;border-width:8px}
.isi .body,.penutup .body{position:absolute;left:84px;right:84px;top:540px;display:flex;flex-direction:column;gap:24px}
.big{background:#0B4D33;color:#FFFFF0;border-radius:40px 40px 40px 14px;padding:36px 44px}.big small{display:block;font-size:28px;font-weight:600;color:#B9D9BE;margin-bottom:4px}
.big b{display:block;font-size:76px;line-height:1.05}.big b i{font-style:italic;font-size:38px;color:#F2D79B;font-weight:600}
.card{background:#fff;border:2px solid #DCE7D3;border-radius:32px 32px 32px 12px;padding:28px 36px;font-size:32px;line-height:1.38;font-weight:500}
.card b{font-weight:700;color:#0B4D33}.card .k{display:block;font-size:23px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#B0852C;margin-bottom:6px}
.card .r{display:block;margin-top:6px;font-size:42px;color:#0B4D33}
.rumus{background:#F2D79B;border-radius:32px;padding:36px 40px;font-size:56px;text-align:center;color:#10291F}
.src{font-size:23px;color:#4A6355;font-weight:500;line-height:1.4}
.penutup{background:#0B4D33;color:#FFFFF0}.penutup .glow{background:#156B47;opacity:.7}
.penutup .brand{color:#FFFFF0}.penutup .brand img{background:#FFFFF0;border-radius:50%;padding:6px}.penutup .pg,.penutup .foot{color:#B9D9BE}.penutup .foot b{color:#F2D79B}
.penutup .eb{color:#F2D79B}.penutup .ill{border-color:#156B47}
.chk{display:flex;gap:22px;font-size:34px;line-height:1.36;font-weight:500}.chk i{flex:none;width:52px;height:52px;border-radius:50%;background:#C99A3B;color:#0B4D33;display:grid;place-items:center;font-style:normal;font-weight:700;font-size:28px}
.cta{margin-top:12px;background:#FFFFF0;color:#0B4D33;border-radius:36px 36px 36px 12px;padding:32px 40px;font-size:34px;line-height:1.35;font-weight:600}.cta b{font-weight:700}`;
function slide(s,i,n,d){const top=`<div class="glow"></div><div class="top"><div class="brand"><img src="${LOGO}">Zakademia</div><div class="pg">${i+1}/${n}</div></div>`;
 const foot=`<div class="foot"><span>zakademia.com</span>${s.type==='cover'?'<span class="swipe">Geser →</span>':s.type==='penutup'?'<b>@zakademia.official</b>':'<span>Geser →</span>'}</div>`;
 if(s.type==='cover')return `<body class="cover">${top}<div class="eb">${d.eyebrow}</div><h1>${s.h}</h1><p class="lead">${s.lead}</p><div class="artbox">${art(s.art||d.art)}</div>${foot}</body>`;
 return `<body class="${s.type}">${top}<div class="eb">${s.eb||d.eyebrow}</div><h2>${s.h}</h2>${s.art?`<div class="artbox ill">${art(s.art)}</div>`:''}<div class="body">${s.body}</div>${foot}</body>`;}
(async()=>{const d=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));const out=path.join(ROOT,d.out);fs.mkdirSync(path.dirname(out),{recursive:true});
 const b=await chromium.launch();const p=await b.newPage({viewport:{width:1080,height:1350}});const tmp=path.join(os.tmpdir(),'zk-ig.html');
 for(let i=0;i<d.slides.length;i++){fs.writeFileSync(tmp,`<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head>${slide(d.slides[i],i,d.slides.length,d)}</html>`);
  await p.goto('file://'+tmp,{waitUntil:'load'});await p.evaluate(()=>document.fonts.ready);
  const warn=await p.evaluate(()=>{const f=document.querySelector('.foot').getBoundingClientRect().top,w=[];
   document.querySelectorAll('.body,.lead,h1,h2').forEach(e=>{if(e.getBoundingClientRect().bottom>f-20)w.push('menabrak footer')});
   const h=document.querySelector('h2'),b=document.querySelector('.body');if(h&&b&&h.getBoundingClientRect().bottom>b.getBoundingClientRect().top-20)w.push('judul menabrak isi');return w;});
  if(warn.length)console.warn('PERINGATAN slide',i+1,warn.join(', '));
  await p.screenshot({path:`${out}-${i+1}.jpg`,type:'jpeg',quality:92});}
 await b.close();console.log('slide:',d.slides.length);})();
