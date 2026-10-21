#!/usr/bin/env python3
"""Buat sitemap.xml dari halaman publik situs. Jalankan: python3 tools/sitemap.py (otomatis dipanggil build literasi)."""
import glob, os, re, datetime, subprocess
ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SITE = 'https://zakademia.com'

def mtime(path):
    try:
        out = subprocess.run(['git', 'log', '-1', '--format=%cs', '--', path], cwd=ROOT, capture_output=True, text=True).stdout.strip()
        if out: return out
    except Exception: pass
    return datetime.date.fromtimestamp(os.path.getmtime(os.path.join(ROOT, path))).isoformat()

pages = [('/', 'index.html', 'weekly', '1.0'), ('/literasi/', 'literasi/index.html', 'daily', '0.9'),
         ('/buku/metodologi/', 'buku/index.php', 'monthly', '0.9'), ('/asesmen/', 'asesmen/index.html', 'monthly', '0.8'),
         ('/survei/', 'survei/index.php', 'monthly', '0.6'), ('/analitik/', 'analitik/index.php', 'monthly', '0.6'),
         ('/belajar/', 'belajar/index.php', 'monthly', '0.6'), ('/layanan/', 'layanan/index.php', 'monthly', '0.6')]
for f in sorted(glob.glob(os.path.join(ROOT, 'literasi', '*', 'index.html'))):
    slug = os.path.basename(os.path.dirname(f))
    pages.append(('/literasi/%s/' % slug, 'literasi/%s/index.html' % slug, 'monthly', '0.8'))

xml = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">']
for url, path, freq, pri in pages:
    if not os.path.exists(os.path.join(ROOT, path)): continue
    xml.append('  <url><loc>%s%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>' % (SITE, url, mtime(path), freq, pri))
xml.append('</urlset>')
open(os.path.join(ROOT, 'sitemap.xml'), 'w', encoding='utf-8').write('\n'.join(xml) + '\n')
print('sitemap.xml: %d halaman' % (len(xml) - 3))
