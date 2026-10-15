// Ekspor ilustrasi saja (latar transparan) untuk gambar utama artikel. Pakai: node tools/ilu_render.js kunci1 kunci2 ...
const fs=require('fs'),path=require('path'),os=require('os');const {chromium}=require('playwright');const {ART,setPlain}=require('./og_art.js');
const ROOT=path.resolve(__dirname,'..');const F=path.join(__dirname,'literasi','fonts');const OUT=path.join(ROOT,'assets/images/ilu');
(async()=>{fs.mkdirSync(OUT,{recursive:true});setPlain(true);const b=await chromium.launch();const p=await b.newPage({viewport:{width:532,height:520},deviceScaleFactor:2});
 for(const k of process.argv.slice(2)){const html=`<!doctype html><html><head><meta charset="utf-8"><style>
@font-face{font-family:'Plus Jakarta Sans';font-weight:500;src:url('file://${F}/plus-jakarta-sans-latin-500-normal.woff')}
@font-face{font-family:'Plus Jakarta Sans';font-weight:600 800;src:url('file://${F}/plus-jakarta-sans-latin-800-normal.woff')}
html,body{margin:0;background:transparent}</style></head><body><svg width="532" height="520" viewBox="0 0 532 520" xmlns="http://www.w3.org/2000/svg">${ART[k]()}</svg></body></html>`;
  const tmp=path.join(os.tmpdir(),'zk-ilu.html');fs.writeFileSync(tmp,html);await p.goto('file://'+tmp);await p.evaluate(()=>document.fonts.ready);
  await p.screenshot({path:path.join(OUT,k+'.png'),omitBackground:true});}
 await b.close();console.log('ilustrasi:',process.argv.length-2);})();
