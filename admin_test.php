<?php
require __DIR__ . '/core.php';
$me = require_admin();

$quizId = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$quiz = q_one(
    'SELECT q.*, c.name AS class_name, c.section AS class_section
     FROM quizzes q JOIN classes c ON c.id = q.class_id WHERE q.id = ?',
    [$quizId]
);
if ($quiz === null) {
    flash('danger', 'Testul nu există.');
    redirect('admin.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $title = post('title');
    $description = post('description');
    $difficulty = post('difficulty', $quiz['difficulty']);
    $classId = (int) post('class_id', (string) $quiz['class_id']);
    $xp = max(20, min(300, (int) post('xp', (string) $quiz['xp'])));
    try {
        $questions = parse_questions_payload((string) ($_POST['questions_payload'] ?? ''));
        if (mb_strlen($title) < 4) {
            flash('warning', 'Titlul testului este prea scurt.');
        } elseif (q_one('SELECT id FROM classes WHERE id = ?', [$classId]) === null) {
            flash('warning', 'Alege o clasă validă.');
        } else {
            q_exec('UPDATE quizzes SET class_id = ?, title = ?, description = ?, xp = ?, difficulty = ?, questions_json = ? WHERE id = ?',
                [$classId, $title, $description !== '' ? $description : 'Test actualizat', $xp, $difficulty,
                 json_encode($questions, JSON_UNESCAPED_UNICODE), $quizId]);
            flash('success', 'Testul a fost salvat.');
            redirect('admin.php');
        }
    } catch (InvalidArgumentException $ex) {
        flash('danger', $ex->getMessage());
    }
    redirect('admin_test.php?id=' . $quizId);
}

$classes = q_all('SELECT * FROM classes ORDER BY name, section');
$questions = json_decode($quiz['questions_json'], true) ?: [];

page_header('Editează testul', 'admin');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Administrare · Teste</div>
      <h1>Editează: <?php echo e($quiz['title']); ?></h1>
      <p class="tiny-muted">Fiecare câmp are bara de indici chimici: selectezi cifrele → X₂, sau ✨ pentru formatare automată.</p>
    </div>

    <section class="panel">
      <form method="post" class="stack-form" id="quizBuilderForm">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $quizId; ?>">
        <div class="grid-form two">
          <label>
            <span>Clasa</span>
            <select name="class_id" required>
              <?php foreach ($classes as $cls): ?>
              <option value="<?php echo (int) $cls['id']; ?>" <?php echo (int) $cls['id'] === (int) $quiz['class_id'] ? 'selected' : ''; ?>><?php echo e($cls['name'] . ' ' . $cls['section']); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label><span>Puncte</span><input type="number" name="xp" min="20" max="300" value="<?php echo (int) $quiz['xp']; ?>"></label>
          <label class="full"><span>Titlu</span><input type="text" name="title" value="<?php echo e($quiz['title']); ?>" required minlength="4" data-chem-editor></label>
          <label class="full"><span>Descriere</span><textarea name="description" rows="3" data-chem-editor><?php echo e($quiz['description']); ?></textarea></label>
          <label>
            <span>Dificultate</span>
            <select name="difficulty">
              <?php foreach (['Standard', 'Avansat', 'Recapitulare finală'] as $level): ?>
              <option <?php echo $quiz['difficulty'] === $level ? 'selected' : ''; ?>><?php echo $level; ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        </div>

        <input type="hidden" name="questions_payload" id="questionsPayload">

        <div class="builder-head">
          <h3>Întrebări</h3>
          <button class="btn ghost small" type="button" id="addQuestionBtn">+ Adaugă întrebare</button>
        </div>
        <div id="questionBuilder" class="builder-list" data-existing-questions='<?php echo e(json_encode($questions, JSON_UNESCAPED_UNICODE)); ?>'></div>

        <div class="form-actions">
          <button class="btn primary" type="submit">Salvează testul</button>
          <a class="btn ghost" href="admin.php">Renunță</a>
        </div>
      </form>
    </section>
  </div>
</section>
<script>
(function () {
  // Preîncarcă întrebările existente în constructorul din app.js.
  var builder = document.getElementById('questionBuilder');
  if (!builder) return;
  var existing = [];
  try { existing = JSON.parse(builder.dataset.existingQuestions || '[]'); } catch (e) {}
  var addBtn = document.getElementById('addQuestionBtn');
  builder.querySelectorAll('[data-question-item]').forEach(function (item) { item.remove(); });
  existing.forEach(function (q) {
    addBtn.click();
    var item = builder.querySelector('[data-question-item]:last-child');
    if (!item) return;
    item.querySelector('[data-q-text]').value = q.text || '';
    [0, 1, 2, 3].forEach(function (idx) {
      var input = item.querySelector('[data-q-option="' + idx + '"]');
      if (input) input.value = (q.options && q.options[idx]) || '';
    });
    item.querySelector('[data-q-correct]').value = String(q.correct != null ? q.correct : 0);
    item.querySelector('[data-q-explanation]').value = q.explanation || '';
  });
  if (!builder.querySelector('[data-question-item]')) addBtn.click();
})();
</script>
<?php page_footer(); ?>
