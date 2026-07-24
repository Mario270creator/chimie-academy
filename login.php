<?php
require __DIR__ . '/core.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = strtolower(post('username'));
    $password = $_POST['password'] ?? '';
    $user = q_one('SELECT * FROM users WHERE username = ?', [$username]);
    if ($user === null || !verify_password((string) $password, $user['password_hash'])) {
        flash('danger', 'Utilizator sau parolă incorectă.');
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        flash('success', 'Bine ai revenit, ' . $user['full_name'] . '!');
        redirect('dashboard.php');
    }
}

page_header('Autentificare', 'login');
?>
<section class="section page-top">
  <div class="container narrow">
    <div class="section-head">
      <div class="eyebrow">Autentificare</div>
      <h1>Intră în cont</h1>
    </div>
    <section class="panel">
      <form method="post" class="stack-form">
        <?php echo csrf_field(); ?>
        <label><span>Utilizator</span><input type="text" name="username" required autocomplete="username"></label>
        <label><span>Parolă</span><input type="password" name="password" required autocomplete="current-password"></label>
        <div class="form-actions">
          <button class="btn primary" type="submit">Intră în cont</button>
          <a class="btn ghost" href="register.php">Creează cont de elev</a>
        </div>
      </form>
      <p class="small-note">Demo rapid: <a href="demo.php">intră instant ca elev demo</a>.</p>
    </section>
  </div>
</section>
<?php page_footer(); ?>
