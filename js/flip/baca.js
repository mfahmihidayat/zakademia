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
})();
