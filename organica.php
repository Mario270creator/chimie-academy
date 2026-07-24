<?php
require __DIR__ . '/core.php';
page_header('Chimie organică', 'organica');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Modul nou · Hidrocarburi</div>
      <h1>Chimie organică: vezi structurile, construiește catene, antrenează-te.</h1>
      <p class="lead">Alege o hidrocarbură și site-ul îi desenează structura. Apoi construiește propriile catene și testează-te în arena cu întrebări generate la infinit.</p>
    </div>

    <!-- ================= GENERATOR ================= -->
    <section class="panel">
      <div class="panel-head">
        <h2>⚛️ Generator de hidrocarburi</h2>
        <span class="tiny-muted">Alcani · alchene · alchine · arene</span>
      </div>
      <div class="org-controls">
        <label>
          <span>Seria</span>
          <select id="genType">
            <option value="alcan">Alcani (C−C)</option>
            <option value="alchena">Alchene (C=C)</option>
            <option value="alchina">Alchine (C≡C)</option>
            <option value="arena">Arene (aromatice)</option>
          </select>
        </label>
        <label id="genCarbonsWrap">
          <span>Atomi de carbon: <strong id="genCarbonsOut">4</strong></span>
          <input type="range" id="genCarbons" min="1" max="10" value="4">
        </label>
        <label id="genPositionWrap" style="display:none">
          <span>Poziția legăturii multiple</span>
          <select id="genPosition"><option value="1">poziția 1</option></select>
        </label>
        <label id="genAreneWrap" style="display:none">
          <span>Arena</span>
          <select id="genArene">
            <option value="benzen">Benzen</option>
            <option value="toluen">Metilbenzen (toluen)</option>
          </select>
        </label>
        <div class="org-mode">
          <button class="btn primary small" type="button" data-gen-mode="structural">Structură completă</button>
          <button class="btn ghost small" type="button" data-gen-mode="skeletal">Zigzag (schelet)</button>
        </div>
      </div>

      <div class="org-result-grid">
        <div class="org-facts">
          <div class="org-fact"><span>Denumire</span><strong id="genName">butan</strong></div>
          <div class="org-fact"><span>Formulă moleculară</span><strong id="genFormula">C₄H₁₀</strong></div>
          <div class="org-fact"><span>Serie · formulă generală</span><strong id="genGeneral">CₙH₂ₙ₊₂</strong></div>
          <div class="org-fact"><span>Formulă restrânsă</span><strong id="genSemi">CH₃−CH₂−CH₂−CH₃</strong></div>
          <p class="tiny-muted" id="genFacts"></p>
        </div>
        <div class="molecule-stage" id="genStage"></div>
        <div class="molecule-stage" id="genStage2Wrap" style="display:none">
          <div id="genStage2"></div>
          <p class="tiny-muted" style="text-align:center;margin:4px 0 0">Reprezentarea modernă: cerc = electroni delocalizați</p>
        </div>
      </div>
    </section>

    <!-- ================= CONSTRUCTOR ================= -->
    <section class="panel">
      <div class="panel-head">
        <h2>🔧 Constructor de catene</h2>
        <span class="tiny-muted">Apasă pe o legătură ca să o faci simplă → dublă → triplă</span>
      </div>
      <div class="inline-actions">
        <button class="btn primary small" type="button" id="buildAdd">+ Adaugă carbon</button>
        <button class="btn ghost small" type="button" id="buildRemove">− Scoate carbon</button>
        <button class="btn ghost small" type="button" id="buildReset">Reset (butan)</button>
      </div>
      <div class="molecule-stage" id="buildStage"></div>
      <div class="bond-row" id="buildBonds"></div>
      <p class="tiny-muted" id="buildMsg"></p>
      <div class="org-facts">
        <div class="org-fact"><span>Denumire</span><strong id="buildName">butan</strong></div>
        <div class="org-fact"><span>Formulă moleculară</span><strong id="buildFormula">C₄H₁₀</strong></div>
        <div class="org-fact"><span>Serie</span><strong id="buildSeries">alcan</strong></div>
        <div class="org-fact"><span>Formulă restrânsă</span><strong id="buildSemi">CH₃−CH₂−CH₂−CH₃</strong></div>
      </div>
      <p class="small-note">Regulă respectată automat: fiecare carbon are cel mult 4 legături. Hidrogenii se completează singuri.</p>
    </section>

    <!-- ================= ARENA ================= -->
    <section class="panel" id="arenaPanel">
      <div class="panel-head">
        <h2>🏟️ Arena de antrenament</h2>
        <span class="tiny-muted">Întrebări generate automat, la infinit</span>
      </div>
      <div class="arena-stats">
        <div class="stat-tile"><strong id="arenaScore">0</strong><span>Puncte sesiune</span></div>
        <div class="stat-tile"><strong id="arenaStreak">0</strong><span>Serie de răspunsuri</span></div>
        <div class="stat-tile"><strong id="arenaBest">0</strong><span>Record personal</span></div>
      </div>
      <div class="arena-cats">
        <label class="checkbox-inline"><input type="checkbox" data-arena-cat="formula" checked> Nume → formulă</label>
        <label class="checkbox-inline"><input type="checkbox" data-arena-cat="nume" checked> Formulă → nume</label>
        <label class="checkbox-inline"><input type="checkbox" data-arena-cat="hidrogen" checked> Câți atomi de H</label>
        <label class="checkbox-inline"><input type="checkbox" data-arena-cat="serie" checked> Formule generale</label>
        <label class="checkbox-inline"><input type="checkbox" data-arena-cat="structura" checked> Structuri desenate</label>
      </div>
      <h3 class="arena-question" id="arenaQuestion">…</h3>
      <div class="molecule-stage" id="arenaStage" style="display:none"></div>
      <div class="arena-options" id="arenaOptions"></div>
      <p class="arena-feedback" id="arenaFeedback"></p>
      <div class="inline-actions">
        <button class="btn primary" type="button" id="arenaNext">Întrebarea următoare →</button>
      </div>
      <p class="small-note">Bonus de serie: fiecare răspuns corect consecutiv aduce puncte în plus. Recordul se salvează în browserul tău.</p>
    </section>

    <!-- ================= FIȘĂ RAPIDĂ ================= -->
    <section class="panel">
      <div class="panel-head"><h2>📌 Fișă rapidă</h2></div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead><tr><th>n</th><th>Prefix</th><th>Alcan CₙH₂ₙ₊₂</th><th>Alchenă CₙH₂ₙ</th><th>Alchină CₙH₂ₙ₋₂</th></tr></thead>
          <tbody>
            <tr><td>1</td><td>met-</td><td>metan · CH₄</td><td>—</td><td>—</td></tr>
            <tr><td>2</td><td>et-</td><td>etan · C₂H₆</td><td>etenă · C₂H₄</td><td>etină · C₂H₂</td></tr>
            <tr><td>3</td><td>prop-</td><td>propan · C₃H₈</td><td>propenă · C₃H₆</td><td>propină · C₃H₄</td></tr>
            <tr><td>4</td><td>but-</td><td>butan · C₄H₁₀</td><td>butenă · C₄H₈</td><td>butină · C₄H₆</td></tr>
            <tr><td>5</td><td>pent-</td><td>pentan · C₅H₁₂</td><td>pentenă · C₅H₁₀</td><td>pentină · C₅H₈</td></tr>
            <tr><td>6</td><td>hex-</td><td>hexan · C₆H₁₄</td><td>hexenă · C₆H₁₂</td><td>hexină · C₆H₁₀</td></tr>
            <tr><td>7</td><td>hept-</td><td>heptan · C₇H₁₆</td><td>heptenă · C₇H₁₄</td><td>heptină · C₇H₁₂</td></tr>
            <tr><td>8</td><td>oct-</td><td>octan · C₈H₁₈</td><td>octenă · C₈H₁₆</td><td>octină · C₈H₁₄</td></tr>
            <tr><td>9</td><td>non-</td><td>nonan · C₉H₂₀</td><td>nonenă · C₉H₁₈</td><td>nonină · C₉H₁₆</td></tr>
            <tr><td>10</td><td>dec-</td><td>decan · C₁₀H₂₂</td><td>decenă · C₁₀H₂₀</td><td>decină · C₁₀H₁₈</td></tr>
          </tbody>
        </table>
      </div>
      <p class="small-note">Arene: seria CₙH₂ₙ₋₆ începe cu benzenul (C₆H₆), urmat de metilbenzen/toluen (C₇H₈). Lecțiile complete sunt în clasa „Chimie Organică · Extra”.</p>
    </section>
  </div>
</section>
<script src="organica.js"></script>
<?php page_footer(); ?>
