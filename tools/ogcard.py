# -*- coding: utf-8 -*-
"""Gambar pratinjau tautan (1200x630) bergaya Zakademia: teks di kiri, foto asli berbingkai di kanan."""
import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageOps

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
FONTS = os.path.join(HERE, 'literasi', 'fonts')
IVORY, INK, GOLD, GOLDT, MUTE, DEEP = (255, 255, 240), (16, 41, 31), (201, 154, 59), (138, 100, 24), (74, 99, 85), (11, 77, 51)

def font(w, s): return ImageFont.truetype(os.path.join(FONTS, f'plus-jakarta-sans-latin-{w}-normal.woff'), s)

def rounded_mask(size, radii, scale=4):
    w, h = size; W, H = w * scale, h * scale
    m = Image.new('L', (W, H), 0); d = ImageDraw.Draw(m)
    tl, tr, br, bl = [r * scale for r in radii]
    d.rectangle((0, 0, W, H), fill=255)
    for (cx, cy, r, box) in [(0, 0, tl, (0, 0)), (W, 0, tr, (W - tr, 0)), (W, H, br, (W - br, H - br)), (0, H, bl, (0, H - bl))]:
        if not r: continue
        x0, y0 = box
        d.rectangle((x0, y0, x0 + r, y0 + r), fill=0)
        ex = (x0 if cx == 0 else x0 - r, y0 if cy == 0 else y0 - r)
        d.pieslice((ex[0], ex[1], ex[0] + 2 * r, ex[1] + 2 * r), 0, 360, fill=255)
    return m.resize((w, h), Image.LANCZOS)

def card(photo, eyebrow, sub, lines, footer, out, icon=None, focus=(0.5, 0.5)):
    im = Image.new('RGB', (1200, 630), IVORY)
    # pendar hijau lembut di kiri bawah
    gl = Image.new('L', (1200, 630), 0); ImageDraw.Draw(gl).ellipse((-300, 380, 380, 900), fill=120)
    im.paste(Image.new('RGB', (1200, 630), (226, 236, 214)), (0, 0), gl.filter(ImageFilter.GaussianBlur(110)))
    d = ImageDraw.Draw(im)
    # daun hijau khas di belakang bingkai foto
    leaf = Image.new('L', (1200, 630), 0); ImageDraw.Draw(leaf).ellipse((640, 250, 1300, 900), fill=255)
    im.paste(Image.new('RGB', (1200, 630), DEEP), (0, 0), leaf.filter(ImageFilter.GaussianBlur(1)))
    # foto berbingkai: sudut asimetris seperti kartu di situs
    X, Y, Wp, Hp = 620, 44, 532, 520
    ph = Image.open(photo).convert('RGB')
    ph = ImageOps.fit(ph, (Wp, Hp), Image.LANCZOS, centering=focus)
    radii = (64, 64, 64, 16)
    sh = Image.new('L', (1200, 630), 0); sh.paste(rounded_mask((Wp, Hp), radii), (X + 8, Y + 18))
    im.paste(Image.new('RGB', (1200, 630), (8, 40, 26)), (0, 0), sh.filter(ImageFilter.GaussianBlur(22)).point(lambda v: int(v * .45)))
    ring = rounded_mask((Wp + 16, Hp + 16), tuple(r + 8 for r in radii))
    im.paste(Image.new('RGB', ring.size, IVORY), (X - 8, Y - 8), ring)
    im.paste(ph, (X, Y), rounded_mask((Wp, Hp), radii))
    # lencana logo di sudut bingkai
    logo = Image.open(os.path.join(ROOT, 'assets/images/logo-color.png')).convert('RGBA')
    bx, by, D = X - 50, Y + Hp - 100, 116
    def disc(dia, color, pos):
        m = Image.new('L', (dia * 4, dia * 4), 0); ImageDraw.Draw(m).ellipse((0, 0, dia * 4, dia * 4), fill=255)
        im.paste(Image.new('RGB', (dia, dia), color), pos, m.resize((dia, dia), Image.LANCZOS))
    disc(D, GOLD, (bx, by)); disc(D - 12, (255, 255, 255), (bx + 6, by + 6))
    lg = logo.copy(); lg.thumbnail((D - 44, D - 44)); im.paste(lg, (bx + (D - lg.width) // 2, by + (D - lg.height) // 2), lg)
    # merek di kiri atas
    lgs = logo.copy(); lgs.thumbnail((40, 40)); im.paste(lgs, (80, 60), lgs)
    d.text((132, 62), 'Zakademia', font=font(800, 28), fill=DEEP)
    # judul: setiap baris dibungkus agar muat, baris terakhir berwarna emas
    size = 54
    last = len(lines) - 1
    words = [(w, i == last) for i, t in enumerate(lines) for w in t.split()]
    def wrap(sz):
        F = font(800, sz); rows = []; cur = []
        for w in words:
            test = ' '.join(x for x, _ in cur + [w])
            if cur and d.textlength(test, font=F) > 480: rows.append(cur); cur = [w]
            else: cur.append(w)
        rows.append(cur); return rows
    rows = wrap(size)
    while len(rows) > 3 and size > 40: size -= 2; rows = wrap(size)
    f = font(800, size); lh = int(size * 1.14)
    top = 92
    block = top + len(rows) * lh + 52
    y = max(126, int(350 - block / 2))
    if icon:
        ic = Image.open(icon).convert('RGBA').resize((60, 60), Image.LANCZOS); im.paste(ic, (82, y), ic)
        d.text((156, y + 4), eyebrow.upper(), font=font(800, 21), fill=GOLD)
        d.text((156, y + 32), sub, font=font(500, 19), fill=MUTE)
    else:
        d.text((84, y + 8), eyebrow.upper(), font=font(800, 21), fill=GOLD)
        if sub: d.text((84, y + 36), sub, font=font(500, 19), fill=MUTE)
    y += top
    for row in rows:
        x = 80
        for k, (w, gold) in enumerate(row):
            t = w + (' ' if k < len(row) - 1 else '')
            d.text((x, y), t, font=f, fill=GOLDT if gold else INK); x += d.textlength(t, font=f)
        y += lh
    d.text((84, y + 16), footer, font=font(500, 21), fill=MUTE)
    im.save(out, optimize=True, quality=88)
