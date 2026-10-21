/* Zakademia — pencarian seluruh situs.
   Tombol: elemen ber-atribut [data-cari] di header. Pintasan: Ctrl/⌘+K atau "/".
   Indeks dibuat oleh tools/cari/indeks.py dan dimuat saat jendela pertama kali dibuka. */
(function () {
  'use strict';
  var INDEKS = '/js/cari-indeks.json';   // diperiksa ulang ke server setiap kunjungan (cache: no-cache)
  var SARAN = ['zakat penghasilan', 'wakaf uang', 'stunting', 'retensi donatur', 'kuesioner', 'BAZNAS'];
  var MAKS = 20;
  var data = null, memuat = null, dlg, inp, list, info, aktif = -1, hasil = [], pemicu = null;

  function norm(s) {
    return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .replace(/[’'`]/g, '').replace(/[^a-z0-9]+/g, ' ').trim();
  }
  function esc(s) {
    return s.replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
  }

  function muat() {
    if (data) return Promise.resolve(data);
    if (memuat) return memuat;
    memuat = fetch(INDEKS, { cache: 'no-cache' }).then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (j) {
        data = j.map(function (it) {
          return { it: it, t: ' ' + norm(it.t) + ' ', d: ' ' + norm(it.d) + ' ', k: ' ' + norm(it.k) + ' ', x: ' ' + norm(it.x) + ' ' };
        });
        return data;
      });
    memuat.catch(function () { memuat = null; });
    return memuat;
  }

  /* Nilai satu kata di satu bidang: kata utuh > awal kata > di tengah kata (min. 4 huruf). */
  function nilaiKata(teks, q) {
    if (teks.indexOf(' ' + q + ' ') > -1) return 3;
    if (teks.indexOf(' ' + q) > -1) return 2;
    if ((q.length >= 4 || /^\d+$/.test(q)) && teks.indexOf(q) > -1) return 1;
    return 0;
  }

  function cari(q) {
    var nq = norm(q), kata = nq.split(' ').filter(Boolean);
    if (!kata.length) return [];
    var out = [];
    data.forEach(function (r) {
      var skor = 0;
      for (var i = 0; i < kata.length; i++) {
        var w = kata[i];
        var t = nilaiKata(r.t, w), d = nilaiKata(r.d, w), k = nilaiKata(r.k, w), x = nilaiKata(r.x, w);
        if (!t && !d && !k && !x) return;          // setiap kata harus ditemukan
        skor += t * 10 + d * 4 + k * 3 + x;
      }
      if (kata.length > 1) {
        if (r.t.indexOf(nq) > -1) skor += 25;
        else if (r.d.indexOf(nq) > -1 || r.x.indexOf(nq) > -1) skor += 8;
      }
      if (r.it.m) skor -= 4;                         // modul yang belum terbit turun sedikit
      out.push({ r: r, s: skor });
    });
    out.sort(function (a, b) { return b.s - a.s; });
    return out.slice(0, MAKS).map(function (o) { return o.r.it; });
  }

  /* Potongan teks di sekitar kata pertama yang cocok, dengan sorotan. */
  function cuplik(it, kata) {
    var sumber = it.d, low = norm(sumber), ada = kata.some(function (w) { return low.indexOf(w) > -1; });
    if (!ada && it.x) {
      var x = it.x, lx = x.toLowerCase(), pos = -1;
      kata.forEach(function (w) { var p = lx.indexOf(w); if (p > -1 && (pos < 0 || p < pos)) pos = p; });
      if (pos > -1) {
        var a = Math.max(0, pos - 70), b = Math.min(x.length, pos + 130);
        while (a > 0 && x[a - 1] !== ' ') a--;
        while (b < x.length && x[b] !== ' ') b++;
        sumber = (a > 0 ? '…' : '') + x.slice(a, b).trim() + (b < x.length ? '…' : '');
      }
    }
    return sorot(sumber, kata);
  }

  function sorot(teks, kata) {
    var pola = kata.filter(function (w) { return w.length > 1 || /\d/.test(w); })
      .sort(function (a, b) { return b.length - a.length; })
      .map(function (w) { return w.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); });
    if (!pola.length) return esc(teks);
    return teks.split(new RegExp('(' + pola.join('|') + ')', 'gi')).map(function (bag, i) {
      return i % 2 ? '<mark>' + esc(bag) + '</mark>' : esc(bag);
    }).join('');
  }

  function tampil(q) {
    aktif = -1;
    var kata = norm(q).split(' ').filter(Boolean);
    if (!kata.length) { hasil = []; kosong(); return; }
    hasil = cari(q);
    if (!hasil.length) {
      list.innerHTML = '';
      info.innerHTML = 'Tidak ada hasil untuk <b>“' + esc(q) + '”</b>. Coba kata lain, misalnya ' + saranHTML() + '.';
      pasangSaran();
      return;
    }
    info.textContent = hasil.length >= MAKS ? MAKS + ' hasil teratas' : hasil.length + ' hasil';
    list.innerHTML = hasil.map(function (it, i) {
      return '<li role="option" id="zk-cari-h' + i + '" aria-selected="false"><a href="' + esc(it.u) + '" data-i="' + i + '">' +
        '<span class="zk-cari-k">' + esc(it.k) + '</span>' +
        '<b>' + sorot(it.t, kata) + '</b>' +
        '<span class="zk-cari-d">' + cuplik(it, kata) + '</span></a></li>';
    }).join('');
  }

  function saranHTML() {
    return SARAN.map(function (s) { return '<button type="button" class="zk-cari-saran" data-q="' + esc(s) + '">' + esc(s) + '</button>'; }).join(' ');
  }
  function pasangSaran() {
    info.querySelectorAll('[data-q]').forEach(function (b) {
      b.onclick = function () { inp.value = b.getAttribute('data-q'); tampil(inp.value); inp.focus(); };
    });
  }
  function kosong() {
    list.innerHTML = '';
    info.innerHTML = 'Cari artikel, kajian, buku, layanan, atau modul belajar. Coba: ' + saranHTML();
    pasangSaran();
  }

  function pilih(i) {
    var li = list.children;
    if (!li.length) return;
    aktif = (i + li.length) % li.length;
    for (var n = 0; n < li.length; n++) li[n].setAttribute('aria-selected', n === aktif ? 'true' : 'false');
    inp.setAttribute('aria-activedescendant', 'zk-cari-h' + aktif);
    li[aktif].scrollIntoView({ block: 'nearest' });
  }

  /* Pindah ke hasil. Untuk kartu di beranda (kajian, buku, artikel ilmiah) kartunya ikut disorot. */
  function buka(it, baru) {
    if (baru) { window.open(it.u, '_blank', 'noopener'); return; }
    var u = new URL(it.u, location.href);
    if (it.s) { try { sessionStorage.setItem('zk-cari-sorot', it.s); } catch (e) {} }
    tutup(true);
    if (u.pathname === location.pathname && u.search === location.search) {
      if (u.hash !== location.hash) location.hash = u.hash;
      else if (!it.s) window.scrollTo(0, 0);
      setTimeout(sorotKartu, 120);
    } else location.href = it.u;
  }

  function sorotKartu() {
    var judul; try { judul = sessionStorage.getItem('zk-cari-sorot'); } catch (e) { return; }
    if (!judul) return;
    var target = null, nj = norm(judul);
    document.querySelectorAll('[data-page]:not([hidden]) article h3').forEach(function (h) {
      if (!target && norm(h.textContent) === nj) target = h.closest('article');
    });
    if (!target) return;
    try { sessionStorage.removeItem('zk-cari-sorot'); } catch (e) {}
    if (!target.offsetParent) {                       // kartu tersembunyi oleh saringan tema
      var semua = document.querySelector('.filters [data-t="semua"]'); if (semua) semua.click();
    }
    setTimeout(function () {
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      target.classList.add('zk-cari-sorot');
      setTimeout(function () { target.classList.remove('zk-cari-sorot'); }, 2600);
    }, 60);
  }

  function bangun() {
    dlg = document.createElement('dialog');
    dlg.className = 'zk-cari';
    dlg.setAttribute('aria-label', 'Cari di Zakademia');
    dlg.innerHTML =
      '<div class="zk-cari-box">' +
      '<div class="zk-cari-bar"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg>' +
      '<input type="search" placeholder="Cari di Zakademia" autocomplete="off" spellcheck="false" role="combobox" aria-expanded="true" aria-controls="zk-cari-list" aria-autocomplete="list" aria-label="Kata kunci">' +
      '<button type="button" class="zk-cari-tutup">Tutup</button></div>' +
      '<p class="zk-cari-info" aria-live="polite"></p>' +
      '<ul class="zk-cari-list" id="zk-cari-list" role="listbox" aria-label="Hasil pencarian"></ul>' +
      '<p class="zk-cari-kaki"><span><kbd>↑</kbd><kbd>↓</kbd> pilih</span><span><kbd>Enter</kbd> buka</span><span><kbd>Esc</kbd> tutup</span></p>' +
      '</div>';
    document.body.appendChild(dlg);
    inp = dlg.querySelector('input'); list = dlg.querySelector('.zk-cari-list'); info = dlg.querySelector('.zk-cari-info');
    var t;
    inp.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(function () {
        if (data) tampil(inp.value);
        else muat().then(function () { tampil(inp.value); }, gagal);
      }, 80);
    });
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { e.preventDefault(); tutup(); }   // kolom type=search biasanya hanya mengosongkan isi
      else if (e.key === 'ArrowDown') { e.preventDefault(); pilih(aktif + 1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); pilih(aktif - 1); }
      else if (e.key === 'Enter') {
        e.preventDefault();
        var i = aktif > -1 ? aktif : 0;
        if (hasil[i]) buka(hasil[i], e.ctrlKey || e.metaKey);
      }
    });
    list.addEventListener('click', function (e) {
      var a = e.target.closest('a[data-i]'); if (!a) return;
      if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1) return;   // biarkan buka di tab baru
      e.preventDefault(); buka(hasil[+a.getAttribute('data-i')]);
    });
    dlg.querySelector('.zk-cari-tutup').addEventListener('click', function () { tutup(); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) tutup(); });   // klik di luar kotak
    dlg.addEventListener('close', function () {
      document.documentElement.classList.remove('zk-cari-on');
      if (pemicu && pemicu.focus) pemicu.focus();
    });
  }

  function gagal() {
    info.textContent = 'Indeks pencarian belum dapat dimuat. Periksa koneksi, lalu coba lagi.';
  }

  function buka_dialog(e) {
    if (!dlg) bangun();
    if (dlg.open) return;
    pemicu = e && e.currentTarget && e.currentTarget.focus ? e.currentTarget : document.activeElement;
    var menu = document.getElementById('menu'), tg = document.getElementById('menuToggle');
    if (menu && menu.classList.contains('open')) { menu.classList.remove('open'); if (tg) tg.setAttribute('aria-expanded', 'false'); }
    document.documentElement.classList.add('zk-cari-on');
    if (dlg.showModal) dlg.showModal(); else dlg.setAttribute('open', '');
    inp.select();
    if (!inp.value) kosong(); else if (data) tampil(inp.value);
    if (!data) {
      muat().then(function () { if (inp.value) tampil(inp.value); }, gagal);
    }
  }
  function tutup(diam) {
    if (!dlg || !dlg.open) return;
    if (diam) pemicu = null;
    if (dlg.close) dlg.close(); else { dlg.removeAttribute('open'); dlg.dispatchEvent(new Event('close')); }
  }

  document.querySelectorAll('[data-cari]').forEach(function (b) { b.addEventListener('click', buka_dialog); });
  document.addEventListener('keydown', function (e) {
    var t = e.target, ketik = t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName));
    if ((e.key === 'k' || e.key === 'K') && (e.ctrlKey || e.metaKey) && !e.altKey) { e.preventDefault(); buka_dialog(); }
    else if (e.key === '/' && !ketik && !e.ctrlKey && !e.metaKey && !e.altKey) { e.preventDefault(); buka_dialog(); }
  });
  window.addEventListener('hashchange', function () { setTimeout(sorotKartu, 120); });
  // ?cari=kata membuka dialog pencarian dengan kata itu (dipakai halaman 404)
  var qCari = new URLSearchParams(location.search).get('cari');
  if (qCari) { bangun(); inp.value = qCari; setTimeout(buka_dialog, 150); }
  if (document.readyState === 'complete') setTimeout(sorotKartu, 200);
  else window.addEventListener('load', function () { setTimeout(sorotKartu, 200); });
  // muat indeks lebih awal saat pengguna mengarahkan kursor ke tombol
  document.querySelectorAll('[data-cari]').forEach(function (b) {
    b.addEventListener('pointerenter', function () { muat().catch(function () {}); }, { once: true });
  });
})();
