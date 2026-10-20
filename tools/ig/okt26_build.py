import os, json
D=os.path.dirname(os.path.abspath(__file__))
F=D+'/fontpkg/node_modules'
LOGO='file:///home/claude/zakademia/assets/images/logo-color.png'
ART=json.load(open(D+'/arts9.json'))
A=dict(zip(['emas','tabungan','toko','fidyah','asnaf','sawah','pajak','nazhir','retensi'],ART))
GT='linear-gradient(345deg,#FFFFF0 0%,#FFFFF0 10%,#0B7A5E 36%,#064E3B 100%)'
GN='linear-gradient(345deg,#0B7A5E 0%,#064E3B 100%)'
CSS='''@font-face{font-family:"ns";font-weight:400;src:url("file://%(f)s/@fontsource/noto-serif/files/noto-serif-latin-400-normal.woff2")}
@font-face{font-family:"ns";font-weight:600;font-style:italic;src:url("file://%(f)s/@fontsource/noto-serif/files/noto-serif-latin-600-italic.woff2")}
@font-face{font-family:"ns";font-weight:800;src:url("file://%(f)s/@fontsource/noto-serif/files/noto-serif-latin-800-normal.woff2")}
@font-face{font-family:"nsc";font-weight:100 900;font-stretch:62.5%% 100%%;src:url("file://%(f)s/@fontsource-variable/noto-serif/files/noto-serif-latin-wdth-normal.woff2")}
*{margin:0;box-sizing:border-box}
body{font-family:"nsc",serif;font-stretch:87.5%%;color:#fff}
.slide{width:1080px;height:1350px;position:relative;overflow:hidden;color:#fff}
.logo{position:absolute;left:84px;top:60px;width:76px;height:76px;border-radius:22px;background:#FFF8EC;display:grid;place-items:center}
.logo img{height:52px}
.badge{position:absolute;left:176px;top:76px;height:44px;padding:0 20px;border-radius:99px;background:#FFD23F;color:#0B1F17;font-family:"ns",serif;font-style:italic;font-weight:600;font-size:24px;display:flex;align-items:center}
.art{position:absolute;right:84px;top:150px;width:300px;height:300px;border-radius:40px 40px 40px 14px;overflow:hidden;border:8px solid #FFF8EC;box-shadow:0 24px 50px -20px rgba(0,0,0,.45)}
.art.sm{top:60px;width:200px;height:200px;border-radius:30px 30px 30px 10px;border-width:6px}
h1{position:absolute;left:84px;right:420px;top:200px;font-family:"ns",serif;font-weight:800;font-size:80px;line-height:1.04;letter-spacing:-.02em}
h2{position:absolute;left:84px;right:330px;top:290px;font-family:"ns",serif;font-weight:800;font-size:66px;line-height:1.06;letter-spacing:-.015em}
h1 span,h2 span{font-style:italic;font-weight:600;color:#FFD23F}
.big{position:absolute;left:84px;right:84px;top:720px}
.big .n{font-family:"ns",serif;font-weight:800;font-size:140px;line-height:1}
.big .c{font-size:36px;margin-top:18px;line-height:1.35}
.rows{position:absolute;left:84px;right:84px;top:720px;display:flex;flex-direction:column;gap:26px}
.row{display:flex;gap:24px;font-size:36px;line-height:1.35;font-weight:600}
.row i{flex:none;width:58px;height:58px;border-radius:50%;display:grid;place-items:center;font-style:normal;font-weight:800;font-size:26px;background:#FFD23F;color:#0B1F17}
.row small{display:block;font-size:28px;font-weight:400;opacity:.9;margin-top:4px}
.card{border-radius:28px;padding:30px 40px;font-size:34px;line-height:1.4;background:#FFF8EC;color:#0B1F17}
.card b{color:#0B6B52}
.card .r{display:block;font-family:"ns",serif;font-weight:800;font-size:46px;color:#0B6B52;margin-top:4px}
.src{position:absolute;left:84px;right:84px;bottom:150px;font-size:22px;line-height:1.4;opacity:.92}
.dk{color:#0B1F17}
.geser{position:absolute;left:84px;bottom:64px;height:60px;padding:0 30px;border-radius:99px;background:#FFD23F;color:#0B1F17;font-weight:800;font-size:28px;display:flex;align-items:center;gap:12px}
.dots{position:absolute;right:84px;bottom:86px;display:flex;gap:10px}.dots i{width:12px;height:12px;border-radius:50%;background:rgba(255,255,255,.4)}.dots i.on{background:#FFD23F;width:34px;border-radius:6px}
.dark .dots i{background:rgba(11,31,23,.25)}
.act{position:absolute;left:84px;right:84px;bottom:56px;display:flex;align-items:center;gap:40px;font-size:28px;font-weight:700}
.act span{display:flex;align-items:center;gap:10px}.act svg{width:46px;height:46px}
.art:not(.sm) ~ h2{right:420px;top:200px;font-size:72px}
.cta{position:absolute;left:84px;right:84px;bottom:150px;background:#FFD23F;color:#0B1F17;border-radius:28px;padding:28px 36px;font-size:32px;font-weight:700;line-height:1.35}
'''.replace('%(f)s',F).replace('%%','%')
IC={'Suka':'<path d="M20 34s-13-8-13-17a7 7 0 0 1 13-4 7 7 0 0 1 13 4c0 9-13 17-13 17z" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linejoin="round"/>',
 'Simpan':'<path d="M11 6h18v28l-9-7-9 7z" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linejoin="round"/>',
 'Bagikan':'<path d="M35 6 5 18l12 5 4 12z M17 23 35 6" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linejoin="round"/>'}
def act(dark=False): return f'<div class="act{" dk" if dark else ""}">'+''.join(f'<span><svg viewBox="0 0 40 40">{v}</svg>{k}</span>' for k,v in IC.items())+'</div>'
def head(series,art,sm=False): return f'<div class="logo"><img src="{LOGO}"></div><div class="badge">{series}</div><div class="art{" sm" if sm else ""}">{A[art]}</div>'
def geser(i,n): return '<div class="geser">Geser →</div><div class="dots">'+''.join(f'<i class="{"on" if k==i else ""}"></i>' for k in range(n))+'</div>'

# (nama, gradien, [slide html])  — slide pertama = sampul
P=[]
def single(name,g,series,art,body,src,dark_bottom=False):
    P.append((name,g,[head(series,art)+body+(f'<div class="src{" dk" if dark_bottom else ""}">{src}</div>' if src else '')+act(dark_bottom)]))
def carousel(name,g,series,art,slides,dark_bottom=False):
    n=len(slides);out=[]
    for i,s in enumerate(slides):
        h=head(series,art,sm=0<i<n-1)+s
        h+=act(dark_bottom) if i==n-1 else geser(i,n)
        out.append(h)
    P.append((name,g,out))

single('zakat-emas',GT,'ZakaPedia','emas','<h1>Punya emas 85 gram? <span>Sudah wajib zakat</span></h1><div class="big"><div class="n">2,5%</div><div class="c">dari emas yang dimiliki selama setahun (haul). Nisabnya dihitung dalam gram, bukan rupiah tetap.</div></div>','Sumber: PMA No. 52 Tahun 2014 Pasal 4 dan 7',True)
single('zakat-tabungan',GT,'ZakaTips','tabungan','<h1>Saldo mengendap setahun? <span>Cek nisabnya</span></h1><div class="rows" style="top:760px"><div class="card"><b>Syarat</b><br>Saldo minimal senilai 85 gram emas dan sudah tersimpan satu tahun.</div><div class="card"><b>Contoh</b><br>Saldo Rp300 juta di atas nisab × 2,5% = <b>Rp7,5 juta</b></div></div>','Sumber: PMA No. 52 Tahun 2014 Pasal 8–10',True)
carousel('zakat-usaha',GT,'ZakaTips','toko',[
 '<h1>Zakat usaha dihitung dari <span>aset lancar</span></h1><div class="big" style="top:760px"><div class="c" style="font-size:40px">Bukan dari omzet. Geser untuk rumus dan contohnya.</div></div>',
 '<h2>Apa saja <span>aset lancar?</span></h2><div class="rows" style="top:640px"><div class="row"><i>1</i><div>Kas dan saldo bank<small>uang tunai dan rekening usaha</small></div></div><div class="row"><i>2</i><div>Stok barang dagangan<small>dinilai dengan harga saat ini</small></div></div><div class="row"><i>3</i><div>Piutang yang lancar<small>yang masih bisa ditagih</small></div></div></div>',
 '<h2>Rumusnya</h2><div class="rows" style="top:560px"><div class="card" style="font-size:40px;text-align:center;font-weight:700">(Aset lancar − utang jangka pendek) × 2,5%</div><div class="card">Wajib bila hasilnya mencapai nisab <b>85 gram emas</b> dan usaha sudah berjalan <b>setahun</b>.</div></div><div class="src dk">Sumber: PMA No. 52 Tahun 2014 Pasal 11–13</div>',
 '<h2>Contoh <span>hitungannya</span></h2><div class="rows" style="top:560px"><div class="card">Aset lancar<span class="r">Rp500 juta</span></div><div class="card">Utang jangka pendek<span class="r">Rp100 juta</span></div><div class="card">Zakat: Rp400 juta × 2,5%<span class="r">Rp10 juta</span></div></div>'
],True)
single('fidyah-2026',GN,'ZakaPedia','fidyah','<h1>Tak mampu puasa? <span>Tunaikan fidyah</span></h1><div class="big"><div class="n">Rp65.000</div><div class="c">per jiwa per hari, bagi yang tidak mampu berpuasa, seperti lansia atau sakit yang tak kunjung sembuh. Utang 10 hari = Rp650.000.</div></div>','Acuan BAZNAS RI. Sumber: Keputusan Ketua BAZNAS RI No. 14 Tahun 2026')
carousel('delapan-asnaf',GN,'ZakaPedia','asnaf',[
 '<h1>Siapa saja <span>penerima zakat?</span></h1><div class="big" style="top:760px"><div class="n">8</div><div class="c">golongan (asnaf) yang disebut dalam QS At-Taubah ayat 60.</div></div>',
 '<h2>Golongan <span>1–4</span></h2><div class="rows" style="top:560px"><div class="row"><i>1</i><div>Fakir<small>tidak punya harta dan penghasilan yang mencukupi</small></div></div><div class="row"><i>2</i><div>Miskin<small>punya penghasilan, tetapi belum mencukupi</small></div></div><div class="row"><i>3</i><div>Amil<small>pihak yang mengelola zakat</small></div></div><div class="row"><i>4</i><div>Mualaf<small>orang yang hatinya perlu dikuatkan dalam Islam</small></div></div></div>',
 '<h2>Golongan <span>5–8</span></h2><div class="rows" style="top:560px"><div class="row"><i>5</i><div>Riqab<small>untuk memerdekakan orang dari perbudakan</small></div></div><div class="row"><i>6</i><div>Gharimin<small>orang yang terlilit utang untuk kebutuhan yang dibolehkan</small></div></div><div class="row"><i>7</i><div>Fisabilillah<small>untuk kepentingan di jalan Allah</small></div></div><div class="row"><i>8</i><div>Ibnu sabil<small>musafir yang kehabisan bekal</small></div></div></div><div class="src">Sumber: QS At-Taubah: 60</div>',
 '<h2>Golongan mana yang paling sering <span>Anda temui?</span></h2><div class="cta" style="bottom:170px">Tulis di kolom komentar, dan bagikan ke teman yang sedang belajar tentang zakat.</div>'
])
single('zakat-pertanian',GN,'ZakaTips','sawah','<h1>Panen mencapai <span>653 kg gabah?</span></h1><div class="rows"><div class="row"><i>10%</i>Bila sawah tadah hujan</div><div class="row"><i>5%</i>Bila memakai irigasi dan perawatan</div><div class="row"><i>✓</i>Ditunaikan saat panen, tanpa menunggu setahun</div></div>','Sumber: PMA No. 52 Tahun 2014 Pasal 14–15')
carousel('zakat-pajak',GN,'ZakaTips','pajak',[
 '<h1 style="font-size:72px">Zakat mengurangi <span>penghasilan kena pajak</span></h1><div class="big" style="top:760px"><div class="c" style="font-size:40px">Asal dibayar dengan cara yang benar. Geser untuk syaratnya.</div></div>',
 '<h2>Tiga <span>syaratnya</span></h2><div class="rows" style="top:600px"><div class="row"><i>1</i>Dibayar melalui BAZNAS atau LAZ resmi</div><div class="row"><i>2</i>Ada bukti setor zakat dari lembaga</div><div class="row"><i>3</i>Bukti dilampirkan saat lapor SPT Tahunan</div></div><div class="src">Sumber: UU No. 23 Tahun 2011 Pasal 22–23; PP No. 60 Tahun 2010</div>',
 '<h2>Yang berkurang <span>dasar pajaknya</span></h2><div class="rows" style="top:600px"><div class="card">Zakat Rp3 juta mengurangi <b>penghasilan yang dikenai pajak</b>, bukan memotong tagihan pajak sebesar Rp3 juta.</div></div><div class="cta">Simpan bukti setor zakat Anda sampai waktu lapor SPT.</div>'
])
single('tugas-nazhir',GN,'ZakaPedia','nazhir','<h1>Apa tugas <span>nazhir wakaf?</span></h1><div class="rows"><div class="row"><i>1</i>Mengadministrasikan harta wakaf</div><div class="row"><i>2</i>Mengelola dan mengembangkannya sesuai peruntukan</div><div class="row"><i>3</i>Mengawasi dan melindunginya</div><div class="row"><i>4</i>Melapor ke Badan Wakaf Indonesia</div></div>','Sumber: UU No. 41 Tahun 2004 Pasal 11')
carousel('retensi-donatur',GN,'ZakaTalk','retensi',[
 '<h1>Donatur lama <span>jangan dilupakan</span></h1><div class="big" style="top:760px"><div class="c" style="font-size:40px">Tiga pertanyaan untuk pengelola lembaga zakat dan wakaf.</div></div>',
 '<h2>Cek <span>minggu ini</span></h2><div class="rows" style="top:560px"><div class="row"><i>1</i>Berapa persen donatur tahun lalu yang kembali berdonasi?</div><div class="row"><i>2</i>Kapan terakhir mereka dihubungi selain saat diminta donasi?</div><div class="row"><i>3</i>Sudahkah mereka menerima laporan dampak?</div></div>',
 '<h2>Hitung retensi donatur <span>lembaga Anda</span></h2><div class="cta" style="bottom:170px">Gratis di zakademia.com/analitik. Cukup isi angka rekap tahunan. Tautan di bio.</div>'
])

def page(g,dark_last,html):
    return f'<section class="slide" style="background:{g}">{html}</section>'
out='<!doctype html><html><head><meta charset="utf-8"><style>'+CSS+'</style></head><body>\n'
manifest=[]
for name,g,slides in P:
    for i,h in enumerate(slides):
        out+=page(g,False,h)+'\n';manifest.append((name,i+1,len(slides)))
out+='</body></html>'
open(D+'/series10.html','w').write(out);json.dump(manifest,open(D+'/series10.json','w'))
print(len(manifest),'slide')
