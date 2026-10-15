# -*- coding: utf-8 -*-
"""Bangun halaman Literasi Zakademia: /literasi/, /literasi/<slug>/, gambar pratinjau, dan kartu di index.html.
Jalankan dari akar repositori:  python3 tools/literasi/build.py
"""
import html, os, re, sys, json
from datetime import date
sys.path.insert(0, os.path.dirname(__file__))
from articles import ARTIKEL, RUBRIK

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
BASE = 'https://zakademia.com'
BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
V = date.today().strftime('%Y%m%d')

def e(s): return html.escape(s, quote=True)
def tgl(iso):
    y, m, d = map(int, iso.split('-')); return f'{d} {BULAN[m-1]} {y}'
def url(a): return f'/literasi/{a["slug"]}/'
def strip_tags(s): return re.sub(r'<[^>]+>', '', s)
def refs(s, slug):
    return re.sub(r'\{(\d+)\}', lambda m: f'<sup class="ref"><a href="#sumber-{m.group(1)}" aria-label="Sumber {m.group(1)}">{m.group(1)}</a></sup>', s)

HEAD = '''<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>{title}</title>
<meta name="description" content="{desc}">
<link rel="canonical" href="{canon}">
<meta property="og:type" content="{ogtype}"><meta property="og:site_name" content="Zakademia">
<meta property="og:title" content="{title}"><meta property="og:description" content="{desc}">
<meta property="og:url" content="{canon}"><meta property="og:image" content="{img}">
<meta property="og:image:width" content="1200"><meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/assets/images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
<link rel="stylesheet" href="/literasi/literasi.css?v={v}">
{extra}
</head>
<body>
<a class="skip" href="#isi">Langsung ke isi</a>
<header class="lt-top"><div class="lt-wrap lt-nav">
  <a class="lt-brand" href="/"><img src="/assets/images/logo-color.png" alt=""><span>Zakademia</span></a>
  <nav aria-label="Utama"><a href="/#beranda">Beranda</a><a href="/#layanan">Layanan</a><a href="/literasi/"{lit}>Literasi</a><a class="lt-btn" href="/asesmen/masuk.php">Masuk</a></nav>
</div></header>
'''
FOOT = '''<footer class="lt-foot"><div class="lt-wrap">
  <a class="lt-brand" href="/"><img src="/assets/images/logo-color.png" alt=""><span>Zakademia</span></a>
  <p>Riset, Data, dan Literasi Filantropi Islam. <em>Bertumbuh bersama, bermanfaat bagi sesama.</em></p>
  <p class="lt-links"><a href="/#layanan">Layanan</a><a href="/literasi/">Literasi</a><a href="/#kontak">Kontak</a><a href="https://instagram.com/zakademia.official" target="_blank" rel="noopener">Instagram</a></p>
</div></footer>
</body></html>
'''

def chip(r, big=False):
    n, q, ic = RUBRIK[r]
    return f'<span class="lt-chip{" big" if big else ""}"><img src="/assets/images/{ic}" alt=""><b>{n}</b>{"<i>" + e(q) + "</i>" if big else ""}</span>'

def card(a):
    return (f'<a class="lt-card" href="{url(a)}">{chip(a["rubrik"])}<h3>{e(a["judul"])}</h3>'
            f'<p>{e(a["dek"])}</p><span class="lt-meta">{tgl(a["tanggal"])} · {a["menit"]} menit baca</span></a>')

def article_page(a):
    rn = RUBRIK[a['rubrik']][0]
    poin = ''.join(f'<li>{e(p)}</li>' for p in a['poin'])
    src = ''.join(f'<li id="sumber-{i}"><span>{e(pub)}.</span> <a href="{e(u)}" target="_blank" rel="noopener">{e(t)}</a>{"" if d == "—" else ". " + e(d)}.</li>'
                  for i, (pub, t, d, u) in enumerate(a['sumber'], 1))
    others = [o for o in ARTIKEL if o is not a][:3]
    canon = BASE + url(a)
    ld = {"@context": "https://schema.org", "@type": "Article", "headline": a['judul'], "description": a['dek'],
          "datePublished": a['tanggal'], "inLanguage": "id", "image": f'{BASE}/assets/images/og-lit-{a["slug"]}.png',
          "author": {"@type": "Organization", "name": "Tim Zakademia"},
          "publisher": {"@type": "Organization", "name": "Zakademia", "logo": {"@type": "ImageObject", "url": BASE + "/assets/images/logo-color.png"}},
          "mainEntityOfPage": canon}
    out = HEAD.format(title=e(f'{a["judul"]} | {rn} Zakademia'), desc=e(a['dek']), canon=canon, ogtype='article',
                      img=f'{BASE}/assets/images/og-lit-{a["slug"]}.png?v={V}', v=V, lit=' aria-current="page"',
                      extra='<script type="application/ld+json">' + json.dumps(ld, ensure_ascii=False) + '</script>')
    out += f'''<main id="isi">
<article class="lt-art">
  <header class="lt-head lt-wrap-n">
    <p class="lt-crumb"><a href="/literasi/">Literasi</a> / <a href="/literasi/?rubrik={a["rubrik"]}">{rn}</a></p>
    {chip(a["rubrik"], True)}
    <h1>{e(a["judul"])}</h1>
    <p class="lt-dek">{e(a["dek"])}</p>
    <p class="lt-byline"><img src="/assets/images/logo-color.png" alt=""><span><b>Tim Zakademia</b><br>{tgl(a["tanggal"])} · {a["menit"]} menit baca</span></p>
  </header>
  <div class="lt-wrap-n">
    <aside class="lt-poin" aria-label="Poin penting"><b>Poin penting</b><ul>{poin}</ul></aside>
    <div class="lt-body">{refs(a["isi"], a["slug"])}</div>
    <section class="lt-src" aria-labelledby="h-sumber"><h2 id="h-sumber">Sumber</h2><ol>{src}</ol>
      <p class="lt-note">Angka dan kutipan diambil dari sumber di atas pada saat tulisan ini terbit. Bila menemukan kekeliruan, kabari kami di <a href="mailto:info@zakademia.com">info@zakademia.com</a>.</p></section>
    <div class="lt-share"><span>Bagikan</span>
      <a class="wa" href="https://wa.me/?text={e(a["judul"] + " " + canon)}" target="_blank" rel="noopener">WhatsApp</a>
      <button type="button" data-copy="{canon}">Salin tautan</button></div>
  </div>
</article>
<section class="lt-more lt-wrap"><h2>Baca juga</h2><div class="lt-grid">{"".join(card(o) for o in others)}</div></section>
<section class="lt-cta lt-wrap"><div><b>Butuh data yang menjawab pertanyaan lembaga Anda?</b><p>Zakademia menemani lembaga filantropi Islam lewat riset, analitik, dan pendampingan.</p></div><a class="lt-btn gold" href="/#layanan">Lihat layanan</a></section>
</main>
<script>document.querySelectorAll('[data-copy]').forEach(function(b){{b.onclick=function(){{var u=b.getAttribute('data-copy');(navigator.clipboard?navigator.clipboard.writeText(u):Promise.reject()).then(function(){{b.textContent='Tersalin';setTimeout(function(){{b.textContent='Salin tautan';}},2000);}},function(){{prompt('Salin tautan ini:',u);}});}};}});</script>
'''
    return out + FOOT

def index_page():
    chips = '<button type="button" class="on" data-r="">Semua</button>' + ''.join(
        f'<button type="button" data-r="{k}"><img src="/assets/images/{v[2]}" alt="">{v[0]}</button>' for k, v in RUBRIK.items())
    cards = ''.join(f'<div data-r="{a["rubrik"]}">{card(a)}</div>' for a in sorted(ARTIKEL, key=lambda x: x['tanggal'], reverse=True))
    out = HEAD.format(title='Literasi | Zakademia', desc='Kabar, istilah, kisah, panduan, angka, dan tanya jawab seputar zakat, wakaf, dan filantropi Islam.',
                      canon=BASE + '/literasi/', ogtype='website', img=f'{BASE}/assets/images/og-image.png', v=V, lit=' aria-current="page"', extra='')
    out += f'''<main id="isi" class="lt-wrap">
<header class="lt-ihead"><p class="lt-eyebrow">Literasi</p><h1>Ilmu yang dibagikan kepada semua.</h1>
<p>Enam rubrik untuk memahami zakat, wakaf, dan filantropi Islam: kabar terbaru, istilah, kisah, panduan, angka, dan tanya jawab. Setiap tulisan disertai sumbernya.</p></header>
<div class="lt-filter" role="group" aria-label="Saring menurut rubrik">{chips}</div>
<div class="lt-grid" id="daftar">{cards}</div>
<p class="lt-empty" id="kosong" hidden>Belum ada tulisan di rubrik ini.</p>
</main>
<script>
(function(){{var bs=[].slice.call(document.querySelectorAll('.lt-filter button')),items=[].slice.call(document.querySelectorAll('#daftar>[data-r]'));
function pick(r){{bs.forEach(function(b){{var on=b.dataset.r===r;b.classList.toggle('on',on);b.setAttribute('aria-pressed',on);}});var n=0;items.forEach(function(i){{var s=!r||i.dataset.r===r;i.hidden=!s;if(s)n++;}});document.getElementById('kosong').hidden=n>0;
 var u=new URL(location.href);if(r)u.searchParams.set('rubrik',r);else u.searchParams.delete('rubrik');history.replaceState(null,'',u);}}
bs.forEach(function(b){{b.onclick=function(){{pick(b.dataset.r);}};}});pick(new URLSearchParams(location.search).get('rubrik')||'');}})();
</script>
'''
    return out + FOOT

def og_images():
    try:
        from PIL import Image, ImageDraw, ImageFont, ImageFilter
    except ImportError:
        print('PIL tidak tersedia; gambar pratinjau dilewati'); return
    F = os.path.join(os.path.dirname(__file__), 'fonts')
    def font(w, s): return ImageFont.truetype(os.path.join(F, f'plus-jakarta-sans-latin-{w}-normal.woff'), s)
    base = Image.open(os.path.join(ROOT, 'assets/images/og-image.png')).convert('RGB')
    for a in ARTIKEL:
        n, q, ic = RUBRIK[a['rubrik']]
        im = base.copy()
        layer = Image.new('RGB', (650, 500), (255, 255, 240)); gl = Image.new('L', (650, 500), 0)
        ImageDraw.Draw(gl).ellipse((-260, 260, 300, 760), fill=110); gl = gl.filter(ImageFilter.GaussianBlur(90))
        layer.paste(Image.new('RGB', (650, 500), (226, 236, 214)), (0, 0), gl); im.paste(layer, (0, 130))
        icon = Image.open(os.path.join(ROOT, 'assets/images', ic)).convert('RGBA').resize((60, 60))
        d = ImageDraw.Draw(im)
        lines = a['og']; size = 60
        while max(d.textlength(t, font=font(800, size)) for t in lines) > 560: size -= 2
        f = font(800, size); lh = int(size * 1.16)
        block = 70 + len(lines) * lh + 46; y = int(330 - block / 2)
        im.paste(icon, (84, y), icon)
        d.text((158, y + 4), n.upper(), font=font(800, 22), fill=(201, 154, 59))
        d.text((158, y + 32), q, font=font(500, 20), fill=(74, 99, 85)); y += 82
        for i, t in enumerate(lines):
            d.text((82, y), t, font=f, fill=(16, 41, 31) if i < len(lines) - 1 else (138, 100, 24)); y += lh
        d.text((86, y + 14), 'Literasi Zakademia · ' + tgl(a['tanggal']), font=font(500, 22), fill=(74, 99, 85))
        im.save(os.path.join(ROOT, f'assets/images/og-lit-{a["slug"]}.png'), optimize=True)

def home_cards():
    """Sisipkan kartu tulisan terbaru di bagian Literasi pada index.html (di antara penanda)."""
    p = os.path.join(ROOT, 'index.html'); s = open(p, encoding='utf-8').read()
    latest = sorted(ARTIKEL, key=lambda x: x['tanggal'], reverse=True)
    blk = ('<!-- LITERASI-START -->\n  <section class="wrap sec lt-home"><div class="sec-head"><h2>Bacaan <em>terbaru</em></h2>'
           '<p>Setiap tulisan disertai sumbernya, agar ilmu yang dibagikan dapat ditelusuri.</p></div>'
           '<div class="lt-grid">' + ''.join(card(a) for a in latest[:6]) + '</div>'
           '<p style="margin-top:28px"><a class="btn btn-deep" href="/literasi/">Semua tulisan</a></p></section>\n  <!-- LITERASI-END -->')
    if '<!-- LITERASI-START -->' in s:
        s = re.sub(r'<!-- LITERASI-START -->.*?<!-- LITERASI-END -->', lambda m: blk, s, flags=re.S)
    else:
        anchor = '<svg class="curve" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true"><path d="M0 70 C 360 0 900 0 1440 50 L1440 70 Z"/></svg>\n  <section class="deep"><div class="wrap sec">\n    <div data-reveal class="rubrics">'
        assert anchor in s, 'penanda bagian rubrik tidak ditemukan'
        s = s.replace(anchor, blk + '\n\n  ' + anchor, 1)
    # kartu rubrik mengarah ke daftar tulisan per rubrik
    for k, (n, q, ic) in RUBRIK.items():
        s = re.sub(r'<a class="rubric" href="[^"]*"( target="_blank" rel="noopener")?><img src="assets/images/' + re.escape(ic) + '"',
                   f'<a class="rubric" href="/literasi/?rubrik={k}"><img src="assets/images/{ic}"', s)
    open(p, 'w', encoding='utf-8').write(s)

if __name__ == '__main__':
    os.makedirs(os.path.join(ROOT, 'literasi'), exist_ok=True)
    for a in ARTIKEL:
        d = os.path.join(ROOT, 'literasi', a['slug']); os.makedirs(d, exist_ok=True)
        open(os.path.join(d, 'index.html'), 'w', encoding='utf-8').write(article_page(a))
    open(os.path.join(ROOT, 'literasi', 'index.html'), 'w', encoding='utf-8').write(index_page())
    og_images(); home_cards()
    print('selesai:', len(ARTIKEL), 'artikel')
