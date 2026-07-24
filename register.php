<?php
require __DIR__ . '/core.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $fullName = post('full_name');
    $username = strtolower(post('username'));
    $password = (string) ($_POST['password'] ?? '');
    if (mb_strlen($fullName) < 3 || mb_strlen($username) < 3 || strlen($password) < 4) {
        flash('warning', 'Completează nume (min. 3), utilizator (min. 3) și parolă (min. 4 caractere).');
    } elseif (!preg_match('/^[a-z0-9_.-]+$/', $username)) {
        flash('warning', 'Utilizatorul poate conține doar litere mici, cifre, punct, liniuță și underscore.');
    } elseif (q_one('SELECT id FROM users WHERE username = ?', [$username]) !== null) {
        flash('danger', 'Numele de utilizator există deja.');
    } else {
        // Din motive de siguranță, înregistrarea publică creează doar conturi de elev.
        // Conturile de profesor se creează din panoul de administrare.
        q_exec("INSERT INTO users(full_name, username, password_hash, role, is_admin, bio, created_at) VALUES (?, ?, ?, 'elev', 0, '', ?)",
            [$fullName, $username, hash_password($password), now_iso()]);
        flash('success', 'Contul a fost creat. Intră cu datele tale.');
        redirect('login.php');
    }
}

page_header('Creează cont', 'register');
?>
<section class="section page-top">
  <div class="container narrow">
    <div class="section-head">
      <div class="eyebrow">Cont nou</div>
      <h1>Creează cont de elev</h1>
      <p class="tiny-muted">Conturile de profesor se creează doar de către administrator, din panoul Admin.</p>
    </div>
    <section class="panel">
      <form method="post" class="stack-form">
        <?php echo csrf_field(); ?>
        <label><span>Nume complet</span><input type="text" name="full_name" required minlength="3"></label>
        <label><span>Utilizator</span><input type="text" name="username" required minlength="3" autocomplete="username"></label>
        <label><span>Parolă</span><input type="password" name="password" required minlength="4" autocomplete="new-password"></label>
        <div class="form-actions">
          <button class="btn primary" type="submit">Creează contul</button>
          <a class="btn ghost" href="login.php">Am deja cont</a>
        </div>
      </form>
    </section>
  </div>
</section>
<?php page_footer(); ?>
