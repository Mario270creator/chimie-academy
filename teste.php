<?php
require __DIR__ . '/core.php';
$user = require_login();
$classes = classes_for_user($user);
$classIds = allowed_class_ids($user);
$in = $classIds ? implode(',', $classIds) : '0';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'profesor') {
    check_csrf();
    $classId = (int) post('class_id');
    $title = post('title');
    $description = post('description');
    $difficulty = post('difficulty', 'Standard');
    $xp = max(20, min(300, (int) post('xp', '120')));
    $owns = q_one('SELECT id FROM classes WHERE id = ? AND teacher_id = ?', [$classId, (int) $user['id']]);
    if ($owns === null) {
        flash('danger', 'Testul poate fi creat doar în clasele tale.');
    } elseif (mb_strlen($title) < 4) {
        flash('warning', 'Titlul testului este prea scurt.');
    } else {
        try {
            $questions = parse_questions_payload((string) ($_POST['questions_payload'] ?? ''));
            q_exec('INSERT INTO quizzes(class_id, title, description, xp, difficulty, questions_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$classId, $title, $description !== '' ? $description : 'Test nou', $xp, $difficulty, json_encode($questions, JSON_UNESCAPED_UNICODE), now_iso()]);
            flash('success', 'Test creat.');
        } catch (InvalidArgumentException $ex) {
            flash('danger', $ex->getMessage());
        }
    }
    redirect('teste.php');
}

$quizzes = q_all(
    "SELECT q.*, c.name AS class_name, c.section AS class_section,
        (SELECT COUNT(DISTINCT a.user_id) FROM attempts a WHERE a.quiz_id = q.id) AS participant_count,
        (SELECT COUNT(*) FROM attempts a WHERE a.quiz_id = q.id AND a.user_id = ?) AS attempt_count,
        (SELECT COALESCE(MAX(a.score_percent), 0) FROM attempts a WHERE a.quiz_id = q.id AND a.user_id = ?) AS best_score
     FROM quizzes q JOIN classes c ON c.id = q.class_id
     WHERE q.class_id IN ($in)
     ORDER BY q.created_at DESC",
    [(int) $user['id'], (int) $user['id']]
);

page_header('Teste', 'teste');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Teste și verificare</div>
      <h1>Teste cu scor, istoric și puncte.</h1>
    </div>

    <?php if ($user['role'] === 'profesor'): ?>
    <section class="panel">
      <div class="panel-head">
        <h2>Creează un test nou</h2>
        <span class="tiny-muted">Constructor cu întrebări dinamice + editor chimic</span>
      </div>
      <form method="post" class="stack-form" id="quizBuilderForm">
        <?php echo csrf_field(); ?>
        <div class="grid-form two">
          <label>
            <span>Clasa</span>
            <select name="class_id" required>
              <?php foreach ($classes as $cls): ?>
              <option value="<?php echo (int) $cls['id']; ?>"><?php echo e($cls['name'] . ' ' . $cls['section']); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label><span>Puncte</span><input type="number" name="xp" min="20" max="300" value="120"></label>
          <label class="full"><span>Titlu</span><input type="text" name="title" placeholder="Test · ..." required data-chem-editor></label>
          <label class="full"><span>Descriere</span><textarea name="description" rows="3" placeholder="Explică ce testează quiz-ul." required data-chem-editor></textarea></label>
          <label>
            <span>Dificultate</span>
            <select name="difficulty">
              <option>Standard</option>
              <option>Avansat</option>
              <option>Recapitulare finală</option>
            </select>
          </label>
        </div>

        <input type="hidden" name="questions_payload" id="questionsPayload">

        <div class="builder-head">
          <h3>Întrebări</h3>
          <button class="btn ghost small" type="button" id="addQuestionBtn">+ Adaugă întrebare</button>
        </div>
        <div id="questionBuilder" class="builder-list"></div>

        <div class="form-actions"><button class="btn primary" type="submit">Publică testul</button></div>
      </form>
    </section>
    <?php endif; ?>

    <section class="arena-grid">
      <?php if (!$quizzes): ?>
        <div class="empty-state">Niciun test activ.</div>
      <?php endif; ?>
      <?php foreach ($quizzes as $quiz): ?>
      <article class="arena-card big">
        <div class="arena-topline">
          <span class="pill"><?php echo e($quiz['class_name'] . ' ' . $quiz['class_section']); ?></span>
          <span class="pill accent"><?php echo (int) $quiz['xp']; ?> puncte</span>
        </div>
        <h3><?php echo e($quiz['title']); ?></h3>
        <p><?php echo e($quiz['description']); ?></p>
        <div class="arena-meta">
          <span><?php echo e($quiz['difficulty']); ?></span>
          <?php if ($user['role'] === 'elev'): ?>
            <span>Scor maxim: <?php echo round((float) $quiz['best_score']); ?>%</span>
            <span>Încercări: <?php echo (int) $quiz['attempt_count']; ?></span>
          <?php else: ?>
            <span>Participanți: <?php echo (int) $quiz['participant_count']; ?></span>
          <?php endif; ?>
        </div>
        <a class="btn primary" href="test.php?id=<?php echo (int) $quiz['id']; ?>">Deschide testul</a>
      </article>
      <?php endforeach; ?>
    </section>
  </div>
</section>
<?php page_footer(); ?>
