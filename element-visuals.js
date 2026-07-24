/* Ilustrații „specimen" realiste pentru toate cele 118 elemente.
   - Redate procedural în SVG, pe baza aspectului real (stare, culoare, formă).
   - Dacă există o fotografie reală la element_SIMBOL.jpg, o folosește automat.
   - Se cuplează la motorul existent: împachetează renderElementDetails().  */
(function () {
  'use strict';

  // Aspect specific (restul primesc valori implicite după categorie/stare):
  // form: tube (gaz în tub cu descărcare) · liquid (fiolă cu lichid) · chunk (bucată de metal)
  //       crystal (cristale) · powder (pulbere) · capsule (element sintetic/radioactiv)
  var LOOK = {
    H:  { form: 'tube', c: '#ff7bc1', note: 'gaz incolor; roz-carmin în tub cu descărcare' },
    He: { form: 'tube', c: '#ffc7a3', note: 'gaz nobil; strălucire piersică în tub' },
    N:  { form: 'tube', c: '#c9a6ff', note: 'gaz incolor; violet pal în tub cu descărcare' },
    O:  { form: 'tube', c: '#9fd4ff', note: 'gaz incolor; lichidul e albastru pal' },
    F:  { form: 'tube', c: '#f2ef9a', note: 'gaz galben-pal, extrem de reactiv' },
    Ne: { form: 'tube', c: '#ff8a5c', note: 'roșu-portocaliu aprins în tuburi luminoase' },
    Cl: { form: 'tube', c: '#c8e86a', note: 'gaz galben-verzui, toxic' },
    Ar: { form: 'tube', c: '#b48aff', note: 'violet-liliachiu în tub cu descărcare' },
    Kr: { form: 'tube', c: '#cfe6ff', note: 'alb-albăstrui în tub cu descărcare' },
    Xe: { form: 'tube', c: '#8fb8ff', note: 'albastru intens în tub cu descărcare' },
    Rn: { form: 'capsule', c: '#9fe2c7', note: 'gaz nobil radioactiv' },
    Br: { form: 'liquid', c: '#8a2f16', note: 'lichid roșu-brun, vapori iritanți' },
    Hg: { form: 'liquid', c: '#c8cdd6', metallic: true, note: 'singurul metal lichid la 25°C' },
    S:  { form: 'crystal', c: '#f2d43c', note: 'cristale galbene, sfărâmicioase' },
    C:  { form: 'crystal', c: '#3a3d45', note: 'grafit cenușiu-negru (sau diamant)' },
    P:  { form: 'powder', c: '#c0392b', note: 'fosfor roșu — pulbere; forma albă luminează' },
    I:  { form: 'crystal', c: '#3d2a52', note: 'cristale violet-închis care sublimează' },
    Si: { form: 'chunk', c: '#5b6c85', metallic: true, note: 'metaloid cenușiu-albăstrui, lucios' },
    B:  { form: 'chunk', c: '#4a3f38', note: 'metaloid brun-negru, foarte dur' },
    Se: { form: 'chunk', c: '#6d6f7a', note: 'seleniu gri metalic' },
    Cu: { form: 'chunk', c: '#c97a4a', metallic: true, note: 'metal roșcat-arămiu' },
    Au: { form: 'chunk', c: '#f0c04a', metallic: true, note: 'metal galben, nu se oxidează' },
    Ag: { form: 'chunk', c: '#d9dee7', metallic: true, note: 'alb-argintiu strălucitor' },
    Cs: { form: 'chunk', c: '#e8d29a', metallic: true, note: 'auriu-pal, moale, extrem de reactiv' },
    Ca: { form: 'chunk', c: '#c3cad6', metallic: true, note: 'metal argintiu, se acoperă cu oxid' },
    Na: { form: 'chunk', c: '#cdd3dd', metallic: true, note: 'moale, se taie cu cuțitul; păstrat în petrol' },
    K:  { form: 'chunk', c: '#c9cfdb', metallic: true, note: 'moale, foarte reactiv; păstrat în petrol' },
    Li: { form: 'chunk', c: '#d3d8e0', metallic: true, note: 'cel mai ușor metal' },
    Fe: { form: 'chunk', c: '#a7adba', metallic: true, note: 'metal cenușiu; ruginește în aer umed' },
    Al: { form: 'chunk', c: '#ccd3de', metallic: true, note: 'ușor, argintiu, rezistent la coroziune' },
    Zn: { form: 'chunk', c: '#b7c1cf', metallic: true, note: 'argintiu-albăstrui' },
    Pb: { form: 'chunk', c: '#7e8694', metallic: true, note: 'gri-închis, moale și foarte dens' },
    Sn: { form: 'chunk', c: '#c8cedb', metallic: true, note: 'alb-argintiu, maleabil' },
    Ni: { form: 'chunk', c: '#c2c8d4', metallic: true, note: 'argintiu cu tentă aurie' },
    Cr: { form: 'chunk', c: '#ccd4e2', metallic: true, note: 'foarte lucios; folosit la cromare' },
    Ti: { form: 'chunk', c: '#b6bdca', metallic: true, note: 'ușor și rezistent' },
    W:  { form: 'chunk', c: '#9aa1ae', metallic: true, note: 'cel mai înalt punct de topire dintre metale' },
    Pt: { form: 'chunk', c: '#dde2ea', metallic: true, note: 'metal prețios alb-argintiu' },
    Mg: { form: 'chunk', c: '#c9cfdb', metallic: true, note: 'argintiu; arde cu lumină orbitoare' },
    U:  { form: 'capsule', c: '#8fb3a3', note: 'metal radioactiv, combustibil nuclear' },
    Pu: { form: 'capsule', c: '#a8b0a5', note: 'sintetic, radioactiv' },
    Tc: { form: 'capsule', c: '#9fb6c9', note: 'primul element produs artificial' }
  };

  var CAT_METAL_DEFAULT = '#c3cad6';

  function svgNS(tag, attrs) {
    var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
    for (var k in attrs) el.setAttribute(k, attrs[k]);
    return el;
  }
  function lighten(hex, f) {
    var n = parseInt(hex.slice(1), 16);
    var r = Math.min(255, ((n >> 16) & 255) + f), g = Math.min(255, ((n >> 8) & 255) + f), b = Math.min(255, (n & 255) + f);
    return 'rgb(' + r + ',' + g + ',' + b + ')';
  }
  function darken(hex, f) { return lighten(hex, -f); }

  var uidN = 0;
  function specimenSVG(look, symbol) {
    var svg = svgNS('svg', { viewBox: '0 0 220 160', role: 'img', 'aria-label': 'Ilustrație ' + symbol });
    var defs = svgNS('defs', {});
    svg.appendChild(defs);
    var gid = 'sg' + (++uidN);

    function grad(id, stops, x2, y2) {
      var g = svgNS('linearGradient', { id: id, x1: '0', y1: '0', x2: x2 || '0', y2: y2 || '1' });
      stops.forEach(function (s) { g.appendChild(svgNS('stop', { offset: s[0], 'stop-color': s[1] })); });
      defs.appendChild(g);
      return 'url(#' + id + ')';
    }

    // fundal de „masă de laborator"
    svg.appendChild(svgNS('rect', { x: 0, y: 0, width: 220, height: 160, rx: 10, fill: 'rgba(10,16,32,0.85)' }));
    svg.appendChild(svgNS('rect', { x: 0, y: 128, width: 220, height: 32, rx: 0, fill: 'rgba(255,255,255,0.05)' }));

    var c = look.c;
    if (look.form === 'tube') {
      // tub cu descărcare luminoasă
      var glowG = grad(gid + 'g', [[0, lighten(c, 70)], [0.5, c], [1, darken(c, 40)]], '0', '1');
      svg.appendChild(svgNS('ellipse', { cx: 110, cy: 78, rx: 74, ry: 40, fill: c, opacity: 0.18, filter: '' }));
      svg.appendChild(svgNS('rect', { x: 40, y: 62, width: 140, height: 32, rx: 16, fill: 'rgba(220,235,255,0.10)', stroke: 'rgba(220,235,255,0.45)', 'stroke-width': 2 }));
      svg.appendChild(svgNS('rect', { x: 30, y: 70, width: 12, height: 16, rx: 3, fill: '#5a6474' }));
      svg.appendChild(svgNS('rect', { x: 178, y: 70, width: 12, height: 16, rx: 3, fill: '#5a6474' }));
      svg.appendChild(svgNS('rect', { x: 48, y: 69, width: 124, height: 18, rx: 9, fill: glowG, opacity: 0.95 }));
      svg.appendChild(svgNS('rect', { x: 48, y: 72, width: 124, height: 5, rx: 2.5, fill: 'rgba(255,255,255,0.65)' }));
    } else if (look.form === 'liquid') {
      var lg = grad(gid + 'l', [[0, lighten(c, 40)], [1, darken(c, 30)]]);
      // fiolă
      svg.appendChild(svgNS('path', { d: 'M92 26 h36 v22 l14 18 v58 a10 10 0 0 1 -10 10 h-44 a10 10 0 0 1 -10 -10 v-58 l14 -18 z', fill: 'rgba(220,235,255,0.08)', stroke: 'rgba(220,235,255,0.45)', 'stroke-width': 2 }));
      svg.appendChild(svgNS('path', { d: 'M80 84 v40 a8 8 0 0 0 8 8 h44 a8 8 0 0 0 8 -8 v-40 z', fill: lg }));
      if (look.metallic) {
        svg.appendChild(svgNS('ellipse', { cx: 100, cy: 96, rx: 9, ry: 4, fill: 'rgba(255,255,255,0.75)' }));
      }
      svg.appendChild(svgNS('ellipse', { cx: 110, cy: 85, rx: 30, ry: 4, fill: lighten(c, 55), opacity: 0.8 }));
      svg.appendChild(svgNS('rect', { x: 90, y: 18, width: 40, height: 12, rx: 4, fill: '#5a6474' }));
    } else if (look.form === 'crystal') {
      var shards = [[70, 120, 96, 52, 118, 120], [104, 122, 128, 66, 150, 122], [88, 124, 108, 84, 132, 124]];
      shards.forEach(function (p, i) {
        var cg = grad(gid + 'c' + i, [[0, lighten(c, 45)], [1, darken(c, 35)]], '1', '1');
        svg.appendChild(svgNS('polygon', { points: p.join(','), fill: cg, stroke: darken(c, 55), 'stroke-width': 1 }));
        svg.appendChild(svgNS('polygon', { points: [p[0] + 6, p[1] - 4, p[2], p[3] + 8, p[2] + 4, p[3] + 22].join(','), fill: 'rgba(255,255,255,0.28)' }));
      });
    } else if (look.form === 'powder') {
      var pg = grad(gid + 'p', [[0, lighten(c, 25)], [1, darken(c, 25)]]);
      svg.appendChild(svgNS('path', { d: 'M55 122 q55 -42 110 0 z', fill: pg }));
      for (var i = 0; i < 26; i++) {
        svg.appendChild(svgNS('circle', {
          cx: 62 + Math.random() * 96,
          cy: 104 + Math.random() * 16,
          r: 1.4 + Math.random() * 2,
          fill: (Math.random() > 0.5 ? lighten(c, 40) : darken(c, 20))
        }));
      }
    } else if (look.form === 'capsule') {
      var kg = grad(gid + 'k', [[0, lighten(c, 30)], [1, darken(c, 35)]]);
      svg.appendChild(svgNS('rect', { x: 72, y: 48, width: 76, height: 76, rx: 14, fill: 'rgba(220,235,255,0.07)', stroke: 'rgba(220,235,255,0.4)', 'stroke-width': 2 }));
      svg.appendChild(svgNS('circle', { cx: 110, cy: 86, r: 24, fill: kg }));
      svg.appendChild(svgNS('circle', { cx: 110, cy: 86, r: 24, fill: 'none', stroke: lighten(c, 60), 'stroke-width': 1.5, opacity: 0.7 }));
      // simbol radioactiv discret
      var t = svgNS('text', { x: 110, y: 92, 'text-anchor': 'middle', 'font-size': 18, fill: 'rgba(8,14,24,0.75)' });
      t.textContent = '☢';
      svg.appendChild(t);
    } else {
      // chunk metalic / solid
      var mg = grad(gid + 'm', [[0, lighten(c, look.metallic ? 60 : 30)], [0.55, c], [1, darken(c, 45)]], '1', '1');
      svg.appendChild(svgNS('path', {
        d: 'M64 116 l14 -34 26 -14 34 8 20 26 -10 22 -36 8 z',
        fill: mg, stroke: darken(c, 60), 'stroke-width': 1.5
      }));
      svg.appendChild(svgNS('path', { d: 'M78 82 l26 -14 12 10 -24 16 z', fill: 'rgba(255,255,255,' + (look.metallic ? 0.45 : 0.22) + ')' }));
      svg.appendChild(svgNS('path', { d: 'M104 68 l34 8 8 12 -30 -4 z', fill: 'rgba(255,255,255,0.16)' }));
      svg.appendChild(svgNS('ellipse', { cx: 112, cy: 124, rx: 52, ry: 6, fill: 'rgba(0,0,0,0.35)' }));
    }
    return svg;
  }

  function lookFor(key, info) {
    var sym = info.simbol;
    if (LOOK[sym]) return LOOK[sym];
    var num = parseInt(info.numar, 10) || 0;
    var state = String(info.stare_de_agregare || '').toLowerCase();
    if (num >= 84) return { form: 'capsule', c: '#9aa8b5', note: 'element radioactiv / sintetic' };
    if (state.indexOf('gas') === 0 || state.indexOf('gaz') === 0) {
      return { form: 'tube', c: '#a9c6ff', note: 'gaz incolor' };
    }
    if (state.indexOf('lich') === 0 || state.indexOf('liq') === 0) {
      return { form: 'liquid', c: '#b9c4d4', note: 'lichid la temperatura camerei' };
    }
    var cat = '';
    try { cat = getElementCategory(info); } catch (e) {}
    if (cat === 'lantanid') return { form: 'chunk', c: '#c9ccd8', metallic: true, note: 'metal argintiu (lantanid)' };
    if (cat === 'actinid') return { form: 'capsule', c: '#a5b3ab', note: 'actinid radioactiv' };
    if (cat === 'metaloid') return { form: 'chunk', c: '#7d8ba0', metallic: true, note: 'metaloid cenușiu, lucios' };
    if (cat === 'nemetal') return { form: 'crystal', c: '#8f9aab', note: 'nemetal solid' };
    return { form: 'chunk', c: CAT_METAL_DEFAULT, metallic: true, note: 'metal argintiu' };
  }

  function injectSpecimen(key) {
    var container = document.getElementById('rezultat');
    if (!container || typeof elemente === 'undefined') return;
    var info = elemente[key];
    if (!info) return;
    var card = container.querySelector('.element-card');
    var host = card ? card.firstElementChild : null; // coloana cu element-hero
    if (!host) host = card || container;

    var old = container.querySelector('.specimen-fig');
    if (old) old.remove();

    var look = lookFor(key, info);
    var fig = document.createElement('figure');
    fig.className = 'specimen-fig';
    var cap = document.createElement('figcaption');
    cap.className = 'specimen-cap';
    cap.textContent = look.note || 'aspect tipic';

    // fotografie reală, dacă există local (element_SIMBOL.jpg)
    var img = new Image();
    img.alt = 'Fotografie ' + key;
    img.loading = 'lazy';
    img.onload = function () {
      fig.innerHTML = '';
      fig.appendChild(img);
      fig.appendChild(cap);
      cap.textContent = 'fotografie: ' + key;
    };
    img.onerror = function () { /* rămâne ilustrația SVG */ };
    img.src = 'element_' + info.simbol + '.jpg';

    fig.appendChild(specimenSVG(look, info.simbol));
    fig.appendChild(cap);
    host.appendChild(fig);
  }

  // ---------------------------------------------------------------- cuplare
  function init() {
    if (typeof window.renderElementDetails === 'function') {
      var orig = window.renderElementDetails;
      window.renderElementDetails = function (key) {
        orig(key);
        injectSpecimen(key);
        var res = document.getElementById('rezultat');
        if (res && res.scrollIntoView) res.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      };
    }

    // căutarea din topbar → cauta() din motor
    var search = document.getElementById('labSearch');
    if (search && typeof window.cauta === 'function' && typeof elemente !== 'undefined') {
      var dl = document.createElement('datalist');
      dl.id = 'labSearchList';
      Object.keys(elemente).forEach(function (name) {
        var opt = document.createElement('option');
        opt.value = name;
        dl.appendChild(opt);
      });
      document.body.appendChild(dl);
      search.setAttribute('list', 'labSearchList');
      function go() {
        var v = search.value.trim();
        if (!v) return;
        cauta(v);
        search.blur();
      }
      search.addEventListener('change', go);
      search.addEventListener('keydown', function (e) { if (e.key === 'Enter') go(); });
    }

    // dock: evidențiez secțiunea activă la scroll
    var links = document.querySelectorAll('.lab-dock a[href^="#"]');
    if ('IntersectionObserver' in window && links.length) {
      var map = {};
      links.forEach(function (a) {
        var id = a.getAttribute('href').slice(1);
        var target = document.getElementById(id);
        if (target) map[id] = a;
      });
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting && map[en.target.id]) {
            links.forEach(function (a) { a.classList.remove('active'); });
            map[en.target.id].classList.add('active');
          }
        });
      }, { rootMargin: '-30% 0px -60% 0px' });
      Object.keys(map).forEach(function (id) { io.observe(document.getElementById(id)); });
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
