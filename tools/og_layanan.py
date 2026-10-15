# -*- coding: utf-8 -*-
"""Gambar pratinjau tautan layanan. Jalankan: python3 tools/og_layanan.py"""
import os, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from ogcard import card, ROOT
L = [
    ('og-riset.png', 'foto-1.jpg', 'Riset Kebutuhan', 'Kuesioner, tautan, dan kode QR', ['Buat survei,', 'dengar kebutuhan.']),
    ('og-analitik.png', 'foto-2.jpg', 'Data & Analitik', 'Retensi, CLV, dan CAC', ['Kesehatan', 'penghimpunan.']),
    ('og-asesmen.png', 'foto-3.jpg', 'Pendampingan Strategi', 'Langkah awal pendampingan lembaga', ['Asesmen kesiapan', 'penghimpunan.']),
    ('og-belajar.png', 'foto-5.jpg', 'Ruang Belajar', 'Kamar Analitik dan Kamar Riset', ['Belajar tenang,', 'praktik langsung.']),
]
for out, foto, eb, sub, lines in L:
    card(os.path.join(ROOT, 'assets/images', foto), eb, sub, lines, 'zakademia.com', os.path.join(ROOT, 'assets/images', out))
print('selesai')
