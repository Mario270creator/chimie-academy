<?php
require __DIR__ . '/core.php';
$me = require_admin();

$lessonId = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
$lesson = q_one(
    'SELECT l.*, c.name AS class_name, c.section AS class_section
     FROM lessons l JOIN classes c ON c.id = l.class_id WHERE l.id = ?',
    [$lessonId]
);
if ($lesson === null) {
    flash('danger', 'Lecția nu există.');
    redirect('admin.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $title = post('title');
    $summary = post('summary');
    $content = post('content');
    $difficulty = post('difficulty', $lesson['difficulty']);
    $classId = (int) post('class_id', (string) $lesson['class_id']);
    $xp = max(10, min(200, (int) post('xp', (string) $lesson['xp'])));
    if (mb_strlen($title) < 4 || mb_strlen($summary) < 8 || mb_strlen($content) < 20) {
        flash('warning', 'Titlul, rezumatul și conținutul sunt prea scurte.');
    } elseif (q_one('SELECT id FROM classes WHERE id = ?', [$classId]) === null) {
        flash('warning', 'Alege o clasă validă.');
    } else {
        q_exec('UPDATE lessons SET class_id = ?, title = ?, summary = ?, content = ?, xp = ?, difficulty = ? WHERE id = ?',
            [$classId, $title, $summary, $content, $xp, $difficulty, $lessonId]);
        flash('success', 'Lecția a fost salvată.');
        redirect('admin.php');
    }
    redirect('admin_lectie.php?id=' . $lessonId);
}

$classes = q_all('SELECT * FROM classes ORDER BY name, section');

page_header('Editează lecția', 'admin');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Administrare · Lecții</div>
      <h1>Editează: <?php echo e($lesson['title']); ?></h1>
      <p class="tiny-muted">Selectezi cifrele și apeși X₂ pentru indici, sau apeși ✨ ca să transformi automat tot textul (H2O → H₂O, -&gt; → →, Ca^2+ → Ca²⁺).</p>
    </div>

    <section class="panel">
      <form method="post" class="grid-form two">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo $lessonId; ?>">
        <label>
          <span>Clasa</span>
          <select name="class_id" required>
            <?php foreach ($classes as $cls): ?>
            <option value="<?php echo (int) $cls['id']; ?>" <?php echo (int) $cls['id'] === (int) $lesson['class_id'] ? 'selected' : ''; ?>><?php echo e($cls['name'] . ' ' . $cls['section']); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label><span>Puncte</span><input type="number" name="xp" value="<?php echo (int) $lesson['xp']; ?>" min="10" max="200"></label>
        <label class="full"><span>Titlu</span><input type="text" name="title" value="<?php echo e($lesson['title']); ?>" required minlength="4" data-chem-editor></label>
        <label class="full"><span>Rezumat</span><textarea name="summary" rows="3" required minlength="8" data-chem-editor><?php echo e($lesson['summary']); ?></textarea></label>
        <label class="full"><span>Conținut</span><textarea name="content" rows="14" required minlength="20" data-chem-editor><?php echo e($lesson['content']); ?></textarea></label>
        <label>
          <span>Dificultate</span>
          <select name="difficulty">
            <?php foreach (['Introductiv', 'Mediu', 'Avansat', 'Recapitulare'] as $level): ?>
            <option <?php echo $lesson['difficulty'] === $level ? 'selected' : ''; ?>><?php echo $level; ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <div class="form-actions">
          <button class="btn primary" type="submit">Salvează lecția</button>
          <a class="btn ghost" href="admin.php">Renunță</a>
        </div>
      </form>
    </section>
  </div>
</section>
<?php page_footer(); ?>
