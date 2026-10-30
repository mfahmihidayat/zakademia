import os, json
D=os.path.dirname(os.path.abspath(__file__))
F=D+'/fontpkg/node_modules'
IMG='file:///home/claude/zakademia/assets/images/'
# Gaya kuning: warna bulatan logo (emas #C29139) dicerahkan; emerald seri sebelumnya tinggal di dasar (transisi)
G='linear-gradient(180deg,#FFE28F 0%,#FFCF55 34%,#F3B73F 70%,#D99A2B 82%,#0B7A5E 93%,#064E3B 100%)'
DK='#0A2E22'; EM='#0B6B52'
CSS='''@font-face{font-family:"ns";font-weight:400;src:url("file://%(f)s/@fontsource/noto-serif/files/noto-serif-latin-400-normal.woff2")}
@font-face{font-family:"ns";font-weight:600;font-style:italic;src:url("file://%(f)s/@fontsource/noto-serif/files/noto-serif-latin-600-italic.woff2")}
@font-face{font-family:"ns";font-weight:800;src:url("file://%(f)s/@fontsource/noto-serif/files/noto-serif-latin-800-normal.woff2")}
@font-face{font-family:"nsc";font-weight:100 900;font-stretch:62.5%% 100%%;src:url("file://%(f)s/@fontsource-variable/noto-serif/files/noto-serif-latin-wdth-normal.woff2")}
*{margin:0;box-sizing:border-box}
body{font-family:"nsc",serif;font-stretch:87.5%%;color:DK}
.slide{width:1080px;height:1350px;position:relative;overflow:hidden;color:DK}
.pano{position:absolute;top:0;width:3240px;height:1350px}
.logo{position:absolute;left:84px;top:60px;width:84px;height:84px;border-radius:50%%;background:#FFFBEA;display:grid;place-items:center;box-shadow:0 10px 24px -12px rgba(10,46,34,.5)}
.logo img{height:56px}
.rb{position:absolute;left:180px;top:60px;width:84px;height:84px;border-radius:50%%;box-shadow:0 10px 24px -12px rgba(10,46,34,.5)}
.art{position:absolute;right:84px;top:60px;width:210px;height:210px;border-radius:50%%;overflow:hidden;background:#FFFBEA;box-shadow:0 18px 40px -18px rgba(10,46,34,.55)}
.art svg{width:100%%;height:100%%}
h2{position:absolute;left:84px;right:330px;top:300px;font-family:"ns",serif;font-weight:800;font-size:68px;line-height:1.06;letter-spacing:-.015em}
h2 span,.cv span{font-style:italic;font-weight:600;color:EM}
.big{position:absolute;left:84px;right:84px;top:640px}
.big .n{font-family:"ns",serif;font-weight:800;font-size:150px;line-height:1;color:EM}
.big .c{font-size:42px;margin-top:20px;line-height:1.35}
.rows{position:absolute;left:84px;right:84px;top:600px;display:flex;flex-direction:column;gap:26px}
.row{display:flex;gap:24px;font-size:40px;line-height:1.35;font-weight:600}
.row i{flex:none;width:60px;height:60px;border-radius:50%%;display:grid;place-items:center;font-style:normal;font-weight:800;font-size:26px;background:#064E3B;color:#FFD866}
.row small{display:block;font-size:31px;font-weight:400;margin-top:4px}
.card{border-radius:30px;padding:36px 44px;font-size:39px;line-height:1.42;background:#FFFBEA;color:DK;box-shadow:0 18px 40px -26px rgba(10,46,34,.6)}
.card b{color:EM}
.q{font-family:"ns",serif;font-style:italic;font-weight:600;font-size:46px;line-height:1.4}
.p{font-size:46px;line-height:1.45}
.src{position:absolute;left:84px;right:84px;bottom:150px;font-size:23px;line-height:1.4;color:#3B2A08}
.geser{position:absolute;left:84px;bottom:56px;height:62px;padding:0 30px;border-radius:99px;background:#FFD866;color:DK;font-weight:800;font-size:28px;display:flex;align-items:center;gap:12px}
.dots{position:absolute;right:84px;bottom:80px;display:flex;gap:10px}.dots i{width:12px;height:12px;border-radius:50%%;background:rgba(255,255,255,.45)}.dots i.on{background:#FFD866;width:34px;border-radius:6px}
.act{position:absolute;left:84px;right:84px;bottom:52px;display:flex;align-items:center;gap:40px;font-size:28px;font-weight:700;color:#FFFBEA}
.act span{display:flex;align-items:center;gap:10px}.act svg{width:46px;height:46px}
.cta{position:absolute;left:84px;right:84px;bottom:180px;background:#064E3B;color:#FFFBEA;border-radius:30px;padding:30px 38px;font-size:33px;font-weight:700;line-height:1.38}
.cta b{color:#FFD866}
/* sampul */
.pw{position:absolute;top:330px;font-family:"ns",serif;font-weight:800;color:DK;line-height:1;letter-spacing:-.02em}
.cv{position:absolute;left:84px;right:84px;bottom:180px;background:#FFFBEA;border-radius:36px;padding:34px 40px 36px;box-shadow:0 24px 50px -26px rgba(10,46,34,.7);display:flex;gap:30px;align-items:center}
.cv .t{font-family:"ns",serif;font-weight:800;font-size:54px;line-height:1.08;letter-spacing:-.01em}
.cv .s{font-size:28px;margin-top:12px;color:#4A3A12}
.cv .ic{flex:none;width:180px;height:180px;border-radius:50%%;overflow:hidden;background:#FFE7A3}
.cv .ic svg{width:100%%;height:100%%}
'''.replace('%(f)s',F).replace('%%','%').replace('DK',DK).replace('EM',EM)

# ---------- ilustrasi (viewBox 200x200, latar krem bulat) ----------
GOLD='#E9A92C'; GOLDL='#FFD866'; IV='#FFFBEA'
def lantern(x,y,s=1,body='#0B6B52'):
    return f'''<g transform="translate({x} {y}) scale({s})"><path d="M-14 0 h28 l-6 -12 h-16z" fill="{GOLD}"/><path d="M-26 0 h52 l14 40 -14 46 h-52 l-14 -46z" fill="{body}"/>
<path d="M-14 8 h28 l8 32 -8 36 h-28 l-8 -36z" fill="{GOLDL}"/><path d="M0 8 v68 M-21 40 h42" stroke="{body}" stroke-width="4"/><path d="M-30 86 h60 l-8 12 h-44z" fill="{GOLD}"/><circle cx="0" cy="110" r="6" fill="{GOLD}"/></g>'''
def art_pedia():  # karung beras + takaran (zakat fitrah)
    return f'''<svg viewBox="0 0 200 200"><circle cx="100" cy="100" r="100" fill="{IV}"/><circle cx="150" cy="52" r="26" fill="{GOLDL}"/><circle cx="160" cy="44" r="22" fill="{IV}"/>
<path d="M58 70 q42 -22 84 0 l10 86 q-52 18 -104 0z" fill="#E8D3A6"/><path d="M62 70 q38 -14 76 0" stroke="#B88F4A" stroke-width="6" fill="none"/><path d="M84 58 q16 -16 32 0" stroke="#0B6B52" stroke-width="7" fill="none" stroke-linecap="round"/>
<text x="100" y="128" font-family="ns,serif" font-weight="800" font-size="26" fill="#0B6B52" text-anchor="middle">2,5 kg</text>
<ellipse cx="100" cy="168" rx="58" ry="8" fill="#0B6B5222"/></svg>'''
def art_stat():
    return f'''<svg viewBox="0 0 200 200"><circle cx="100" cy="100" r="100" fill="{IV}"/>
<rect x="44" y="118" width="26" height="40" rx="6" fill="#0B6B52"/><rect x="80" y="92" width="26" height="66" rx="6" fill="{GOLD}"/><rect x="116" y="66" width="26" height="92" rx="6" fill="#0B6B52"/>
<path d="M40 110 L80 84 L114 92 L158 48" stroke="{GOLD}" stroke-width="7" fill="none" stroke-linecap="round" stroke-linejoin="round"/><circle cx="158" cy="48" r="10" fill="{GOLD}"/>
<rect x="36" y="160" width="128" height="6" rx="3" fill="#0A2E22"/></svg>'''
def art_story():  # piring kurma + gelas, meja berbuka
    dates=''.join(f'<ellipse cx="{x}" cy="{y}" rx="13" ry="8" fill="#7A3E1D" transform="rotate({r} {x} {y})"/>' for x,y,r in [(78,118,-20),(100,112,10),(120,120,-8),(90,128,25),(112,130,-25)])
    return f'''<svg viewBox="0 0 200 200"><circle cx="100" cy="100" r="100" fill="{IV}"/><circle cx="148" cy="54" r="24" fill="{GOLDL}"/><circle cx="158" cy="47" r="20" fill="{IV}"/>
<rect x="20" y="146" width="160" height="12" rx="6" fill="#0B6B52"/><ellipse cx="98" cy="134" rx="52" ry="14" fill="{GOLD}"/><ellipse cx="98" cy="130" rx="44" ry="10" fill="#FFE7A3"/>{dates}
<path d="M150 96 h22 l-4 50 h-14z" fill="#FFFFFF" stroke="#0B6B52" stroke-width="4"/><path d="M153 112 h16 l-2 32 h-12z" fill="#F7E7C6"/></svg>'''
ARTS={'ZakaPedia':art_pedia(),'ZakaStat':art_stat(),'ZakaStory':art_story()}
RB={'ZakaPedia':'rb-pedia.png','ZakaStat':'rb-stat.png','ZakaStory':'rb-story.png'}

IC={'Suka':'<path d="M20 34s-13-8-13-17a7 7 0 0 1 13-4 7 7 0 0 1 13 4c0 9-13 17-13 17z" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linejoin="round"/>',
 'Simpan':'<path d="M11 6h18v28l-9-7-9 7z" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linejoin="round"/>',
 'Bagikan':'<path d="M35 6 5 18l12 5 4 12z M17 23 35 6" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linejoin="round"/>'}
def act(): return '<div class="act">'+''.join(f'<span><svg viewBox="0 0 40 40">{v}</svg>{k}</span>' for k,v in IC.items())+'</div>'
def geser(i,n): return '<div class="geser">Geser →</div><div class="dots">'+''.join(f'<i class="{"on" if k==i else ""}"></i>' for k in range(n))+'</div>'
def head(series,art=True): return f'<div class="logo"><img src="{IMG}logo-color.png"></div><img class="rb" src="{IMG}{RB[series]}">'+(f'<div class="art">{ARTS[series]}</div>' if art else '')

# ---------- panorama sampul (3240 px: kiri = post 3, tengah = post 2, kanan = post 1) ----------
def panorama():
    # tali lampion bergelombang melintasi tiga sampul (horizontal, aman saat grid memotong tepi)
    path='M-20 190 '+' '.join(f'Q{x+270} {250 if (x//540)%2==0 else 150} {x+540} 190' for x in range(-20,3240,540))
    lan=''
    for x,l,s in [(300,70,1.0),(760,120,.8),(1360,60,.9),(1880,30,1.0),(2440,80,.85),(2960,130,1.0)]:
        yy=190+(30 if (x//270)%2==0 else -10)
        lan+=f'<path d="M{x} {yy} V{yy+l}" stroke="#0A2E22" stroke-width="3"/>'+lantern(x,yy+l+12,s)
    stars=''.join(f'<path transform="translate({x} {y}) scale({k})" d="M0 -16 l4 12 12 4 -12 4 -4 12 -4 -12 -12 -4 12 -4z" fill="#FFFBEA"/>' for x,y,k in [(560,120,1),(1040-90,300,.7),(1250,130,.8),(2100,110,1),(2700,320,.7),(3100,110,.9),(180,330,.7)])
    # bulatan besar (motif bulatan logo) di belakang "H-100", bulan sabit di sampul kiri
    glow='<circle cx="1620" cy="520" r="330" fill="#FFF3C4" opacity=".75"/><circle cx="1620" cy="520" r="250" fill="#FFE28F" opacity=".9"/>'
    moon='<circle cx="860" cy="700" r="120" fill="#FFFBEA"/><circle cx="910" cy="668" r="112" fill="#FFD866"/>'
    svg=f'<svg class="pano" viewBox="0 0 3240 1350" style="left:LEFTpx">{glow}{moon}<path d="{path}" stroke="#0A2E22" stroke-width="4" fill="none"/>{lan}{stars}</svg>'
    words=[('<div class="pw" style="left:84px;top:430px;font-size:118px">Menyambut</div><div class="pw" style="left:88px;top:570px;font-size:44px;font-family:nsc;font-weight:700;font-stretch:87.5%">bulan suci yang dinanti</div>'),
           ('<div class="pw" style="left:0;right:0;text-align:center;top:400px;font-size:250px;color:#064E3B">H-100</div><div class="pw" style="left:0;right:0;text-align:center;top:660px;font-size:44px;font-family:nsc;font-weight:700;font-stretch:87.5%">perkiraan 1 Ramadan: 8 Februari 2027</div>'),
           ('<div class="pw" style="right:84px;top:400px;font-size:130px;text-align:right">Ramadan</div><div class="pw" style="right:88px;top:550px;font-size:74px;text-align:right;font-style:italic;font-weight:600;color:#0B6B52">1448 H</div>')]
    return svg,words
PSVG,PWORDS=panorama()
def cover(k,series,title,sub):
    return (PSVG.replace('LEFT',str(-1080*k))+PWORDS[k]+head(series,False)
            +f'<div class="cv"><div class="ic">{ARTS[series]}</div><div><div class="t">{title}</div><div class="s">{sub}</div></div></div>')

P=[]
def carousel(name,k,series,title,sub,slides):
    n=len(slides)+1;out=[cover(k,series,title,sub)+geser(0,n)]
    for i,s in enumerate(slides,1):
        h=head(series)+s
        h+=act() if i==n-1 else geser(i,n)
        out.append(h)
    P.append((name,out))

# Post 1 (terbit pertama, kanan di grid): ZakaPedia
carousel('ramadan-pedia',2,'ZakaPedia','Ramadan, bulan <span>paling dermawan</span>','Zakat fitrah, fidyah, dan memberi buka puasa',[
 '<h2>Mengapa Ramadan identik dengan <span>memberi?</span></h2><div class="rows" style="top:640px"><div class="card q">“Rasulullah saw. adalah manusia paling dermawan, dan beliau lebih dermawan lagi di bulan Ramadan … lebih dermawan daripada angin yang berembus.”</div></div><div class="src">HR Bukhari no. 6 dan Muslim no. 2308, dari Ibnu Abbas</div>',
 '<h2>Zakat fitrah, <span>wajib bagi semua</span></h2><div class="rows" style="top:600px"><div class="row"><i>1</i><div>Setiap Muslim<small>laki-laki dan perempuan, anak dan dewasa</small></div></div><div class="row"><i>2</i><div>Satu sha’ makanan pokok<small>acuan BAZNAS 2026: 2,5 kg beras atau Rp50.000 per jiwa</small></div></div><div class="row"><i>3</i><div>Sebelum salat Idulfitri<small>agar sampai ke mustahik di hari raya</small></div></div></div><div class="src">HR Bukhari no. 1503; Keputusan Ketua BAZNAS RI No. 14 Tahun 2026</div>',
 '<h2>Tak mampu puasa? <span>Ada fidyah</span></h2><div class="rows" style="top:600px"><div class="card">Bagi yang <b>tidak mampu berpuasa</b>, seperti lansia atau sakit menahun: setiap hari yang ditinggalkan diganti dengan memberi makan satu orang miskin.</div><div class="card">Acuan BAZNAS 2026<br><b style="font-family:ns,serif;font-size:56px">Rp65.000</b> per hari</div></div><div class="src">QS Al-Baqarah: 184; Keputusan Ketua BAZNAS RI No. 14 Tahun 2026</div>',
 '<h2>Pahala dari <span>sepiring takjil</span></h2><div class="rows" style="top:640px"><div class="card q">“Siapa yang memberi makan berbuka kepada orang yang berpuasa, ia mendapat pahala seperti orang itu, tanpa mengurangi pahala orang yang berpuasa sedikit pun.”</div></div><div class="src">HR Tirmidzi no. 807, hasan sahih</div>',
 '<h2>100 hari <span>untuk bersiap</span></h2><div class="rows" style="top:600px"><div class="row"><i>1</i>Hitung zakat fitrah seluruh anggota keluarga</div><div class="row"><i>2</i>Sisihkan sedikit tiap pekan untuk sedekah Ramadan</div><div class="row"><i>3</i>Catat lembaga zakat resmi tempat Anda menyalurkan</div></div><div class="cta" style="bottom:170px">Simpan post ini sebagai pengingat, lalu bagikan ke keluarga.</div>'])

# Post 2 (tengah di grid): ZakaStat
carousel('ramadan-stat',1,'ZakaStat','Angka-angka <span>filantropi Ramadan</span>','Dari zakat fitrah sampai peringkat kedermawanan',[
 '<h2>Hitung mundur <span>dimulai</span></h2><div class="big"><div class="n">100 hari</div><div class="c">menuju 1 Ramadan 1448 H, yang diperkirakan jatuh pada Senin, 8 Februari 2027.</div></div><div class="src">Kriteria MABIMS (BRIN, BMKG) dan KHGT Muhammadiyah. Penetapan resmi melalui sidang isbat Kemenag.</div>',
 '<h2>Zakat fitrah <span>2026</span></h2><div class="big"><div class="n">Rp50.000</div><div class="c">per jiwa, setara 2,5 kg beras. Naik dari Rp47.000 pada tahun sebelumnya.</div></div><div class="src">Keputusan Ketua BAZNAS RI No. 14 Tahun 2026; Katadata, Februari 2026</div>',
 '<h2>Target ZIS Ramadan <span>BAZNAS RI</span></h2><div class="big"><div class="n" style="font-size:118px">Rp515 miliar</div><div class="c">target penghimpunan zakat, infak, dan sedekah Ramadan 1447 H, khusus BAZNAS tingkat pusat.</div></div><div class="src">Metro TV News, Februari 2026</div>',
 '<h2>Potensi yang <span>belum tergarap</span></h2><div class="big"><div class="n">±10%</div><div class="c">baru sekitar sepersepuluh dari potensi zakat nasional Rp327 triliun per tahun yang sudah terhimpun.</div></div><div class="src">Katadata mengutip data BAZNAS, Februari 2026</div>',
 '<h2>Seberapa dermawan <span>orang Indonesia?</span></h2><div class="big"><div class="n">1,55%</div><div class="c">dari pendapatan rata-rata didonasikan, di atas rata-rata global 1,04%. Indonesia peringkat 21 dari 101 negara.</div></div><div class="src">World Giving Report 2025 (CNN Indonesia, Agustus 2025)</div>',
 '<h2>Angka mana yang paling <span>mengejutkan?</span></h2><div class="cta" style="bottom:200px">Tulis di kolom komentar. Bagikan ke teman yang sedang menyiapkan program Ramadan di lembaganya.</div>'])

# Post 3 (terbit terakhir, kiri di grid): ZakaStory
carousel('ramadan-story',0,'ZakaStory','Ibnu Umar dan <span>meja berbuka</span>','Kisah sahabat yang tak mau berbuka sendirian',[
 '<h2>Kebiasaan <span>seorang sahabat</span></h2><div class="rows" style="top:600px"><div class="card p">Abdullah bin Umar, putra Umar bin Khattab, punya kebiasaan di bulan Ramadan: ia <b>tidak berbuka puasa kecuali bersama anak yatim dan orang miskin.</b></div></div>',
 '<h2>Malam itu ia <span>tidak makan</span></h2><div class="rows" style="top:600px"><div class="card p">Pernah keluarganya menolak orang-orang miskin yang datang. Begitu tahu, Ibnu Umar memilih <b>tidak makan malam itu.</b></div></div>',
 '<h2>Tradisi yang <span>berlanjut</span></h2><div class="rows" style="top:600px"><div class="card p">Generasi sesudahnya meneruskan. Hammad bin Abi Sulaiman, guru Imam Abu Hanifah, <b>memberi makan berbuka 500 orang setiap malam Ramadan.</b></div></div><div class="src">Kisah dihimpun Ibnu Rajab al-Hanbali dalam Latha’if al-Ma’arif</div>',
 '<h2>Hikmahnya</h2><div class="rows" style="top:560px"><div class="card q">Bagi mereka, berbuka bukan urusan perut sendiri. Meja makan adalah tempat berbagi.</div></div><div class="cta" style="bottom:170px">Siapa yang ingin Anda ajak berbuka Ramadan nanti? <b>Tandai di komentar.</b></div>'])

out='<!doctype html><html><head><meta charset="utf-8"><style>'+CSS+'</style></head><body>\n'
man=[]
for name,slides in P:
    for i,h in enumerate(slides):
        out+=f'<section class="slide" style="background:{G}">{h}</section>\n';man.append((name,i+1,len(slides)))
out+='</body></html>'
open(D+'/series11.html','w').write(out);json.dump(man,open(D+'/series11.json','w'))
print(len(man),'slide')
