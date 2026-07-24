/* Chimie Academy · strat de interacțiuni premium
   - rețea moleculară animată în hero (canvas)
   - reveal la scroll (IntersectionObserver)
   - contoare animate pentru statistici
   - topbar cu stare „scrolled"
   Respectă prefers-reduced-motion. */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---------------------------------------------------- topbar la scroll
  var topbar = document.querySelector('.topbar');
  if (topbar) {
    var onScroll = function () {
      topbar.classList.toggle('scrolled', window.scrollY > 12);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ---------------------------------------------------- reveal la scroll
  var revealTargets = document.querySelectorAll(
    '.panel, .mission-card, .arena-card, .stat-tile, .tiny-card, .leader-row'
  );
  if (!reduced && 'IntersectionObserver' in window && revealTargets.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -6% 0px' });
    revealTargets.forEach(function (el, i) {
      el.classList.add('reveal');
      el.style.transitionDelay = Math.min(i % 6, 4) * 45 + 'ms';
      io.observe(el);
    });
  }

  // ---------------------------------------------------- contoare animate
  function animateCounter(el) {
    var raw = el.textContent.trim();
    if (!/^\d{1,6}$/.test(raw)) return;
    var target = parseInt(raw, 10);
    if (target === 0) return;
    var start = null;
    var dur = Math.min(1400, 500 + target * 8);
    function frame(ts) {
      if (!start) start = ts;
      var p = Math.min(1, (ts - start) / dur);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = String(Math.round(target * eased));
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }
  var counters = document.querySelectorAll('.kpi strong, .stat-tile strong');
  if (!reduced && 'IntersectionObserver' in window && counters.length) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          cio.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });
    counters.forEach(function (el) { cio.observe(el); });
  }

  // ---------------------------------------------------- moleculele din hero
  var hero = document.querySelector('.hero');
  if (!hero || reduced) return;

  var canvas = document.createElement('canvas');
  canvas.className = 'molecules';
  canvas.setAttribute('aria-hidden', 'true');
  hero.style.position = 'relative';
  hero.insertBefore(canvas, hero.firstChild);
  var ctx = canvas.getContext('2d');

  var atoms = [];
  var LINK_DIST = 130;
  var running = true;
  var dpr = Math.min(2, window.devicePixelRatio || 1);

  function palette() {
    var light = document.body.getAttribute('data-theme') === 'light';
    return light
      ? { atoms: ['rgba(8,150,200,', 'rgba(110,90,240,', 'rgba(6,160,115,'], bond: '76,96,180' }
      : { atoms: ['rgba(56,225,255,', 'rgba(143,123,255,', 'rgba(60,240,174,'], bond: '140,170,255' };
  }

  function resize() {
    var rect = hero.getBoundingClientRect();
    canvas.width = Math.floor(rect.width * dpr);
    canvas.height = Math.floor(rect.height * dpr);
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    var count = Math.max(16, Math.min(46, Math.floor(rect.width / 34)));
    atoms = [];
    for (var i = 0; i < count; i++) {
      atoms.push({
        x: Math.random() * rect.width,
        y: Math.random() * rect.height,
        vx: (Math.random() - 0.5) * 0.35,
        vy: (Math.random() - 0.5) * 0.35,
        r: 1.6 + Math.random() * 2.4,
        c: Math.floor(Math.random() * 3)
      });
    }
  }

  function step() {
    if (!running) return;
    var w = canvas.width / dpr, h = canvas.height / dpr;
    var pal = palette();
    ctx.clearRect(0, 0, w, h);

    for (var i = 0; i < atoms.length; i++) {
      var a = atoms[i];
      a.x += a.vx;
      a.y += a.vy;
      if (a.x < -12) a.x = w + 12;
      if (a.x > w + 12) a.x = -12;
      if (a.y < -12) a.y = h + 12;
      if (a.y > h + 12) a.y = -12;
    }

    // legături („covalente") între atomii apropiați
    for (var m = 0; m < atoms.length; m++) {
      for (var n = m + 1; n < atoms.length; n++) {
        var dx = atoms[m].x - atoms[n].x;
        var dy = atoms[m].y - atoms[n].y;
        var d2 = dx * dx + dy * dy;
        if (d2 < LINK_DIST * LINK_DIST) {
          var alpha = 0.34 * (1 - Math.sqrt(d2) / LINK_DIST);
          ctx.strokeStyle = 'rgba(' + pal.bond + ',' + alpha.toFixed(3) + ')';
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(atoms[m].x, atoms[m].y);
          ctx.lineTo(atoms[n].x, atoms[n].y);
          ctx.stroke();
        }
      }
    }

    for (var k = 0; k < atoms.length; k++) {
      var at = atoms[k];
      ctx.fillStyle = pal.atoms[at.c] + '0.85)';
      ctx.beginPath();
      ctx.arc(at.x, at.y, at.r, 0, Math.PI * 2);
      ctx.fill();
    }
    requestAnimationFrame(step);
  }

  document.addEventListener('visibilitychange', function () {
    var wasRunning = running;
    running = !document.hidden;
    if (running && !wasRunning) requestAnimationFrame(step);
  });
  window.addEventListener('resize', resize);
  resize();
  requestAnimationFrame(step);
})();
