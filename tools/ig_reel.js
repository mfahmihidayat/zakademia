// Render Reels Zakademia 1080x1920 (MP4, 30 fps) dari adegan HTML. Pakai: node tools/ig_reel.js <keluaran-tanpa-ekstensi>
// Adegan diatur di SCENES; tiap bingkai digambar deterministik lalu digabung ffmpeg.
const fs=require('fs'),path=require('path'),os=require('os'),{execFileSync}=require('child_process');const {chromium}=require('playwright');
const {setFont}=require('./og_art.js');const {ART}=require('./ig_art.js');setFont('Instrument Sans');
const ROOT=path.resolve(__dirname,'..'),F=path.join(__dirname,'ig','fonts'),LOGO=`file://${ROOT}/assets/images/logo-color.png`;
const art=k=>`<svg width="100%" height="100%" viewBox="0 0 532 520" xmlns="http://www.w3.org/2000/svg">${ART[k]()}</svg>`;
const FPS=30;
// [mulai, selesai, label, judul, poin[], ilustrasi]
const SCENES=[
 [0,3.6,'ZakaTalk','Zakat, infak, sedekah, wakaf.<br><em>Apa bedanya?</em>',[],'bedanya'],
 [3.6,8.4,'1 · Zakat','Wajib.',['Ada nisab dan kadarnya','Untuk 8 golongan penerima (QS At-Taubah: 60)'],'nisab'],
 [8.4,12.6,'2 · Infak','Sukarela.',['Selalu berupa harta','Untuk kemaslahatan umum'],'infak'],
 [12.6,16.8,'3 · Sedekah','Sukarela, tak harus harta.',['Boleh berupa nonharta: tenaga, ilmu, senyum'],'sedekah'],
 [16.8,21.4,'4 · Wakaf','Pokoknya ditahan.',['Manfaatnya terus mengalir','Dikelola nazhir'],'pokok'],
 [21.4,25,'Tips','Sebutkan peruntukannya.',['Zakat, infak, sedekah, atau wakaf?','Simpan bukti setornya'],'tunaikan'],
];
const DUR=25;
const ff=(fam,w,file)=>`@font-face{font-family:'${fam}';font-weight:${w};src:url('file://${F}/${file}')}`;
const html=`<!doctype html><html><head><meta charset="utf-8"><style>
${ff('Fraunces',600,'fraunces-latin-600-normal.woff2')}${ff('Fraunces',700,'fraunces-latin-700-normal.woff2')}${['500','600','700'].map(w=>ff('Instrument Sans',w,`instrument-sans-latin-${w}-normal.woff2`)).join('')}
*{margin:0;box-sizing:border-box}body{width:1080px;height:1920px;overflow:hidden;background:#FFFFF0;font-family:'Instrument Sans';color:#10291F;position:relative}
.glow{position:absolute;right:-300px;top:-260px;width:900px;height:760px;border-radius:50%;background:#E2ECD6;filter:blur(100px)}
.brand{position:absolute;left:90px;top:150px;display:flex;align-items:center;gap:16px;font-weight:700;font-size:40px;color:#0B4D33}.brand img{width:62px}
.sc{position:absolute;left:0;right:0;top:0;bottom:0;opacity:0}
.eb{position:absolute;left:90px;top:300px;color:#B0852C;font-size:34px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
h1{position:absolute;left:90px;right:90px;top:360px;font-family:'Fraunces';font-weight:700;font-size:112px;line-height:1.04;letter-spacing:-.02em}
h1 em{font-style:italic;font-weight:600;color:#8A6418}
.ill{position:absolute;left:90px;right:90px;top:820px;height:640px;border-radius:80px 80px 80px 28px;overflow:hidden;border:12px solid #fff;box-shadow:0 34px 70px -28px rgba(8,40,26,.5);background:linear-gradient(135deg,#F2F5E6,#E3EEDC)}
.pts{position:absolute;left:90px;right:90px;top:1500px;display:flex;flex-direction:column;gap:18px}
.pt{display:flex;gap:20px;align-items:flex-start;font-size:42px;line-height:1.3;font-weight:600}.pt i{flex:none;width:52px;height:52px;margin-top:2px;border-radius:50%;background:#0B4D33;color:#F2D79B;display:grid;place-items:center;font-style:normal;font-size:28px}
.s0 .ill{top:900px;height:760px}
.foot{position:absolute;left:90px;right:90px;bottom:120px;display:flex;justify-content:space-between;font-size:32px;font-weight:600;color:#4A6355}.foot b{color:#0B4D33}
.bar{position:absolute;left:90px;right:90px;top:250px;height:8px;border-radius:4px;background:#E2ECD6}.bar i{display:block;height:100%;width:0;border-radius:4px;background:#C99A3B}
</style></head><body><div class="glow"></div><div class="brand"><img src="${LOGO}">Zakademia</div><div class="bar"><i id="pb"></i></div>
${SCENES.map((s,i)=>`<div class="sc s${i}" id="s${i}"><div class="eb">${s[2]}</div><h1>${s[3]}</h1><div class="ill">${art(s[5])}</div><div class="pts">${s[4].map(p=>`<div class="pt"><i>✓</i><span>${p}</span></div>`).join('')}</div></div>`).join('')}
<div class="foot"><span>zakademia.com</span><b>@zakademia.official</b></div>
<script>
const S=${JSON.stringify(SCENES.map(s=>[s[0],s[1]]))},D=${DUR};
const ease=x=>x<0?0:x>1?1:1-Math.pow(1-x,3);
window.frame=t=>{document.getElementById('pb').style.width=(Math.min(t/D,1)*100)+'%';
 S.forEach(([a,b],i)=>{const el=document.getElementById('s'+i);const last=i===S.length-1;
  const vin=ease((t-a)/.45),vout=last?1:1-ease((t-(b-.35))/.35);el.style.opacity=Math.min(vin,vout);
  const parts=[el.querySelector('.eb'),el.querySelector('h1'),el.querySelector('.ill'),...el.querySelectorAll('.pt')];
  parts.forEach((p,k)=>{const e=ease((t-a-.08*k-(k>=3?.35:0))/.5);p.style.opacity=e;p.style.transform='translateY('+((1-e)*40)+'px)'+(k===2?' scale('+(0.96+0.04*e+0.02*Math.max(0,(t-a)/(b-a)))+')':'');});});};
</script></body></html>`;
(async()=>{const out=path.resolve(process.argv[2]||path.join(ROOT,'assets/images/ig/reel'));const dir=fs.mkdtempSync(path.join(os.tmpdir(),'zk-reel-'));
 const tmp=path.join(dir,'reel.html');fs.writeFileSync(tmp,html);
 const b=await chromium.launch();const p=await b.newPage({viewport:{width:1080,height:1920}});await p.goto('file://'+tmp,{waitUntil:'load'});await p.evaluate(()=>document.fonts.ready);
 const N=Math.round(DUR*FPS);for(let f=0;f<N;f++){await p.evaluate(t=>window.frame(t),f/FPS);await p.screenshot({path:path.join(dir,`f${String(f).padStart(4,'0')}.jpg`),type:'jpeg',quality:90});}
 await p.evaluate(t=>window.frame(t),2.5);await p.screenshot({path:out+'-cover.jpg',type:'jpeg',quality:92});
 await b.close();
 execFileSync('ffmpeg',['-y','-loglevel','error','-framerate',String(FPS),'-i',path.join(dir,'f%04d.jpg'),'-f','lavfi','-i','anullsrc=r=44100:cl=stereo','-shortest','-c:v','libx264','-profile:v','high','-pix_fmt','yuv420p','-crf','20','-c:a','aac','-b:a','128k','-movflags','+faststart',out+'.mp4']);
 fs.rmSync(dir,{recursive:true});console.log('reel:',out+'.mp4',N,'bingkai');})();
