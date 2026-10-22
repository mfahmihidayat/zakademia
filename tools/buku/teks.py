#!/usr/bin/env python3
"""Ekstrak teks per halaman flipbook untuk fitur cari: python3 tools/buku/teks.py <slug> <pdf>
Pemetaan mengikuti render.py: bentangan sampul -> halaman 1 (depan) dan terakhir (belakang); halaman PDF ke-i (indeks 1..) -> halaman flip i+1."""
import sys, os, json, re
import pymupdf
slug, pdf = sys.argv[1], sys.argv[2]
ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
d = pymupdf.open(pdf)
p0 = d[0]; sampul = p0.rect.width > 1.5 * p0.rect.height
teks = {}
def bersih(t): return re.sub(r'\s+', ' ', t.replace('­', '')).strip()
if sampul:
    teks[1] = bersih(p0.get_text()); start = 1
else: start = 0
for i in range(start, len(d)): teks[i - start + 1 + (1 if sampul else 0)] = bersih(d[i].get_text())
if sampul: teks[len(d) + 1] = ''
out = os.path.join(ROOT, 'buku', 'halaman', slug, 'teks.json')
json.dump({str(k): v for k, v in teks.items()}, open(out, 'w', encoding='utf-8'), ensure_ascii=False)
print(out, len(teks), 'halaman', os.path.getsize(out) // 1024, 'KB')
