<?php
require __DIR__ . '/core.php';
$me = require_admin();

function pack_installed(string $code): bool
{
    return q_one('SELECT id FROM classes WHERE code = ?', [$code]) !== null;
}

function load_pack(string $file): ?array
{
    $path = __DIR__ . '/' . basename($file);
    if (!is_file($path)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pack = load_pack((string) post('pack'));
    if ($pack === null) {
        flash('danger', 'Pachetul nu a putut fi citit.');
        redirect('biblioteca.php');
    }
    if (pack_installed($pack['code'])) {
        flash('warning', 'Pachetul este deja instalat (clasa cu codul ' . $pack['code'] . ' există).');
        redirect('biblioteca.php');
    }

    $now = now_iso();
    q_exec('INSERT INTO classes(name, section, description, code, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?)',
        [$pack['class']['name'], $pack['class']['section'], $pack['class']['description'], $pack['code'], (int) $me['id'], $now]);
    $classId = (int) db()->lastInsertId();

    foreach ($pack['lessons'] as $lesson) {
        q_exec('INSERT INTO lessons(class_id, title, summary, content, xp, difficulty, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$classId, $lesson['title'], $lesson['summary'], $lesson['content'], (int) $lesson['xp'], $lesson['difficulty'], $now]);
    }
    foreach ($pack['quizzes'] as $quiz) {
        q_exec('INSERT INTO quizzes(class_id, title, description, xp, difficulty, questions_json, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$classId, $quiz['title'], $quiz['description'], (int) $quiz['xp'], $quiz['difficulty'],
             json_encode($quiz['questions'], JSON_UNESCAPED_UNICODE), $now]);
    }
    // înscriem automat toți elevii existenți, ca să vadă imediat materialul
    foreach (q_all("SELECT id FROM users WHERE role = 'elev'") as $student) {
        q_exec('INSERT IGNORE INTO enrollments(class_id, user_id, joined_at) VALUES (?, ?, ?)',
            [$classId, (int) $student['id'], $now]);
    }
    flash('success', 'Pachetul „' . $pack['class']['name'] . '” a fost instalat: '
        . count($pack['lessons']) . ' lecții și ' . count($pack['quizzes']) . ' teste. Toți elevii au fost înscriși automat.');
    redirect('biblioteca.php');
}

$organica = load_pack('organica.json');

page_header('Biblioteca de pachete', 'admin');
?>
<section class="section page-top">
  <div class="container narrow">
    <div class="section-head">
      <div class="eyebrow">Administrare · Biblioteca</div>
      <h1>Pachete de lecții gata făcute</h1>
      <p class="lead">Instalezi cu un click o clasă completă, cu lecții și teste. Poți edita apoi orice din panoul Admin.</p>
    </div>

    <?php if ($organica !== null): $installed = pack_installed($organica['code']); ?>
    <section class="panel">
      <div class="panel-head">
        <h2>⚛️ <?php echo e($organica['class']['name']); ?></h2>
        <?php if ($installed): ?><span class="status good">Instalat</span><?php endif; ?>
      </div>
      <p><?php echo e($organica['class']['description']); ?></p>
      <ul style="margin:10px 0 14px;padding-left:20px">
        <?php foreach ($organica['lessons'] as $lesson): ?>
        <li><?php echo e($lesson['title']); ?> · <?php echo (int) $lesson['xp']; ?> puncte</li>
        <?php endforeach; ?>
        <?php foreach ($organica['quizzes'] as $quiz): ?>
        <li>🧪 <?php echo e($quiz['title']); ?> · <?php echo count($quiz['questions']); ?> întrebări</li>
        <?php endforeach; ?>
      </ul>
      <?php if ($installed): ?>
        <p class="small-note">Clasa cu codul <code><?php echo e($organica['code']); ?></code> există deja — pachetul e activ. Îl găsești în Lecții și Teste.</p>
      <?php else: ?>
        <form method="post">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="pack" value="organica.json">
          <button class="btn primary" type="submit">Instalează pachetul acum</button>
        </form>
        <p class="small-note">Se creează clasa „<?php echo e($organica['class']['name'] . ' ' . $organica['class']['section']); ?>” (profesor: contul tău), iar toți elevii existenți sunt înscriși automat.</p>
      <?php endif; ?>
    </section>
    <?php else: ?>
    <section class="panel"><div class="empty-state">Fișierul organica.json lipsește de pe server.</div></section>
    <?php endif; ?>

    <div class="inline-actions">
      <a class="btn ghost" href="admin.php">← Înapoi la Admin</a>
      <a class="btn ghost" href="organica.php">Deschide modulul interactiv Organică</a>
    </div>
  </div>
</section>
<?php page_footer(); ?>
