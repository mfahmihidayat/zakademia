// Ilustrasi vektor Zakademia untuk gambar pratinjau (kanvas 532x520). Palet merek.
const C={deep:'#0B4D33',deep2:'#156B47',leaf:'#6FAF78',soft:'#B9D9BE',mist:'#F2F5E6',gold:'#C99A3B',goldS:'#F2D79B',ivory:'#FFFFF0',ink:'#10291F',white:'#FFFFFF',paper:'#FBFAF2'};
const T=(x,y,s,t,o={})=>`<text x="${x}" y="${y}" font-family="Plus Jakarta Sans" font-weight="${o.w||800}" font-size="${s}" fill="${o.c||C.ink}" text-anchor="${o.a||'middle'}" letter-spacing="${o.ls||0}">${t}</text>`;
const shadow=`<filter id="sh" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="14" stdDeviation="14" flood-color="#0B4D33" flood-opacity=".22"/></filter>`;
let PLAIN=false;
const bg=(a=C.mist,b='#E3EEDC')=>PLAIN?`<defs>${shadow}</defs>`:`<defs>${shadow}<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${a}"/><stop offset="1" stop-color="${b}"/></linearGradient></defs><rect width="532" height="520" fill="url(#bg)"/>
<circle cx="440" cy="90" r="120" fill="${C.white}" opacity=".45"/><circle cx="70" cy="470" r="150" fill="${C.soft}" opacity=".35"/>`;
const coin=(x,y,r=26)=>`<g><ellipse cx="${x}" cy="${y+6}" rx="${r}" ry="${r*.42}" fill="#A87B26"/><ellipse cx="${x}" cy="${y}" rx="${r}" ry="${r*.42}" fill="${C.gold}"/><ellipse cx="${x}" cy="${y}" rx="${r*.68}" ry="${r*.27}" fill="none" stroke="${C.goldS}" stroke-width="3"/></g>`;
const stack=(x,y,n,r=26)=>Array.from({length:n},(_,i)=>coin(x,y-i*11,r)).join('');
// buku undang-undang
const lawBook=(x,y,w,h,col,label,sub)=>`<g filter="url(#sh)"><rect x="${x}" y="${y}" width="${w}" height="${h}" rx="10" fill="${col}"/><rect x="${x+w-14}" y="${y}" width="14" height="${h}" rx="6" fill="#00000022"/>
<rect x="${x+18}" y="${y+26}" width="${w-50}" height="4" rx="2" fill="${C.goldS}"/><rect x="${x+18}" y="${y+h-34}" width="${w-50}" height="4" rx="2" fill="${C.goldS}"/>
${T(x+(w-14)/2,y+h/2-6,30,label,{c:C.goldS})}${T(x+(w-14)/2,y+h/2+26,17,sub,{c:'#E9F2E6',w:600})}</g>`;
const gavel=(x,y,s=1,rot=-35)=>`<g transform="translate(${x} ${y}) rotate(${rot}) scale(${s})" filter="url(#sh)">
<rect x="-12" y="0" width="24" height="190" rx="10" fill="#8A5A2B"/><rect x="-12" y="150" width="24" height="40" rx="8" fill="#6E4520"/>
<rect x="-70" y="-46" width="140" height="58" rx="16" fill="#9C6631"/><rect x="-82" y="-50" width="22" height="66" rx="8" fill="${C.gold}"/><rect x="60" y="-50" width="22" height="66" rx="8" fill="${C.gold}"/></g>`;
const block=(x,y)=>`<g filter="url(#sh)"><rect x="${x}" y="${y}" width="150" height="30" rx="10" fill="#7A4D24"/><rect x="${x+12}" y="${y-18}" width="126" height="22" rx="8" fill="#9C6631"/></g>`;
const arrowUp=(x,y,h,col=C.gold)=>`<g><rect x="${x-9}" y="${y-h+30}" width="18" height="${h-30}" rx="6" fill="${col}"/><path d="M${x-30} ${y-h+36} L${x} ${y-h} L${x+30} ${y-h+36} Z" fill="${col}"/></g>`;
const building=(x,y,w,h,col=C.white)=>`<g filter="url(#sh)"><rect x="${x}" y="${y}" width="${w}" height="${h}" rx="8" fill="${col}"/>${Array.from({length:3},(_,r)=>Array.from({length:3},(_,c)=>`<rect x="${x+18+c*(w-36)/3+4}" y="${y+22+r*36}" width="${(w-36)/3-8}" height="22" rx="4" fill="${C.soft}"/>`).join('')).join('')}</g>`;
const mosque=(x,y,s=1)=>`<g transform="translate(${x} ${y}) scale(${s})" filter="url(#sh)"><rect x="-80" y="0" width="160" height="90" rx="8" fill="${C.white}"/><path d="M-60 0 Q0 -100 60 0 Z" fill="${C.deep}"/><rect x="-4" y="-78" width="8" height="18" fill="${C.gold}"/><circle cx="0" cy="-84" r="7" fill="${C.gold}"/>
<rect x="-110" y="-60" width="20" height="150" rx="6" fill="${C.white}"/><path d="M-112 -60 L-100 -88 L-88 -60 Z" fill="${C.deep}"/><rect x="90" y="-60" width="20" height="150" rx="6" fill="${C.white}"/><path d="M88 -60 L100 -88 L112 -60 Z" fill="${C.deep}"/>
<path d="M-18 90 L-18 50 Q0 30 18 50 L18 90 Z" fill="${C.deep2}"/></g>`;
const doc=(x,y,w,h,title,lines=4,seal=true)=>`<g filter="url(#sh)"><rect x="${x}" y="${y}" width="${w}" height="${h}" rx="12" fill="${C.paper}"/><rect x="${x}" y="${y}" width="${w}" height="44" rx="12" fill="${C.deep}"/><rect x="${x}" y="${y+30}" width="${w}" height="14" fill="${C.deep}"/>
${T(x+w/2,y+29,18,title,{c:C.goldS})}${Array.from({length:lines},(_,i)=>`<rect x="${x+22}" y="${y+66+i*24}" width="${w-44-(i%2)*30}" height="8" rx="4" fill="${C.soft}"/>`).join('')}
${seal?`<circle cx="${x+w-42}" cy="${y+h-40}" r="22" fill="${C.gold}"/><circle cx="${x+w-42}" cy="${y+h-40}" r="14" fill="none" stroke="${C.goldS}" stroke-width="3"/>`:''}</g>`;
const ART={
 // ZakaFlash: target Rakornas + revisi UU
 rakornas:()=>bg()+`
 <g filter="url(#sh)"><rect x="60" y="120" width="250" height="300" rx="20" fill="${C.white}"/></g>
 ${T(185,160,17,'ZIS-DSKL NASIONAL',{c:C.deep,ls:1})}
 <rect x="92" y="300" width="48" height="90" rx="8" fill="${C.soft}"/><rect x="162" y="282" width="48" height="108" rx="8" fill="${C.leaf}"/><rect x="232" y="196" width="48" height="194" rx="8" fill="none" stroke="${C.gold}" stroke-width="4" stroke-dasharray="10 7"/>
 ${T(116,292,16,'40,8',{c:C.ink})}${T(186,274,16,'44,7',{c:C.ink})}${T(256,188,18,'70',{c:'#8A6418'})}
 ${T(116,410,14,'2024',{c:'#4A6355',w:600})}${T(186,410,14,'2025',{c:'#4A6355',w:600})}${T(256,410,14,'2027',{c:'#4A6355',w:600})}

 ${lawBook(318,250,170,220,C.deep,'UU',' Zakat')}
 ${gavel(440,170,.72,-40)}`,
 // ZakaPedia: wakaf uang, melalui uang, sukuk
 wakaf:()=>bg()+`
 ${stack(120,330,5,40)}${T(120,400,17,'Wakaf uang',{c:C.deep})}
 <path d="M175 260 C 230 200, 260 180, 300 175" stroke="${C.gold}" stroke-width="6" fill="none" stroke-dasharray="2 12" stroke-linecap="round"/>
 <path d="M175 330 C 240 340, 270 360, 300 370" stroke="${C.gold}" stroke-width="6" fill="none" stroke-dasharray="2 12" stroke-linecap="round"/>
 ${mosque(400,160,.85)}${T(400,268,17,'Wakaf melalui uang',{c:C.deep})}
 ${doc(318,300,170,170,'SUKUK WAKAF',3)}`,
 // ZakaStory: rumah sakit mata
 mata:()=>bg()+`
 ${building(70,250,190,200)}<rect x="138" y="196" width="54" height="54" rx="10" fill="${C.white}" filter="url(#sh)"/><rect x="159" y="206" width="12" height="34" rx="3" fill="#C0392B"/><rect x="148" y="217" width="34" height="12" rx="3" fill="#C0392B"/>
 <g filter="url(#sh)"><path d="M250 250 Q380 120 510 250 Q380 380 250 250 Z" fill="${C.white}"/></g>
 <circle cx="380" cy="250" r="72" fill="${C.leaf}"/><circle cx="380" cy="250" r="44" fill="${C.deep}"/><circle cx="400" cy="228" r="16" fill="${C.white}" opacity=".9"/>
 <g fill="${C.gold}"><path d="M470 120 l8 22 22 8 -22 8 -8 22 -8 -22 -22 -8 22 -8z"/><path d="M290 110 l5 14 14 5 -14 5 -5 14 -5 -14 -14 -5 14 -5z"/></g>
 <rect x="0" y="450" width="532" height="70" fill="${C.soft}" opacity=".7"/>`,
 // ZakaTips: kalkulator 2,5% + emas
 kalkulator:()=>bg()+`
 <g filter="url(#sh)"><rect x="80" y="80" width="230" height="360" rx="26" fill="${C.deep}"/></g>
 <rect x="104" y="106" width="182" height="78" rx="12" fill="#E9F2E6"/>${T(270,162,40,'2,5%',{a:'end',c:C.deep})}
 ${Array.from({length:4},(_,r)=>Array.from({length:3},(_,c)=>`<rect x="${106+c*62}" y="${206+r*56}" width="50" height="44" rx="10" fill="${r==3&&c==2?C.gold:C.deep2}"/>`).join('')).join('')}
 <g filter="url(#sh)"><path d="M340 380 L370 330 L470 330 L500 380 Z" fill="${C.gold}"/><path d="M370 330 L380 316 L460 316 L470 330 Z" fill="${C.goldS}"/></g>${T(420,368,20,'85 g',{c:C.white})}
 ${stack(410,290,4,36)}`,
 // ZakaStat: grafik dan angka besar
 statistik:()=>bg()+`
 <g filter="url(#sh)"><rect x="60" y="90" width="410" height="330" rx="22" fill="${C.white}"/></g>
 ${[90,120,105,150,190,230].map((h,i)=>`<rect x="${100+i*58}" y="${380-h}" width="38" height="${h}" rx="8" fill="${i==5?C.deep:C.leaf}" opacity="${i==5?1:.55+i*.08}"/>`).join('')}
 <polyline points="119,280 177,255 235,268 293,220 351,185 409,140" fill="none" stroke="${C.gold}" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
 <circle cx="409" cy="140" r="10" fill="${C.gold}"/>
 <g filter="url(#sh)"><rect x="250" y="34" width="230" height="84" rx="18" fill="${C.deep}"/></g>${T(365,90,40,'Rp44,7 T',{c:C.goldS})}
 <g filter="url(#sh)"><circle cx="110" cy="430" r="46" fill="none" stroke="${C.deep}" stroke-width="14"/><rect x="138" y="462" width="70" height="22" rx="11" transform="rotate(40 138 462)" fill="${C.deep}"/></g>`,
 // ZakaTalk: UU zakat & UU wakaf + tanya
 bedanya:()=>bg()+`
 ${lawBook(60,200,150,230,C.deep,'UU','23/2011')}${lawBook(220,170,150,260,'#7A5A12','UU','41/2004')}
 <g filter="url(#sh)"><path d="M370 70 h120 a26 26 0 0 1 26 26 v70 a26 26 0 0 1 -26 26 h-70 l-30 30 v-30 h-20 a26 26 0 0 1 -26 -26 v-70 a26 26 0 0 1 26 -26z" fill="${C.white}"/></g>${T(430,160,80,'?',{c:C.gold})}
 ${stack(440,420,3,34)}<g transform="translate(420 300)"><path d="M0 20 C -40 -20, -10 -50, 0 -22 C 10 -50, 40 -20, 0 20 Z" fill="#C0392B" opacity=".85"/></g>`,
 // Layanan: Riset — kuesioner + QR
 survei:()=>bg()+`
 <g filter="url(#sh)"><rect x="90" y="90" width="250" height="350" rx="20" fill="${C.white}"/><rect x="165" y="70" width="100" height="40" rx="12" fill="${C.deep}"/></g>
 ${[0,1,2,3].map(i=>`<rect x="120" y="${150+i*66}" width="30" height="30" rx="8" fill="${i<3?C.deep:C.mist}" stroke="${C.deep}" stroke-width="3"/>${i<3?`<path d="M127 ${165+i*66} l7 7 13 -14" stroke="${C.white}" stroke-width="4" fill="none" stroke-linecap="round"/>`:''}<rect x="166" y="${158+i*66}" width="${140-(i%2)*36}" height="10" rx="5" fill="${C.soft}"/><rect x="166" y="${174+i*66}" width="${90}" height="8" rx="4" fill="${C.mist}"/>`).join('')}
 <g filter="url(#sh)"><rect x="330" y="250" width="160" height="160" rx="18" fill="${C.white}"/></g>
 ${[[0,0],[0,1],[1,0],[2,2],[3,1],[1,3],[3,3],[2,0],[0,3],[3,0]].map(([a,b])=>`<rect x="${352+a*30}" y="${272+b*30}" width="26" height="26" rx="4" fill="${C.deep}"/>`).join('')}`,
 // Layanan: Analitik — dasbor kesehatan
 dasbor:()=>bg()+`
 <g filter="url(#sh)"><rect x="56" y="80" width="420" height="300" rx="22" fill="${C.white}"/></g>
 <polyline points="86,250 150,250 175,190 205,300 235,160 262,250 330,250 355,215 380,250 446,250" fill="none" stroke="${C.deep}" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
 <rect x="86" y="300" width="110" height="52" rx="12" fill="${C.mist}"/>${T(141,334,22,'51,6%',{c:C.deep})}<rect x="212" y="300" width="110" height="52" rx="12" fill="${C.mist}"/>${T(267,334,22,'14 : 1',{c:C.deep})}<rect x="338" y="300" width="110" height="52" rx="12" fill="#FFF1D6"/>${T(393,334,22,'Rp12,7',{c:'#8A6418'})}
`,
 // Layanan: Asesmen — daftar periksa + radar
 asesmen:()=>bg()+`
 <g filter="url(#sh)"><rect x="70" y="90" width="230" height="330" rx="20" fill="${C.white}"/></g>
 ${[0,1,2,3,4].map(i=>`<circle cx="108" cy="${140+i*60}" r="16" fill="${i<4?C.leaf:C.mist}"/>${i<4?`<path d="M100 ${140+i*60} l6 6 11 -12" stroke="${C.white}" stroke-width="4" fill="none" stroke-linecap="round"/>`:''}<rect x="136" y="${134+i*60}" width="${130-(i%3)*20}" height="12" rx="6" fill="${C.soft}"/>`).join('')}
 <g filter="url(#sh)"><circle cx="390" cy="300" r="120" fill="${C.white}"/></g>
 ${[1,.66,.33].map(k=>`<polygon points="${[0,1,2,3,4,5].map(j=>{const a=Math.PI/3*j-Math.PI/2;return (390+Math.cos(a)*95*k)+','+(300+Math.sin(a)*95*k)}).join(' ')}" fill="none" stroke="${C.soft}" stroke-width="2"/>`).join('')}
 <polygon points="${[.9,.6,.8,.45,.7,.85].map((k,j)=>{const a=Math.PI/3*j-Math.PI/2;return (390+Math.cos(a)*95*k)+','+(300+Math.sin(a)*95*k)}).join(' ')}" fill="${C.leaf}" fill-opacity=".55" stroke="${C.deep}" stroke-width="4"/>`,
 // Layanan: Ruang Belajar — buku bertumpuk + lampu
 belajar:()=>bg()+`
 <g filter="url(#sh)"><rect x="90" y="370" width="300" height="50" rx="10" fill="${C.deep}"/><rect x="110" y="320" width="270" height="50" rx="10" fill="${C.gold}"/><rect x="80" y="270" width="290" height="50" rx="10" fill="${C.leaf}"/></g>
 <rect x="100" y="388" width="200" height="6" rx="3" fill="${C.goldS}"/><rect x="130" y="338" width="180" height="6" rx="3" fill="${C.white}" opacity=".7"/><rect x="100" y="288" width="200" height="6" rx="3" fill="${C.white}" opacity=".7"/>
 <g filter="url(#sh)"><path d="M120 270 L240 230 L360 270 L240 300 Z" fill="${C.ink}"/><rect x="232" y="230" width="16" height="14" fill="${C.ink}"/></g><path d="M330 262 v40" stroke="${C.gold}" stroke-width="5"/><circle cx="330" cy="306" r="8" fill="${C.gold}"/>
 <g filter="url(#sh)"><circle cx="420" cy="150" r="58" fill="${C.goldS}"/><circle cx="420" cy="150" r="40" fill="${C.white}"/></g><rect x="402" y="200" width="36" height="34" rx="8" fill="${C.deep}"/>
 <g stroke="${C.gold}" stroke-width="6" stroke-linecap="round"><path d="M420 70 v-22"/><path d="M490 150 h22"/><path d="M350 150 h-22"/><path d="M470 100 l16 -16"/><path d="M370 100 l-16 -16"/></g>`,
};
module.exports={ART,setPlain:v=>{PLAIN=v;}};
