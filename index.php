<?php
require __DIR__ . '/core.php';

$counts = [
    'users' => q_scalar('SELECT COUNT(*) FROM users'),
    'classes' => q_scalar('SELECT COUNT(*) FROM classes'),
    'lessons' => q_scalar('SELECT COUNT(*) FROM lessons'),
    'quizzes' => q_scalar('SELECT COUNT(*) FROM quizzes'),
];
$topPlayers = leaderboard_rows(5);

page_header('Acasă', 'home');
?>
<section class="hero">
  <div class="container hero-grid">
    <div class="hero-copy">
      <div class="eyebrow">Platformă educațională + laborator interactiv</div>
      <h1>Chimie, dar cum n-ai <span class="grad">văzut-o</span> vreodată.</h1>
      <p class="hero-text">
        Lecții cu formule scrise corect (H₂O, Ca²⁺, →), teste cu punctaj automat, laborator interactiv
        și un modul de organică ce desenează hidrocarburi și te antrenează la nesfârșit — totul într-un
        singur loc, direct din browser.
      </p>
      <div class="hero-actions">
        <a class="btn primary large" href="demo.php">Intră instant în demo</a>
        <a class="btn ghost large" href="laborator.html">Deschide laboratorul</a>
        <a class="btn ghost large" href="organica.php">Chimie organică</a>
        <a class="btn ghost large" href="timer.php">Timer prezentare</a>
      </div>
      <p class="small-note">Vrei contul tău? <a href="register.php">Creează cont</a> sau <a href="login.php">intră în cont</a>.</p>

      <div class="kpi-row">
        <div class="kpi"><strong><?php echo $counts['classes']; ?></strong><span>clase active</span></div>
        <div class="kpi"><strong><?php echo $counts['lessons']; ?></strong><span>lecții disponibile</span></div>
        <div class="kpi"><strong><?php echo $counts['quizzes']; ?></strong><span>teste disponibile</span></div>
        <div class="kpi"><strong><?php echo $counts['users']; ?></strong><span>utilizatori</span></div>
      </div>
    </div>

    <div class="hero-card">
      <div class="monitor">
        <div class="monitor-bar">
          <span class="dot red"></span>
          <span class="dot yellow"></span>
          <span class="dot green"></span>
          <span class="monitor-label">Top clasament</span>
        </div>
        <div class="monitor-body">
          <div class="glass-panel">
            <div class="mini-title">Progres · clasa VII A</div>
            <div class="progress-line"><span style="width:68%"></span></div>
            <div class="quest-list-mini">
              <?php foreach (array_slice($topPlayers, 0, 3) as $p): ?>
              <div><?php echo $p['icon']; ?> <?php echo e($p['full_name']); ?> · <?php echo $p['xp']; ?> puncte</div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="glass-panel">
            <div class="mini-title">Editor chimic · live</div>
            <div class="quest-list-mini">
              <div>2H₂ + O₂ → 2H₂O</div>
              <div>CaCO₃ → CaO + CO₂↑</div>
              <div>N₂ + 3H₂ ⇌ 2NH₃</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="features">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Funcții</div>
      <h2>Tot ce ai nevoie pentru ora de chimie</h2>
    </div>
    <div class="stat-row">
      <div class="stat-tile"><strong>📘</strong><span>Lecții cu formule chimice formatate corect, organizate pe clase</span></div>
      <div class="stat-tile"><strong>🧪</strong><span>Teste cu punctaj automat, explicații și istoric de încercări</span></div>
      <div class="stat-tile"><strong>⚗️</strong><span>Laborator interactiv cu simulări, fără cont necesar</span></div>
      <div class="stat-tile"><strong>🏅</strong><span>Clasament cu puncte și niveluri pentru motivarea elevilor</span></div>
      <div class="stat-tile"><strong>⚙️</strong><span>Panou de administrare pentru conturi, parole și conținut</span></div>
      <div class="stat-tile"><strong>⏱️</strong><span>Cronometru de prezentare pentru susțineri și concursuri</span></div>
      <div class="stat-tile"><strong>⚛️</strong><span>Modul de chimie organică: generator de structuri, constructor de catene și arenă de antrenament</span></div>
    </div>
  </div>
</section>
<?php page_footer(); ?>
