<?php
require __DIR__ . '/core.php';
$user = require_login();
$classIds = allowed_class_ids($user);
$in = $classIds ? implode(',', $classIds) : '0';

$quizId = (int) ($_GET['id'] ?? 0);
$quiz = q_one(
    "SELECT q.*, c.name AS class_name, c.section AS class_section
     FROM quizzes q JOIN classes c ON c.id = q.class_id
     WHERE q.id = ? AND q.class_id IN ($in)",
    [$quizId]
);
if ($quiz === null) {
    flash('danger', 'Testul nu este disponibil pentru contul tău.');
    redirect('teste.php');
}
$questions = json_decode($quiz['questions_json'], true) ?: [];

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $answers = [];
    $correctCount = 0;
    foreach ($questions as $i => $q) {
        $picked = isset($_POST['q_' . $i]) ? (int) $_POST['q_' . $i] : -1;
        $answers[(string) $i] = $picked;
        if ($picked === (int) $q['correct']) {
            $correctCount++;
        }
    }
    $total = count($questions);
    $scorePercent = $total > 0 ? round($correctCount / $total * 100, 1) : 0.0;
    q_exec('INSERT INTO attempts(quiz_id, user_id, score_percent, correct_count, total_count, answers_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$quizId, (int) $user['id'], $scorePercent, $correctCount, $total, json_encode($answers), now_iso()]);
    $result = [
        'answers' => $answers,
        'score_percent' => $scorePercent,
        'correct_count' => $correctCount,
        'total_count' => $total,
        'xp_reward' => (int) round(((float) $quiz['xp']) * $scorePercent / 100.0),
    ];
}

$attempts = q_all('SELECT * FROM attempts WHERE quiz_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 10',
    [$quizId, (int) $user['id']]);

page_header($quiz['title'], 'teste');
?>
<section class="section page-top">
  <div class="container narrow">
    <div class="section-head">
      <div class="eyebrow">Test</div>
      <h1><?php echo e($quiz['title']); ?></h1>
      <p class="lead"><?php echo e($quiz['description']); ?></p>
    </div>

    <section class="panel">
      <div class="quest-top">
        <span class="pill"><?php echo e($quiz['class_name'] . ' ' . $quiz['class_section']); ?></span>
        <span class="pill accent"><?php echo (int) $quiz['xp']; ?> puncte max</span>
      </div>
      <form method="post" class="quiz-play-form" autocomplete="off">
        <?php echo csrf_field(); ?>
        <?php foreach ($questions as $qIdx => $q): ?>
        <article class="quiz-question" data-quiz-question>
          <h3><?php echo $qIdx + 1; ?>. <?php echo e($q['text']); ?></h3>
          <div class="quiz-options-list" role="radiogroup" aria-label="Întrebarea <?php echo $qIdx + 1; ?>">
            <?php foreach ($q['options'] as $optIdx => $option):
                $picked = $result !== null && ($result['answers'][(string) $qIdx] ?? -1) === $optIdx;
                $optId = 'quiz-' . $quizId . '-q-' . $qIdx . '-opt-' . $optIdx;
            ?>
            <label for="<?php echo $optId; ?>" class="option-card <?php echo $picked ? 'selected' : ''; ?>" data-option-card>
              <input id="<?php echo $optId; ?>" type="radio" name="q_<?php echo $qIdx; ?>" value="<?php echo $optIdx; ?>" data-quiz-option <?php echo $picked ? 'checked' : ''; ?>>
              <span><?php echo e($option); ?></span>
            </label>
            <?php endforeach; ?>
          </div>

          <?php if ($result !== null): $good = ($result['answers'][(string) $qIdx] ?? -1) === (int) $q['correct']; ?>
          <div class="quiz-feedback <?php echo $good ? 'good' : 'bad'; ?>">
            <strong>Răspuns corect:</strong> <?php echo e($q['options'][(int) $q['correct']]); ?><br>
            <span><?php echo e($q['explanation']); ?></span>
          </div>
          <?php endif; ?>
        </article>
        <?php endforeach; ?>

        <div class="form-actions">
          <button class="btn primary large" type="submit">Trimite răspunsurile</button>
          <a class="btn ghost large" href="teste.php">Înapoi la teste</a>
        </div>
      </form>

      <?php if ($result !== null): ?>
      <div class="result-banner">
        <strong><?php echo $result['score_percent']; ?>%</strong>
        <span><?php echo $result['correct_count']; ?> / <?php echo $result['total_count']; ?> răspunsuri corecte</span>
        <span>Puncte potențiale: <?php echo $result['xp_reward']; ?></span>
      </div>
      <?php endif; ?>
    </section>

    <section class="panel">
      <div class="panel-head"><h2>Istoric încercări</h2></div>
      <div class="leaderboard-table full">
        <?php if (!$attempts): ?>
          <div class="empty-state">Nu ai încercări salvate încă.</div>
        <?php endif; ?>
        <?php foreach ($attempts as $i => $item): ?>
        <div class="leader-row">
          <div class="leader-row-rank">#<?php echo $i + 1; ?></div>
          <div class="leader-row-name"><?php echo (int) $item['correct_count']; ?> / <?php echo (int) $item['total_count']; ?></div>
          <div class="leader-row-role"><?php echo e(str_replace('T', ' ', substr($item['created_at'], 0, 16))); ?></div>
          <div class="leader-row-score"><?php echo (float) $item['score_percent']; ?>%</div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</section>
<?php page_footer(); ?>
