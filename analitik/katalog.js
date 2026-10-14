/* Zakademia — katalog analitik lanjutan (melalui konsultasi). Visual adalah ilustrasi, bukan data lembaga. */
(function(){
'use strict';
var G='#0B4D33',G2='#6FAF78',G3='#B9D9BE',GOLD='#C99A3B',RED='#B4532A',MIST='#F2F5E6',INK='#10291F',MUTE='#4A6355',LINE='#DCE5D7';
function svg(w,h,body){return '<svg viewBox="0 0 '+w+' '+h+'" width="100%" role="img" aria-hidden="true" focusable="false" style="display:block;max-height:150px">'+body+'</svg>';}
function t(x,y,s,o){o=o||{};return '<text x="'+x+'" y="'+y+'" font-size="'+(o.fs||9)+'" fill="'+(o.c||MUTE)+'" text-anchor="'+(o.a||'start')+'" font-family="Plus Jakarta Sans,sans-serif" font-weight="'+(o.w||500)+'">'+s+'</text>';}
function r(x,y,w,h,c,rx){return '<rect x="'+x+'" y="'+y+'" width="'+w+'" height="'+h+'" rx="'+(rx==null?2:rx)+'" fill="'+c+'"/>';}
function mix(a,b,u){a=a.match(/\w\w/g).map(function(x){return parseInt(x,16);});b=b.match(/\w\w/g).map(function(x){return parseInt(x,16);});return 'rgb('+a.map(function(x,i){return Math.round(x+(b[i]-x)*u);}).join(',')+')';}

var V={
 kohort:function(){var s='',rows=['2021','2022','2023','2024','2025'],ret=[[100,48,38,33,30],[100,45,35,31],[100,52,41],[100,50],[100]];
   rows.forEach(function(y,i){s+=t(26,30+i*22,y,{a:'end'});ret[i].forEach(function(v,j){var u=v/100;s+=r(32+j*44,18+i*22,42,19,mix('F2F5E6','0B4D33',Math.pow(u,.8)));s+=t(53+j*44,31+i*22,v+'%',{a:'middle',c:u>.4?'#fff':INK,fs:8.5,w:700});});});
   ['Thn 0','Thn 1','Thn 2','Thn 3','Thn 4'].forEach(function(x,j){s+=t(53+j*44,12,x,{a:'middle'});});return svg(256,130,s);},
 rfm:function(){var seg=[['Juara',3,3,G,'#fff'],['Loyal',2,3,'#2E7D4F','#fff'],['Potensial',3,2,G2,INK],['Perlu disapa',2,2,G3,INK],['Baru',3,1,'#DCE5D7',INK],['Berisiko',1,3,GOLD,INK],['Hampir hilang',1,2,'#E2B37A',INK],['Hilang',1,1,RED,'#fff'],['Jarang',2,1,'#EFE7D2',INK]],s='';
   seg.forEach(function(g){var x=30+(g[1]-1)*74,y=8+(3-g[2])*38;s+=r(x,y,72,36,g[3],4)+t(x+36,y+21,g[0],{a:'middle',c:g[4],fs:8.5,w:700});});
   s+=t(141,128,'Kebaruan donasi →',{a:'middle'})+'<g transform="rotate(-90 12 65)">'+t(12,65,'Frekuensi & nilai →',{a:'middle'})+'</g>';return svg(256,132,s);},
 piramida:function(){var T=[['Besar','5%','55%',G,'#fff'],['Menengah','20%','30%',G2,INK],['Ritel','75%','15%',G3,INK]],s='',y0=24,hh=36,L=70,R=230,W=60,H=hh*3;
   function lw(y){return W*(y-y0)/H;}function rw(y){return W*(y0+H-y)/H;}
   s+=t(L,12,'Jumlah donatur',{a:'middle',c:INK,w:700,fs:9})+t(R,12,'Kontribusi dana',{a:'middle',c:INK,w:700,fs:9});
   T.forEach(function(x,i){var a=y0+i*hh,b=a+hh,m=(a+b)/2+3;
     s+='<polygon points="'+(L-lw(a))+','+a+' '+(L+lw(a))+','+a+' '+(L+lw(b))+','+b+' '+(L-lw(b))+','+b+'" fill="'+x[3]+'" stroke="#fff" stroke-width="1.5"/>';
     s+='<polygon points="'+(R-rw(a))+','+a+' '+(R+rw(a))+','+a+' '+(R+rw(b))+','+b+' '+(R-rw(b))+','+b+'" fill="'+x[3]+'" stroke="#fff" stroke-width="1.5"/>';
     s+=t(L,i?m:m+8,x[1],{a:'middle',c:x[4],w:800,fs:i?10:8.5})+t(R,i<2?m:m-8,x[2],{a:'middle',c:i<2?x[4]:INK,w:800,fs:i<2?10:8.5});
     s+=t(150,m,x[0],{a:'middle',c:MUTE,w:700,fs:8.5});});
   s+=t(150,y0+H+14,'Lapisan donatur menurut nilai donasi',{a:'middle',fs:8});return svg(300,y0+H+20,s);},
 clv:function(){var d=[['Juara',8.4],['Loyal',5.1],['Potensial',2.6],['Baru',1.2],['Berisiko',0.8]],s='';
   d.forEach(function(x,i){var w=x[1]/8.4*150;s+=t(58,22+i*22,x[0],{a:'end'})+r(64,12+i*22,w,15,i<2?G:i<4?G2:GOLD)+t(68+w,24+i*22,'Rp'+String(x[1]).replace('.',',')+' jt',{fs:8.5,w:700,c:INK});});return svg(256,122,s);},
 churn:function(){var b=[30,26,22,17,14,11,9,8,10,13],s='';b.forEach(function(v,i){var h=v*3;s+=r(14+i*23,100-h,20,h,i<4?G2:i<7?GOLD:RED,2);});
   s+='<line x1="12" y1="100" x2="246" y2="100" stroke="'+LINE+'"/>'+t(14,114,'Risiko rendah')+t(246,114,'Risiko tinggi',{a:'end'})+t(246,14,'Disapa lebih dulu →',{a:'end',c:RED,w:700});return svg(256,120,s);},
 musim:function(){var y=[40,42,45,50,62,95,58,46,44,48,52,70],p='',f='',s='';y.forEach(function(v,i){p+=(i?'L':'M')+(18+i*19)+' '+(110-v);});
   [72,48,46,52,66,98].forEach(function(v,i){f+=(i?'L':'M')+(18+(i+11)*19-19*11+209)+' '+(110-v);});
   s+=r(108,8,22,104,'#FFF1D6',3)+t(119,120,'Ramadan',{a:'middle',c:GOLD,w:700,fs:8});
   s+='<path d="'+p+'" fill="none" stroke="'+G+'" stroke-width="2.5" stroke-linejoin="round"/>';
   s+='<path d="M227 '+(110-70)+' L246 '+(110-72)+'" fill="none" stroke="'+G2+'" stroke-width="2.5" stroke-dasharray="4 3"/>';
   s+=t(246,(110-80),'Prakiraan',{a:'end',c:G2,w:700,fs:8});return svg(256,124,s);},
 kanal:function(){var d=[['Situs',70,40,16],['QRIS',50,60,13],['Konter',36,25,10],['Iklan',30,90,12],['Potong gaji',85,20,15]],s='';
   s+='<line x1="24" y1="104" x2="246" y2="104" stroke="'+LINE+'"/><line x1="24" y1="8" x2="24" y2="104" stroke="'+LINE+'"/>';
   d.forEach(function(x){var cx=24+x[2]*2.3,cy=104-x[1];s+='<circle cx="'+cx+'" cy="'+cy+'" r="'+x[3]+'" fill="'+(x[2]>70?GOLD:G2)+'" fill-opacity=".85"/>'+t(cx,cy+3,x[0],{a:'middle',fs:8,w:700,c:INK});});
   s+=t(246,118,'Biaya per donatur →',{a:'end'})+'<g transform="rotate(-90 11 56)">'+t(11,56,'Retensi →',{a:'middle'})+'</g>';return svg(256,124,s);},
 jalur:function(){var d=[['Donasi pertama',100],['Donasi kedua',46],['3+ kali',28],['Donasi rutin',12],['Lintas jenis dana',9]],s='';
   d.forEach(function(x,i){var w=x[1]*1.1;s+=t(84,21+i*22,x[0],{a:'end',c:INK,fs:8.5,w:600})+r(90,9+i*22,w,17,mix('6FAF78','0B4D33',i/4),3)+t(94+w,21+i*22,x[1]+'%',{fs:8.5,w:700,c:INK});});
   return svg(256,120,s);},
 ipa:function(){var s='';s+=r(24,8,110,52,'#FBEFE6',0)+r(136,8,110,52,'#DDF0E1',0)+r(24,62,110,52,MIST,0)+r(136,62,110,52,'#F7F3E6',0);
   s+=t(79,22,'Prioritas perbaikan',{a:'middle',c:RED,w:700,fs:8.5})+t(191,22,'Pertahankan',{a:'middle',c:G,w:700,fs:8.5})+t(79,108,'Prioritas rendah',{a:'middle',fs:8.5})+t(191,108,'Berlebih',{a:'middle',fs:8.5});
   [[60,40,RED],[95,30,RED],[170,35,G],[210,48,G],[200,80,MUTE],[70,85,MUTE],[110,50,RED]].forEach(function(p){s+='<circle cx="'+p[0]+'" cy="'+p[1]+'" r="5" fill="'+p[2]+'"/>';});
   s+=t(135,124,'Kinerja (kepuasan) →',{a:'middle'})+'<g transform="rotate(-90 12 62)">'+t(12,62,'Kepentingan →',{a:'middle'})+'</g>';return svg(256,128,s);}
};

var K=[
 {k:'rfm',viz:['rfm','piramida'],wide:true,n:'Segmentasi RFM dan piramida donatur',q:'Siapa donatur juara, siapa yang mulai menjauh, dan siapa yang perlu disapa sekarang? Seberapa besar dana bergantung pada segelintir donatur besar dibanding basis donatur ritel?',v:'Peta segmen dari kebaruan, frekuensi, dan nilai donasi, ditambah dua segitiga berlawanan: piramida jumlah donatur per lapisan (besar, menengah, ritel) dan segitiga terbalik kontribusi dana dari lapisan yang sama. Keduanya menunjukkan lapisan mana yang menopang dana dan lapisan mana yang perlu ditumbuhkan.',d:'Tanggal, ID donatur, dan nominal setiap transaksi.'},
 {k:'kohort',n:'Analisis kohort',q:'Dari donatur yang mulai memberi pada tahun tertentu, berapa persen yang masih bertahan setelah 1, 2, dan 3 tahun?',v:'Tabel panas retensi per angkatan donatur.',d:'Riwayat transaksi per donatur (ID donatur dapat disamarkan), minimal 2 tahun.'},
 {k:'clv',n:'CLV per segmen',q:'Segmen mana yang paling bernilai dalam jangka panjang, dan berapa layak dibelanjakan untuk menjaganya?',v:'Nilai seumur hidup donatur untuk setiap segmen.',d:'Riwayat transaksi per donatur dan biaya per kanal.'},
 {k:'churn',n:'Prediksi donatur berhenti',q:'Donatur mana yang berisiko tidak memberi lagi, sehingga bisa disapa sebelum terlambat?',v:'Sebaran skor risiko dan daftar prioritas sapaan.',d:'Riwayat transaksi per donatur, minimal 2 tahun.'},
 {k:'musim',n:'Pola musiman dan prakiraan',q:'Kapan puncak dan titik rendah penghimpunan, dan berapa perkiraan bulan-bulan berikutnya?',v:'Tren bulanan dengan penanda Ramadan, Zulhijah, akhir tahun, serta garis prakiraan.',d:'Total dana per bulan, minimal 3 tahun.'},
 {k:'kanal',n:'Efektivitas kanal',q:'Kanal mana yang menghasilkan donatur paling setia dengan biaya paling efisien?',v:'Peta kanal: biaya per donatur, retensi, dan nilai dana.',d:'Kanal setiap transaksi dan biaya per kanal.'},
 {k:'jalur',n:'Jalur donatur',q:'Berapa donatur yang berlanjut dari donasi pertama ke kedua, menjadi rutin, hingga memberi lintas jenis dana (zakat, infak, wakaf)?',v:'Corong tahapan donatur dan titik terbanyak donatur berhenti.',d:'Riwayat transaksi per donatur beserta jenis dana.'},
 {k:'ipa',n:'Survei lanjutan (IPA)',q:'Aspek layanan mana yang dianggap penting oleh donatur tetapi kinerjanya masih rendah?',v:'Kuadran kepentingan dan kinerja, dilengkapi uji reliabilitas dan tabulasi silang.',d:'Hasil survei Zakademia atau survei lembaga sendiri.'}
];

var box=document.getElementById('katalog');if(!box)return;
K.forEach(function(x){var c=document.createElement('article');c.className='card kat'+(x.wide?' wide':'');
  c.innerHTML='<div class="viz">'+(x.viz?'<div class="vz2">'+x.viz.map(function(k){return '<div>'+V[k]()+'</div>';}).join('')+'</div>':V[x.k]())+'<span class="ilu">Ilustrasi</span></div><div class="kb"><h3></h3><p class="q"></p><dl><dt>Visualisasi</dt><dd class="v"></dd><dt>Data yang diperlukan</dt><dd class="d"></dd></dl><a class="btn ghost sm">Konsultasikan</a></div>';
  c.querySelector('h3').textContent=x.n;c.querySelector('.q').textContent=x.q;c.querySelector('.v').textContent=x.v;c.querySelector('.d').textContent=x.d;
  c.querySelector('a').href='/?topik='+encodeURIComponent('Data & Analitik')+'&pesan='+encodeURIComponent('Kami tertarik dengan '+x.n+'. ')+'#kontak';
  c.querySelector('a').setAttribute('aria-label','Konsultasikan '+x.n);box.appendChild(c);});
var cta=document.createElement('article');cta.className='card kat katcta';cta.innerHTML='<div class="kb"><h3>Punya pertanyaan lain?</h3><p class="q">Ceritakan keputusan yang sedang dihadapi lembaga. Kami bantu memilih metode dan data yang tepat.</p><a class="btn sm" href="/?topik=Data%20%26%20Analitik#kontak">Jadwalkan konsultasi</a></div>';box.appendChild(cta);
})();
