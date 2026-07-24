<?php
require __DIR__ . '/core.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && $user['role'] === 'profesor') {
        $name = post('name');
        $section = post('section');
        $description = post('description');
        if (mb_strlen($name) < 3 || mb_strlen($section) < 1 || mb_strlen($description) < 12) {
            flash('warning', 'Completează corect numele, secțiunea și descrierea clasei.');
        } else {
            $code = make_class_code($name, $section);
            q_exec('INSERT INTO classes(name, section, description, code, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?)',
                [$name, $section, $description, $code, (int) $user['id'], now_iso()]);
            flash('success', 'Clasa a fost creată. Codul de acces este ' . $code . '.');
        }
    }

    if ($action === 'join' && $user['role'] === 'elev') {
        $code = strtoupper(post('code'));
        $found = q_one('SELECT * FROM classes WHERE code = ?', [$code]);
        if ($found === null) {
            flash('danger', 'Codul de clasă nu a fost găsit.');
        } else {
            $exists = q_one('SELECT id FROM enrollments WHERE class_id = ? AND user_id = ?', [(int) $found['id'], (int) $user['id']]);
            if ($exists !== null) {
                flash('warning', 'Ești deja înscris în această clasă.');
            } else {
                q_exec('INSERT INTO enrollments(class_id, user_id, joined_at) VALUES (?, ?, ?)', [(int) $found['id'], (int) $user['id'], now_iso()]);
                flash('success', 'Ai intrat în ' . $found['name'] . ' ' . $found['section'] . '.');
            }
        }
    }

    if ($action === 'announce' && $user['role'] === 'profesor') {
        $classId = (int) post('class_id');
        $title = post('title');
        $content = post('content');
        $owns = q_one('SELECT id FROM classes WHERE id = ? AND teacher_id = ?', [$classId, (int) $user['id']]);
        if ($owns === null) {
            flash('danger', 'Nu poți publica anunț în această clasă.');
        } elseif (mb_strlen($title) < 3 || mb_strlen($content) < 8) {
            flash('warning', 'Completează titlul și mesajul anunțului.');
        } else {
            q_exec('INSERT INTO announcements(class_id, teacher_id, title, content, created_at) VALUES (?, ?, ?, ?, ?)',
                [$classId, (int) $user['id'], $title, $content, now_iso()]);
            flash('success', 'Anunț publicat.');
        }
    }
    redirect('clase.php');
}

$classes = classes_for_user($user);
$classIds = allowed_class_ids($user);
$in = $classIds ? implode(',', $classIds) : '0';
$announcements = q_all(
    "SELECT a.*, c.name AS class_name, c.section AS class_section
     FROM announcements a JOIN classes c ON c.id = a.class_id
     WHERE a.class_id IN ($in) ORDER BY a.created_at DESC LIMIT 12"
);

page_header('Clase', 'clase');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Clase și organizare</div>
      <h1>Clasele tale</h1>
    </div>

    <div class="grid-form two" style="align-items:start">
      <?php if ($user['role'] === 'profesor'): ?>
      <section class="panel">
        <div class="panel-head"><h2>Creează o clasă</h2></div>
        <form method="post" class="stack-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="create">
          <label><span>Nume clasă</span><input type="text" name="name" placeholder="Clasa IX" required></label>
          <label><span>Secțiune</span><input type="text" name="section" placeholder="A" required></label>
          <label><span>Descriere</span><textarea name="description" rows="4" placeholder="Ce învață elevii în această clasă?" required></textarea></label>
          <div class="form-actions"><button class="btn primary" type="submit">Creează clasa</button></div>
        </form>
      </section>

      <section class="panel">
        <div class="panel-head"><h2>Publică un anunț</h2></div>
        <form method="post" class="stack-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="announce">
          <label>
            <span>Clasa</span>
            <select name="class_id">
              <?php foreach ($classes as $cls): ?>
              <option value="<?php echo (int) $cls['id']; ?>"><?php echo e($cls['name'] . ' ' . $cls['section']); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label><span>Titlu</span><input type="text" name="title" placeholder="Anunț nou" required></label>
          <label><span>Mesaj</span><textarea name="content" rows="4" placeholder="Scrie noutatea pentru clasă." required data-chem-editor></textarea></label>
          <div class="form-actions"><button class="btn primary" type="submit">Publică</button></div>
        </form>
      </section>
      <?php else: ?>
      <section class="panel">
        <div class="panel-head"><h2>Intră într-o clasă</h2></div>
        <form method="post" class="stack-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="join">
          <label><span>Cod de acces</span><input type="text" name="code" placeholder="CHIM7A" required></label>
          <div class="form-actions"><button class="btn primary" type="submit">Intră în clasă</button></div>
        </form>
      </section>
      <?php endif; ?>

      <section class="panel">
        <div class="panel-head"><h2>Clasele mele</h2></div>
        <?php if (!$classes): ?>
          <div class="empty-state">Nu ești în nicio clasă încă.</div>
        <?php endif; ?>
        <?php foreach ($classes as $cls): ?>
        <article style="padding:10px 0;border-bottom:1px solid var(--line)">
          <div class="mission-card-head">
            <h3 style="margin:0"><?php echo e($cls['name'] . ' ' . $cls['section']); ?></h3>
            <?php if ($user['role'] === 'profesor'): ?>
            <span class="pill accent">Cod: <?php echo e($cls['code']); ?></span>
            <?php endif; ?>
          </div>
          <p style="margin:6px 0 0"><?php echo e($cls['description']); ?></p>
        </article>
        <?php endforeach; ?>
      </section>
    </div>

    <section class="panel">
      <div class="panel-head"><h2>Anunțuri</h2></div>
      <?php if (!$announcements): ?>
        <div class="empty-state">Nu există anunțuri.</div>
      <?php endif; ?>
      <?php foreach ($announcements as $ann): ?>
      <article style="padding:10px 0;border-bottom:1px solid var(--line)">
        <span class="pill"><?php echo e($ann['class_name'] . ' ' . $ann['class_section']); ?></span>
        <h3 style="margin:8px 0 4px"><?php echo e($ann['title']); ?></h3>
        <p style="margin:0"><?php echo nl2br(e($ann['content'])); ?></p>
      </article>
      <?php endforeach; ?>
    </section>
  </div>
</section>
<?php page_footer(); ?>
