// Ilustrasi seri Oktober 2026 (kanvas 532x520), gaya sama dengan ig_art.js.
const {ART}=require('./ig_art.js');const {H}=require('./og_art.js');const {C,T,bg,coin,stack,mosque,doc}=H;
const person=(x,y,s=1,col=C.deep2)=>`<g transform="translate(${x} ${y}) scale(${s})"><circle cx="0" cy="-34" r="16" fill="${col}"/><path d="M-26 10 Q-26 -14 0 -14 Q26 -14 26 10 Z" fill="${col}"/></g>`;
const check=(x,y,r=22,bgc=C.leaf)=>`<circle cx="${x}" cy="${y}" r="${r}" fill="${bgc}"/><path d="M${x-r*.45} ${y} l${r*.32} ${r*.32} ${r*.6} -${r*.62}" stroke="${C.white}" stroke-width="${r*.22}" fill="none" stroke-linecap="round" stroke-linejoin="round"/>`;
const sparkle=(x,y,k=1)=>`<path transform="translate(${x} ${y}) scale(${k})" d="M0 -22 l6 16 16 6 -16 6 -6 16 -6 -16 -16 -6 16 -6z" fill="${C.gold}"/>`;
const bar=(x,y,w=120,h=44)=>`<g filter="url(#sh)"><path d="M${x} ${y+h} L${x+18} ${y} H${x+w-18} L${x+w} ${y+h} Z" fill="${C.gold}"/><path d="M${x+18} ${y} H${x+w-18} L${x+w-26} ${y+10} H${x+26} Z" fill="${C.goldS}"/></g>`;
const cal=(x,y,label)=>`<g filter="url(#sh)"><rect x="${x}" y="${y}" width="150" height="150" rx="16" fill="${C.white}"/><rect x="${x}" y="${y}" width="150" height="42" rx="16" fill="${C.deep}"/><rect x="${x}" y="${y+28}" width="150" height="14" fill="${C.deep}"/><rect x="${x+32}" y="${y-12}" width="10" height="26" rx="5" fill="${C.ink}"/><rect x="${x+108}" y="${y-12}" width="10" height="26" rx="5" fill="${C.ink}"/></g>${T(x+75,y+108,46,label,{c:C.deep})}`;
const ART2={
 // Emas batangan bertumpuk + label 85 g + kalender haul
 emas:()=>bg()+`
 ${bar(90,360,150)}${bar(250,360,150)}${bar(170,312,150)}
 <g filter="url(#sh)"><rect x="196" y="230" width="98" height="40" rx="20" fill="${C.deep}"/></g>${T(245,258,22,'85 g',{c:C.goldS})}
 ${cal(330,70,'12')}${T(405,248,18,'bulan (haul)',{c:'#4A6355',w:700})}
 ${sparkle(110,120,.9)}${sparkle(470,330,.7)}`,
 // Buku tabungan + koin + kalender
 tabungan:()=>bg()+`
 <g filter="url(#sh)" transform="rotate(-8 200 260)"><rect x="80" y="130" width="240" height="300" rx="18" fill="${C.deep}"/><rect x="104" y="160" width="192" height="40" rx="8" fill="${C.deep2}"/></g>
 ${T(196,188,22,'TABUNGAN',{c:C.goldS,ls:2})}
 <g filter="url(#sh)"><rect x="210" y="210" width="230" height="200" rx="14" fill="${C.paper}"/></g>
 ${[0,1,2,3].map(i=>`<rect x="232" y="${240+i*36}" width="110" height="10" rx="5" fill="${C.soft}"/><rect x="360" y="${240+i*36}" width="58" height="10" rx="5" fill="${i==3?C.gold:C.leaf}"/>`).join('')}
 ${stack(460,480,4,30)}${sparkle(440,110,.9)}`,
 // Toko + kotak stok + nota
 toko:()=>bg()+`
 <g filter="url(#sh)"><rect x="70" y="170" width="290" height="240" rx="12" fill="${C.white}"/></g>
 <path d="M60 170 h310 l-20 -70 h-270 z" fill="${C.deep}"/>${[0,1,2,3,4].map(i=>`<path d="M${80+i*56} 100 h28 l-6 70 h-28 z" fill="${C.leaf}" opacity=".55"/>`).join('')}
 <rect x="104" y="280" width="90" height="130" rx="6" fill="${C.soft}"/><rect x="220" y="240" width="110" height="80" rx="8" fill="#E9F2E6"/>
 <g filter="url(#sh)"><rect x="250" y="330" width="80" height="70" rx="6" fill="#C9A26B"/><rect x="290" y="300" width="70" height="60" rx="6" fill="#B88A50"/></g>
 <g filter="url(#sh)"><rect x="380" y="200" width="110" height="200" rx="10" fill="${C.paper}"/></g>
 ${T(435,236,16,'NOTA',{c:C.deep,ls:2})}${[0,1,2,3].map(i=>`<rect x="398" y="${256+i*28}" width="74" height="8" rx="4" fill="${C.soft}"/>`).join('')}<rect x="398" y="370" width="74" height="12" rx="6" fill="${C.gold}"/>
 ${sparkle(470,120,.8)}`,
 // Piring nasi + kalender hari puasa
 fidyah:()=>bg()+`
 <g filter="url(#sh)"><ellipse cx="200" cy="330" rx="150" ry="62" fill="${C.white}"/></g><ellipse cx="200" cy="322" rx="112" ry="42" fill="#F3F1E4"/>
 <path d="M120 318 Q200 230 280 318 Z" fill="${C.white}" stroke="#E7E2CC" stroke-width="3"/>${[0,1,2,3,4,5].map(i=>`<ellipse cx="${150+i*20}" cy="${300-(i%2)*14}" rx="6" ry="3" fill="#E7E2CC"/>`).join('')}
 <g filter="url(#sh)"><rect x="300" y="80" width="170" height="170" rx="16" fill="${C.white}"/><rect x="300" y="80" width="170" height="40" rx="16" fill="${C.deep}"/><rect x="300" y="106" width="170" height="14" fill="${C.deep}"/></g>
 ${Array.from({length:12},(_,i)=>{const x=322+(i%4)*36,y=140+Math.floor(i/4)*34;return i<7?`<rect x="${x}" y="${y}" width="24" height="22" rx="5" fill="${C.leaf}"/><path d="M${x+5} ${y+11} l5 5 9 -10" stroke="#fff" stroke-width="3" fill="none"/>`:`<rect x="${x}" y="${y}" width="24" height="22" rx="5" fill="${C.soft}"/>`}).join('')}
 ${stack(440,440,3,28)}${sparkle(90,110,.8)}`,
 // Delapan asnaf melingkar
 asnaf:()=>bg()+`
 <circle cx="266" cy="260" r="160" fill="none" stroke="${C.soft}" stroke-width="4" stroke-dasharray="4 14"/>
 ${Array.from({length:8},(_,i)=>{const a=-Math.PI/2+i*Math.PI/4,x=266+160*Math.cos(a),y=260+160*Math.sin(a);return `<g filter="url(#sh)"><circle cx="${x}" cy="${y}" r="40" fill="${C.white}"/></g>${person(x,y+22,.8,i%2?C.leaf:C.deep2)}`}).join('')}
 <g filter="url(#sh)"><circle cx="266" cy="260" r="62" fill="${C.deep}"/></g>${coin(266,250,34)}${T(266,300,20,'8',{c:C.goldS})}`,
 // Sawah, padi, hujan dan irigasi
 sawah:()=>bg()+`
 <path d="M0 360 Q266 300 532 360 V520 H0 Z" fill="${C.leaf}"/><path d="M0 420 Q266 370 532 420 V520 H0 Z" fill="${C.deep2}"/>
 <path d="M30 470 Q266 430 500 470" stroke="#9FD3E6" stroke-width="10" fill="none" stroke-linecap="round"/>
 ${[0,1,2,3,4,5,6].map(i=>{const x=70+i*62;return `<path d="M${x} 400 Q${x-4} 330 ${x+10} 280" stroke="${C.deep}" stroke-width="5" fill="none"/>${[0,1,2,3].map(k=>`<ellipse cx="${x+8+k*3}" cy="${288+k*14}" rx="8" ry="4" fill="${C.gold}" transform="rotate(-30 ${x+8+k*3} ${288+k*14})"/>`).join('')}`}).join('')}
 <g filter="url(#sh)"><ellipse cx="150" cy="120" rx="80" ry="40" fill="${C.white}"/><ellipse cx="200" cy="100" rx="60" ry="40" fill="${C.white}"/></g>
 ${[0,1,2,3].map(i=>`<path d="M${130+i*28} 170 l-6 18" stroke="#7EC0DA" stroke-width="6" stroke-linecap="round"/>`).join('')}
 <g filter="url(#sh)"><rect x="350" y="80" width="120" height="70" rx="35" fill="${C.deep}"/></g>${T(410,126,28,'653 kg',{c:C.goldS})}`,
 // Formulir SPT + bukti setor
 pajak:()=>bg()+doc(70,80,220,300,'SPT TAHUNAN',5,false)+`
 <g filter="url(#sh)" transform="rotate(6 360 300)"><rect x="260" y="200" width="200" height="230" rx="12" fill="${C.white}"/><rect x="260" y="200" width="200" height="40" rx="12" fill="${C.gold}"/><rect x="260" y="226" width="200" height="14" fill="${C.gold}"/></g>
 ${T(362,228,14,'BUKTI SETOR ZAKAT',{c:C.ink,ls:1})}
 ${[0,1,2].map(i=>`<rect x="${286+i*2}" y="${266+i*34}" width="140" height="10" rx="5" fill="${C.soft}"/>`).join('')}
 ${check(410,390,26)}${sparkle(470,110,.8)}`,
 // Nazhir: aset wakaf dilindungi + laporan
 nazhir:()=>bg()+`
 <g filter="url(#sh)"><path d="M180 100 L300 140 V250 Q300 340 180 390 Q60 340 60 250 V140 Z" fill="${C.deep}"/></g>
 <circle cx="180" cy="262" r="78" fill="${C.soft}"/><g transform="translate(180 300) scale(.62)">${mosque(0,0,1).replace('filter="url(#sh)"','')}</g>
 ${doc(300,170,180,230,'LAPORAN',4,false)}
 ${T(390,440,18,'ke BWI',{c:'#4A6355',w:700})}
 <path d="M300 300 C 320 300 330 290 300 260" stroke="${C.gold}" stroke-width="5" fill="none" stroke-dasharray="3 10" stroke-linecap="round"/>
 ${sparkle(450,100,.9)}`,
 // Retensi donatur: lingkaran panah + donatur + hati
 retensi:()=>bg()+`
 <circle cx="266" cy="250" r="150" fill="none" stroke="${C.soft}" stroke-width="22"/>
 <path d="M266 100 A150 150 0 1 1 128 190" fill="none" stroke="${C.deep}" stroke-width="22" stroke-linecap="round"/>
 <path d="M104 166 l30 4 -6 32" stroke="${C.deep}" stroke-width="14" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
 ${person(226,300,1.3,C.deep2)}${person(306,300,1.3,C.leaf)}
 <path d="M266 160 c-14 -22 -50 -12 -40 14 c6 14 40 34 40 34 s34 -20 40 -34 c10 -26 -26 -36 -40 -14z" fill="${C.gold}"/>
 ${sparkle(450,90,.8)}${stack(450,470,3,26)}`
};
Object.assign(ART,ART2);module.exports={ART};
