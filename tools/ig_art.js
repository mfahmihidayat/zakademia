// Ilustrasi tambahan per slide Instagram (kanvas 532x520), memakai pembantu dari og_art.js.
const {ART,H}=require('./og_art.js');const {C,T,bg,coin,stack,lawBook,gavel,arrowUp,mosque,doc}=H;
const person=(x,y,s=1,col=C.deep2)=>`<g transform="translate(${x} ${y}) scale(${s})"><circle cx="0" cy="-34" r="16" fill="${col}"/><path d="M-26 10 Q-26 -14 0 -14 Q26 -14 26 10 Z" fill="${col}"/></g>`;
const check=(x,y,r=22,bgc=C.leaf)=>`<circle cx="${x}" cy="${y}" r="${r}" fill="${bgc}"/><path d="M${x-r*.45} ${y} l${r*.32} ${r*.32} ${r*.6} -${r*.62}" stroke="${C.white}" stroke-width="${r*.22}" fill="none" stroke-linecap="round" stroke-linejoin="round"/>`;
const sparkle=(x,y,k=1)=>`<path transform="translate(${x} ${y}) scale(${k})" d="M0 -22 l6 16 16 6 -16 6 -6 16 -6 -16 -16 -6 16 -6z" fill="${C.gold}"/>`;
const IG={
 // Rakornas: panggung + peserta
 pertemuan:()=>bg()+`
 <g filter="url(#sh)"><rect x="70" y="70" width="392" height="150" rx="18" fill="${C.deep}"/></g>
 ${T(266,132,30,'RAKORNAS',{c:C.goldS,ls:4})}${T(266,172,22,'ZAKAT 2026',{c:'#E9F2E6',w:600,ls:3})}
 <g filter="url(#sh)"><path d="M226 236 h80 l-10 96 h-60 z" fill="${C.white}"/><rect x="216" y="226" width="100" height="16" rx="6" fill="${C.gold}"/></g>
 ${person(266,226,.9,C.ink)}
 ${[0,1,2,3,4,5].map(i=>person(96+i*68,402,1,i%2?C.leaf:C.deep2)).join('')}
 ${[0,1,2,3,4].map(i=>person(130+i*68,462,1.05,i%2?C.deep2:C.leaf)).join('')}
 ${sparkle(470,60,.9)}${sparkle(60,250,.6)}`,
 // Target: tangga batang naik + koin
 target:()=>bg()+`
 <g filter="url(#sh)"><rect x="56" y="80" width="420" height="360" rx="22" fill="${C.white}"/></g>
 <line x1="90" y1="390" x2="440" y2="390" stroke="${C.soft}" stroke-width="3"/>
 <rect x="110" y="290" width="70" height="100" rx="10" fill="${C.soft}"/><rect x="220" y="270" width="70" height="120" rx="10" fill="${C.leaf}"/>
 <rect x="330" y="160" width="70" height="230" rx="10" fill="${C.deep}"/>
 ${T(145,280,20,'40,8')}${T(255,260,20,'44,7')}${T(365,148,24,'70',{c:'#8A6418'})}
 ${T(145,420,16,'2024',{c:'#4A6355',w:600})}${T(255,420,16,'2025',{c:'#4A6355',w:600})}${T(365,420,16,'2027',{c:'#4A6355',w:600})}
 <path d="M150 250 C 220 210, 270 190, 322 150" stroke="${C.gold}" stroke-width="6" fill="none" stroke-dasharray="3 12" stroke-linecap="round"/><path d="M308 140 l22 6 -10 20" stroke="${C.gold}" stroke-width="6" fill="none" stroke-linecap="round"/>
 ${stack(460,480,4,30)}`,
 // Regulasi: UU + palu + kaca pembesar audit
 regulasi:()=>bg()+`
 ${lawBook(70,170,170,260,C.deep,'UU','23/2011')}${gavel(200,110,.62,-38)}
 ${doc(270,150,190,240,'AUDIT',5,false)}
 ${check(430,360,26)}
 <g filter="url(#sh)"><circle cx="380" cy="420" r="44" fill="${C.white}" fill-opacity=".6" stroke="${C.ink}" stroke-width="12"/><rect x="408" y="452" width="64" height="20" rx="10" transform="rotate(40 408 452)" fill="${C.ink}"/></g>`,
 // Bagi lembaga: papan periksa + basis data
 lembaga:()=>bg()+`
 <g filter="url(#sh)"><rect x="80" y="90" width="230" height="340" rx="20" fill="${C.white}"/><rect x="150" y="70" width="90" height="40" rx="12" fill="${C.deep}"/></g>
 ${[0,1,2].map(i=>check(122,168+i*84,18)+`<rect x="152" y="${158+i*84}" width="${120-(i%2)*24}" height="12" rx="6" fill="${C.soft}"/><rect x="152" y="${178+i*84}" width="80" height="8" rx="4" fill="${C.mist}"/>`).join('')}
 <g filter="url(#sh)">${[2,1,0].map(i=>`<ellipse cx="410" cy="${400-i*62}" rx="74" ry="24" fill="${i==2?C.leaf:C.deep2}"/><rect x="336" y="${352-i*62}" width="148" height="48" fill="${i==2?C.leaf:C.deep2}"/><ellipse cx="410" cy="${352-i*62}" rx="74" ry="24" fill="${i==0?C.deep:'#1E7A52'}"/>`).join('')}</g>
 ${sparkle(470,120,.8)}`,
 // Nisab: timbangan emas
 nisab:()=>bg()+`
 <rect x="258" y="130" width="16" height="290" rx="6" fill="${C.deep}"/><rect x="186" y="410" width="160" height="30" rx="12" fill="${C.deep}" filter="url(#sh)"/>
 <rect x="96" y="140" width="340" height="12" rx="6" fill="${C.deep2}" transform="rotate(-6 266 146)"/><circle cx="266" cy="128" r="18" fill="${C.gold}"/>
 <path d="M110 165 L70 285 M110 165 L150 285" stroke="${C.deep2}" stroke-width="3"/><path d="M420 130 L380 250 M420 130 L460 250" stroke="${C.deep2}" stroke-width="3"/>
 <path d="M54 285 h112 q-8 36 -56 36 q-48 0 -56 -36z" fill="${C.deep}" filter="url(#sh)"/><path d="M364 250 h112 q-8 36 -56 36 q-48 0 -56 -36z" fill="${C.deep}" filter="url(#sh)"/>
 <g filter="url(#sh)"><path d="M70 285 L84 256 L136 256 L150 285 Z" fill="${C.gold}"/></g>${T(110,280,16,'85 g',{c:C.white})}
 ${stack(420,244,4,28)}${T(420,350,20,'Penghasilan',{c:C.deep})}${T(110,370,20,'Nisab',{c:C.deep})}`,
 // Rumus: persen + diagram lingkaran 2,5%
 rumus:()=>bg()+`
 <g filter="url(#sh)"><circle cx="230" cy="270" r="150" fill="${C.leaf}"/></g>
 <path d="M230 270 L230 120 A150 150 0 0 1 253.4 121.8 Z" fill="${C.gold}"/>
 <circle cx="230" cy="270" r="70" fill="${C.mist}"/>${T(230,284,40,'97,5%',{c:C.deep})}
 <g filter="url(#sh)"><rect x="330" y="70" width="160" height="90" rx="20" fill="${C.deep}"/></g>${T(410,128,40,'2,5%',{c:C.goldS})}
 <path d="M300 112 L250 128" stroke="${C.gold}" stroke-width="5" stroke-linecap="round"/>
 ${stack(440,450,3,32)}`,
 // Contoh: slip gaji + koin
 slip:()=>bg()+`
 <g filter="url(#sh)" transform="rotate(-6 200 270)"><rect x="80" y="100" width="240" height="320" rx="16" fill="${C.white}"/><rect x="80" y="100" width="240" height="60" rx="16" fill="${C.deep}"/><rect x="80" y="140" width="240" height="20" fill="${C.deep}"/>
 ${T(200,140,22,'SLIP GAJI',{c:C.goldS,ls:2})}
 ${[0,1,2,3].map(i=>`<rect x="104" y="${190+i*40}" width="110" height="10" rx="5" fill="${C.soft}"/><rect x="236" y="${190+i*40}" width="60" height="10" rx="5" fill="${C.mist}"/>`).join('')}
 <rect x="104" y="356" width="192" height="40" rx="10" fill="#FFF1D6"/>${T(200,383,20,'Zakat 2,5%',{c:'#8A6418'})}</g>
 ${stack(400,430,5,40)}${sparkle(440,200,.9)}`,
 // Tunaikan: masjid + bukti setor
 tunaikan:()=>bg()+`
 ${mosque(170,220,1)}
 <g filter="url(#sh)"><rect x="290" y="170" width="190" height="250" rx="14" fill="${C.white}"/></g>
 ${T(385,210,18,'BUKTI SETOR',{c:C.deep,ls:1.5})}
 ${[0,1,2,3].map(i=>`<rect x="312" y="${232+i*30}" width="${140-(i%2)*36}" height="9" rx="4" fill="${C.soft}"/>`).join('')}
 ${check(430,380,26)}
 <rect x="0" y="440" width="532" height="80" fill="${C.soft}" opacity=".6"/>
 ${stack(110,460,3,30)}`,
 // Infak: koin masuk kotak
 infak:()=>bg()+`
 <g filter="url(#sh)"><rect x="140" y="260" width="250" height="180" rx="20" fill="${C.deep}"/><rect x="200" y="252" width="130" height="16" rx="8" fill="${C.ink}"/></g>
 <path d="M140 320 h250" stroke="${C.deep2}" stroke-width="6"/>${T(265,390,26,'INFAK',{c:C.goldS,ls:4})}
 ${coin(265,190,34)}${coin(205,120,26)}${coin(330,90,22)}
 <path d="M265 210 v28" stroke="${C.gold}" stroke-width="5" stroke-linecap="round" stroke-dasharray="2 9"/>${sparkle(440,170,.8)}`,
 // Sedekah: hati + tangan/ilmu/senyum
 sedekah:()=>bg()+`
 <g filter="url(#sh)"><path d="M266 420 C 90 300, 120 130, 266 210 C 412 130, 442 300, 266 420 Z" fill="${C.leaf}"/></g>
 ${[['Ilmu',110,120],['Tenaga',420,120],['Senyum',266,470]].map(([t,x,y])=>`<g filter="url(#sh)"><rect x="${x-70}" y="${y-30}" width="140" height="56" rx="28" fill="${C.white}"/></g>${T(x,y+6,20,t,{c:C.deep})}`).join('')}
 ${coin(266,290,40)}`,
 // ZakaStat: 2024 ke 2025 naik
 naik:()=>bg()+`
 <g filter="url(#sh)"><rect x="66" y="80" width="400" height="360" rx="22" fill="${C.white}"/></g>
 <line x1="100" y1="390" x2="430" y2="390" stroke="${C.soft}" stroke-width="3"/>
 <rect x="130" y="200" width="100" height="190" rx="12" fill="${C.leaf}"/><rect x="290" y="168" width="100" height="222" rx="12" fill="${C.deep}"/>
 ${T(180,188,24,'40,8')}${T(340,156,24,'44,7',{c:'#8A6418'})}${T(180,420,18,'2024',{c:'#4A6355',w:600})}${T(340,420,18,'2025',{c:'#4A6355',w:600})}
 <g filter="url(#sh)"><rect x="330" y="40" width="150" height="64" rx="16" fill="${C.gold}"/></g>${T(405,82,28,'+9,54%',{c:C.white})}`,
 // Wakaf uang: koin di dalam perisai (pokok dijaga), hasil mengalir
 pokok:()=>bg()+`
 <g filter="url(#sh)"><path d="M180 90 L300 130 L300 250 Q300 360 180 420 Q60 360 60 250 L60 130 Z" fill="${C.deep}"/></g>
 ${stack(180,300,5,46)}
 <path d="M320 260 C 380 250, 410 220, 440 170" stroke="${C.gold}" stroke-width="6" fill="none" stroke-dasharray="3 12" stroke-linecap="round"/>
 <g filter="url(#sh)"><circle cx="450" cy="140" r="46" fill="${C.white}"/></g><path d="M450 166 C 410 138, 428 112, 450 132 C 472 112, 490 138, 450 166 Z" fill="${C.leaf}"/>
 ${T(180,470,20,'Pokok tetap utuh',{c:C.deep})}${T(450,220,18,'Hasilnya',{c:C.deep})}`,
 // Wakaf melalui uang: koin menjadi bangunan
 aset:()=>bg()+`
 ${stack(110,380,4,40)}
 <path d="M170 330 h70" stroke="${C.gold}" stroke-width="8" stroke-linecap="round"/><path d="M228 312 l20 18 -20 18" stroke="${C.gold}" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
 ${mosque(380,250,1)}
 <rect x="0" y="440" width="532" height="80" fill="${C.soft}" opacity=".6"/>
 ${T(110,460,20,'Uang',{c:C.deep})}${T(380,460,20,'Aset wakaf',{c:C.deep})}`,
 // CWLS: sertifikat sukuk + imbal hasil ke program
 sukuk:()=>bg()+`
 ${doc(60,110,220,280,'SUKUK NEGARA',5)}
 <path d="M290 250 C 330 250, 340 200, 370 190" stroke="${C.gold}" stroke-width="6" fill="none" stroke-dasharray="3 12" stroke-linecap="round"/>
 ${[['Klinik',150],['Pangan',260],['Pendidikan',370]].map(([t,y])=>`<g filter="url(#sh)"><rect x="350" y="${y-38}" width="140" height="76" rx="16" fill="${C.white}"/></g><circle cx="382" cy="${y}" r="14" fill="${C.leaf}"/>${T(444,y+6,t.length>8?14:17,t,{c:C.deep})}`).join('')}
 ${stack(140,460,3,30)}`,
 // ZakaStory: pasien kembali melihat
 kisah:()=>bg()+`
 <g filter="url(#sh)"><circle cx="230" cy="200" r="80" fill="${C.deep2}"/><path d="M90 440 Q90 300 230 300 Q370 300 370 440 Z" fill="${C.deep2}"/></g>
 <g stroke="${C.ink}" stroke-width="8" fill="${C.white}" fill-opacity=".55"><circle cx="196" cy="200" r="30"/><circle cx="264" cy="200" r="30"/><path d="M226 200 h8" fill="none"/></g>
 <g filter="url(#sh)"><rect x="330" y="300" width="150" height="110" rx="10" fill="${C.white}"/></g><path d="M405 310 v90" stroke="${C.soft}" stroke-width="4"/>${[0,1,2].map(i=>`<rect x="345" y="${325+i*24}" width="48" height="7" rx="3" fill="${C.soft}"/><rect x="417" y="${325+i*24}" width="48" height="7" rx="3" fill="${C.soft}"/>`).join('')}
 ${sparkle(420,120,1)}${sparkle(90,120,.6)}`,
 // ZakaStat: 73% di luar neraca
 neraca:()=>bg()+`
 <g filter="url(#sh)"><circle cx="230" cy="260" r="160" fill="${C.deep}"/></g>
 <path d="M230 260 L230 100 A160 160 0 0 0 76.4 215.3 Z" fill="${C.goldS}"/>
 <circle cx="230" cy="260" r="80" fill="${C.mist}"/>${T(230,272,40,'73%',{c:C.deep})}
 <g filter="url(#sh)"><rect x="370" y="90" width="130" height="66" rx="16" fill="${C.white}"/></g>${T(435,132,22,'27% neraca',{c:'#8A6418'})}
 ${T(230,470,20,'Di luar neraca',{c:C.deep})}`,
 // ZakaStat: muzaki turun, dana naik
 muzaki:()=>bg()+`
 ${[0,1,2,3,4].map(i=>person(90+i*60,250,1,i<4?C.deep2:C.soft)).join('')}
 <path d="M90 300 L380 330" stroke="#C0392B" stroke-width="6" stroke-linecap="round"/><path d="M362 316 l20 14 -20 14" stroke="#C0392B" stroke-width="6" fill="none" stroke-linecap="round"/>
 ${T(230,380,22,'31,5 jt → 28,7 jt muzaki',{c:C.deep})}
 ${stack(120,480,2,30)}${stack(200,480,3,30)}${stack(280,480,4,30)}${stack(360,480,5,30)}
 ${arrowUp(450,480,150,C.gold)}`,
 // ZakaStat: realisasi vs potensi
 potensi:()=>bg()+`
 <g filter="url(#sh)"><circle cx="266" cy="250" r="190" fill="${C.white}"/></g>
 <circle cx="266" cy="250" r="190" fill="none" stroke="${C.soft}" stroke-width="3" stroke-dasharray="8 10"/>
 <circle cx="266" cy="368" r="72" fill="${C.deep}"/>${T(266,364,24,'Rp44,7 T',{c:C.goldS})}${T(266,392,15,'realisasi',{c:'#E9F2E6',w:600})}
 ${T(266,150,28,'Rp327,6 T',{c:'#8A6418'})}${T(266,180,17,'potensi per tahun',{c:'#4A6355',w:600})}`,
};
Object.assign(ART,IG);module.exports={ART};
