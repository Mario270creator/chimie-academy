/* Laborator · Experimente ghidate + jocul „Ghicește elementul"
   Modul complet independent: se montează singur în pagină, nu modifică nimic existent. */
(function () {
  'use strict';

  // ================================================================ date
  var EXPERIMENTS = [
    {
      id: 'neutralizare',
      title: '🌸 Neutralizare cu fenolftaleină',
      type: 'neutralizare',
      equation: 'NaOH + HCl → NaCl + H₂O',
      safety: ['🥽 Ochelari', '🧤 Mănuși'],
      lesson: 'Acidul și baza își anulează reciproc proprietățile: rezultă sare și apă. Indicatorul arată exact momentul neutralizării.',
      steps: [
        { text: 'În pahar avem o soluție de <strong>hidroxid de sodiu (NaOH)</strong> — o bază incoloră.', fx: { liquid: 'rgba(170,210,255,0.30)', level: 46 } },
        { text: 'Adăugăm 2-3 picături de <strong>fenolftaleină</strong>. În mediu bazic, indicatorul devine <strong>roz-carmin</strong>!', fx: { liquid: 'rgba(255,80,150,0.55)', pour: 'rgba(255,120,180,0.8)' } },
        { text: 'Turnăm treptat <strong>acid clorhidric (HCl)</strong>. Acidul consumă baza, iar culoarea începe să pălească…', fx: { liquid: 'rgba(255,140,190,0.35)', pour: 'rgba(200,225,255,0.8)', level: 60 } },
        { text: 'La <strong>neutralizare completă</strong> soluția devine incoloră: toată baza a reacționat. Paharul e ușor cald — reacția e <strong>exotermă</strong>.', fx: { liquid: 'rgba(200,225,255,0.22)', glow: 'rgba(255,190,120,0.25)' } }
      ]
    },
    {
      id: 'zinc-acid',
      title: '💨 Zinc + acid clorhidric',
      type: 'substituție',
      equation: 'Zn + 2HCl → ZnCl₂ + H₂↑',
      safety: ['🥽 Ochelari', '🔥 Fără flacără lângă vas'],
      lesson: 'Metalele active scot hidrogenul din acizi. Hidrogenul „pocnește" la apropierea unui chibrit — testul clasic de recunoaștere.',
      steps: [
        { text: 'Punem în pahar <strong>acid clorhidric diluat</strong> — o soluție incoloră.', fx: { liquid: 'rgba(200,225,255,0.25)', level: 46 } },
        { text: 'Adăugăm câteva <strong>granule de zinc</strong>. Le vezi cum se scufundă.', fx: { grains: '#9aa7b8', sediment: { color: 'rgba(150,160,175,0.8)', h: 10 } } },
        { text: 'Imediat apar <strong>bule de hidrogen</strong> pe suprafața zincului — efervescență!', fx: { bubbles: 2 } },
        { text: 'Reacția continuă până se consumă zincul sau acidul. Gazul captat „pocnește" la flacără: e <strong>H₂</strong>.', fx: { bubbles: 3, glow: 'rgba(56,225,255,0.18)' } }
      ]
    },
    {
      id: 'precipitat-albastru',
      title: '💙 Precipitatul albastru Cu(OH)₂',
      type: 'dublă substituție',
      equation: 'CuSO₄ + 2NaOH → Cu(OH)₂↓ + Na₂SO₄',
      safety: ['🥽 Ochelari', '🧤 Mănuși'],
      lesson: 'Două soluții limpezi pot forma un solid insolubil — precipitatul. Săgeata ↓ arată că produsul „cade" din soluție.',
      steps: [
        { text: 'În pahar: soluție de <strong>sulfat de cupru (CuSO₄)</strong> — albastrul ei e de neconfundat.', fx: { liquid: 'rgba(40,140,255,0.55)', level: 46 } },
        { text: 'Turnăm soluție incoloră de <strong>hidroxid de sodiu (NaOH)</strong>…', fx: { pour: 'rgba(200,225,255,0.8)', level: 58 } },
        { text: 'Instant se formează un <strong>precipitat gelatinos albastru-deschis</strong>: hidroxidul de cupru.', fx: { liquid: 'rgba(90,170,255,0.40)', sediment: { color: 'rgba(96,168,255,0.85)', h: 34 } } },
        { text: 'Lăsat în repaus, precipitatul se depune la fund. Deasupra rămâne soluția de Na₂SO₄.', fx: { liquid: 'rgba(170,205,255,0.22)', sediment: { color: 'rgba(96,168,255,0.9)', h: 44 } } }
      ]
    },
    {
      id: 'calcar-acid',
      title: '🫧 Calcar + acid → CO₂',
      type: 'dublă substituție',
      equation: 'CaCO₃ + 2HCl → CaCl₂ + H₂O + CO₂↑',
      safety: ['🥽 Ochelari'],
      lesson: 'Carbonații fac efervescență cu acizii — așa recunoști calcarul. CO₂-ul degajat tulbură apa de var: testul de recunoaștere al dioxidului de carbon.',
      steps: [
        { text: 'Punem în pahar <strong>acid clorhidric diluat</strong>.', fx: { liquid: 'rgba(200,225,255,0.25)', level: 40 } },
        { text: 'Adăugăm bucățele de <strong>cretă / calcar (CaCO₃)</strong>.', fx: { grains: '#e8e4da', sediment: { color: 'rgba(230,226,215,0.9)', h: 14 } } },
        { text: '<strong>Efervescență puternică!</strong> Se degajă dioxid de carbon.', fx: { bubbles: 3 } },
        { text: 'Dirijat printr-un tub în <strong>apă de var</strong>, gazul o tulbură (se formează CaCO₃ fin) — dovada că e CO₂.', fx: { bubbles: 1, glow: 'rgba(230,230,240,0.15)' } }
      ]
    },
    {
      id: 'fier-cupru',
      title: '🔩 Cuiul de fier în CuSO₄',
      type: 'substituție',
      equation: 'Fe + CuSO₄ → FeSO₄ + Cu↓',
      safety: ['🧤 Mănuși'],
      lesson: 'Fierul e mai activ decât cuprul, așa că îl „scoate" din sare. Seria activității metalelor prezice cine pe cine înlocuiește.',
      steps: [
        { text: 'Soluție albastră de <strong>sulfat de cupru</strong> în pahar.', fx: { liquid: 'rgba(40,140,255,0.55)', level: 50 } },
        { text: 'Scufundăm un <strong>cui de fier</strong> curat.', fx: { grains: '#8d949e' } },
        { text: 'Pe cui apare un <strong>strat roșcat-arămiu</strong>: cupru metalic depus!', fx: { sediment: { color: 'rgba(205,115,70,0.9)', h: 12 } } },
        { text: 'Albastrul soluției <strong>pălește spre verzui</strong>: ionii Cu²⁺ dispar, apar ionii Fe²⁺.', fx: { liquid: 'rgba(110,190,170,0.35)', sediment: { color: 'rgba(205,115,70,0.9)', h: 16 } } }
      ]
    },
    {
      id: 'apa-oxigenata',
      title: '⚡ Descompunerea apei oxigenate',
      type: 'descompunere',
      equation: '2H₂O₂ →(MnO₂) 2H₂O + O₂↑',
      safety: ['🥽 Ochelari'],
      lesson: 'Un catalizator (MnO₂) grăbește reacția fără să se consume. Oxigenul reaprinde un băț cu scânteie — testul lui de recunoaștere.',
      steps: [
        { text: '<strong>Apă oxigenată (H₂O₂)</strong> în pahar — pare apă obișnuită.', fx: { liquid: 'rgba(210,230,255,0.28)', level: 46 } },
        { text: 'Presărăm puțin <strong>dioxid de mangan (MnO₂)</strong> — pulbere neagră, catalizator.', fx: { grains: '#2d2f36', sediment: { color: 'rgba(45,47,54,0.9)', h: 8 } } },
        { text: '<strong>Spumare intensă!</strong> Se degajă oxigen rapid.', fx: { bubbles: 3, liquid: 'rgba(225,240,255,0.4)' } },
        { text: 'Un băț cu scânteie apropiat de gura paharului <strong>se reaprinde</strong>: gazul e O₂. MnO₂ rămâne neschimbat.', fx: { bubbles: 2, glow: 'rgba(255,200,110,0.3)' } }
      ]
    },
    {
      id: 'flacara',
      title: '🎆 Testul flăcării',
      type: 'analiză',
      equation: 'Na → galben · K → violet · Cu → verde-albăstrui',
      safety: ['🥽 Ochelari', '🔥 Flacără — doar cu profesorul'],
      lesson: 'Fiecare metal colorează flacăra în culoarea lui caracteristică — electronii excitați emit lumină specifică. Așa funcționează și artificiile!',
      steps: [
        { text: 'Aprindem <strong>becul de gaz</strong>. Flacăra curată e aproape incoloră-albăstruie.', fx: { flame: 'rgba(120,170,255,0.85)', level: 0 } },
        { text: 'Atingem flacăra cu o sârmă înmuiată în sare de <strong>sodiu (Na)</strong>: flacăra devine <strong>galben intens</strong>.', fx: { flame: 'rgba(255,200,40,0.95)', glow: 'rgba(255,200,60,0.3)' } },
        { text: 'Cu sare de <strong>potasiu (K)</strong>: flacără <strong>violet-liliachie</strong>.', fx: { flame: 'rgba(190,130,255,0.9)', glow: 'rgba(190,130,255,0.28)' } },
        { text: 'Cu sare de <strong>cupru (Cu)</strong>: flacără <strong>verde-albăstruie</strong> spectaculoasă.', fx: { flame: 'rgba(60,240,190,0.9)', glow: 'rgba(60,240,190,0.3)' } }
      ]
    },
    {
      id: 'magneziu',
      title: '✨ Arderea magneziului',
      type: 'combinare',
      equation: '2Mg + O₂ → 2MgO',
      safety: ['🥽 NU privi direct lumina!', '🔥 Doar demonstrativ, cu profesorul'],
      lesson: 'Combinarea directă a unui metal cu oxigenul. Lumina orbitoare a magneziului se folosea la primele blitzuri foto.',
      steps: [
        { text: 'O <strong>panglică de magneziu</strong> argintie, ținută cu cleștele deasupra flăcării.', fx: { flame: 'rgba(120,170,255,0.8)', level: 0 } },
        { text: 'Magneziul se aprinde și arde cu o <strong>lumină albă orbitoare</strong>!', fx: { flame: 'rgba(255,255,255,1)', glow: 'rgba(255,255,255,0.6)' } },
        { text: 'Se degajă <strong>fum alb</strong>: particule fine de oxid de magneziu.', fx: { smoke: true, flame: 'rgba(255,255,255,0.9)', glow: 'rgba(255,255,255,0.35)' } },
        { text: 'Rămâne o <strong>pulbere albă, sfărâmicioasă</strong>: MgO — complet diferită de metalul lucios inițial.', fx: { flame: null, glow: null, sediment: { color: 'rgba(240,240,245,0.9)', h: 10 } } }
      ]
    }
  ];

  var ELEMENTS_GAME = [
    { s: 'H', n: 'Hidrogen', clues: ['Cel mai ușor element din univers', 'Arde cu o mică explozie („pocnitură")', 'Intră în compoziția apei'] },
    { s: 'O', n: 'Oxigen', clues: ['Îl respiri chiar acum', 'Reaprinde un băț cu scânteie', 'Circa 21% din aer'] },
    { s: 'C', n: 'Carbon', clues: ['Scheletul chimiei organice', 'Există și ca diamant, și ca grafit', 'În creionul tău'] },
    { s: 'N', n: 'Azot', clues: ['Circa 78% din aer', 'Gaz inert la temperatura camerei', 'În îngrășăminte'] },
    { s: 'Na', n: 'Sodiu', clues: ['Metal moale, se taie cu cuțitul', 'Reacționează violent cu apa', 'Colorează flacăra în galben'] },
    { s: 'Cl', n: 'Clor', clues: ['Gaz galben-verzui toxic', 'Dezinfectează apa piscinelor', 'Împreună cu sodiul → sarea de bucătărie'] },
    { s: 'Fe', n: 'Fier', clues: ['Ruginește în aer umed', 'E atras de magnet', 'În hemoglobina din sânge'] },
    { s: 'Cu', n: 'Cupru', clues: ['Metal roșcat-arămiu', 'Excelent conductor electric', 'Colorează flacăra verde-albăstrui'] },
    { s: 'Au', n: 'Aur', clues: ['Nu ruginește niciodată', 'Simbolul vine din latinescul „aurum"', 'Metal galben prețios'] },
    { s: 'Ag', n: 'Argint', clues: ['Cel mai bun conductor electric', 'Se înnegrește în timp', 'În oglinzi și bijuterii'] },
    { s: 'He', n: 'Heliu', clues: ['Gaz nobil foarte ușor', 'Umflă baloanele care zboară', 'Îți subțiază vocea'] },
    { s: 'Ca', n: 'Calciu', clues: ['În oase și dinți', 'În calcar și cretă', 'Metal alcalino-pământos'] },
    { s: 'K', n: 'Potasiu', clues: ['Simbolul vine din „kalium"', 'Colorează flacăra violet', 'Mult în banane'] },
    { s: 'Mg', n: 'Magneziu', clues: ['Arde cu lumină albă orbitoare', 'În clorofilă', 'Metal ușor pentru aliaje'] },
    { s: 'Zn', n: 'Zinc', clues: ['Protejează fierul de rugină (zincare)', 'Cu acizii degajă hidrogen', 'În alamă, alături de cupru'] },
    { s: 'Al', n: 'Aluminiu', clues: ['Cel mai răspândit metal din scoarță', 'Folia de bucătărie', 'Ușor și rezistent la coroziune'] },
    { s: 'S', n: 'Sulf', clues: ['Nemetal galben', 'Miros de „ouă stricate" în compuși', 'La vulcani'] },
    { s: 'Ne', n: 'Neon', clues: ['Gaz nobil', 'Luminează roșu-portocaliu în tuburi', 'Numele înseamnă „nou"'] },
    { s: 'Hg', n: 'Mercur', clues: ['Singurul metal lichid la 25°C', 'În termometrele vechi', 'Simbol din „hydrargyrum"'] },
    { s: 'Pb', n: 'Plumb', clues: ['Metal foarte dens și moale', 'Simbol din „plumbum"', 'Oprește radiațiile'] },
    { s: 'I', n: 'Iod', clues: ['Solid violet-închis care sublimează', 'Tinctura pentru răni', 'Necesar glandei tiroide'] },
    { s: 'Si', n: 'Siliciu', clues: ['Inima cipurilor de calculator', 'În nisip (SiO₂)', 'Metaloid'] },
    { s: 'P', n: 'Fosfor', clues: ['Pe cutia de chibrituri', 'În oase și ADN', 'Forma albă luminează în întuneric'] },
    { s: 'F', n: 'Fluor', clues: ['Cel mai reactiv nemetal', 'În pasta de dinți', 'Halogen galben-pal'] },
    { s: 'U', n: 'Uraniu', clues: ['Combustibil pentru centrale nucleare', 'Element radioactiv natural', 'Numit după o planetă'] }
  ];

  // ================================================================ montare
  function el(tag, cls, html) {
    var node = document.createElement(tag);
    if (cls) node.className = cls;
    if (html !== undefined) node.innerHTML = html;
    return node;
  }

  function mountAnchor() {
    // după secțiunea mixerului de reacții (conține #react1); fallback: finalul containerului
    var mixer = document.getElementById('react1');
    var host = mixer;
    while (host && host.parentElement && !host.parentElement.classList.contains('container')) {
      host = host.parentElement;
    }
    return { after: host || null, container: document.querySelector('.container') };
  }

  // ================================================================ experimente
  function buildExperiments() {
    var section = el('section', 'gx-section');
    section.id = 'gxLab';
    section.innerHTML =
      '<div class="gx-head"><h2>🧪 Experimente ghidate</h2>' +
      '<span class="gx-badges"><span class="gx-badge">pas cu pas · animat</span></span></div>' +
      '<p class="gx-sub">Alege un experiment clasic de laborator și urmărește-l în siguranță, pas cu pas, cu observații și explicații.</p>' +
      '<div class="gx-picker" id="gxPicker"></div>' +
      '<div class="gx-stage-grid">' +
      '  <div class="gx-stage" id="gxStage">' +
      '    <span class="gx-stage-label">Simulare</span>' +
      '    <div class="gx-glow" id="gxGlow"></div>' +
      '    <div class="gx-beaker" id="gxBeaker">' +
      '      <div class="gx-liquid" id="gxLiquid"></div>' +
      '      <div class="gx-sediment" id="gxSediment"></div>' +
      '    </div>' +
      '    <div class="gx-flame" id="gxFlame"></div>' +
      '  </div>' +
      '  <div class="gx-side">' +
      '    <div class="gx-eq" id="gxEq"></div>' +
      '    <div class="gx-badges" id="gxBadges"></div>' +
      '    <div class="gx-narration" id="gxNarration"></div>' +
      '    <div class="gx-progress" id="gxProgress"></div>' +
      '    <div class="gx-controls">' +
      '      <button type="button" class="gx-btn primary" id="gxNext">Pasul următor →</button>' +
      '      <button type="button" class="gx-btn" id="gxReset">↺ De la început</button>' +
      '    </div>' +
      '    <div class="gx-narration" id="gxLesson" style="display:none"></div>' +
      '  </div>' +
      '</div>';

    var anchor = mountAnchor();
    if (anchor.after) anchor.after.insertAdjacentElement('afterend', section);
    else if (anchor.container) anchor.container.appendChild(section);
    else return;

    var refs = {
      picker: section.querySelector('#gxPicker'),
      stage: section.querySelector('#gxStage'),
      beaker: section.querySelector('#gxBeaker'),
      liquid: section.querySelector('#gxLiquid'),
      sediment: section.querySelector('#gxSediment'),
      flame: section.querySelector('#gxFlame'),
      glow: section.querySelector('#gxGlow'),
      eq: section.querySelector('#gxEq'),
      badges: section.querySelector('#gxBadges'),
      narration: section.querySelector('#gxNarration'),
      progress: section.querySelector('#gxProgress'),
      next: section.querySelector('#gxNext'),
      reset: section.querySelector('#gxReset'),
      lesson: section.querySelector('#gxLesson')
    };

    var current = EXPERIMENTS[0];
    var stepIdx = -1;
    var bubbleTimer = null;

    function stopBubbles() {
      if (bubbleTimer) { clearInterval(bubbleTimer); bubbleTimer = null; }
    }

    function spawnBubbles(intensity) {
      stopBubbles();
      if (!intensity) return;
      bubbleTimer = setInterval(function () {
        for (var i = 0; i < intensity; i++) {
          var b = el('div', 'gx-bubble');
          b.style.left = 8 + Math.random() * 82 + '%';
          b.style.width = b.style.height = 4 + Math.random() * 7 + 'px';
          b.style.animationDuration = 1 + Math.random() * 1.2 + 's';
          refs.liquid.appendChild(b);
          (function (node) { setTimeout(function () { node.remove(); }, 2400); })(b);
        }
      }, 260);
    }

    function dropGrains(color) {
      for (var i = 0; i < 7; i++) {
        (function (k) {
          setTimeout(function () {
            var g = el('div', 'gx-grain');
            g.style.background = color;
            g.style.left = 22 + Math.random() * 56 + '%';
            refs.beaker.appendChild(g);
            setTimeout(function () { g.remove(); }, 1500);
          }, k * 130);
        })(i);
      }
    }

    function puffSmoke() {
      for (var i = 0; i < 5; i++) {
        (function (k) {
          setTimeout(function () {
            var s = el('div', 'gx-smoke');
            s.style.left = 40 + Math.random() * 20 + '%';
            refs.stage.appendChild(s);
            setTimeout(function () { s.remove(); }, 1900);
          }, k * 300);
        })(i);
      }
    }

    function resetVisual() {
      stopBubbles();
      refs.liquid.style.background = 'rgba(160,210,255,0.18)';
      refs.liquid.style.height = '46%';
      refs.sediment.style.height = '0';
      refs.flame.style.opacity = '0';
      refs.glow.style.opacity = '0';
    }

    function applyFx(fx) {
      if (!fx) return;
      if (fx.liquid) refs.liquid.style.background = fx.liquid;
      if (fx.level !== undefined) refs.liquid.style.height = fx.level + '%';
      if (fx.bubbles !== undefined) spawnBubbles(fx.bubbles);
      if (fx.grains) dropGrains(fx.grains);
      if (fx.sediment) {
        refs.sediment.style.background = fx.sediment.color;
        refs.sediment.style.height = fx.sediment.h + 'px';
      }
      if ('flame' in fx) {
        if (fx.flame) {
          refs.flame.style.background = 'radial-gradient(circle at 50% 80%, ' + fx.flame + ', transparent 72%)';
          refs.flame.style.opacity = '1';
        } else {
          refs.flame.style.opacity = '0';
        }
      }
      if ('glow' in fx) {
        if (fx.glow) {
          refs.glow.style.background = 'radial-gradient(circle at 50% 60%, ' + fx.glow + ', transparent 70%)';
          refs.glow.style.opacity = '1';
        } else {
          refs.glow.style.opacity = '0';
        }
      }
      if (fx.smoke) puffSmoke();
    }

    function renderProgress() {
      refs.progress.innerHTML = '';
      current.steps.forEach(function (_, i) {
        var dot = el('i');
        if (i <= stepIdx) dot.className = 'done';
        refs.progress.appendChild(dot);
      });
    }

    function loadExperiment(exp) {
      current = exp;
      stepIdx = -1;
      resetVisual();
      refs.eq.textContent = exp.equation;
      refs.badges.innerHTML =
        '<span class="gx-badge type">' + exp.type + '</span>' +
        exp.safety.map(function (s) { return '<span class="gx-badge safety">' + s + '</span>'; }).join('');
      refs.narration.innerHTML = 'Apasă <strong>„Pasul următor"</strong> ca să pornești experimentul.';
      refs.lesson.style.display = 'none';
      refs.next.disabled = false;
      renderProgress();
      Array.prototype.forEach.call(refs.picker.children, function (chip) {
        chip.classList.toggle('active', chip.dataset.exp === exp.id);
      });
    }

    function nextStep() {
      if (stepIdx >= current.steps.length - 1) return;
      stepIdx++;
      var step = current.steps[stepIdx];
      refs.narration.innerHTML = '<strong>Pasul ' + (stepIdx + 1) + ':</strong> ' + step.text;
      applyFx(step.fx);
      renderProgress();
      if (stepIdx === current.steps.length - 1) {
        refs.next.disabled = true;
        refs.lesson.style.display = '';
        refs.lesson.innerHTML = '<strong>Ce am învățat:</strong> ' + current.lesson;
      }
    }

    EXPERIMENTS.forEach(function (exp) {
      var chip = el('button', 'gx-chip', exp.title);
      chip.type = 'button';
      chip.dataset.exp = exp.id;
      chip.addEventListener('click', function () { loadExperiment(exp); });
      refs.picker.appendChild(chip);
    });
    refs.next.addEventListener('click', nextStep);
    refs.reset.addEventListener('click', function () { loadExperiment(current); });
    loadExperiment(EXPERIMENTS[0]);

    return section;
  }

  // ================================================================ joc elemente
  function buildElementGame(afterNode) {
    var section = el('section', 'gx-section');
    section.id = 'egGame';
    section.innerHTML =
      '<div class="gx-head"><h2>🔮 Ghicește elementul</h2>' +
      '<span class="gx-badges"><span class="gx-badge">indicii progresive</span></span></div>' +
      '<p class="gx-sub">Primești până la 3 indicii. Cu cât ghicești mai repede, cu atât primești mai multe puncte: 30 → 20 → 10.</p>' +
      '<div class="eg-grid">' +
      '  <div class="eg-score-row">Scor: <b id="egScore">0</b> · Serie: <b id="egStreak">0</b> · Record: <b id="egBest">0</b></div>' +
      '  <div class="eg-clues" id="egClues"></div>' +
      '  <div class="eg-options" id="egOptions"></div>' +
      '  <p class="eg-feedback" id="egFeedback"></p>' +
      '  <div class="gx-controls">' +
      '    <button type="button" class="gx-btn" id="egHint">💡 Încă un indiciu</button>' +
      '    <button type="button" class="gx-btn primary" id="egNext">Elementul următor →</button>' +
      '  </div>' +
      '</div>';
    afterNode.insertAdjacentElement('afterend', section);

    var refs = {
      clues: section.querySelector('#egClues'),
      options: section.querySelector('#egOptions'),
      feedback: section.querySelector('#egFeedback'),
      hint: section.querySelector('#egHint'),
      next: section.querySelector('#egNext'),
      score: section.querySelector('#egScore'),
      streak: section.querySelector('#egStreak'),
      best: section.querySelector('#egBest')
    };
    var state = { score: 0, streak: 0, cluesShown: 1, answer: null, locked: false };
    var BEST_KEY = 'chimieAcademy.elementGameBest';
    try { refs.best.textContent = localStorage.getItem(BEST_KEY) || '0'; } catch (e) {}

    function rand(n) { return Math.floor(Math.random() * n); }
    function shuffle(a) {
      for (var i = a.length - 1; i > 0; i--) {
        var j = rand(i + 1), t = a[i]; a[i] = a[j]; a[j] = t;
      }
      return a;
    }

    function newRound() {
      state.answer = ELEMENTS_GAME[rand(ELEMENTS_GAME.length)];
      state.cluesShown = 1;
      state.locked = false;
      refs.feedback.textContent = '';
      refs.feedback.className = 'eg-feedback';
      refs.hint.disabled = false;
      renderClues();
      var opts = [state.answer];
      while (opts.length < 4) {
        var cand = ELEMENTS_GAME[rand(ELEMENTS_GAME.length)];
        if (opts.indexOf(cand) === -1) opts.push(cand);
      }
      shuffle(opts);
      refs.options.innerHTML = '';
      opts.forEach(function (o) {
        var b = el('button', 'eg-option', o.n + ' (' + o.s + ')');
        b.type = 'button';
        b.addEventListener('click', function () { answer(b, o); });
        refs.options.appendChild(b);
      });
    }

    function renderClues() {
      refs.clues.innerHTML = '';
      for (var i = 0; i < state.cluesShown; i++) {
        refs.clues.appendChild(el('div', 'eg-clue', '🔍 ' + state.answer.clues[i]));
      }
      refs.hint.disabled = state.cluesShown >= 3;
    }

    function answer(btn, opt) {
      if (state.locked) return;
      state.locked = true;
      var good = opt === state.answer;
      Array.prototype.forEach.call(refs.options.children, function (b) {
        b.disabled = true;
        if (b.textContent.indexOf('(' + state.answer.s + ')') !== -1) b.classList.add('good');
        else if (b === btn) b.classList.add('bad');
      });
      if (good) {
        var pts = [30, 20, 10][state.cluesShown - 1];
        state.score += pts;
        state.streak++;
        refs.feedback.textContent = '✅ Corect! +' + pts + ' puncte — ' + state.answer.n + ' (' + state.answer.s + ').';
        refs.feedback.className = 'eg-feedback good';
      } else {
        state.streak = 0;
        refs.feedback.textContent = '❌ Era ' + state.answer.n + ' (' + state.answer.s + ').';
        refs.feedback.className = 'eg-feedback bad';
      }
      refs.score.textContent = state.score;
      refs.streak.textContent = state.streak;
      try {
        var best = +(localStorage.getItem(BEST_KEY) || 0);
        if (state.score > best) {
          localStorage.setItem(BEST_KEY, String(state.score));
          best = state.score;
        }
        refs.best.textContent = best;
      } catch (e) {}
    }

    refs.hint.addEventListener('click', function () {
      if (state.locked || state.cluesShown >= 3) return;
      state.cluesShown++;
      renderClues();
    });
    refs.next.addEventListener('click', newRound);
    newRound();
    return section;
  }

  // ================================================================ start
  function init() {
    var expSection = buildExperiments();
    if (expSection) buildElementGame(expSection);

    // adaug linkuri în navigarea rapidă existentă, dacă e prezentă
    var nav = document.querySelector('.lab-quick-nav');
    if (nav && expSection) {
      ['gxLab:🧪 Experimente', 'egGame:🔮 Joc elemente'].forEach(function (pair) {
        var parts = pair.split(':');
        var a = el('a', 'lab-quick-link', parts[1]);
        a.href = '#' + parts[0];
        nav.appendChild(a);
      });
    }
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
