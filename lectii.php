<?php
require __DIR__ . '/core.php';
$user = require_login();
$classes = classes_for_user($user);
$classIds = allowed_class_ids($user);
$in = $classIds ? implode(',', $classIds) : '0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && $user['role'] === 'profesor') {
        $classId = (int) post('class_id');
        $title = post('title');
        $summary = post('summary');
        $content = post('content');
        $difficulty = post('difficulty', 'Mediu');
        $xp = max(10, min(200, (int) post('xp', '40')));
        $owns = q_one('SELECT id FROM classes WHERE id = ? AND teacher_id = ?', [$classId, (int) $user['id']]);
        if ($owns === null) {
            flash('danger', 'Nu poți adăuga lecții într-o clasă care nu îți aparține.');
        } elseif (mb_strlen($title) < 4 || mb_strlen($summary) < 8 || mb_strlen($content) < 20) {
            flash('warning', 'Lecția este prea scurtă. Detaliază conținutul.');
        } else {
            q_exec('INSERT INTO lessons(class_id, title, summary, content, xp, difficulty, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$classId, $title, $summary, $content, $xp, $difficulty, now_iso()]);
            flash('success', 'Lecția a fost adăugată.');
        }
    }

    if ($action === 'complete' && $user['role'] === 'elev') {
        $lessonId = (int) post('lesson_id');
        $lesson = q_one("SELECT * FROM lessons WHERE id = ? AND class_id IN ($in)", [$lessonId]);
        if ($lesson === null) {
            flash('danger', 'Lecția nu este disponibilă pentru contul tău.');
        } else {
            $already = q_one('SELECT id FROM completions WHERE lesson_id = ? AND user_id = ?', [$lessonId, (int) $user['id']]);
            if ($already !== null) {
                flash('warning', 'Această lecție era deja marcată ca parcursă.');
            } else {
                q_exec('INSERT INTO completions(lesson_id, user_id, completed_at) VALUES (?, ?, ?)', [$lessonId, (int) $user['id'], now_iso()]);
                flash('success', 'Lecție parcursă. +' . $lesson['xp'] . ' puncte');
            }
        }
    }
    redirect('lectii.php');
}

$lessons = q_all(
    "SELECT l.*, c.name AS class_name, c.section AS class_section,
        (SELECT COUNT(*) FROM completions x WHERE x.lesson_id = l.id) AS completion_count,
        (SELECT COUNT(*) FROM completions x WHERE x.lesson_id = l.id AND x.user_id = ?) AS is_completed
     FROM lessons l JOIN classes c ON c.id = l.class_id
     WHERE l.class_id IN ($in)
     ORDER BY l.created_at DESC",
    [(int) $user['id']]
);

page_header('Lecții', 'lectii');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Lecții și activități</div>
      <h1>Lecțiile sunt ușor de administrat și de parcurs.</h1>
    </div>

    <?php if ($user['role'] === 'profesor'): ?>
    <section class="panel">
      <div class="panel-head">
        <h2>Adaugă o lecție nouă</h2>
        <span class="tiny-muted">Cu editor chimic: scrie H2O și apasă ✨</span>
      </div>
      <form method="post" class="grid-form two">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="create">
        <label>
          <span>Clasa</span>
          <select name="class_id" required>
            <?php foreach ($classes as $cls): ?>
            <option value="<?php echo (int) $cls['id']; ?>"><?php echo e($cls['name'] . ' ' . $cls['section']); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label><span>Puncte</span><input type="number" name="xp" value="50" min="10" max="200"></label>
        <label class="full"><span>Titlu</span><input type="text" name="title" placeholder="Lecția 3 · ..." required data-chem-editor></label>
        <label class="full"><span>Rezumat</span><textarea name="summary" rows="3" placeholder="Scurtă descriere a lecției" required data-chem-editor></textarea></label>
        <label class="full"><span>Conținut</span><textarea name="content" rows="8" placeholder="Explică lecția. Ex: 2H2 + O2 -> 2H2O apoi apasă ✨" required data-chem-editor></textarea></label>
        <label>
          <span>Dificultate</span>
          <select name="difficulty">
            <option>Introductiv</option>
            <option selected>Mediu</option>
            <option>Avansat</option>
            <option>Recapitulare</option>
          </select>
        </label>
        <div class="form-actions"><button class="btn primary" type="submit">Adaugă lecția</button></div>
      </form>
    </section>
    <?php endif; ?>

    <div class="lesson-search">
      <input type="search" id="lessonSearch" placeholder="🔍 Caută în lecții (titlu, rezumat, clasă)…" autocomplete="off">
    </div>

    <section class="mission-columns">
      <?php if (!$lessons): ?>
        <div class="empty-state">Nu există lecții încă.</div>
      <?php endif; ?>
      <?php foreach ($lessons as $lesson): ?>
      <article class="mission-card">
        <div class="mission-card-head">
          <span class="pill"><?php echo e($lesson['class_name'] . ' ' . $lesson['class_section']); ?></span>
          <span class="pill accent"><?php echo (int) $lesson['xp']; ?> puncte</span>
        </div>
        <h3><?php echo e($lesson['title']); ?></h3>
        <p class="mission-summary"><?php echo e($lesson['summary']); ?></p>
        <details>
          <summary>Deschide conținutul lecției</summary>
          <div class="details-body"><?php echo nl2br(e($lesson['content'])); ?></div>
        </details>
        <div class="mission-foot">
          <span class="tiny-muted"><?php echo e($lesson['difficulty']); ?></span>
          <?php if ($user['role'] === 'elev'): ?>
            <?php if ((int) $lesson['is_completed'] > 0): ?>
              <span class="status good">Finalizată</span>
            <?php else: ?>
              <form method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="complete">
                <input type="hidden" name="lesson_id" value="<?php echo (int) $lesson['id']; ?>">
                <button class="btn primary small" type="submit">Marchează ca parcursă</button>
              </form>
            <?php endif; ?>
          <?php else: ?>
            <span class="status"><?php echo (int) $lesson['completion_count']; ?> completări</span>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </section>
  </div>
</section>
<script>
(function () {
  var input = document.getElementById('lessonSearch');
  if (!input) return;
  var cards = document.querySelectorAll('.mission-card');
  input.addEventListener('input', function () {
    var term = input.value.trim().toLowerCase();
    cards.forEach(function (card) {
      card.style.display = card.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
    });
  });
})();
</script>
<?php page_footer(); ?>
