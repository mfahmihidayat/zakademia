#!/usr/bin/env python3
"""Ubah PDF buku menjadi halaman gambar WebP untuk pembaca flip di /buku/.

    python3 tools/buku/render.py metodologi /path/buku.pdf [--lebar 1100] [--mutu 68]
    Hemat ruang (dipakai sejak Okt 2026): --lebar 900 --mutu 35 --hemat

Keluaran: buku/halaman/<slug>/p0001.webp ... dan meta.json. Halaman pertama PDF yang berbentuk
bentangan sampul (lebar > 1,5 x tinggi) dipotong menjadi sampul depan (halaman 1) dan sampul belakang (halaman terakhir).
Butuh: pip install pymupdf pillow numpy
"""
import sys, os, json, argparse
import pymupdf, numpy as np
from PIL import Image

ap = argparse.ArgumentParser()
ap.add_argument('slug'); ap.add_argument('pdf')
ap.add_argument('--lebar', type=int, default=1100); ap.add_argument('--mutu', type=int, default=68)
ap.add_argument('--hemat', action='store_true', help='halaman bertulisan disimpan berpalet 16 warna (lossless), halaman bergambar tetap lossy; total jauh lebih kecil')
a = ap.parse_args()
root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
out = os.path.join(root, 'buku', 'halaman', a.slug)
os.makedirs(out, exist_ok=True)
for f in os.listdir(out):
    if f.endswith('.webp'): os.remove(os.path.join(out, f))
d = pymupdf.open(a.pdf)

def render(page, clip=None):
    r = clip or page.rect
    z = a.lebar / r.width
    pix = page.get_pixmap(matrix=pymupdf.Matrix(z, z), clip=clip, alpha=False)
    return Image.frombytes('RGB', (pix.width, pix.height), pix.samples)

pages = []  # (judul, gambar)
belakang = None
p0 = d[0]
if p0.rect.width > 1.5 * p0.rect.height:
    im = render(p0)
    arr = np.asarray(im).astype(int); dark = (arr.sum(-1) < 300).mean(0)
    cols = [x for x in range(arr.shape[1]) if dark[x] > 0.8]
    W = arr.shape[1]
    if cols and 0.3 * W < cols[0] < 0.7 * W:
        s0, s1 = cols[0], cols[-1]
    else:
        s0 = s1 = W // 2
    scale = p0.rect.width / W
    front = pymupdf.Rect(s1 * scale, 0, p0.rect.width, p0.rect.height)
    back = pymupdf.Rect(0, 0, s0 * scale, p0.rect.height)
    pages.append(('Sampul depan', render(p0, front)))
    belakang = ('Sampul belakang', render(p0, back))
    start = 1
else:
    start = 0
for i in range(start, len(d)):
    pages.append((f'Halaman {i - start + 1 + (1 if belakang else 0)}', render(d[i])))
if belakang: pages.append(belakang)

def simpan(im, f):
    """--hemat: jumlah warna adaptif per halaman (8 s.d. 64, lossless) selama warnanya tidak bergeser;
    halaman berfoto/bergradasi memakai WebP lossy. Buku 483 halaman pada --lebar 900 --mutu 35 sekitar 19 MB."""
    if not a.hemat:
        im.save(f, 'WEBP', quality=a.mutu, method=6); return
    A = np.asarray(im, dtype=np.int16)
    for n in (8, 12, 16, 24, 32, 48, 64):
        q = im.quantize(n, method=Image.Quantize.MEDIANCUT, dither=Image.Dither.NONE)
        e = np.abs(np.asarray(q.convert('RGB'), dtype=np.int16) - A)
        if e.mean() <= 1.5 and np.percentile(e.max(-1), 99.5) <= 40:
            q.save(f, 'WEBP', lossless=True, quality=100, method=2); return
    im.save(f, 'WEBP', quality=a.mutu, method=6)

meta = {'slug': a.slug, 'jumlah': len(pages), 'lebar': None, 'tinggi': None, 'halaman': []}
for n, (judul, im) in enumerate(pages, 1):
    # samakan ukuran semua halaman dengan halaman isi (halaman kedua)
    if meta['lebar'] is None and n == 2: meta['lebar'], meta['tinggi'] = im.size
    f = os.path.join(out, f'p{n:04d}.webp')
    simpan(im, f)
    meta['halaman'].append({'n': n, 'w': im.size[0], 'h': im.size[1]})
    if n % 50 == 0: print(n, '/', len(pages))
for n, (judul, im) in enumerate(pages, 1):
    if im.size != (meta['lebar'], meta['tinggi']):
        im2 = im.resize((meta['lebar'], meta['tinggi']), Image.LANCZOS)
        simpan(im2, os.path.join(out, f'p{n:04d}.webp'))
        meta['halaman'][n - 1].update({'w': meta['lebar'], 'h': meta['tinggi']})
json.dump(meta, open(os.path.join(out, 'meta.json'), 'w'))
tot = sum(os.path.getsize(os.path.join(out, f)) for f in os.listdir(out))
print('selesai:', len(pages), 'halaman,', round(tot / 1e6, 1), 'MB di', out)
