# -*- coding: utf-8 -*-
"""Bangun indeks pencarian situs Zakademia: js/cari-indeks.json.

Sumber yang diindeks:
  - artikel Literasi (tools/literasi/articles.py), termasuk isi lengkapnya
  - isi beranda di index.html: halaman, layanan, bidang kajian, buku, dan artikel ilmiah
  - modul Ruang Belajar (belajar/modul.js, dibaca lewat Node)
  - halaman alat (daftar ALAT di bawah)

Jalankan dari akar repositori:  python3 tools/cari/indeks.py
Skrip ini juga dijalankan otomatis oleh tools/literasi/build.py.
Jalankan ulang setiap kali isi index.html, artikel, atau modul berubah.
Hanya memakai pustaka bawaan Python (ditambah Node untuk membaca modul.js).
"""
import html, json, os, re, subprocess, sys
from html.parser import HTMLParser

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.abspath(os.path.join(HERE, '..', '..'))
OUT = os.path.join(ROOT, 'js', 'cari-indeks.json')

# Halaman alat yang tidak punya teks statis untuk diindeks.
ALAT = [
    dict(t='Asesmen Kesiapan Penghimpunan', k='Alat', u='/asesmen/',
         d='Formulir untuk memetakan kesiapan penghimpunan lembaga, sebagai langkah awal Pendampingan Strategi.',
         x='asesmen kesiapan penghimpunan fundraising lembaga formulir pendampingan strategi'),
    dict(t='Survei', k='Alat', u='/survei/',
         d='Buat dan sebarkan survei untuk Riset Kebutuhan lembaga, lalu baca hasilnya. Perlu masuk akun.',
         x='survei kuesioner riset kebutuhan responden formulir hasil buat survei'),
    dict(t='Kesehatan Penghimpunan', k='Alat', u='/analitik/',
         d='Hitung retensi, CLV, CAC, dan biaya per rupiah terhimpun dari rekap penghimpunan lembaga. Perlu masuk akun.',
         x='data analitik kesehatan penghimpunan retensi clv cac donatur rekap kalkulator'),
    dict(t='Ruang Belajar', k='Alat', u='/belajar/',
         d='Kamar Analitik bersama Zaida dan Kamar Riset bersama Wafiy. Modul singkat dengan kuis. Perlu masuk akun.',
         x='ruang belajar modul kuis kelas metodologi penelitian zaida wafiy kamar analitik kamar riset'),
    dict(t='Masuk atau daftar akun', k='Akun', u='/asesmen/masuk.php',
         d='Masuk untuk memakai Survei, Kesehatan Penghimpunan, dan Ruang Belajar.',
         x='masuk login daftar akun registrasi kata sandi lupa'),
]

NAMA_HALAMAN = {'beranda': 'Beranda', 'layanan': 'Layanan', 'kajian': 'Kajian',
                'literasi': 'Literasi', 'tentang': 'Tentang', 'kontak': 'Kontak'}

# ---------------------------------------------------------------- pengurai HTML sederhana
VOID = {'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'source', 'track', 'wbr'}
SKIP = {'script', 'style', 'svg', 'template', 'noscript', 'select', 'textarea', 'button', 'form', 'video'}


class Node:
    def __init__(self, tag, attrs, parent=None):
        self.tag, self.attrs, self.parent, self.kids = tag, dict(attrs), parent, []

    def cls(self):
        return (self.attrs.get('class') or '').split()

    def walk(self):
        for k in self.kids:
            if isinstance(k, Node):
                yield k
                yield from k.walk()

    def find_all(self, tag=None, cls=None, attr=None):
        return [n for n in self.walk() if (tag is None or n.tag == tag) and (cls is None or cls in n.cls())
                and (attr is None or attr in n.attrs)]

    def find(self, tag=None, cls=None, attr=None):
        r = self.find_all(tag, cls, attr)
        return r[0] if r else None

    def text(self):
        out = []
        for k in self.kids:
            if isinstance(k, str):
                out.append(k)
            elif k.tag not in SKIP:
                blockish = k.tag in ('p', 'h1', 'h2', 'h3', 'h4', 'li', 'div', 'article', 'section', 'br', 'span', 'a', 'b', 'figcaption')
                t = k.text()
                out.append(' ' + t + ' ' if blockish else t)
        return rapi(''.join(out))


class Tree(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.root = self.cur = Node('root', [])

    def handle_starttag(self, tag, attrs):
        n = Node(tag, attrs, self.cur)
        self.cur.kids.append(n)
        if tag not in VOID:
            self.cur = n

    def handle_endtag(self, tag):
        c = self.cur
        while c is not self.root and c.tag != tag:
            c = c.parent
        if c is not self.root:
            self.cur = c.parent

    def handle_data(self, d):
        self.cur.kids.append(d)


def rapi(s):
    return re.sub(r'\s+', ' ', s).strip()


def potong(s, n=220):
    s = rapi(s)
    return s if len(s) <= n else s[:n].rsplit(' ', 1)[0] + '…'


def tanpa_tag(s):
    return rapi(html.unescape(re.sub(r'<[^>]+>', ' ', re.sub(r'\{\d+\}', '', s))))


# ---------------------------------------------------------------- sumber
def dari_literasi():
    sys.path.insert(0, os.path.join(ROOT, 'tools', 'literasi'))
    from articles import ARTIKEL, RUBRIK
    out = []
    for a in sorted(ARTIKEL, key=lambda x: x['tanggal'], reverse=True):
        out.append(dict(t=a['judul'], k='Literasi · ' + RUBRIK[a['rubrik']][0], u=f'/literasi/{a["slug"]}/',
                        d=a['dek'], x=' '.join(a['poin']) + ' ' + tanpa_tag(a['isi'])))
    return out


def dari_beranda():
    t = Tree()
    t.feed(open(os.path.join(ROOT, 'index.html'), encoding='utf-8').read())
    pages = {n.attrs['data-page']: n for n in t.root.find_all(attr='data-page')}
    out = []
    # setiap halaman beranda sebagai satu hasil
    for key, nama in NAMA_HALAMAN.items():
        p = pages.get(key)
        if p is None:
            continue
        h1 = p.find('h1')
        lede = p.find(cls='lede')
        d = lede.text() if lede else ''
        out.append(dict(t=nama, k='Halaman', u='/' if key == 'beranda' else f'/#{key}',
                        d=potong(d or (h1.text() if h1 else '')), x=p.text()))
    lay = pages.get('layanan')
    if lay is not None:
        for art in lay.find_all('article'):
            i = art.attrs.get('id', '')
            if not i.startswith('layanan-'):
                continue
            h = art.find('h2') or art.find('h3')
            ps = [x.text() for x in art.find_all('p')]
            out.append(dict(t=h.text() if h else i, k='Layanan', u='/#layanan/' + i[len('layanan-'):],
                            d=potong(ps[0] if ps else ''), x=art.text()))
    kaj = pages.get('kajian')
    if kaj is not None:
        for art in kaj.find_all('article', 'study'):
            h, sub, lead = art.find('h3'), art.find(cls='study-sub'), art.find(cls='study-lead')
            out.append(dict(t=h.text(), k='Bidang kajian', u='/#kajian', s=h.text(),
                            d=potong((lead or sub).text() if (lead or sub) else ''), x=art.text()))
        for art in kaj.find_all('article', 'book'):
            h, sub = art.find('h3'), art.find(cls='book-sub')
            body = [x for x in art.find_all('p') if not x.cls()]
            a = art.find('a')
            href = (a.attrs.get('href') or '') if a else ''
            u = href if href.startswith('/buku/') else '/#kajian'
            out.append(dict(t=h.text() + (': ' + sub.text() if sub else ''), k='Buku digital' if u != '/#kajian' else 'Buku', u=u, s=h.text(),
                            d=potong(body[0].text() if body else ''), x=art.text()))
        for art in kaj.find_all('article', 'paper'):
            h, au, jr, sm = art.find('h3'), art.find(cls='paper-au'), art.find(cls='paper-jr'), art.find(cls='paper-sum')
            meta = ' · '.join(x.text() for x in (au, jr) if x)
            out.append(dict(t=h.text(), k='Artikel ilmiah', u='/#kajian', s=h.text(),
                            d=potong(sm.text() if sm else meta), x=art.text()))
    return out


def dari_belajar():
    js = ("global.window={};require(process.argv[1]);"
          "process.stdout.write(JSON.stringify({k:window.ZK_KAMAR,m:window.ZK_MODUL}))")
    try:
        r = subprocess.run(['node', '-e', js, os.path.join(ROOT, 'belajar', 'modul.js')],
                           capture_output=True, text=True, check=True)
    except (OSError, subprocess.CalledProcessError) as err:
        print('peringatan: modul Ruang Belajar dilewati (Node tidak tersedia atau modul.js galat):', err)
        return []
    data = json.loads(r.stdout)
    kamar = {k['id']: k for k in data['k']}
    out = []
    for mid, m in data['m'].items():
        km = kamar.get(m['kamar'], {})
        siap = m.get('status') == 'siap'
        isi = [' '.join(m.get('tujuan', []))]
        for b in m.get('bagian', []):
            isi.append(b.get('h', ''))
            for key in ('p', 'rumus', 'contoh', 'p2', 'daftar'):
                v = b.get(key)
                isi.extend([v] if isinstance(v, str) else [x for x in (v or []) if isinstance(x, str)])
        d = 'Tujuan: ' + '; '.join(m.get('tujuan', []))
        out.append(dict(t=m['judul'], k=f'Ruang Belajar · {km.get("nama", "")}',
                        u=f'/belajar/?m={mid}', d=potong(d) + ('' if siap else ' (Segera hadir)'),
                        x=rapi(' '.join(isi)) + ' ' + km.get('pemandu', ''), m=0 if siap else 1))
    return out


def main():
    items = dari_literasi() + dari_beranda() + dari_belajar() + ALAT
    for it in items:
        it['x'] = potong(it['x'], 6000)
    os.makedirs(os.path.dirname(OUT), exist_ok=True)
    with open(OUT, 'w', encoding='utf-8') as f:
        json.dump(items, f, ensure_ascii=False, separators=(',', ':'))
    print(f'indeks pencarian: {len(items)} entri, {os.path.getsize(OUT) // 1024} KB -> js/cari-indeks.json')


if __name__ == '__main__':
    main()
