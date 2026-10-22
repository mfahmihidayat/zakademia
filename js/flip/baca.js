/* Pembaca buku digital Zakademia: membungkus pustaka page-flip (StPageFlip, MIT). */
(function () {
  'use strict';
  var B = window.BK, St = window.St;
  var wrap = document.getElementById('buku'), main = wrap.parentNode;
  var no = document.getElementById('no'), slider = document.getElementById('slider'), go = document.getElementById('go');
  var ratio = B.h / B.w, flip, cur = 1, loaded = {};

  function src(n) { return '/buku/hal.php?b=' + encodeURIComponent(B.slug) + '&n=' + n; }
  function pageEl(n) {
    var d = document.createElement('div');
    d.className = 'page' + (n === 1 || n === B.jumlah ? ' hard' : '');
    d.setAttribute('data-density', n === 1 || n === B.jumlah ? 'hard' : 'soft');
    d.dataset.n = n;
    if (n > B.batas && n !== B.jumlah) {
      var l = document.createElement('div'); l.className = 'lock';
      l.innerHTML = '<b>Halaman ' + n + ' terkunci</b><p>Pratinjau berakhir di halaman ' + B.batas + '. Beli bukunya untuk membaca seluruh ' + B.jumlah + ' halaman.</p>'
        + (B.lynk ? '<a href="' + B.lynk + '" target="_blank" rel="noopener">Beli di Lynk.id</a>' : '')
        + (B.email ? '' : '<a class="g" href="' + B.masuk + '">Sudah membeli? Masuk</a>');
      d.appendChild(l);
    } else {
      var img = document.createElement('img'); img.alt = 'Halaman ' + n; img.loading = 'lazy'; img.decoding = 'async';
      img.dataset.src = src(n);
      d.appendChild(img);
      var wm = document.createElement('div'); wm.className = 'wm'; d.appendChild(wm);
    }
    return d;
  }
  function load(n) {
    for (var i = Math.max(1, n - 3); i <= Math.min(B.jumlah, n + 4); i++) {
      if (loaded[i]) continue;
      var el = wrap.querySelector('.page[data-n="' + i + '"] img');
      if (el && el.dataset.src) { el.src = el.dataset.src; delete el.dataset.src; }
      loaded[i] = true;
    }
  }
  function size() {
    var W = main.clientWidth - 20, H = main.clientHeight - 20;
    var portrait = W < 760;
    var ph = Math.min(H, (portrait ? W : W / 2) * ratio), pw = ph / ratio;
    return { w: Math.floor(pw), h: Math.floor(ph), portrait: portrait };
  }
  function build() {
    var s = size();
    if (flip) { flip.destroy(); wrap.innerHTML = ''; }
    wrap.innerHTML = '';
    var frag = document.createDocumentFragment();
    for (var i = 1; i <= B.jumlah; i++) frag.appendChild(pageEl(i));
    wrap.appendChild(frag);
    loaded = {};
    flip = new St.PageFlip(wrap, {
      width: s.w, height: s.h, size: 'fixed', usePortrait: s.portrait, showCover: true, maxShadowOpacity: 0.35,
      mobileScrollSupport: false, flippingTime: 650, swipeDistance: 20, startPage: Math.max(0, cur - 1)
    });
    flip.loadFromHTML(wrap.querySelectorAll('.page'));
    flip.on('flip', function (e) { cur = e.data + 1; sync(); });
    sync();
  }
  function sync() {
    no.textContent = cur + ' / ' + B.jumlah; slider.value = cur; load(cur);
    try { localStorage.setItem('bk_' + B.slug, cur); } catch (e) {}
    document.getElementById('prev').disabled = document.getElementById('first').disabled = cur <= 1;
    document.getElementById('next').disabled = document.getElementById('last').disabled = cur >= B.jumlah;
  }
  function goTo(n) { n = Math.max(1, Math.min(B.jumlah, n | 0)); cur = n; flip.turnToPage(n - 1); sync(); }

  try { var saved = parseInt(localStorage.getItem('bk_' + B.slug) || '1', 10); if (saved > 1 && saved <= B.batas) cur = saved; } catch (e) {}
  build();
  var tip = document.getElementById('tip'); tip.classList.add('on'); setTimeout(function () { tip.classList.remove('on'); }, 3500);

  document.getElementById('prev').onclick = function () { flip.flipPrev(); };
  document.getElementById('next').onclick = function () { flip.flipNext(); };
  document.getElementById('first').onclick = function () { goTo(1); };
  document.getElementById('last').onclick = function () { goTo(B.jumlah); };
  slider.oninput = function () { no.textContent = this.value + ' / ' + B.jumlah; };
  slider.onchange = function () { goTo(parseInt(this.value, 10)); };
  go.onkeydown = function (e) { if (e.key === 'Enter') { goTo(parseInt(this.value, 10)); this.value = ''; this.blur(); } };
  document.getElementById('full').onclick = function () {
    var d = document.documentElement;
    if (document.fullscreenElement) document.exitFullscreen(); else if (d.requestFullscreen) d.requestFullscreen();
  };
  document.addEventListener('keydown', function (e) {
    if (e.target === go) return;
    if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') { e.preventDefault(); flip.flipNext(); }
    else if (e.key === 'ArrowLeft' || e.key === 'PageUp') { e.preventDefault(); flip.flipPrev(); }
    else if (e.key === 'Home') goTo(1); else if (e.key === 'End') goTo(B.jumlah);
  });
  var t; window.addEventListener('resize', function () { clearTimeout(t); t = setTimeout(build, 250); });
  document.addEventListener('contextmenu', function (e) { if (e.target.closest('.page')) e.preventDefault(); });

  /* ---------- cari di buku ---------- */
  var cari = document.getElementById('cari'), qCari = document.getElementById('qCari'), crCari = document.getElementById('crCari'), ciCari = document.getElementById('ciCari');
  function bukaCari() { cari.hidden = false; qCari.focus(); qCari.select(); }
  function tutupCari() { cari.hidden = true; }
  document.getElementById('btnCari').onclick = function () { cari.hidden ? bukaCari() : tutupCari(); };
  document.getElementById('xCari').onclick = tutupCari;
  document.getElementById('fCari').addEventListener('submit', function (e) {
    e.preventDefault(); var q = qCari.value.trim(); if (q.length < 3) return;
    ciCari.textContent = 'Mencari…'; crCari.innerHTML = '';
    fetch('/buku/cari.php?b=' + encodeURIComponent(B.slug) + '&q=' + encodeURIComponent(q), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (j) {
      if (!j.ok) { ciCari.textContent = j.error || 'Gagal mencari.'; return; }
      var n = j.hasil.length;
      ciCari.textContent = j.total === 0 ? 'Tidak ditemukan "' + j.q + '".' : 'Ditemukan di ' + j.total + ' halaman' + (j.terkunci ? ' (' + j.terkunci + ' di bagian berbayar)' : '') + '.';
      j.hasil.forEach(function (h) { var li = document.createElement('li'); var b = document.createElement('button'); b.type = 'button'; b.innerHTML = '<b>Halaman ' + h.n + (h.k > 1 ? ' · ' + h.k + ' kali' : '') + '</b>' + h.s; b.onclick = function () { goTo(h.n); if (window.innerWidth < 760) tutupCari(); }; li.appendChild(b); crCari.appendChild(li); });
      if (j.terkunci && !j.penuh) { var li = document.createElement('li'); li.className = 'lock'; var b = document.createElement('button'); b.type = 'button'; b.innerHTML = '<b>' + j.terkunci + ' hasil lagi di halaman berbayar</b>' + (B.lynk ? 'Beli bukunya untuk membuka seluruh halaman.' : 'Masuk dengan akun pembeli untuk membuka.'); b.onclick = function () { location.href = B.lynk || B.masuk; }; li.appendChild(b); crCari.appendChild(li); }
    }).catch(function () { ciCari.textContent = 'Gagal menghubungi server.'; });
  });

  /* ---------- perbesar halaman ---------- */
  var zoom = document.getElementById('zoom'), zs = document.getElementById('zs'), zImg = document.getElementById('zImg'), zPct = document.getElementById('zPct'), zNo = document.getElementById('zNo');
  var zn = 1, zk = 1, drag = null, pinch = null;
  function zTerapkan() { zImg.style.width = Math.round((zs.clientWidth - 32) * zk) + 'px'; zPct.textContent = Math.round(zk * 100) + '%'; }
  function bukaZoom(n) {
    n = Math.max(1, Math.min(B.jumlah, n)); if (n > B.batas && n !== B.jumlah) { alert('Halaman ini terkunci. Beli bukunya untuk membaca seluruh halaman.'); return; }
    zn = n; zNo.textContent = 'Halaman ' + n + ' / ' + B.jumlah; zImg.src = src(n); zoom.hidden = false; document.body.style.overflow = 'hidden'; zk = Math.max(1, zk); zImg.onload = function () { zTerapkan(); zs.scrollTop = 0; }; zTerapkan();
  }
  function tutupZoom() { zoom.hidden = true; document.body.style.overflow = ''; goTo(zn); }
  function zSet(k, cx, cy) { var lama = zk; zk = Math.max(1, Math.min(4, k)); var rx = (zs.scrollLeft + (cx || zs.clientWidth / 2)) / lama, ry = (zs.scrollTop + (cy || zs.clientHeight / 2)) / lama; zTerapkan(); zs.scrollLeft = rx * zk - (cx || zs.clientWidth / 2); zs.scrollTop = ry * zk - (cy || zs.clientHeight / 2); }
  document.getElementById('btnZoom').onclick = function () { bukaZoom(cur); };
  document.getElementById('zX').onclick = tutupZoom; document.getElementById('zPlus').onclick = function () { zSet(zk + 0.25); }; document.getElementById('zMin').onclick = function () { zSet(zk - 0.25); }; document.getElementById('zFit').onclick = function () { zSet(1); };
  document.getElementById('zPrev').onclick = function () { bukaZoom(zn - 1); }; document.getElementById('zNext').onclick = function () { bukaZoom(zn + 1); };
  wrap.addEventListener('dblclick', function (e) { var p = e.target.closest('.page'); if (p && p.querySelector('img')) bukaZoom(parseInt(p.dataset.n, 10)); });
  zs.addEventListener('wheel', function (e) { if (e.ctrlKey || e.metaKey) { e.preventDefault(); var r = zs.getBoundingClientRect(); zSet(zk * (e.deltaY < 0 ? 1.1 : 0.9), e.clientX - r.left, e.clientY - r.top); } }, { passive: false });
  zs.addEventListener('pointerdown', function (e) { if (e.pointerType === 'touch') return; drag = { x: e.clientX, y: e.clientY, l: zs.scrollLeft, t: zs.scrollTop }; zs.classList.add('drag'); zs.setPointerCapture(e.pointerId); });
  zs.addEventListener('pointermove', function (e) { if (!drag) return; zs.scrollLeft = drag.l - (e.clientX - drag.x); zs.scrollTop = drag.t - (e.clientY - drag.y); });
  zs.addEventListener('pointerup', function () { drag = null; zs.classList.remove('drag'); }); zs.addEventListener('pointercancel', function () { drag = null; zs.classList.remove('drag'); });
  zs.addEventListener('touchstart', function (e) { if (e.touches.length === 2) { pinch = { d: Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY), k: zk }; } else if (e.touches.length === 1) { drag = { x: e.touches[0].clientX, y: e.touches[0].clientY, l: zs.scrollLeft, t: zs.scrollTop }; } }, { passive: true });
  zs.addEventListener('touchmove', function (e) { if (e.touches.length === 2 && pinch) { e.preventDefault(); var d = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY); zSet(pinch.k * d / pinch.d); } else if (e.touches.length === 1 && drag) { zs.scrollLeft = drag.l - (e.touches[0].clientX - drag.x); zs.scrollTop = drag.t - (e.touches[0].clientY - drag.y); } }, { passive: false });
  zs.addEventListener('touchend', function () { pinch = null; drag = null; });
  document.addEventListener('keydown', function (e) {
    if ((e.key === 'f' || e.key === 'F') && (e.ctrlKey || e.metaKey)) { e.preventDefault(); bukaCari(); return; }
    if (!zoom.hidden) { if (e.key === 'Escape') tutupZoom(); else if (e.key === '+' || e.key === '=') zSet(zk + 0.25); else if (e.key === '-') zSet(zk - 0.25); else if (e.key === 'ArrowRight') bukaZoom(zn + 1); else if (e.key === 'ArrowLeft') bukaZoom(zn - 1); e.stopImmediatePropagation(); return; }
    if (e.key === 'Escape' && !cari.hidden) tutupCari();
  }, true);
})();
