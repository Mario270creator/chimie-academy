<?php
require __DIR__ . '/core.php';
$me = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'user_create') {
        $fullName = post('full_name');
        $username = strtolower(post('username'));
        $password = (string) ($_POST['password'] ?? '');
        $role = post('role', 'elev') === 'profesor' ? 'profesor' : 'elev';
        $makeAdmin = ($role === 'profesor' && isset($_POST['is_admin'])) ? 1 : 0;
        if (mb_strlen($fullName) < 3 || mb_strlen($username) < 3 || strlen($password) < 4) {
            flash('warning', 'Completează nume (min. 3), utilizator (min. 3) și parolă (min. 4 caractere).');
        } elseif (q_one('SELECT id FROM users WHERE username = ?', [$username]) !== null) {
            flash('danger', 'Numele de utilizator există deja.');
        } else {
            q_exec("INSERT INTO users(full_name, username, password_hash, role, is_admin, bio, created_at) VALUES (?, ?, ?, ?, ?, '', ?)",
                [$fullName, $username, hash_password($password), $role, $makeAdmin, now_iso()]);
            flash('success', 'Contul „' . $username . '” a fost creat.');
        }
    }

    if ($action === 'user_update') {
        $userId = (int) post('user_id');
        $target = q_one('SELECT * FROM users WHERE id = ?', [$userId]);
        if ($target === null) {
            flash('danger', 'Utilizatorul nu există.');
        } else {
            $fullName = post('full_name');
            $username = strtolower(post('username'));
            $role = post('role', $target['role']) === 'profesor' ? 'profesor' : 'elev';
            $makeAdmin = ($role === 'profesor' && isset($_POST['is_admin'])) ? 1 : 0;
            $dupe = q_one('SELECT id FROM users WHERE username = ? AND id != ?', [$username, $userId]);
            $otherAdmins = q_scalar('SELECT COUNT(*) FROM users WHERE is_admin = 1 AND id != ?', [$userId]);
            if (mb_strlen($fullName) < 3 || mb_strlen($username) < 3) {
                flash('warning', 'Numele și utilizatorul trebuie să aibă cel puțin 3 caractere.');
            } elseif ($dupe !== null) {
                flash('danger', 'Numele de utilizator este deja folosit de alt cont.');
            } elseif ((int) $target['is_admin'] === 1 && $makeAdmin === 0 && $otherAdmins === 0) {
                flash('danger', 'Nu poți elimina drepturile ultimului administrator.');
            } else {
                q_exec('UPDATE users SET full_name = ?, username = ?, role = ?, is_admin = ? WHERE id = ?',
                    [$fullName, $username, $role, $makeAdmin, $userId]);
                if ($userId === (int) $me['id'] && $makeAdmin === 0) {
                    flash('warning', 'Ți-ai retras drepturile de administrator.');
                    redirect('dashboard.php');
                }
                flash('success', 'Contul a fost actualizat.');
            }
        }
    }

    if ($action === 'user_password') {
        $userId = (int) post('user_id');
        $target = q_one('SELECT * FROM users WHERE id = ?', [$userId]);
        $newPw = (string) ($_POST['new_password'] ?? '');
        if ($target === null) {
            flash('danger', 'Utilizatorul nu există.');
        } elseif (strlen($newPw) < 4) {
            flash('warning', 'Parola nouă trebuie să aibă cel puțin 4 caractere.');
        } else {
            q_exec('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($newPw), $userId]);
            flash('success', 'Parola pentru „' . $target['username'] . '” a fost resetată.');
        }
    }

    if ($action === 'user_delete') {
        $userId = (int) post('user_id');
        $target = q_one('SELECT * FROM users WHERE id = ?', [$userId]);
        $otherAdmins = q_scalar('SELECT COUNT(*) FROM users WHERE is_admin = 1 AND id != ?', [$userId]);
        if ($userId === (int) $me['id']) {
            flash('danger', 'Nu îți poți șterge propriul cont din panoul de administrare.');
        } elseif ($target === null) {
            flash('danger', 'Utilizatorul nu există.');
        } elseif ((int) $target['is_admin'] === 1 && $otherAdmins === 0) {
            flash('danger', 'Nu poți șterge ultimul administrator.');
        } else {
            q_exec('DELETE FROM users WHERE id = ?', [$userId]);
            flash('success', 'Contul „' . $target['username'] . '” a fost șters, împreună cu datele asociate.');
        }
    }

    if ($action === 'lesson_delete') {
        q_exec('DELETE FROM lessons WHERE id = ?', [(int) post('id')]);
        flash('success', 'Lecția a fost ștearsă.');
    }
    if ($action === 'quiz_delete') {
        q_exec('DELETE FROM quizzes WHERE id = ?', [(int) post('id')]);
        flash('success', 'Testul a fost șters, împreună cu încercările asociate.');
    }
    if ($action === 'class_delete') {
        q_exec('DELETE FROM classes WHERE id = ?', [(int) post('id')]);
        flash('success', 'Clasa a fost ștearsă, împreună cu lecțiile, testele și înscrierile ei.');
    }
    if ($action === 'announcement_delete') {
        q_exec('DELETE FROM announcements WHERE id = ?', [(int) post('id')]);
        flash('success', 'Anunțul a fost șters.');
    }
    redirect('admin.php');
}

$searchQuery = trim((string) ($_GET['q'] ?? ''));
if ($searchQuery !== '') {
    $users = q_all('SELECT * FROM users WHERE full_name LIKE ? OR username LIKE ? ORDER BY id',
        ['%' . $searchQuery . '%', '%' . $searchQuery . '%']);
} else {
    $users = q_all('SELECT * FROM users ORDER BY id');
}
foreach ($users as $i => $u) {
    $level = compute_level((int) $u['id']);
    $users[$i]['xp'] = $level['xp'];
    $users[$i]['level_title'] = $level['title'];
}

$overview = [
    'users' => q_scalar('SELECT COUNT(*) FROM users'),
    'teachers' => q_scalar("SELECT COUNT(*) FROM users WHERE role = 'profesor'"),
    'students' => q_scalar("SELECT COUNT(*) FROM users WHERE role = 'elev'"),
    'classes' => q_scalar('SELECT COUNT(*) FROM classes'),
    'lessons' => q_scalar('SELECT COUNT(*) FROM lessons'),
    'quizzes' => q_scalar('SELECT COUNT(*) FROM quizzes'),
    'attempts' => q_scalar('SELECT COUNT(*) FROM attempts'),
    'announcements' => q_scalar('SELECT COUNT(*) FROM announcements'),
];
$classes = q_all(
    'SELECT c.*, u.full_name AS teacher_name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.class_id = c.id) AS student_count
     FROM classes c JOIN users u ON u.id = c.teacher_id ORDER BY c.id'
);
$lessons = q_all(
    'SELECT l.*, c.name AS class_name, c.section AS class_section,
        (SELECT COUNT(*) FROM completions x WHERE x.lesson_id = l.id) AS completion_count
     FROM lessons l JOIN classes c ON c.id = l.class_id ORDER BY l.id DESC'
);
$quizzes = q_all(
    'SELECT q.*, c.name AS class_name, c.section AS class_section,
        (SELECT COUNT(*) FROM attempts a WHERE a.quiz_id = q.id) AS attempt_count
     FROM quizzes q JOIN classes c ON c.id = q.class_id ORDER BY q.id DESC'
);
$announcements = q_all(
    'SELECT a.*, c.name AS class_name, c.section AS class_section
     FROM announcements a JOIN classes c ON c.id = a.class_id ORDER BY a.id DESC LIMIT 30'
);
$recentAttempts = q_all(
    'SELECT a.*, u.full_name, q.title AS quiz_title
     FROM attempts a JOIN users u ON u.id = a.user_id JOIN quizzes q ON q.id = a.quiz_id
     ORDER BY a.created_at DESC LIMIT 12'
);

page_header('Administrare', 'admin');
?>
<section class="section page-top">
  <div class="container">
    <div class="section-head">
      <div class="eyebrow">Panou de administrare</div>
      <h1>Control complet: conturi, parole, lecții, teste și clase.</h1>
    </div>

    <div class="stat-row">
      <div class="stat-tile"><strong><?php echo $overview['users']; ?></strong><span>Conturi (<?php echo $overview['teachers']; ?> profesori · <?php echo $overview['students']; ?> elevi)</span></div>
      <div class="stat-tile"><strong><?php echo $overview['classes']; ?></strong><span>Clase</span></div>
      <div class="stat-tile"><strong><?php echo $overview['lessons']; ?></strong><span>Lecții</span></div>
      <div class="stat-tile"><strong><?php echo $overview['quizzes']; ?></strong><span>Teste</span></div>
      <div class="stat-tile"><strong><?php echo $overview['attempts']; ?></strong><span>Încercări la teste</span></div>
      <div class="stat-tile"><strong><?php echo $overview['announcements']; ?></strong><span>Anunțuri</span></div>
    </div>

    <div class="admin-tabs" data-admin-tabs>
      <button class="admin-tab active" type="button" data-tab-btn="users">👥 Conturi &amp; parole</button>
      <button class="admin-tab" type="button" data-tab-btn="lessons">📘 Lecții</button>
      <button class="admin-tab" type="button" data-tab-btn="quizzes">🧪 Teste</button>
      <button class="admin-tab" type="button" data-tab-btn="classes">🏫 Clase</button>
      <button class="admin-tab" type="button" data-tab-btn="announcements">📣 Anunțuri</button>
      <button class="admin-tab" type="button" data-tab-btn="activity">📊 Activitate</button>
    </div>

    <!-- ============ CONTURI ============ -->
    <div class="admin-section active" data-tab-panel="users">
      <section class="panel">
        <div class="panel-head">
          <h2>Creează un cont nou</h2>
          <span class="tiny-muted">Elev sau profesor</span>
        </div>
        <form method="post" class="grid-form two">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="action" value="user_create">
          <label><span>Nume complet</span><input type="text" name="full_name" required minlength="3"></label>
          <label><span>Utilizator</span><input type="text" name="username" required minlength="3" autocomplete="off"></label>
          <label><span>Parolă</span><input type="text" name="password" required minlength="4" autocomplete="off"></label>
          <label>
            <span>Rol</span>
            <select name="role">
              <option value="elev">Elev</option>
              <option value="profesor">Profesor</option>
            </select>
          </label>
          <label class="checkbox-inline"><input type="checkbox" name="is_admin"> Drepturi de administrator (doar profesori)</label>
          <div class="form-actions"><button class="btn primary" type="submit">Creează contul</button></div>
        </form>
      </section>

      <section class="panel">
        <div class="panel-head">
          <h2>Toate conturile</h2>
          <form method="get" action="admin.php" class="admin-inline-form">
            <input type="text" name="q" value="<?php echo e($searchQuery); ?>" placeholder="Caută nume sau utilizator…" style="padding:8px 12px;border-radius:10px;border:1px solid var(--line);background:var(--surface-strong);color:var(--text)">
            <button class="btn ghost small" type="submit">Caută</button>
            <?php if ($searchQuery !== ''): ?><a class="btn ghost small" href="admin.php">Resetează</a><?php endif; ?>
          </form>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>Cont</th><th>Rol</th><th>Puncte</th><th>Parolă nouă</th><th style="text-align:right">Acțiuni</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): $uid = (int) $u['id']; ?>
              <tr>
                <td>
                  <form method="post" id="userForm<?php echo $uid; ?>" class="admin-inline-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="user_update">
                    <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                    <input type="text" name="full_name" value="<?php echo e($u['full_name']); ?>" required minlength="3">
                    <input type="text" name="username" value="<?php echo e($u['username']); ?>" required minlength="3">
                  </form>
                </td>
                <td>
                  <select name="role" form="userForm<?php echo $uid; ?>">
                    <option value="elev" <?php echo $u['role'] === 'elev' ? 'selected' : ''; ?>>Elev</option>
                    <option value="profesor" <?php echo $u['role'] === 'profesor' ? 'selected' : ''; ?>>Profesor</option>
                  </select>
                  <label class="checkbox-inline" style="margin-top:6px">
                    <input type="checkbox" name="is_admin" form="userForm<?php echo $uid; ?>" <?php echo (int) $u['is_admin'] === 1 ? 'checked' : ''; ?>> Admin
                  </label>
                  <?php if ((int) $u['is_admin'] === 1): ?><span class="badge-admin">ADMIN</span><?php endif; ?>
                </td>
                <td><?php echo $u['xp']; ?> · <?php echo e($u['level_title']); ?></td>
                <td>
                  <form method="post" class="admin-inline-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="user_password">
                    <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                    <input type="text" name="new_password" placeholder="Parolă nouă" minlength="4" required autocomplete="off">
                    <button class="btn ghost small" type="submit">Resetează</button>
                  </form>
                </td>
                <td>
                  <div class="admin-row-actions">
                    <button class="btn primary small" type="submit" form="userForm<?php echo $uid; ?>">Salvează</button>
                    <?php if ($uid !== (int) $me['id']): ?>
                    <form method="post" onsubmit="return confirm('Ștergi contul <?php echo e($u['username']); ?> și toate datele lui?');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="user_delete">
                      <input type="hidden" name="user_id" value="<?php echo $uid; ?>">
                      <button class="btn danger small" type="submit">Șterge</button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- ============ LECȚII ============ -->
    <div class="admin-section" data-tab-panel="lessons">
      <section class="panel">
        <div class="panel-head">
          <h2>Toate lecțiile</h2>
          <div class="inline-actions">
            <a class="btn ghost small" href="lectii.php">+ Adaugă lecție nouă</a>
            <a class="btn primary small" href="biblioteca.php">📦 Pachete de lecții</a>
          </div>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead><tr><th>Titlu</th><th>Clasă</th><th>Puncte</th><th>Completări</th><th style="text-align:right">Acțiuni</th></tr></thead>
            <tbody>
              <?php if (!$lessons): ?><tr><td colspan="5">Nu există lecții.</td></tr><?php endif; ?>
              <?php foreach ($lessons as $l): ?>
              <tr>
                <td><?php echo e($l['title']); ?></td>
                <td><?php echo e($l['class_name'] . ' ' . $l['class_section']); ?></td>
                <td><?php echo (int) $l['xp']; ?></td>
                <td><?php echo (int) $l['completion_count']; ?></td>
                <td>
                  <div class="admin-row-actions">
                    <a class="btn primary small" href="admin_lectie.php?id=<?php echo (int) $l['id']; ?>">Editează</a>
                    <form method="post" onsubmit="return confirm('Ștergi lecția?');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="lesson_delete">
                      <input type="hidden" name="id" value="<?php echo (int) $l['id']; ?>">
                      <button class="btn danger small" type="submit">Șterge</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- ============ TESTE ============ -->
    <div class="admin-section" data-tab-panel="quizzes">
      <section class="panel">
        <div class="panel-head">
          <h2>Toate testele</h2>
          <a class="btn ghost small" href="teste.php">+ Creează test nou</a>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead><tr><th>Titlu</th><th>Clasă</th><th>Puncte</th><th>Încercări</th><th style="text-align:right">Acțiuni</th></tr></thead>
            <tbody>
              <?php if (!$quizzes): ?><tr><td colspan="5">Nu există teste.</td></tr><?php endif; ?>
              <?php foreach ($quizzes as $q): ?>
              <tr>
                <td><?php echo e($q['title']); ?></td>
                <td><?php echo e($q['class_name'] . ' ' . $q['class_section']); ?></td>
                <td><?php echo (int) $q['xp']; ?></td>
                <td><?php echo (int) $q['attempt_count']; ?></td>
                <td>
                  <div class="admin-row-actions">
                    <a class="btn primary small" href="admin_test.php?id=<?php echo (int) $q['id']; ?>">Editează</a>
                    <form method="post" onsubmit="return confirm('Ștergi testul și toate rezultatele lui?');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="quiz_delete">
                      <input type="hidden" name="id" value="<?php echo (int) $q['id']; ?>">
                      <button class="btn danger small" type="submit">Șterge</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- ============ CLASE ============ -->
    <div class="admin-section" data-tab-panel="classes">
      <section class="panel">
        <div class="panel-head">
          <h2>Toate clasele</h2>
          <a class="btn ghost small" href="clase.php">+ Creează clasă nouă</a>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead><tr><th>Clasă</th><th>Cod acces</th><th>Profesor</th><th>Elevi</th><th style="text-align:right">Acțiuni</th></tr></thead>
            <tbody>
              <?php if (!$classes): ?><tr><td colspan="5">Nu există clase.</td></tr><?php endif; ?>
              <?php foreach ($classes as $c): ?>
              <tr>
                <td><?php echo e($c['name'] . ' ' . $c['section']); ?></td>
                <td><code><?php echo e($c['code']); ?></code></td>
                <td><?php echo e($c['teacher_name']); ?></td>
                <td><?php echo (int) $c['student_count']; ?></td>
                <td>
                  <div class="admin-row-actions">
                    <form method="post" onsubmit="return confirm('Ștergi clasa împreună cu lecțiile, testele și înscrierile ei?');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="class_delete">
                      <input type="hidden" name="id" value="<?php echo (int) $c['id']; ?>">
                      <button class="btn danger small" type="submit">Șterge</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- ============ ANUNȚURI ============ -->
    <div class="admin-section" data-tab-panel="announcements">
      <section class="panel">
        <div class="panel-head"><h2>Anunțuri recente</h2></div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead><tr><th>Titlu</th><th>Clasă</th><th>Mesaj</th><th style="text-align:right">Acțiuni</th></tr></thead>
            <tbody>
              <?php if (!$announcements): ?><tr><td colspan="4">Nu există anunțuri.</td></tr><?php endif; ?>
              <?php foreach ($announcements as $a): ?>
              <tr>
                <td><?php echo e($a['title']); ?></td>
                <td><?php echo e($a['class_name'] . ' ' . $a['class_section']); ?></td>
                <td><?php echo e(mb_substr($a['content'], 0, 90)); ?><?php echo mb_strlen($a['content']) > 90 ? '…' : ''; ?></td>
                <td>
                  <div class="admin-row-actions">
                    <form method="post" onsubmit="return confirm('Ștergi anunțul?');">
                      <?php echo csrf_field(); ?>
                      <input type="hidden" name="action" value="announcement_delete">
                      <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                      <button class="btn danger small" type="submit">Șterge</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <!-- ============ ACTIVITATE ============ -->
    <div class="admin-section" data-tab-panel="activity">
      <section class="panel">
        <div class="panel-head">
          <h2>Ultimele încercări la teste</h2>
          <a class="btn ghost small" href="export.php">Descarcă backup JSON</a>
        </div>
        <div class="admin-table-wrap">
          <table class="admin-table">
            <thead><tr><th>Elev</th><th>Test</th><th>Scor</th><th>Corecte</th><th>Data</th></tr></thead>
            <tbody>
              <?php if (!$recentAttempts): ?><tr><td colspan="5">Nu există încercări încă.</td></tr><?php endif; ?>
              <?php foreach ($recentAttempts as $at): ?>
              <tr>
                <td><?php echo e($at['full_name']); ?></td>
                <td><?php echo e($at['quiz_title']); ?></td>
                <td><?php echo round((float) $at['score_percent']); ?>%</td>
                <td><?php echo (int) $at['correct_count']; ?> / <?php echo (int) $at['total_count']; ?></td>
                <td><?php echo e(str_replace('T', ' ', substr($at['created_at'], 0, 16))); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>

  </div>
</section>
<script>
(function () {
  var buttons = document.querySelectorAll('[data-tab-btn]');
  var panels = document.querySelectorAll('[data-tab-panel]');
  function activate(name) {
    buttons.forEach(function (b) { b.classList.toggle('active', b.dataset.tabBtn === name); });
    panels.forEach(function (p) { p.classList.toggle('active', p.dataset.tabPanel === name); });
    try { localStorage.setItem('chimie.adminTab', name); } catch (e) {}
  }
  buttons.forEach(function (b) { b.addEventListener('click', function () { activate(b.dataset.tabBtn); }); });
  try {
    var saved = localStorage.getItem('chimie.adminTab');
    if (saved && document.querySelector('[data-tab-panel="' + saved + '"]')) activate(saved);
  } catch (e) {}
})();
</script>
<?php page_footer(); ?>
