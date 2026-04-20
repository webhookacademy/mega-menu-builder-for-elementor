/* global elementorFrontend */
(function () {
  'use strict';

  function initMegaMenu(wrap) {
    if (!wrap || wrap.dataset.mmbMMInit) return;
    wrap.dataset.mmbMMInit = '1';

    var isVertical = wrap.classList.contains('mmb-mm-vertical');
    var trigger    = wrap.dataset.mmbTrigger || 'hover';
    var bp         = parseInt(wrap.dataset.mmbBp || '1024', 10);
    var nav        = wrap.querySelector('.mmb-mm-nav');
    var catHeader  = wrap.querySelector('.mmb-mm-cat-header');
    var hamburger  = wrap.querySelector('.mmb-mm-hamburger');

    console.log('[MMB MegaMenu] init', {
      wrap: wrap,
      isVertical: isVertical,
      trigger: trigger,
      bp: bp,
      nav: nav,
      hamburger: hamburger,
      catHeader: catHeader,
      windowWidth: window.innerWidth
    });

    function isMobile() { return window.innerWidth <= bp; }

    /* ── Category header toggle (vertical) ──────────────────── */
    if (catHeader && nav) {
      catHeader.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = nav.classList.toggle('mmb-mm-nav-open');
        catHeader.classList.toggle('mmb-mm-cat-open', open);
        catHeader.setAttribute('aria-expanded', open ? 'true' : 'false');
        console.log('[MMB MegaMenu] catHeader clicked, open:', open);
      });
    }

    /* ── Hamburger toggle ────────────────────────────────────── */
    if (hamburger && nav) {
      console.log('[MMB MegaMenu] hamburger found, attaching click');
      hamburger.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = nav.classList.toggle('mmb-mm-nav-open');
        hamburger.classList.toggle('is-active', open);
        hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
        console.log('[MMB MegaMenu] hamburger clicked, open:', open, 'nav classes:', nav.className, 'nav computed display:', window.getComputedStyle(nav).display);
      });
    } else {
      console.warn('[MMB MegaMenu] hamburger or nav NOT found', { hamburger: hamburger, nav: nav });
    }

    /* ── Dropdown items ──────────────────────────────────────── */
    var items = Array.prototype.slice.call(
      wrap.querySelectorAll('.mmb-mm-nav > .mmb-mm-item.mmb-mm-has-drop')
    );
    console.log('[MMB MegaMenu] dropdown items found:', items.length);

    function closeAll(except) {
      items.forEach(function (item) {
        if (item === except) return;
        item.classList.remove('mmb-mm-open');
        var lnk = item.querySelector('.mmb-mm-link');
        if (lnk) lnk.setAttribute('aria-expanded', 'false');
      });
    }

    items.forEach(function (item) {
      var link     = item.querySelector('.mmb-mm-link');
      var dropdown = item.querySelector('.mmb-mm-dropdown');
      if (!link || !dropdown) return;

      link.setAttribute('aria-haspopup', 'true');
      link.setAttribute('aria-expanded', 'false');

      function openItem()  { closeAll(item); item.classList.add('mmb-mm-open'); link.setAttribute('aria-expanded', 'true'); }
      function closeItem() { item.classList.remove('mmb-mm-open'); link.setAttribute('aria-expanded', 'false'); }
      function toggleItem(e) {
        e.preventDefault(); e.stopPropagation();
        console.log('[MMB MegaMenu] item toggle', item.classList.contains('mmb-mm-open') ? 'closing' : 'opening');
        item.classList.contains('mmb-mm-open') ? closeItem() : openItem();
      }

      if (trigger === 'click') {
        link.addEventListener('click', toggleItem);
      } else {
        var timer;
        item.addEventListener('mouseenter', function () { if (isMobile()) return; clearTimeout(timer); openItem(); });
        item.addEventListener('mouseleave', function () { if (isMobile()) return; timer = setTimeout(closeItem, 150); });
        link.addEventListener('click', function (e) { if (!isMobile()) return; toggleItem(e); });
      }

      link.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); item.classList.contains('mmb-mm-open') ? closeItem() : openItem(); }
        if (e.key === 'Escape') { closeItem(); link.focus(); }
      });
    });

    /* ── Close on outside click ──────────────────────────────── */
    document.addEventListener('click', function (e) {
      if (wrap.contains(e.target)) return;
      closeAll(null);
      if (nav) nav.classList.remove('mmb-mm-nav-open');
      if (hamburger) { hamburger.classList.remove('is-active'); hamburger.setAttribute('aria-expanded', 'false'); }
      if (catHeader) { catHeader.classList.remove('mmb-mm-cat-open'); catHeader.setAttribute('aria-expanded', 'false'); }
    });

    /* ── Resize ──────────────────────────────────────────────── */
    window.addEventListener('resize', function () {
      if (!isMobile() && !isVertical) {
        if (nav) nav.classList.remove('mmb-mm-nav-open');
        if (hamburger) { hamburger.classList.remove('is-active'); hamburger.setAttribute('aria-expanded', 'false'); }
      }
    });
  }

  function bootAll() {
    var wraps = document.querySelectorAll('.mmb-mm-wrap');
    console.log('[MMB MegaMenu] bootAll found wraps:', wraps.length);
    wraps.forEach(initMegaMenu);
  }

  if (typeof window.elementorFrontend !== 'undefined') {
    window.elementorFrontend.hooks.addAction(
      'frontend/element_ready/mmb-mega-menu.default',
      function ($el) {
        console.log('[MMB MegaMenu] elementor frontend hook fired');
        var wrap = $el[0] && $el[0].querySelector('.mmb-mm-wrap');
        if (wrap) { delete wrap.dataset.mmbMMInit; initMegaMenu(wrap); }
      }
    );
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAll);
  } else {
    bootAll();
  }

})();
