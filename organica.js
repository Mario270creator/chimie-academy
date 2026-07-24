/* Chimie Organică · generator de structuri + constructor de catene + arena de antrenament
   Vanilla JS, fără dependențe. Desenele sunt SVG generate dinamic. */
(function () {
  'use strict';

  // ---------------------------------------------------------------- utilitare
  var PREFIX = ['', 'met', 'et', 'prop', 'but', 'pent', 'hex', 'hept', 'oct', 'non', 'dec'];
  var PREFIX_A = ['', 'meta', 'eta', 'propa', 'buta', 'penta', 'hexa', 'hepta', 'octa', 'nona', 'deca'];
  var SUBS = { '0': '₀', '1': '₁', '2': '₂', '3': '₃', '4': '₄', '5': '₅', '6': '₆', '7': '₇', '8': '₈', '9': '₉' };
  var ISOMERS = { 4: 2, 5: 3, 6: 5, 7: 9, 8: 18, 9: 35, 10: 75 };
  var SVG_NS = 'http://www.w3.org/2000/svg';

  function sub(str) {
    return String(str).replace(/\d/g, function (d) { return SUBS[d]; });
  }

  function hCount(n, doubles, triples) {
    return 2 * n + 2 - 2 * doubles - 4 * triples;
  }

  function molFormula(n, doubles, triples) {
    var h = hCount(n, doubles, triples);
    return 'C' + sub(n) + 'H' + sub(h);
  }

  // bonds: array de lungime n-1 cu valori 1/2/3 (ordinul legăturii dintre C[i] și C[i+1])
  function bondStats(bonds) {
    var d = 0, t = 0;
    for (var i = 0; i < bonds.length; i++) {
      if (bonds[i] === 2) d++;
      if (bonds[i] === 3) t++;
    }
    return { d: d, t: t };
  }

  // valența: suma ordinelor de legătură la fiecare carbon trebuie să fie ≤ 4
  function valenceOk(bonds) {
    var n = bonds.length + 1;
    for (var c = 0; c < n; c++) {
      var s = 0;
      if (c > 0) s += bonds[c - 1];
      if (c < n - 1) s += bonds[c];
      if (s > 4) return false;
    }
    return true;
  }

  // locant minim: alege sensul de numerotare care dă lista de locanți cea mai mică
  function locants(bonds, order) {
    var n = bonds.length + 1;
    var left = [], right = [];
    for (var i = 0; i < bonds.length; i++) {
      if (bonds[i] === order) {
        left.push(i + 1);
        right.push(n - 1 - i);
      }
    }
    right.sort(function (a, b) { return a - b; });
    left.sort(function (a, b) { return a - b; });
    for (var k = 0; k < left.length; k++) {
      if (left[k] < right[k]) return left;
      if (right[k] < left[k]) return right;
    }
    return left;
  }

  function chainName(n, bonds) {
    var st = bondStats(bonds);
    if (st.d === 0 && st.t === 0) return PREFIX[n] + 'an';
    if (st.d === 1 && st.t === 0) {
      if (n <= 3) return PREFIX[n] + 'enă';
      return PREFIX[n] + '-' + locants(bonds, 2)[0] + '-enă';
    }
    if (st.d === 0 && st.t === 1) {
      if (n <= 3) return PREFIX[n] + 'ină';
      return PREFIX[n] + '-' + locants(bonds, 3)[0] + '-ină';
    }
    if (st.d === 2 && st.t === 0) {
      return PREFIX_A[n] + '-' + locants(bonds, 2).join(',') + '-dienă';
    }
    if (st.d === 0 && st.t === 2) {
      return PREFIX_A[n] + '-' + locants(bonds, 3).join(',') + '-diină';
    }
    return null; // combinație avansată
  }

  function seriesInfo(bonds) {
    var st = bondStats(bonds);
    if (st.d === 0 && st.t === 0) return { name: 'alcan (saturat)', general: 'CₙH₂ₙ₊₂' };
    if (st.t === 0 && st.d === 1) return { name: 'alchenă (nesaturată)', general: 'CₙH₂ₙ' };
    if (st.d === 0 && st.t === 1) return { name: 'alchină (nesaturată)', general: 'CₙH₂ₙ₋₂' };
    return { name: 'polienă/poliină (nesaturată)', general: '—' };
  }

  // formula semi-structurală: CH₃−CH₂−CH=CH₂ etc.
  function semiStructural(n, bonds) {
    if (n === 1) return 'CH' + sub(4);
    var parts = [];
    for (var c = 0; c < n; c++) {
      var used = 0;
      if (c > 0) used += bonds[c - 1];
      if (c < n - 1) used += bonds[c];
      var h = 4 - used;
      parts.push(h === 0 ? 'C' : (h === 1 ? 'CH' : 'CH' + sub(h)));
    }
    var out = parts[0];
    var glyph = { 1: '−', 2: '=', 3: '≡' };
    for (var i = 0; i < bonds.length; i++) {
      out += glyph[bonds[i]] + parts[i + 1];
    }
    return out;
  }

  // ---------------------------------------------------------------- desen SVG
  function svgEl(tag, attrs) {
    var el = document.createElementNS(SVG_NS, tag);
    for (var k in attrs) el.setAttribute(k, attrs[k]);
    return el;
  }

  function line(svg, x1, y1, x2, y2, w) {
    svg.appendChild(svgEl('line', {
      x1: x1, y1: y1, x2: x2, y2: y2,
      stroke: 'currentColor', 'stroke-width': w || 2.2, 'stroke-linecap': 'round'
    }));
  }

  function text(svg, x, y, str, size, bold) {
    var t = svgEl('text', {
      x: x, y: y, 'text-anchor': 'middle', 'dominant-baseline': 'central',
      'font-size': size || 17, 'font-family': 'inherit',
      'font-weight': bold ? 700 : 500, fill: 'currentColor'
    });
    t.textContent = str;
    svg.appendChild(t);
    return t;
  }

  function multiBond(svg, x1, y1, x2, y2, order, gap) {
    gap = gap || 4;
    var dx = x2 - x1, dy = y2 - y1;
    var len = Math.sqrt(dx * dx + dy * dy) || 1;
    var ox = -dy / len * gap, oy = dx / len * gap;
    if (order === 1) line(svg, x1, y1, x2, y2);
    if (order === 2) {
      line(svg, x1 + ox, y1 + oy, x2 + ox, y2 + oy);
      line(svg, x1 - ox, y1 - oy, x2 - ox, y2 - oy);
    }
    if (order === 3) {
      line(svg, x1, y1, x2, y2);
      line(svg, x1 + ox * 1.7, y1 + oy * 1.7, x2 + ox * 1.7, y2 + oy * 1.7);
      line(svg, x1 - ox * 1.7, y1 - oy * 1.7, x2 - ox * 1.7, y2 - oy * 1.7);
    }
  }

  // structura completă (cu toți atomii de H)
  function drawStructural(container, n, bonds) {
    container.innerHTML = '';
    var dx = 70, cy = 84, pad = 52;
    var width = pad * 2 + dx * Math.max(0, n - 1);
    var svg = svgEl('svg', { viewBox: '0 0 ' + width + ' 168', class: 'mol-svg' });
    container.appendChild(svg);

    if (n === 1) {
      var cx = width / 2;
      text(svg, cx, cy, 'C', 19, true);
      line(svg, cx, cy - 12, cx, cy - 34); text(svg, cx, cy - 44, 'H');
      line(svg, cx, cy + 12, cx, cy + 34); text(svg, cx, cy + 44, 'H');
      line(svg, cx - 13, cy, cx - 34, cy); text(svg, cx - 44, cy, 'H');
      line(svg, cx + 13, cy, cx + 34, cy); text(svg, cx + 44, cy, 'H');
      return;
    }

    var xs = [];
    for (var c = 0; c < n; c++) xs.push(pad + c * dx);

    for (var b = 0; b < bonds.length; b++) {
      multiBond(svg, xs[b] + 14, cy, xs[b + 1] - 14, cy, bonds[b]);
    }
    for (var i = 0; i < n; i++) {
      text(svg, xs[i], cy, 'C', 19, true);
      var used = 0;
      if (i > 0) used += bonds[i - 1];
      if (i < n - 1) used += bonds[i];
      var free = 4 - used;
      var slots = [];
      if (i === 0) slots.push('left');
      if (i === n - 1) slots.push('right');
      slots.push('up', 'down');
      for (var f = 0; f < free; f++) {
        var pos = slots[f];
        if (pos === 'up') { line(svg, xs[i], cy - 12, xs[i], cy - 34); text(svg, xs[i], cy - 44, 'H'); }
        if (pos === 'down') { line(svg, xs[i], cy + 12, xs[i], cy + 34); text(svg, xs[i], cy + 44, 'H'); }
        if (pos === 'left') { line(svg, xs[i] - 13, cy, xs[i] - 34, cy); text(svg, xs[i] - 44, cy, 'H'); }
        if (pos === 'right') { line(svg, xs[i] + 13, cy, xs[i] + 34, cy); text(svg, xs[i] + 44, cy, 'H'); }
      }
    }
  }

  // schelet zigzag (linia frântă folosită de chimiști)
  function drawSkeletal(container, n, bonds) {
    container.innerHTML = '';
    var dx = 44, hi = 58, lo = 100, pad = 26;
    var width = pad * 2 + dx * Math.max(1, n - 1);
    var svg = svgEl('svg', { viewBox: '0 0 ' + width + ' 158', class: 'mol-svg' });
    container.appendChild(svg);
    if (n === 1) { text(svg, width / 2, 80, 'CH' + sub(4), 20, true); return; }
    var pts = [];
    for (var c = 0; c < n; c++) pts.push([pad + c * dx, c % 2 === 0 ? lo : hi]);
    for (var b = 0; b < bonds.length; b++) {
      multiBond(svg, pts[b][0], pts[b][1], pts[b + 1][0], pts[b + 1][1], bonds[b], 3.4);
    }
  }

  // benzen / metilbenzen
  function drawBenzene(container, variant, methyl) {
    container.innerHTML = '';
    var svg = svgEl('svg', { viewBox: '0 0 220 210', class: 'mol-svg' });
    container.appendChild(svg);
    var cx = 110, cy = methyl ? 118 : 105, R = 52;
    var pts = [];
    for (var k = 0; k < 6; k++) {
      var a = Math.PI / 180 * (60 * k - 90);
      pts.push([cx + R * Math.cos(a), cy + R * Math.sin(a)]);
    }
    for (var i = 0; i < 6; i++) {
      var p1 = pts[i], p2 = pts[(i + 1) % 6];
      line(svg, p1[0], p1[1], p2[0], p2[1], 2.6);
      if (variant === 'kekule' && i % 2 === 0) {
        var mx = (p1[0] + p2[0]) / 2, my = (p1[1] + p2[1]) / 2;
        var vx = cx - mx, vy = cy - my;
        var vl = Math.sqrt(vx * vx + vy * vy);
        var off = 7.5;
        line(svg,
          p1[0] + vx / vl * off + (p2[0] - p1[0]) * 0.16, p1[1] + vy / vl * off + (p2[1] - p1[1]) * 0.16,
          p2[0] + vx / vl * off - (p2[0] - p1[0]) * 0.16, p2[1] + vy / vl * off - (p2[1] - p1[1]) * 0.16, 2.2);
      }
    }
    if (variant === 'circle') {
      svg.appendChild(svgEl('circle', { cx: cx, cy: cy, r: R * 0.58, fill: 'none', stroke: 'currentColor', 'stroke-width': 2.2 }));
    }
    if (methyl) {
      var top = pts[0];
      line(svg, top[0], top[1], top[0], top[1] - 30, 2.6);
      text(svg, top[0], top[1] - 42, 'CH' + sub(3), 16, true);
    }
  }

  // ---------------------------------------------------------------- GENERATOR
  var gen = {
    type: document.getElementById('genType'),
    carbons: document.getElementById('genCarbons'),
    carbonsOut: document.getElementById('genCarbonsOut'),
    position: document.getElementById('genPosition'),
    positionWrap: document.getElementById('genPositionWrap'),
    areneWrap: document.getElementById('genAreneWrap'),
    arene: document.getElementById('genArene'),
    carbonsWrap: document.getElementById('genCarbonsWrap'),
    name: document.getElementById('genName'),
    formula: document.getElementById('genFormula'),
    general: document.getElementById('genGeneral'),
    semi: document.getElementById('genSemi'),
    facts: document.getElementById('genFacts'),
    stage: document.getElementById('genStage'),
    stage2: document.getElementById('genStage2'),
    stage2Wrap: document.getElementById('genStage2Wrap'),
    modeButtons: document.querySelectorAll('[data-gen-mode]')
  };
  var genMode = 'structural';

  function genBonds(n, type, pos) {
    var bonds = [];
    for (var i = 0; i < n - 1; i++) bonds.push(1);
    if (type === 'alchena') bonds[pos - 1] = 2;
    if (type === 'alchina') bonds[pos - 1] = 3;
    return bonds;
  }

  function alkaneState(n) {
    if (n <= 4) return 'gaz';
    if (n <= 17) return 'lichid';
    return 'solid';
  }

  function renderGenerator() {
    if (!gen.type) return;
    var type = gen.type.value;
    var isArene = type === 'arena';
    gen.areneWrap.style.display = isArene ? '' : 'none';
    gen.carbonsWrap.style.display = isArene ? 'none' : '';
    gen.stage2Wrap.style.display = isArene ? '' : 'none';

    if (isArene) {
      var which = gen.arene.value;
      gen.positionWrap.style.display = 'none';
      if (which === 'benzen') {
        gen.name.textContent = 'benzen';
        gen.formula.textContent = 'C' + sub(6) + 'H' + sub(6);
        gen.facts.textContent = 'Cea mai simplă arenă. Nucleul aromatic este stabil: benzenul dă reacții de substituție, nu de adiție. Toxic — se studiază doar teoretic.';
        drawBenzene(gen.stage, 'kekule', false);
        drawBenzene(gen.stage2, 'circle', false);
      } else {
        gen.name.textContent = 'metilbenzen (toluen)';
        gen.formula.textContent = 'C' + sub(7) + 'H' + sub(8);
        gen.facts.textContent = 'Un nucleu benzenic cu o grupare −CH₃. Solvent industrial; al doilea termen al seriei aromatice.';
        drawBenzene(gen.stage, 'kekule', true);
        drawBenzene(gen.stage2, 'circle', true);
      }
      gen.general.textContent = 'CₙH₂ₙ₋₆ (n ≥ 6)';
      gen.semi.textContent = which === 'benzen' ? 'C₆H₆ — hexagon aromatic' : 'C₆H₅−CH₃';
      return;
    }

    var minC = type === 'alcan' ? 1 : 2;
    if (+gen.carbons.value < minC) gen.carbons.value = minC;
    gen.carbons.min = minC;
    var n = +gen.carbons.value;
    gen.carbonsOut.textContent = n;

    // pozițiile posibile ale legăturii multiple
    var needPos = type !== 'alcan' && n >= 4;
    gen.positionWrap.style.display = needPos ? '' : 'none';
    var maxPos = Math.max(1, Math.floor((n - 1)));
    if (needPos) {
      var current = +gen.position.value || 1;
      gen.position.innerHTML = '';
      for (var p = 1; p <= n - 1; p++) {
        // afișăm doar pozițiile cu locant minim (p ≤ n-p)
        if (p > n - p) break;
        var opt = document.createElement('option');
        opt.value = p;
        opt.textContent = 'poziția ' + p;
        gen.position.appendChild(opt);
      }
      if (current <= Math.floor((n) / 2)) gen.position.value = String(Math.min(current, Math.floor(n / 2)));
    }
    var pos = needPos ? +gen.position.value : 1;

    var bonds = genBonds(n, type, pos);
    var st = bondStats(bonds);
    gen.name.textContent = chainName(n, bonds) || '—';
    gen.formula.textContent = molFormula(n, st.d, st.t);
    var info = seriesInfo(bonds);
    gen.general.textContent = info.general + ' · ' + info.name;
    gen.semi.textContent = semiStructural(n, bonds);

    var facts = [];
    if (type === 'alcan') {
      facts.push('Stare la 25°C: ' + alkaneState(n) + '.');
      if (ISOMERS[n]) facts.push('Formula C' + sub(n) + 'H' + sub(2 * n + 2) + ' are ' + ISOMERS[n] + ' izomeri de catenă.');
      if (n === 1) facts.push('Metanul este componenta principală a gazului natural.');
    }
    if (type === 'alchena' && n === 2) facts.push('Din etenă se obține polietilena — plasticul pungilor.');
    if (type === 'alchina' && n === 2) facts.push('Etina (acetilena) arde la ~3000°C — flacăra de sudură.');
    if (type !== 'alcan') facts.push('Nesaturată: decolorează apa de brom (reacție de adiție).');
    gen.facts.textContent = facts.join(' ');

    if (genMode === 'structural') drawStructural(gen.stage, n, bonds);
    else drawSkeletal(gen.stage, n, bonds);
  }

  if (gen.type) {
    ['change', 'input'].forEach(function (evt) {
      gen.type.addEventListener(evt, renderGenerator);
      gen.carbons.addEventListener(evt, renderGenerator);
      gen.position.addEventListener(evt, renderGenerator);
      gen.arene.addEventListener(evt, renderGenerator);
    });
    gen.modeButtons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        genMode = btn.dataset.genMode;
        gen.modeButtons.forEach(function (b) {
          b.classList.toggle('primary', b === btn);
          b.classList.toggle('ghost', b !== btn);
        });
        renderGenerator();
      });
    });
    renderGenerator();
  }

  // ---------------------------------------------------------------- CONSTRUCTOR
  var builder = {
    stage: document.getElementById('buildStage'),
    bondsRow: document.getElementById('buildBonds'),
    name: document.getElementById('buildName'),
    formula: document.getElementById('buildFormula'),
    series: document.getElementById('buildSeries'),
    semi: document.getElementById('buildSemi'),
    msg: document.getElementById('buildMsg'),
    addBtn: document.getElementById('buildAdd'),
    removeBtn: document.getElementById('buildRemove'),
    resetBtn: document.getElementById('buildReset')
  };
  var chain = { n: 4, bonds: [1, 1, 1] };

  function renderBuilder() {
    if (!builder.stage) return;
    drawStructural(builder.stage, chain.n, chain.bonds);

    builder.bondsRow.innerHTML = '';
    var glyph = { 1: '−', 2: '=', 3: '≡' };
    for (var i = 0; i < chain.bonds.length; i++) {
      (function (idx) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'bond-btn';
        b.textContent = 'C' + (idx + 1) + ' ' + glyph[chain.bonds[idx]] + ' C' + (idx + 2);
        b.title = 'Apasă pentru a schimba ordinul legăturii';
        b.addEventListener('click', function () {
          var next = chain.bonds[idx] === 3 ? 1 : chain.bonds[idx] + 1;
          var test = chain.bonds.slice();
          test[idx] = next;
          while (!valenceOk(test)) {
            next = next === 3 ? 1 : next + 1;
            test[idx] = next;
            if (next === chain.bonds[idx]) break;
          }
          if (!valenceOk(test)) {
            builder.msg.textContent = '⚠️ Carbonul ar depăși 4 legături — imposibil chimic.';
            return;
          }
          chain.bonds[idx] = test[idx];
          builder.msg.textContent = '';
          renderBuilder();
        });
        builder.bondsRow.appendChild(b);
      })(i);
    }

    var st = bondStats(chain.bonds);
    var nm = chainName(chain.n, chain.bonds);
    builder.name.textContent = nm || 'denumire avansată (peste nivelul introductiv)';
    builder.formula.textContent = molFormula(chain.n, st.d, st.t);
    var info = seriesInfo(chain.bonds);
    builder.series.textContent = info.name + (info.general !== '—' ? ' · ' + info.general : '');
    builder.semi.textContent = semiStructural(chain.n, chain.bonds);
  }

  if (builder.stage) {
    builder.addBtn.addEventListener('click', function () {
      if (chain.n >= 10) { builder.msg.textContent = 'Maxim 10 atomi de carbon în acest constructor.'; return; }
      chain.n++;
      chain.bonds.push(1);
      builder.msg.textContent = '';
      renderBuilder();
    });
    builder.removeBtn.addEventListener('click', function () {
      if (chain.n <= 1) return;
      chain.n--;
      chain.bonds.pop();
      builder.msg.textContent = '';
      renderBuilder();
    });
    builder.resetBtn.addEventListener('click', function () {
      chain = { n: 4, bonds: [1, 1, 1] };
      builder.msg.textContent = '';
      renderBuilder();
    });
    renderBuilder();
  }

  // ---------------------------------------------------------------- ARENA
  var arena = {
    panel: document.getElementById('arenaPanel'),
    question: document.getElementById('arenaQuestion'),
    stage: document.getElementById('arenaStage'),
    options: document.getElementById('arenaOptions'),
    feedback: document.getElementById('arenaFeedback'),
    next: document.getElementById('arenaNext'),
    score: document.getElementById('arenaScore'),
    streak: document.getElementById('arenaStreak'),
    best: document.getElementById('arenaBest'),
    catBoxes: document.querySelectorAll('[data-arena-cat]')
  };
  var arenaState = { score: 0, streak: 0, locked: false, current: null };

  function rand(n) { return Math.floor(Math.random() * n); }
  function pick(arr) { return arr[rand(arr.length)]; }
  function shuffle(arr) {
    for (var i = arr.length - 1; i > 0; i--) {
      var j = rand(i + 1);
      var t = arr[i]; arr[i] = arr[j]; arr[j] = t;
    }
    return arr;
  }

  function uniqueOptions(correct, candidates) {
    var opts = [correct];
    shuffle(candidates);
    for (var i = 0; i < candidates.length && opts.length < 4; i++) {
      if (opts.indexOf(candidates[i]) === -1) opts.push(candidates[i]);
    }
    return shuffle(opts.slice(0, 4));
  }

  var QGEN = {
    // nume -> formulă moleculară
    formula: function () {
      var type = pick(['alcan', 'alchena', 'alchina']);
      var n = type === 'alcan' ? 1 + rand(10) : 2 + rand(9);
      var bonds = genBonds(n, type, 1);
      var st = bondStats(bonds);
      var name = type === 'alcan' ? PREFIX[n] + 'an' : (type === 'alchena' ? PREFIX[n] + 'enă' : PREFIX[n] + 'ină');
      var correct = molFormula(n, st.d, st.t);
      var cands = [molFormula(n, 0, 0), molFormula(n, 1, 0), molFormula(n, 0, 1), molFormula(n + 1, st.d, st.t), molFormula(Math.max(1, n - 1), st.d, st.t)];
      return { q: 'Care este formula moleculară a hidrocarburii numite „' + name + '”?', options: uniqueOptions(correct, cands), correct: correct, why: name + ': n=' + n + ' → ' + correct + '.' };
    },
    // formulă -> nume
    nume: function () {
      var type = pick(['alcan', 'alchena', 'alchina']);
      var n = type === 'alcan' ? 1 + rand(10) : 2 + rand(9);
      var bonds = genBonds(n, type, 1);
      var st = bondStats(bonds);
      var correct = type === 'alcan' ? PREFIX[n] + 'an' : (type === 'alchena' ? PREFIX[n] + 'enă' : PREFIX[n] + 'ină');
      var cands = [PREFIX[n] + 'an', PREFIX[n] + 'enă', PREFIX[n] + 'ină'];
      if (n > 1) cands.push(PREFIX[n - 1] + 'an');
      if (n < 10) cands.push(PREFIX[n + 1] + 'an');
      return { q: 'Ce hidrocarbură are formula ' + molFormula(n, st.d, st.t) + '?', options: uniqueOptions(correct, cands), correct: correct, why: 'Numărul de H indică seria; prefixul „' + PREFIX[n] + '-” = ' + n + ' atomi de C.' };
    },
    // câți H
    hidrogen: function () {
      var type = pick(['alcan', 'alchena', 'alchina']);
      var n = type === 'alcan' ? 2 + rand(9) : 2 + rand(9);
      var bonds = genBonds(n, type, 1);
      var st = bondStats(bonds);
      var name = type === 'alcan' ? PREFIX[n] + 'an' : (type === 'alchena' ? PREFIX[n] + 'enă' : PREFIX[n] + 'ină');
      var h = hCount(n, st.d, st.t);
      var correct = String(h);
      var cands = [String(h + 2), String(h - 2), String(h + 1), String(2 * n), String(2 * n + 2)];
      return { q: 'Câți atomi de hidrogen conține o moleculă de ' + name + '?', options: uniqueOptions(correct, cands), correct: correct, why: 'Seria dă formula; pentru n=' + n + ' rezultă ' + h + ' atomi de H.' };
    },
    // formulă generală
    serie: function () {
      var map = [
        ['alcanii', 'CₙH₂ₙ₊₂'],
        ['alchenele', 'CₙH₂ₙ'],
        ['alchinele', 'CₙH₂ₙ₋₂'],
        ['arenele', 'CₙH₂ₙ₋₆']
      ];
      var chosen = pick(map);
      var correct = chosen[1];
      var cands = map.map(function (m) { return m[1]; });
      return { q: 'Care este formula generală pentru ' + chosen[0] + '?', options: uniqueOptions(correct, cands), correct: correct, why: chosen[0] + ' → ' + correct + '.' };
    },
    // structură desenată -> nume
    structura: function () {
      var type = pick(['alcan', 'alchena', 'alchina']);
      var n = type === 'alcan' ? 2 + rand(5) : 2 + rand(5);
      var maxPos = Math.max(1, Math.floor(n / 2));
      var posn = 1 + rand(maxPos);
      var bonds = genBonds(n, type, type === 'alcan' ? 1 : posn);
      var correct = chainName(n, bonds);
      var cands = [];
      [1, 2, 3].forEach(function (o) {
        for (var p = 1; p <= Math.max(1, Math.floor(n / 2)); p++) {
          var b2 = genBonds(n, o === 1 ? 'alcan' : (o === 2 ? 'alchena' : 'alchina'), p);
          var nm = chainName(n, b2);
          if (nm && nm !== correct) cands.push(nm);
        }
      });
      if (n < 10) cands.push(PREFIX[n + 1] + 'an');
      return { q: 'Cum se numește hidrocarbura din desen?', draw: { n: n, bonds: bonds }, options: uniqueOptions(correct, cands), correct: correct, why: 'Numără atomii de C (' + n + ') și identifică tipul legăturilor.' };
    }
  };

  function activeCats() {
    var cats = [];
    arena.catBoxes.forEach(function (cb) { if (cb.checked) cats.push(cb.dataset.arenaCat); });
    return cats.length ? cats : ['formula', 'nume', 'hidrogen', 'serie', 'structura'];
  }

  function nextQuestion() {
    var cat = pick(activeCats());
    var item = QGEN[cat]();
    arenaState.current = item;
    arenaState.locked = false;
    arena.question.textContent = item.q;
    arena.feedback.textContent = '';
    arena.feedback.className = 'arena-feedback';
    arena.stage.style.display = item.draw ? '' : 'none';
    if (item.draw) drawStructural(arena.stage, item.draw.n, item.draw.bonds);
    arena.options.innerHTML = '';
    item.options.forEach(function (opt) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'arena-option';
      b.textContent = opt;
      b.addEventListener('click', function () { answer(b, opt); });
      arena.options.appendChild(b);
    });
  }

  function answer(btn, opt) {
    if (arenaState.locked) return;
    arenaState.locked = true;
    var item = arenaState.current;
    var good = opt === item.correct;
    Array.prototype.forEach.call(arena.options.children, function (b) {
      if (b.textContent === item.correct) b.classList.add('good');
      else if (b === btn) b.classList.add('bad');
      b.disabled = true;
    });
    if (good) {
      arenaState.streak++;
      var gained = 10 + Math.min(10, (arenaState.streak - 1) * 2);
      arenaState.score += gained;
      arena.feedback.textContent = '✅ Corect! +' + gained + ' puncte. ' + item.why;
      arena.feedback.className = 'arena-feedback good';
    } else {
      arenaState.streak = 0;
      arena.feedback.textContent = '❌ Răspunsul corect: ' + item.correct + '. ' + item.why;
      arena.feedback.className = 'arena-feedback bad';
    }
    arena.score.textContent = arenaState.score;
    arena.streak.textContent = arenaState.streak;
    try {
      var best = +(localStorage.getItem('chimie.arenaBest') || 0);
      if (arenaState.score > best) {
        localStorage.setItem('chimie.arenaBest', String(arenaState.score));
        best = arenaState.score;
      }
      arena.best.textContent = best;
    } catch (e) {}
  }

  if (arena.panel) {
    try { arena.best.textContent = localStorage.getItem('chimie.arenaBest') || '0'; } catch (e) {}
    arena.next.addEventListener('click', nextQuestion);
    arena.catBoxes.forEach(function (cb) { cb.addEventListener('change', nextQuestion); });
    nextQuestion();
  }
})();
