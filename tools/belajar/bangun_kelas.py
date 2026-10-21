#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Bangun kelas "Analitik Data Donatur untuk Amil": template XLSX, video slide bernarasi teks, naskah, dan seed JSON.
Jalankan dari akar repositori: python3 tools/belajar/bangun_kelas.py [--tanpa-video]
Hasil: belajar/berkas/*.xlsx, belajar/video/analitik-donatur-N.mp4, belajar/naskah/*.txt, belajar/seed/analitik-donatur.json
"""
import json, os, sys, subprocess, tempfile, html, shutil
sys.path.insert(0, os.path.dirname(__file__))
from kelas_analitik_donatur import KELAS, MODUL

ROOT = os.path.abspath(os.path.join(os.path.dirname(__file__), '..', '..'))
SLUG = KELAS['slug']
for d in ['berkas', 'video', 'naskah', 'seed']: os.makedirs(os.path.join(ROOT, 'belajar', d), exist_ok=True)

# ---------- template XLSX ----------
def templates():
    from openpyxl import Workbook
    from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
    from openpyxl.worksheet.datavalidation import DataValidation
    from openpyxl.utils import get_column_letter
    H = Font(bold=True, color='FFFFF0'); HF = PatternFill('solid', fgColor='0B4D33'); G = PatternFill('solid', fgColor='F2F5E6'); B = Font(bold=True)
    thin = Side(style='thin', color='DCE5D7'); BR = Border(left=thin, right=thin, top=thin, bottom=thin)
    def head(ws, row, cols, widths=None):
        for i, c in enumerate(cols, 1):
            x = ws.cell(row=row, column=i, value=c); x.font = H; x.fill = HF; x.alignment = Alignment(wrap_text=True, vertical='center'); x.border = BR
        if widths:
            for i, w in enumerate(widths, 1): ws.column_dimensions[get_column_letter(i)].width = w
    def catatan(ws, baris):
        for i, t in enumerate(baris): c = ws.cell(row=i + 1, column=1, value=t); c.font = B if i == 0 else Font(color='4A6355'); c.alignment = Alignment(wrap_text=False)

    # 1. Rekap Donasi Standar
    wb = Workbook(); ws = wb.active; ws.title = 'Petunjuk'
    catatan(ws, ['Rekap Donasi Standar · Zakademia', 'Lembar Donasi: satu baris per transaksi. Kolom A tanggal (format tahun-bulan-tanggal), B ID donatur, C nominal angka murni, D jenis dana, E kanal, F program.',
                 'Kolom H (ID dari WhatsApp) membantu membuat ID: tempel nomor WhatsApp di kolom G, rumus di H membersihkannya.',
                 'Lembar Donatur terisi otomatis dari lembar Donasi (ID unik, jumlah transaksi, total, donasi pertama dan terakhir). Perbarui dengan Data > Refresh bila memakai tabel pivot, atau biarkan rumus bekerja.',
                 'Hapus transaksi ganda: Data > Remove Duplicates pada kolom A, B, C.'])
    ws.column_dimensions['A'].width = 120
    ws = wb.create_sheet('Donasi'); head(ws, 1, ['Tanggal', 'ID donatur', 'Nominal', 'Jenis dana', 'Kanal', 'Program', 'No. WhatsApp (bantu)', 'ID dari WhatsApp (bantu)'], [14, 16, 14, 14, 14, 18, 22, 22])
    dv = DataValidation(type='list', formula1='"zakat,infak,sedekah,wakaf,kurban,fidyah,DSKL,lainnya"', allow_blank=True); ws.add_data_validation(dv); dv.add('D2:D5000')
    dv2 = DataValidation(type='list', formula1='"transfer,QRIS,aplikasi,kotak amal,payroll,mitra,acara,tunai,lainnya"', allow_blank=True); ws.add_data_validation(dv2); dv2.add('E2:E5000')
    for r in range(2, 5001): ws.cell(row=r, column=8, value=f'=IF(G{r}="","",SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(TRIM(G{r})," ",""),"-",""),"+62","0"),"62","0"))')
    for r, row in enumerate([['2025-03-05', '081234567890', 500000, 'zakat', 'transfer', 'Zakat Ramadan'], ['2025-03-07', '081298765432', 150000, 'infak', 'QRIS', 'Paket sembako'], ['2025-04-01', '081234567890', 100000, 'infak', 'aplikasi', '']], 2):
        for c, v in enumerate(row, 1): ws.cell(row=r, column=c, value=v)
    ws.freeze_panes = 'A2'
    ws = wb.create_sheet('Donatur'); head(ws, 1, ['ID donatur', 'Jumlah transaksi', 'Total nominal', 'Donasi pertama', 'Donasi terakhir', 'Nama (isi sendiri)', 'Kota', 'Kanal pertama'], [16, 16, 16, 14, 14, 24, 14, 14])
    ws['A2'] = '=IFERROR(INDEX(UNIQUE(FILTER(Donasi!B2:B5000,Donasi!B2:B5000<>"")),ROW()-1),"")'
    ws.cell(row=1, column=10, value='Kolom A memakai UNIQUE/FILTER (Excel 365 / Google Sheets). Di Excel lama, salin kolom B lembar Donasi ke sini lalu Remove Duplicates.').font = Font(color='4A6355')
    for r in range(2, 3001):
        ws.cell(row=r, column=2, value=f'=IF(A{r}="","",COUNTIF(Donasi!$B$2:$B$5000,A{r}))'); ws.cell(row=r, column=3, value=f'=IF(A{r}="","",SUMIF(Donasi!$B$2:$B$5000,A{r},Donasi!$C$2:$C$5000))')
        ws.cell(row=r, column=4, value=f'=IF(A{r}="","",MINIFS(Donasi!$A$2:$A$5000,Donasi!$B$2:$B$5000,A{r}))'); ws.cell(row=r, column=5, value=f'=IF(A{r}="","",MAXIFS(Donasi!$A$2:$A$5000,Donasi!$B$2:$B$5000,A{r}))')
    ws.freeze_panes = 'A2'
    ws = wb.create_sheet('Ringkasan'); catatan(ws, ['Ringkasan rekap']); ws['A3'] = 'Jumlah transaksi'; ws['B3'] = '=COUNTA(Donasi!B2:B5000)'; ws['A4'] = 'Donatur unik'; ws['B4'] = '=COUNTA(Donatur!A2:A3000)'; ws['A5'] = 'Total dana'; ws['B5'] = '=SUM(Donasi!C2:C5000)'; ws['A6'] = 'Baris tanpa ID'; ws['B6'] = '=COUNTBLANK(Donasi!B2:B5000)-(5000-COUNTA(Donasi!A2:A5000)-1)'
    for r in range(3, 7): ws.cell(row=r, column=1).font = B
    ws.column_dimensions['A'].width = 24; ws.column_dimensions['B'].width = 18
    wb.save(os.path.join(ROOT, 'belajar', 'berkas', 'rekap-donasi-standar.xlsx'))

    # 2. Kohort Retensi
    wb = Workbook(); ws = wb.active; ws.title = 'Petunjuk'
    catatan(ws, ['Kohort Retensi · Zakademia', 'Tempel lembar Donasi dari Rekap Donasi Standar ke lembar Donasi di sini (kolom A tanggal, B ID donatur, C nominal).', 'Isi tahun-tahun yang dianalisis di lembar Ringkasan baris 2 (misalnya 2022, 2023, 2024, 2025).',
                 'Lembar Matriks menandai, untuk tiap donatur, apakah memberi di tiap tahun; lembar Ringkasan menghitung bertahan, hilang, baru, kembali, retensi, dan kohort.', 'Rumus memakai UNIQUE/FILTER (Excel 365 / Google Sheets).'])
    ws.column_dimensions['A'].width = 120
    ws = wb.create_sheet('Donasi'); head(ws, 1, ['Tanggal', 'ID donatur', 'Nominal'], [14, 18, 14]); ws.freeze_panes = 'A2'
    ws = wb.create_sheet('Matriks'); head(ws, 1, ['ID donatur', 'Tahun pertama', 'T1', 'T2', 'T3', 'T4'], [18, 14, 8, 8, 8, 8])
    ws['A2'] = '=IFERROR(INDEX(UNIQUE(FILTER(Donasi!B2:B20000,Donasi!B2:B20000<>"")),ROW()-1),"")'
    for r in range(2, 6001):
        ws.cell(row=r, column=2, value=f'=IF(A{r}="","",YEAR(MINIFS(Donasi!$A$2:$A$20000,Donasi!$B$2:$B$20000,A{r})))')
        for j in range(4): col = 3 + j; ws.cell(row=r, column=col, value=f'=IF($A{r}="","",IF(COUNTIFS(Donasi!$B$2:$B$20000,$A{r},Donasi!$A$2:$A$20000,">="&DATE(Ringkasan!{get_column_letter(2+j)}$2,1,1),Donasi!$A$2:$A$20000,"<="&DATE(Ringkasan!{get_column_letter(2+j)}$2,12,31))>0,1,0))')
    ws.freeze_panes = 'A2'
    ws = wb.create_sheet('Ringkasan'); ws['A1'] = 'Isi tahun di baris 2 (urut, maksimal 4)'; ws['A1'].font = B
    ws['A2'] = 'Tahun'; ws['B2'] = 2022; ws['C2'] = 2023; ws['D2'] = 2024; ws['E2'] = 2025
    baris = [('Donatur aktif', '=SUM(Matriks!{c}2:{c}6000)'), ('Baru (tahun pertama = tahun ini)', '=COUNTIFS(Matriks!$B$2:$B$6000,{c}$2)'), ('Bertahan (memberi tahun ini dan tahun lalu)', '=SUMPRODUCT((Matriks!{p}2:{p}6000=1)*(Matriks!{c}2:{c}6000=1))'),
             ('Hilang (tahun lalu memberi, tahun ini tidak)', '=SUMPRODUCT((Matriks!{p}2:{p}6000=1)*(Matriks!{c}2:{c}6000=0))'), ('Kembali (pernah memberi sebelum tahun lalu, absen tahun lalu, memberi lagi)', '={c}3-{c}4-{c}5'),
             ('Retensi total', '=IF({p}3=0,"",{c}5/{p}3)'), ('Retensi donatur baru (dari yang pertama kali memberi tahun lalu)', '=IF({p}4=0,"",SUMPRODUCT((Matriks!$B$2:$B$6000={p}$2)*(Matriks!{c}2:{c}6000=1))/{p}4)'), ('Pertumbuhan bersih (baru − hilang)', '={c}4-{c}6')]
    for i, (lbl, f) in enumerate(baris, 3):
        ws.cell(row=i, column=1, value=lbl).font = B
        for j in range(4):
            c = get_column_letter(2 + j); p = get_column_letter(1 + j); mc = get_column_letter(3 + j); mp = get_column_letter(2 + j)
            if j == 0 and ('{p}' in f): ws.cell(row=i, column=2 + j, value='–'); continue
            ws.cell(row=i, column=2 + j, value=f.replace('{c}', mc if 'Matriks!{c}' in f else c).replace('Matriks!' + mc, 'Matriks!' + mc).replace('{p}', mp if 'Matriks!{p}' in f else p))
    for r in (8, 9):
        for j in range(4): ws.cell(row=r, column=2 + j).number_format = '0.0%'
    ws['A13'] = 'Tabel kohort: persentase angkatan yang masih memberi di tahun berikutnya'; ws['A13'].font = B
    ws['A14'] = 'Angkatan'; ws['B14'] = 'Jumlah'; ws['C14'] = 'Tahun ke-2'; ws['D14'] = 'Tahun ke-3'; ws['E14'] = 'Tahun ke-4'
    for j in range(4):
        r = 15 + j; ws.cell(row=r, column=1, value=f'={get_column_letter(2 + j)}2'); ws.cell(row=r, column=2, value=f'=COUNTIFS(Matriks!$B$2:$B$6000,A{r})')
        for k in range(1, 4):
            if j + k < 4: ws.cell(row=r, column=2 + k, value=f'=IF(B{r}=0,"",SUMPRODUCT((Matriks!$B$2:$B$6000=A{r})*(Matriks!{get_column_letter(3 + j + k)}2:{get_column_letter(3 + j + k)}6000=1))/B{r})').number_format = '0.0%'
    ws.column_dimensions['A'].width = 64
    for c in 'BCDE': ws.column_dimensions[c].width = 14
    wb.save(os.path.join(ROOT, 'belajar', 'berkas', 'kohort-retensi.xlsx'))

    # 3. RFM
    wb = Workbook(); ws = wb.active; ws.title = 'Petunjuk'
    catatan(ws, ['RFM dan Piramida Donatur · Zakademia', 'Tempel lembar Donasi (tanggal, ID, nominal) untuk 2 sampai 3 tahun terakhir. Isi tanggal acuan di Segmen!B1 (biasanya hari ini).',
                 'Lembar Segmen menghitung Recency (hari sejak donasi terakhir), Frequency, Monetary, skor 1–5 per kuintil, dan label segmen.', 'Lembar Piramida: urutkan kolom Total dari besar ke kecil (Data > Sort) lalu baca konsentrasi 1%, 10%, 50% teratas.'])
    ws.column_dimensions['A'].width = 120
    ws = wb.create_sheet('Donasi'); head(ws, 1, ['Tanggal', 'ID donatur', 'Nominal'], [14, 18, 14]); ws.freeze_panes = 'A2'
    ws = wb.create_sheet('Segmen'); ws['A1'] = 'Tanggal acuan'; ws['A1'].font = B; ws['B1'] = '=TODAY()'
    head(ws, 2, ['ID donatur', 'Hari sejak terakhir (R)', 'Jumlah donasi (F)', 'Total (M)', 'Skor R', 'Skor F', 'Skor M', 'RFM', 'Segmen'], [18, 20, 16, 16, 8, 8, 8, 8, 14])
    ws['A3'] = '=IFERROR(INDEX(UNIQUE(FILTER(Donasi!B2:B20000,Donasi!B2:B20000<>"")),ROW()-2),"")'
    for r in range(3, 6001):
        ws.cell(row=r, column=2, value=f'=IF(A{r}="","",$B$1-MAXIFS(Donasi!$A$2:$A$20000,Donasi!$B$2:$B$20000,A{r}))'); ws.cell(row=r, column=3, value=f'=IF(A{r}="","",COUNTIF(Donasi!$B$2:$B$20000,A{r}))'); ws.cell(row=r, column=4, value=f'=IF(A{r}="","",SUMIF(Donasi!$B$2:$B$20000,A{r},Donasi!$C$2:$C$20000))')
        ws.cell(row=r, column=5, value=f'=IF(A{r}="","",6-CEILING(PERCENTRANK.INC($B$3:$B$6000,B{r},4)*5+0.000001,1))'); ws.cell(row=r, column=6, value=f'=IF(A{r}="","",MAX(1,CEILING(PERCENTRANK.INC($C$3:$C$6000,C{r},4)*5+0.000001,1)))'); ws.cell(row=r, column=7, value=f'=IF(A{r}="","",MAX(1,CEILING(PERCENTRANK.INC($D$3:$D$6000,D{r},4)*5+0.000001,1)))')
        ws.cell(row=r, column=8, value=f'=IF(A{r}="","",E{r}&F{r}&G{r})')
        ws.cell(row=r, column=9, value=f'=IF(A{r}="","",IF(AND(E{r}>=4,F{r}>=4,G{r}>=4),"Juara",IF(AND(F{r}>=4,E{r}>=3),"Setia",IF(AND(E{r}=5,F{r}<=2,G{r}>=3),"Berpotensi",IF(AND(E{r}<=3,E{r}>=2,F{r}>=3),"Rawan",IF(AND(E{r}<=2,F{r}<=2),"Tidur","Umum"))))))')
    ws.freeze_panes = 'A3'
    ws = wb.create_sheet('Rekap segmen'); head(ws, 1, ['Segmen', 'Jumlah donatur', 'Total dana', 'Tindakan'], [14, 16, 16, 70])
    tindakan = {'Juara': 'Laporan dampak personal, ajak jadi duta, jangan dibanjiri promosi', 'Setia': 'Tawarkan program rutin bulanan / autodebit', 'Berpotensi': 'Donatur baru bernilai besar: tindak lanjut dalam 90 hari', 'Rawan': 'Hubungi secara personal sebelum hilang', 'Tidur': 'Kampanye ulang ringan, jangan mahal', 'Umum': 'Komunikasi rutin'}
    for i, (s, t) in enumerate(tindakan.items(), 2): ws.cell(row=i, column=1, value=s).font = B; ws.cell(row=i, column=2, value=f'=COUNTIF(Segmen!I3:I6000,A{i})'); ws.cell(row=i, column=3, value=f'=SUMIF(Segmen!I3:I6000,A{i},Segmen!D3:D6000)'); ws.cell(row=i, column=4, value=t)
    ws = wb.create_sheet('Piramida'); head(ws, 1, ['Peringkat', 'ID donatur', 'Total'], [10, 18, 16])
    for r in range(2, 6001): ws.cell(row=r, column=1, value=r - 1); ws.cell(row=r, column=2, value=f'=IFERROR(INDEX(Segmen!$A$3:$A$6000,MATCH(C{r},Segmen!$D$3:$D$6000,0)),"")'); ws.cell(row=r, column=3, value=f'=IFERROR(LARGE(Segmen!$D$3:$D$6000,A{r}),"")')
    ws['E1'] = 'Konsentrasi dana'; ws['E1'].font = B; ws['E2'] = 'Jumlah donatur'; ws['F2'] = '=COUNT(C2:C6000)'; ws['E3'] = 'Total dana'; ws['F3'] = '=SUM(C2:C6000)'
    for i, (lbl, p) in enumerate([('1% teratas', 0.01), ('10% teratas', 0.1), ('50% teratas', 0.5)], 4): ws.cell(row=i, column=5, value=lbl); ws.cell(row=i, column=6, value=f'=IF($F$2=0,"",SUM(C2:INDEX(C2:C6000,MAX(1,ROUND($F$2*{p},0))))/$F$3)').number_format = '0.0%'
    ws.column_dimensions['E'].width = 18; ws.column_dimensions['F'].width = 14
    wb.save(os.path.join(ROOT, 'belajar', 'berkas', 'rfm.xlsx'))

    # 4. Biaya per kanal
    wb = Workbook(); ws = wb.active; ws.title = 'Biaya per kanal'
    catatan(ws, ['Biaya per Kanal, CAC, dan CLV · Zakademia', 'Isi sel kuning. Biaya memuat iklan, konten, honor, hadiah, cetak, dan perkiraan waktu tim. Donatur baru = yang pertama kali memberi lewat kanal itu.'])
    ws['A4'] = 'Donasi rata-rata per donatur per tahun'; ws['B4'] = 1200000; ws['A5'] = 'Retensi (dari Kohort Retensi)'; ws['B5'] = 0.4; ws['B5'].number_format = '0%'; ws['A6'] = 'CLV (nilai donatur)'; ws['B6'] = '=IF(B5>=1,"",B4/(1-B5))'
    for r in (4, 5, 6): ws.cell(row=r, column=1).font = B
    for r in (4, 5): ws.cell(row=r, column=2).fill = PatternFill('solid', fgColor='FFF1D6')
    head(ws, 8, ['Kanal', 'Biaya setahun', 'Dana terhimpun', 'Donatur baru', 'CAC', 'Biaya per Rp100 terhimpun', 'CLV : CAC', 'Keputusan'], [26, 16, 16, 14, 14, 22, 12, 40])
    kanal = ['Transfer donatur lama', 'Iklan digital', 'QRIS masjid dan mitra', 'Payroll / perusahaan', 'Acara', 'Lainnya']
    for i, k in enumerate(kanal, 9):
        ws.cell(row=i, column=1, value=k)
        for c in (2, 3, 4): ws.cell(row=i, column=c).fill = PatternFill('solid', fgColor='FFF1D6')
        ws.cell(row=i, column=5, value=f'=IF(D{i}>0,B{i}/D{i},"")'); ws.cell(row=i, column=6, value=f'=IF(C{i}>0,B{i}/C{i}*100,"")'); ws.cell(row=i, column=7, value=f'=IF(AND(E{i}<>"",E{i}>0,$B$6<>""),$B$6/E{i},"")'); ws.cell(row=i, column=7).number_format = '0.0'
        ws.cell(row=i, column=8, value=f'=IF(G{i}="","",IF(G{i}<1,"Hentikan atau perbaiki",IF(G{i}<3,"Pertahankan, benahi biaya/retensi","Layak ditambah anggaran")))')
    ws['A16'] = 'Total'; ws['A16'].font = B; ws['B16'] = '=SUM(B9:B14)'; ws['C16'] = '=SUM(C9:C14)'; ws['D16'] = '=SUM(D9:D14)'; ws['E16'] = '=IF(D16>0,B16/D16,"")'; ws['F16'] = '=IF(C16>0,B16/C16*100,"")'
    ws['A18'] = 'Acuan: biaya per Rp100 untuk seluruh kanal sebaiknya jauh di bawah 12,5 (hak amil seperdelapan), dengan ruang untuk biaya penyaluran dan operasional.'; ws['A18'].font = Font(color='4A6355')
    wb.save(os.path.join(ROOT, 'belajar', 'berkas', 'biaya-per-kanal.xlsx'))

    # 5. Proyeksi musiman
    wb = Workbook(); ws = wb.active; ws.title = 'Petunjuk'
    catatan(ws, ['Proyeksi Musiman · Zakademia', 'Lembar Bulanan: isi 24 sampai 36 baris, satu per bulan Masehi: tahun, bulan, dana terhimpun, dan nama bulan Hijriah yang dominan di bulan itu (misalnya Ramadan).',
                 'Lembar Indeks menghitung indeks musiman per bulan Hijriah. Lembar Tren menghitung rata-rata bergerak 12 bulan. Lembar Proyeksi memakai dana Ramadan per tahun dan pertumbuhannya.'])
    ws.column_dimensions['A'].width = 120
    ws = wb.create_sheet('Bulanan'); head(ws, 1, ['Tahun', 'Bulan (1-12)', 'Dana terhimpun', 'Bulan Hijriah dominan', 'Rata-rata bergerak 12 bulan'], [10, 12, 16, 22, 24])
    hij = '"Muharam,Safar,Rabiulawal,Rabiulakhir,Jumadilawal,Jumadilakhir,Rajab,Syakban,Ramadan,Syawal,Zulkaidah,Zulhijah"'
    dv = DataValidation(type='list', formula1=hij, allow_blank=True); ws.add_data_validation(dv); dv.add('D2:D200')
    for r in range(2, 200): ws.cell(row=r, column=5, value=f'=IF(ROW()<13,"",IF(C{r}="","",AVERAGE(C{r-11}:C{r})))')
    ws.freeze_panes = 'A2'
    ws = wb.create_sheet('Indeks'); head(ws, 1, ['Bulan Hijriah', 'Rata-rata dana', 'Indeks musiman'], [18, 16, 16])
    for i, h in enumerate(hij.strip('"').split(','), 2): ws.cell(row=i, column=1, value=h); ws.cell(row=i, column=2, value=f'=IFERROR(AVERAGEIF(Bulanan!$D$2:$D$200,A{i},Bulanan!$C$2:$C$200),"")'); ws.cell(row=i, column=3, value=f'=IF(B{i}="","",B{i}/AVERAGE($B$2:$B$13))'); ws.cell(row=i, column=3).number_format = '0.00'
    ws = wb.create_sheet('Proyeksi'); head(ws, 1, ['Tahun', 'Dana Ramadan', 'Pertumbuhan'], [10, 16, 14])
    for i in range(2, 6): ws.cell(row=i, column=1, value=f'=IFERROR(INDEX(UNIQUE(FILTER(Bulanan!$A$2:$A$200,Bulanan!$D$2:$D$200="Ramadan")),ROW()-1),"")'); ws.cell(row=i, column=2, value=f'=IF(A{i}="","",SUMIFS(Bulanan!$C$2:$C$200,Bulanan!$A$2:$A$200,A{i},Bulanan!$D$2:$D$200,"Ramadan"))'); ws.cell(row=i, column=3, value=f'=IF(OR(A{i}="",B{i-1}="",ROW()=2),"",B{i}/B{i-1}-1)' if i > 2 else ''); ws.cell(row=i, column=3).number_format = '0.0%'
    ws['A8'] = 'Ramadan terakhir'; ws['B8'] = '=LOOKUP(2,1/(B2:B5<>""),B2:B5)'; ws['A9'] = 'Pertumbuhan terendah'; ws['B9'] = '=MIN(C3:C5)'; ws['A10'] = 'Pertumbuhan rata-rata'; ws['B10'] = '=AVERAGE(C3:C5)'; ws['A11'] = 'Pertumbuhan tertinggi'; ws['B11'] = '=MAX(C3:C5)'
    ws['A13'] = 'Proyeksi Ramadan berikutnya'; ws['A13'].font = B; ws['A14'] = 'Skenario rendah'; ws['B14'] = '=B8*(1+B9)'; ws['A15'] = 'Skenario tengah'; ws['B15'] = '=B8*(1+B10)'; ws['A16'] = 'Skenario tinggi'; ws['B16'] = '=B8*(1+B11)'
    for r in (9, 10, 11): ws.cell(row=r, column=2).number_format = '0.0%'
    for r in range(8, 17): ws.cell(row=r, column=1).font = B
    ws.column_dimensions['A'].width = 28
    wb.save(os.path.join(ROOT, 'belajar', 'berkas', 'proyeksi-musiman.xlsx'))

    # 6. Laporan pimpinan
    wb = Workbook(); ws = wb.active; ws.title = 'Laporan'
    ws['A1'] = 'Laporan Analitik Penghimpunan · satu halaman'; ws['A1'].font = Font(bold=True, size=14, color='0B4D33'); ws['A2'] = 'Lembaga:'; ws['B2'] = '(isi)'; ws['A3'] = 'Periode:'; ws['B3'] = '(isi, bandingkan dengan periode yang sama tahun lalu)'
    head(ws, 5, ['Angka', 'Periode ini', 'Periode pembanding', 'Perubahan', 'Artinya (satu kalimat)'], [40, 16, 18, 12, 70])
    angka = ['Dana terhimpun', 'Donatur aktif', 'Donatur baru', 'Donatur hilang', 'Retensi total', 'Retensi donatur baru', 'Dana dari 10% donatur teratas', 'CLV : CAC', 'Biaya per Rp100 terhimpun']
    for i, a in enumerate(angka, 6): ws.cell(row=i, column=1, value=a).font = B; ws.cell(row=i, column=4, value=f'=IF(OR(B{i}="",C{i}="",C{i}=0),"",B{i}/C{i}-1)').number_format = '0.0%'
    ws['A17'] = 'Tiga keputusan yang diusulkan'; ws['A17'].font = Font(bold=True, size=12, color='0B4D33')
    head(ws, 18, ['Keputusan', 'Angka yang mendasari', 'Tindakan', 'Biaya / sumber daya', 'Ukuran keberhasilan 6 bulan lagi'])
    for i in range(19, 22): ws.cell(row=i, column=1, value=f'Keputusan {i-18}').font = B
    ws['A24'] = 'Grafik: tempel satu grafik (rata-rata bergerak 12 bulan atau kohort retensi) di sini.'; ws['A24'].font = Font(color='4A6355')
    wb.save(os.path.join(ROOT, 'belajar', 'berkas', 'laporan-pimpinan.xlsx'))
    print('template xlsx: 6 berkas')

# ---------- video ----------
SLIDE = '''<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
@font-face{font-family:Inter;src:url(__F400__)}@font-face{font-family:Inter;font-weight:700;src:url(__F700__)}@font-face{font-family:Inter;font-weight:800;src:url(__F800__)}
*{box-sizing:border-box}body{margin:0;width:1280px;height:720px;overflow:hidden;background:#FFFFF0;font-family:Inter,sans-serif;color:#10291F;position:relative}
.bar{position:absolute;left:0;top:0;bottom:0;width:18px;background:#0B4D33}.bar2{position:absolute;left:18px;top:0;bottom:0;width:5px;background:#C99A3B}
.top{position:absolute;left:64px;top:36px;display:flex;align-items:center;gap:12px;font-weight:800;color:#0B4D33;font-size:22px}.top img{height:40px}.top small{font-weight:500;color:#4A6355;font-size:14px;margin-left:10px}
.mod{position:absolute;right:56px;top:44px;font-size:13px;letter-spacing:.12em;text-transform:uppercase;color:#8A6418;font-weight:700}
h1{position:absolute;left:64px;top:120px;width:860px;font-size:46px;line-height:1.12;letter-spacing:-.02em;margin:0;font-weight:800}
ul{position:absolute;left:64px;top:235px;width:820px;list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:16px}
li{font-size:25px;line-height:1.35;padding-left:34px;position:relative;opacity:.22}li.on{opacity:1}li::before{content:"";position:absolute;left:0;top:13px;width:14px;height:14px;border-radius:50%;background:#C99A3B}
.cap{position:absolute;left:64px;right:56px;bottom:40px;background:#0B4D33;color:#FFFFF0;border-radius:18px 18px 18px 6px;padding:18px 24px 18px 110px;font-size:21px;line-height:1.45;min-height:96px;display:flex;align-items:center}
.cap img{position:absolute;left:18px;top:50%;transform:translateY(-50%);width:74px;height:74px;border-radius:50%;object-fit:cover;border:3px solid #C99A3B}
.cap b{position:absolute;left:110px;top:-12px;background:#C99A3B;color:#1E1606;font-size:12px;padding:2px 10px;border-radius:999px;letter-spacing:.08em}
.ilu{position:absolute;right:56px;top:150px;width:300px;height:300px;border-radius:50%;background:#F2F5E6;display:flex;align-items:center;justify-content:center;font-size:150px;font-weight:800;color:#0B4D33;opacity:.9}
.judul{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:0 120px 0 150px;background:linear-gradient(135deg,#14613F,#0B4D33);color:#FFFFF0}
.judul .k{font-size:15px;letter-spacing:.16em;text-transform:uppercase;color:#E2BC67;font-weight:700}.judul h2{font-size:58px;line-height:1.1;margin:14px 0 12px;letter-spacing:-.02em;font-weight:800}.judul p{font-size:24px;color:#C9DECE;margin:0}
.judul img{position:absolute;right:120px;top:50%;transform:translateY(-50%);width:220px;height:220px;border-radius:50%;object-fit:cover;border:6px solid #C99A3B}
.prog{position:absolute;left:0;right:0;bottom:0;height:6px;background:#DCE5D7}.prog i{display:block;height:100%;background:#C99A3B;width:__PROG__%}
</style></head><body>__BODY__</body></html>'''

import base64
_DU = {}
def data_uri(path, mime):
    if path not in _DU: _DU[path] = 'data:' + mime + ';base64,' + base64.b64encode(open(path, 'rb').read()).decode()
    return _DU[path]

def slide_html(kind, d, prog):
    font = os.path.join(ROOT, 'belajar', 'fonts'); logo = data_uri(os.path.join(ROOT, 'assets/images/logo-color.png'), 'image/png'); zaida = data_uri(os.path.join(ROOT, 'assets/images/zaida.jpg'), 'image/jpeg')
    if kind == 'judul':
        body = f'<div class="judul"><div class="k">{html.escape(d["k"])}</div><h2>{html.escape(d["h"])}</h2><p>{html.escape(d["p"])}</p><img src="{zaida}" alt=""></div>'
    else:
        lis = ''.join(f'<li class="{"on" if i <= d["on"] else ""}">{html.escape(p)}</li>' for i, p in enumerate(d['p']))
        body = (f'<div class="bar"></div><div class="bar2"></div><div class="top"><img src="{logo}" alt="">Zakademia<small>Ruang Belajar</small></div><div class="mod">{html.escape(d["mod"])}</div>'
                f'<h1>{html.escape(d["h"])}</h1><ul>{lis}</ul><div class="ilu">{d["no"]}</div>'
                f'<div class="cap"><img src="{zaida}" alt=""><b>ZAIDA</b><span>{html.escape(d["n"])}</span></div>')
    return SLIDE.replace('__F400__', data_uri(font + '/Inter-Regular.otf', 'font/otf')).replace('__F700__', data_uri(font + '/Inter-Bold.otf', 'font/otf')).replace('__F800__', data_uri(font + '/Inter-ExtraBold.otf', 'font/otf')).replace('__BODY__', body).replace('__PROG__', str(prog))

def durasi(teks): return max(5.0, min(16.0, 2.0 + len(teks) / 15.0))

def video(i, m, browser):
    out = os.path.join(ROOT, 'belajar', 'video', f'{SLUG}-{i}.mp4'); tmp = tempfile.mkdtemp()
    frames = []  # (png, detik)
    page = browser.new_page(viewport={'width': 1280, 'height': 720})
    def shot(h, dur):
        p = os.path.join(tmp, f'f{len(frames):03d}.png'); page.set_content(h); page.wait_for_timeout(80); page.screenshot(path=p); frames.append((p, dur))
    v = m['video']; total = sum(len(a['p']) for a in v['adegan']) + 2; k = 0
    shot(slide_html('judul', {'k': f'{KELAS["judul"]} · Modul {i}', 'h': v['judul'], 'p': m['judul']}, 0), 4.0); k += 1
    for ai, a in enumerate(v['adegan'], 1):
        n = len(a['p']); d = durasi(a['n']) / n
        for j in range(n): shot(slide_html('isi', {'mod': f'Modul {i} · {ai}/{len(v["adegan"])}', 'h': a['h'], 'p': a['p'], 'on': j, 'n': a['n'], 'no': ai}, round(k / total * 100)), d); k += 1
    shot(slide_html('judul', {'k': 'Selesai', 'h': 'Lanjut ke bacaan dan praktik', 'p': 'Unduh template, kerjakan dengan data lembaga Anda, lalu kuis.'}, 100), 4.0)
    page.close()
    lst = os.path.join(tmp, 'list.txt')
    with open(lst, 'w') as f:
        for p, d in frames: f.write(f"file '{p}'\nduration {d:.2f}\n")
        f.write(f"file '{frames[-1][0]}'\n")
    subprocess.run(['ffmpeg', '-y', '-loglevel', 'error', '-f', 'concat', '-safe', '0', '-i', lst, '-vf', 'fps=24,format=yuv420p', '-c:v', 'libx264', '-preset', 'slow', '-crf', '27', '-tune', 'stillimage', '-movflags', '+faststart', out], check=True)
    shutil.rmtree(tmp)
    # naskah
    with open(os.path.join(ROOT, 'belajar', 'naskah', f'{SLUG}-{i}.txt'), 'w', encoding='utf-8') as f:
        f.write(f'{KELAS["judul"]} · Modul {i}: {m["judul"]}\nVideo: {v["judul"]}\n\n')
        for ai, a in enumerate(v['adegan'], 1): f.write(f'[{ai}] {a["h"]}\n{a["n"]}\n\n')
    print('video', out, os.path.getsize(out) // 1024, 'KB', round(sum(d for _, d in frames)), 'detik')

def seed():
    mods = []
    for i, m in enumerate(MODUL, 1):
        mat = [
            {'judul': m['video']['judul'], 'jenis': 'video', 'menit': 5, 'tautan': f'/belajar/video.php?k={SLUG}&n={i}', 'isi': '## Naskah video\n\n' + '\n\n'.join(f'**{a["h"]}.** {a["n"]}' for a in m['video']['adegan'])},
            {'judul': m['judul'], 'jenis': 'teks', 'menit': max(10, m['menit'] - 25), 'isi': m['bacaan']},
            {'judul': 'Template: ' + m['berkas'].replace('-', ' ').title(), 'jenis': 'berkas', 'menit': 5, 'tautan': f'/belajar/berkas.php?k={SLUG}&f={m["berkas"]}', 'isi': 'Unduh template spreadsheet modul ini (Excel 365 atau Google Sheets). Lembar Petunjuk di dalamnya menjelaskan cara mengisi.'},
            {'judul': m['praktik']['judul'], 'jenis': 'praktik', 'menit': 10, 'isi': m['praktik']['isi'], 'tautan': m['praktik']['tautan']},
            {'judul': 'Kuis modul ' + str(i), 'jenis': 'kuis', 'menit': 5, 'kuis': m['kuis']},
        ]
        mods.append({'judul': f'Modul {i} · {m["judul"]}', 'materi': mat})
    k = dict(KELAS); k['modul'] = mods; k['status'] = 'draf'
    with open(os.path.join(ROOT, 'belajar', 'seed', f'{SLUG}.json'), 'w', encoding='utf-8') as f: json.dump(k, f, ensure_ascii=False, indent=1)
    print('seed', f'belajar/seed/{SLUG}.json', len(mods), 'modul')

if __name__ == '__main__':
    templates(); seed()
    if '--tanpa-video' not in sys.argv:
        from playwright.sync_api import sync_playwright
        with sync_playwright() as pw:
            b = pw.chromium.launch()
            for i, m in enumerate(MODUL, 1): video(i, m, b)
            b.close()
