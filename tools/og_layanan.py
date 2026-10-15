# -*- coding: utf-8 -*-
"""Gambar pratinjau tautan layanan (ilustrasi). Jalankan: python3 tools/og_layanan.py"""
import os, sys
HERE = os.path.dirname(os.path.abspath(__file__)); ROOT = os.path.dirname(HERE)
sys.path.insert(0, os.path.join(HERE, 'literasi'))
from build import render_og
L = [('og-riset.png', 'survei', 'Riset Kebutuhan', 'Kuesioner, tautan, dan kode QR', ['Buat survei,', 'dengar kebutuhan.']),
     ('og-analitik.png', 'dasbor', 'Data & Analitik', 'Retensi, CLV, dan CAC', ['Kesehatan', 'penghimpunan.']),
     ('og-asesmen.png', 'asesmen', 'Pendampingan Strategi', 'Langkah awal pendampingan lembaga', ['Asesmen kesiapan', 'penghimpunan.']),
     ('og-belajar.png', 'belajar', 'Ruang Belajar', 'Kamar Analitik dan Kamar Riset', ['Belajar tenang,', 'praktik langsung.'])]
render_og([dict(out=os.path.join(ROOT, 'assets/images', o), art=a, eyebrow=e, sub=s, lines=l, footer='zakademia.com', icon=None) for o, a, e, s, l in L])
