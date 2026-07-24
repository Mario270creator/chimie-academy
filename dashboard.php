<?php
require __DIR__ . '/core.php';
$user = require_login();

// Schimbarea parolei proprii
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    check_csrf();
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (!verify_password($current, $user['password_hash'])) {
        flash('danger', 'Parola actuală nu este corectă.');
    } elseif (strlen($new) < 4) {
        flash('warning', 'Parola nouă trebuie să aibă cel puțin 4 caractere.');
    } elseif ($new !== $confirm) {
        flash('warning', 'Parolele noi nu coincid.');
    } else {
        q_exec('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($new), (int) $user['id']]);
        flash('success', 'Parola a fost schimbată cu succes.');
    }
    redirect('dashboard.php');
}

$level = compute_level((int) $user['id']);
$classIds = allowed_class_ids($user);
$classCount = count($classIds);
$in = $classIds ? implode(',', $classIds) : '0';

$lessonCount = q_scalar("SELECT COUNT(*) FROM lessons WHERE class_id IN ($in)");
$quizCount = q_scalar("SELECT COUNT(*) FROM quizzes WHERE class_id IN ($in)");
if ($user['role'] === 'elev') {
    $completed = q_scalar('SELECT COUNT(*) FROM completions WHERE user_id = ?', [(int) $user['id']]);
    $avgRow = q_one('SELECT AVG(score_percent) AS a FROM attempts WHERE user_id = ?', [(int) $user['id']]);
} else {
    $completed = q_scalar("SELECT COUNT(*) FROM completions c JOIN lessons l ON l.id = c.lesson_id WHERE l.class_id IN ($in)");
    $avgRow = q_one("SELECT AVG(score_percent) AS a FROM attempts a JOIN quizzes q ON q.id = a.quiz_id WHERE q.class_id IN ($in)");
}
$avgScore = ($avgRow && $avgRow['a'] !== null) ? round((float) $avgRow['a'], 1) : 0;

$announcements = q_all(
    "SELECT a.*, c.name AS class_name, c.section AS class_section
     FROM announcements a JOIN classes c ON c.id = a.class_id
     WHERE a.class_id IN ($in) ORDER BY a.created_at DESC LIMIT 4"
);
$board = leaderboard_rows(8);

page_header('Panou principal', 'dashboard');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Panou principal</div>
      <h1>Salut, <?php echo e($user['full_name']); ?>! <?php echo $level['icon']; ?></h1>
      <p class="tiny-muted">Nivel: <?php echo e($level['title']); ?> · <?php echo $level['xp']; ?> puncte
        <?php if ($level['remaining'] > 0): ?> · încă <?php echo $level['remaining']; ?> puncte până la „<?php echo e($level['next_title']); ?>”<?php endif; ?>
      </p>
    </div>

    <div class="stat-row">
      <div class="stat-tile"><strong><?php echo $classCount; ?></strong><span>Clase</span></div>
      <div class="stat-tile"><strong><?php echo $lessonCount; ?></strong><span>Lecții disponibile</span></div>
      <div class="stat-tile"><strong><?php echo $quizCount; ?></strong><span>Teste disponibile</span></div>
      <div class="stat-tile"><strong><?php echo $completed; ?></strong><span>Lecții parcurse</span></div>
      <div class="stat-tile"><strong><?php echo $avgScore; ?>%</strong><span>Scor mediu la teste</span></div>
      <div class="stat-tile"><strong><?php echo $level['xp']; ?></strong><span>Puncte totale</span></div>
    </div>

    <section class="panel">
      <div class="panel-head">
        <h2>Anunțuri recente</h2>
        <a href="clase.php">Toate clasele</a>
      </div>
      <?php if ($announcements): ?>
        <?php foreach ($announcements as $ann): ?>
        <article style="padding:10px 0;border-bottom:1px solid var(--line)">
          <span class="pill"><?php echo e($ann['class_name'] . ' ' . $ann['class_section']); ?></span>
          <h3 style="margin:8px 0 4px"><?php echo e($ann['title']); ?></h3>
          <p style="margin:0"><?php echo nl2br(e($ann['content'])); ?></p>
        </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">Nu există încă anunțuri.</div>
      <?php endif; ?>
    </section>

    <section class="panel leaderboard-panel">
      <div class="panel-head">
        <h2>Top clasament</h2>
        <a href="clasament.php">Clasament complet</a>
      </div>
      <div class="leaderboard-table">
        <?php foreach ($board as $i => $item): ?>
        <div class="leader-row">
          <div class="leader-row-rank">#<?php echo $i + 1; ?></div>
          <div class="leader-row-name"><?php echo $item['icon']; ?> <?php echo e($item['full_name']); ?></div>
          <div class="leader-row-role"><?php echo e(ucfirst($item['role'])); ?></div>
          <div class="leader-row-score"><?php echo $item['xp']; ?> puncte</div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="panel">
      <div class="panel-head">
        <h2>Contul meu</h2>
        <span class="tiny-muted"><?php echo e($user['username']); ?></span>
      </div>
      <details>
        <summary style="cursor:pointer">🔐 Schimbă parola</summary>
        <form method="post" class="grid-form two" style="margin-top:14px">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="password">
          <label><span>Parola actuală</span><input type="password" name="current_password" required autocomplete="current-password"></label>
          <label><span>Parola nouă</span><input type="password" name="new_password" required minlength="4" autocomplete="new-password"></label>
          <label><span>Repetă parola nouă</span><input type="password" name="confirm_password" required minlength="4" autocomplete="new-password"></label>
          <div class="form-actions"><button class="btn primary" type="submit">Salvează parola</button></div>
        </form>
      </details>
    </section>
  </div>
</section>
<?php page_footer(); ?>
