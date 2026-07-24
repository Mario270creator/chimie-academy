<?php
/**
 * Chimie Academy · versiune PHP pentru hosting clasic (cPanel / FTP)
 * Nucleul aplicației: bază de date SQLite, autentificare, layout comun.
 * Nu necesită nimic special: PHP 7.4+ cu extensia PDO SQLite (activă implicit pe cPanel).
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0'); // pe hosting nu afișăm erorile vizitatorilor
ini_set('log_errors', '1');

define('APP_NAME', 'Chimie Academy · Clasele VII-VIII');

/* ============================================================
   ⚙️  CONFIGURARE BAZĂ DE DATE MySQL — COMPLETEAZĂ AICI!
   Datele le primești de la administratorul serverului (cPanel → MySQL Databases).
   ============================================================ */
define('DB_HOST', 'localhost');        // aproape întotdeauna "localhost"
define('DB_NAME', 'cngmm_chimie-academy');
define('DB_USER', 'cngmm_chimie-academy');
define('DB_PASS', 'chimie2026!!!');
/* ============================================================ */

define('PBKDF2_ITERATIONS', 100000);

// ---------------------------------------------------------------- sesiune ---
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// ------------------------------------------------------------ bază de date ---
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $ex) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;padding:40px;max-width:640px;margin:auto">';
            echo '<h1>⚗️ Chimie Academy · configurare necesară</h1>';
            echo '<p>Nu m-am putut conecta la baza de date MySQL. Cel mai probabil datele din <b>core.php</b> nu sunt completate sau sunt greșite.</p>';
            echo '<p>Deschide <b>core.php</b> și completează DB_NAME, DB_USER și DB_PASS cu datele primite de la administratorul serverului.</p>';
            echo '<p style="color:#888;font-size:0.85em">Detaliu tehnic: ' . htmlspecialchars($ex->getMessage()) . '</p>';
            exit;
        }
        // auto-instalare la prima rulare: dacă tabelele lipsesc, le creăm și încărcăm conținutul
        $has = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
        if ($has === false) {
            init_schema($pdo);
            seed_demo($pdo);
        }
    }
    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(190) NOT NULL,
        username VARCHAR(120) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('profesor','elev') NOT NULL,
        is_admin TINYINT NOT NULL DEFAULT 0,
        bio TEXT,
        created_at VARCHAR(40) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(190) NOT NULL,
        section VARCHAR(60) NOT NULL,
        description TEXT NOT NULL,
        code VARCHAR(40) NOT NULL UNIQUE,
        teacher_id INT NOT NULL,
        created_at VARCHAR(40) NOT NULL,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        user_id INT NOT NULL,
        joined_at VARCHAR(40) NOT NULL,
        UNIQUE KEY uniq_enroll (class_id, user_id),
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        teacher_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at VARCHAR(40) NOT NULL,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS lessons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        summary TEXT NOT NULL,
        content MEDIUMTEXT NOT NULL,
        xp INT NOT NULL DEFAULT 40,
        difficulty VARCHAR(60) NOT NULL DEFAULT 'Mediu',
        created_at VARCHAR(40) NOT NULL,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS completions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lesson_id INT NOT NULL,
        user_id INT NOT NULL,
        completed_at VARCHAR(40) NOT NULL,
        UNIQUE KEY uniq_completion (lesson_id, user_id),
        FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS quizzes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        xp INT NOT NULL DEFAULT 120,
        difficulty VARCHAR(60) NOT NULL DEFAULT 'Standard',
        questions_json MEDIUMTEXT NOT NULL,
        created_at VARCHAR(40) NOT NULL,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT NOT NULL,
        user_id INT NOT NULL,
        score_percent DOUBLE NOT NULL,
        correct_count INT NOT NULL,
        total_count INT NOT NULL,
        answers_json TEXT NOT NULL,
        created_at VARCHAR(40) NOT NULL,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function seed_demo(PDO $pdo): void
{
    $seedFile = __DIR__ . '/seed_data.php';
    if (is_file($seedFile)) {
        $data = require $seedFile;
        foreach ($data as $table => $rows) {
            if (!$rows) { continue; }
            $cols = array_keys($rows[0]);
            $sql = 'INSERT INTO `' . $table . '`(`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')';
            $st = $pdo->prepare($sql);
            foreach ($rows as $row) {
                $st->execute(array_values($row));
            }
        }
        return;
    }
    // fallback minimal dacă seed_data.php lipsește
    $now = now_iso();
    $ins = $pdo->prepare("INSERT INTO users(full_name, username, password_hash, role, is_admin, bio, created_at) VALUES (?, ?, ?, ?, ?, '', ?)");
    $ins->execute(['Profesor Demo', 'profesor_demo', hash_password('1234'), 'profesor', 1, $now]);
    $ins->execute(['Elev Demo', 'elev_demo', hash_password('1234'), 'elev', 0, $now]);
}

// ------------------------------------------------------------ utilitare DB ---
function q_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function q_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $row = $st->fetch();
    return $row === false ? null : $row;
}

function q_exec(string $sql, array $params = []): void
{
    $st = db()->prepare($sql);
    $st->execute($params);
}

function q_scalar(string $sql, array $params = []): int
{
    $row = q_one($sql, $params);
    return $row ? (int) reset($row) : 0;
}

function now_iso(): string
{
    return gmdate('Y-m-d\TH:i:sP');
}

// ----------------------------------------------------------------- parole ---
// Format portabil: pbkdf2$iterații$salt_hex$hash_hex (funcționează identic pe orice PHP)
function hash_password(string $password): string
{
    $salt = bin2hex(random_bytes(16));
    $hash = hash_pbkdf2('sha256', $password, $salt, PBKDF2_ITERATIONS, 64, false);
    return 'pbkdf2$' . PBKDF2_ITERATIONS . '$' . $salt . '$' . $hash;
}

function verify_password(string $password, string $stored): bool
{
    $parts = explode('$', $stored);
    if (count($parts) !== 4 || $parts[0] !== 'pbkdf2') {
        return false;
    }
    $iterations = (int) $parts[1];
    if ($iterations < 1000) {
        return false;
    }
    $calc = hash_pbkdf2('sha256', $password, $parts[2], $iterations, 64, false);
    return hash_equals($parts[3], $calc);
}

// ---------------------------------------------------------- autentificare ---
function current_user(): ?array
{
    static $cached = false;
    static $user = null;
    if ($cached) {
        return $user;
    }
    $cached = true;
    if (!empty($_SESSION['user_id'])) {
        $user = q_one('SELECT * FROM users WHERE id = ?', [(int) $_SESSION['user_id']]);
    }
    return $user;
}

function is_admin(?array $user = null): bool
{
    $user = $user ?? current_user();
    return $user !== null && (int) $user['is_admin'] === 1;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        flash('warning', 'Intră în cont ca să accesezi platforma.');
        redirect('login.php');
    }
    return $user;
}

function require_teacher(): array
{
    $user = require_login();
    if ($user['role'] !== 'profesor') {
        flash('danger', 'Această zonă este rezervată profesorului.');
        redirect('dashboard.php');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (!is_admin($user)) {
        flash('danger', 'Zona de administrare este rezervată administratorului.');
        redirect('dashboard.php');
    }
    return $user;
}

// ----------------------------------------------------------- CSRF & flash ---
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf_token']) . '">';
}

function check_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        flash('danger', 'Sesiunea a expirat. Reîncearcă.');
        redirect('index.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function post(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

// ------------------------------------------------------------ logică joc ---
function compute_total_xp(int $userId): int
{
    $lessonXp = q_scalar(
        'SELECT COALESCE(SUM(l.xp), 0) FROM completions c JOIN lessons l ON l.id = c.lesson_id WHERE c.user_id = ?',
        [$userId]
    );
    $rows = q_all(
        'SELECT q.xp, MAX(a.score_percent) AS best FROM attempts a JOIN quizzes q ON q.id = a.quiz_id WHERE a.user_id = ? GROUP BY q.id',
        [$userId]
    );
    $quizXp = 0;
    foreach ($rows as $row) {
        $quizXp += (int) round(((float) $row['xp']) * ((float) $row['best'] / 100.0));
    }
    return $lessonXp + $quizXp;
}

function compute_level(int $userId): array
{
    $xp = compute_total_xp($userId);
    $tiers = [
        [0, 'Începător', '🌱'],
        [120, 'În formare', '⚗️'],
        [260, 'Aplicat', '🧪'],
        [420, 'Avansat', '📘'],
        [620, 'Excelent', '🏅'],
    ];
    $current = $tiers[0];
    $next = null;
    foreach ($tiers as $i => $tier) {
        if ($xp >= $tier[0]) {
            $current = $tier;
            $next = $tiers[$i + 1] ?? null;
        }
    }
    return [
        'xp' => $xp,
        'title' => $current[1],
        'icon' => $current[2],
        'next_title' => $next ? $next[1] : 'Maxim',
        'remaining' => $next ? max(0, $next[0] - $xp) : 0,
    ];
}

function classes_for_user(array $user): array
{
    if ($user['role'] === 'profesor') {
        return q_all('SELECT * FROM classes WHERE teacher_id = ? ORDER BY name, section', [(int) $user['id']]);
    }
    return q_all(
        'SELECT c.* FROM classes c JOIN enrollments e ON e.class_id = c.id WHERE e.user_id = ? ORDER BY c.name, c.section',
        [(int) $user['id']]
    );
}

function allowed_class_ids(array $user): array
{
    return array_map(fn($c) => (int) $c['id'], classes_for_user($user));
}

function leaderboard_rows(int $limit = 20): array
{
    $rows = [];
    foreach (q_all('SELECT * FROM users') as $u) {
        $level = compute_level((int) $u['id']);
        $rows[] = [
            'full_name' => $u['full_name'],
            'role' => $u['role'],
            'xp' => $level['xp'],
            'icon' => $level['icon'],
            'title' => $level['title'],
        ];
    }
    usort($rows, fn($a, $b) => [$b['xp'], strtolower($a['full_name'])] <=> [$a['xp'], strtolower($b['full_name'])]);
    return array_slice($rows, 0, $limit);
}

function make_class_code(string $name, string $section): string
{
    $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name . $section));
    $base = substr($base, 0, 6) ?: 'CLASA';
    do {
        $code = $base . str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
    } while (q_one('SELECT id FROM classes WHERE code = ?', [$code]) !== null);
    return $code;
}

function parse_questions_payload(string $raw): array
{
    $payload = json_decode($raw, true);
    if (!is_array($payload) || count($payload) < 1) {
        throw new InvalidArgumentException('Adaugă cel puțin o întrebare.');
    }
    $cleaned = [];
    foreach ($payload as $item) {
        if (!is_array($item)) {
            throw new InvalidArgumentException('Structura întrebărilor este invalidă.');
        }
        $text = trim((string) ($item['text'] ?? ''));
        $options = $item['options'] ?? [];
        $correct = $item['correct'] ?? null;
        $explanation = trim((string) ($item['explanation'] ?? ''));
        if ($text === '') {
            throw new InvalidArgumentException('Există o întrebare fără enunț.');
        }
        if (!is_array($options) || count($options) !== 4) {
            throw new InvalidArgumentException('Fiecare întrebare trebuie să aibă exact 4 opțiuni.');
        }
        $options = array_map(fn($o) => trim((string) $o), array_values($options));
        foreach ($options as $opt) {
            if ($opt === '') {
                throw new InvalidArgumentException('Toate opțiunile trebuie completate.');
            }
        }
        if (!is_int($correct) || $correct < 0 || $correct > 3) {
            throw new InvalidArgumentException('Indicele răspunsului corect este invalid.');
        }
        $cleaned[] = [
            'text' => $text,
            'options' => $options,
            'correct' => $correct,
            'explanation' => $explanation !== '' ? $explanation : 'Explicația nu a fost completată.',
        ];
    }
    return $cleaned;
}

// ------------------------------------------------------------------ layout ---
function page_header(string $title, string $active = ''): void
{
    $user = current_user();
    $admin = is_admin($user);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    ?><!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#08111f">
  <meta name="color-scheme" content="light dark">
  <meta name="description" content="Chimie Academy pentru clasele VII-VIII - platformă educațională de chimie, laborator interactiv și instrumente de prezentare.">
  <link rel="icon" href="icon-192.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap">
  <link rel="stylesheet" href="style.css">
  <title><?php echo e($title); ?> · <?php echo e(APP_NAME); ?></title>
</head>
<body>
  <a class="skip-link" href="#mainContent">Sari la conținut</a>
  <div class="page-shell">
    <header class="topbar">
      <div class="container topbar-inner">
        <a class="brand" href="index.php" aria-label="Chimie Academy · acasă">
          <div class="brand-badge" aria-hidden="true">
            <span class="brand-num">6</span>
            <span class="brand-symbol">C</span>
          </div>
          <div>
            <strong>Chimie Academy</strong>
            <small>Clasele VII-VIII</small>
          </div>
        </a>

        <button class="nav-toggle" type="button" aria-label="Deschide meniul" data-nav-toggle>☰</button>

        <nav class="topnav" data-nav-menu>
          <?php if ($user !== null): ?>
            <a class="<?php echo $active === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">Panou principal</a>
            <a class="<?php echo $active === 'clase' ? 'active' : ''; ?>" href="clase.php">Clase</a>
            <a class="<?php echo $active === 'lectii' ? 'active' : ''; ?>" href="lectii.php">Lecții</a>
            <a class="<?php echo $active === 'teste' ? 'active' : ''; ?>" href="teste.php">Teste</a>
            <a class="<?php echo $active === 'organica' ? 'active' : ''; ?>" href="organica.php">Organică</a>
            <a class="<?php echo $active === 'lab' ? 'active' : ''; ?>" href="laborator.html">Laborator</a>
            <a class="<?php echo $active === 'clasament' ? 'active' : ''; ?>" href="clasament.php">Clasament</a>
            <?php if ($admin): ?>
              <a class="<?php echo $active === 'admin' ? 'active' : ''; ?>" href="admin.php">⚙️ Admin</a>
            <?php endif; ?>
          <?php else: ?>
            <a class="<?php echo $active === 'organica' ? 'active' : ''; ?>" href="organica.php">Organică</a>
            <a class="<?php echo $active === 'lab' ? 'active' : ''; ?>" href="laborator.html">Laborator</a>
            <a class="<?php echo $active === 'timer' ? 'active' : ''; ?>" href="timer.php">Cronometru</a>
            <a class="<?php echo $active === 'clasament' ? 'active' : ''; ?>" href="clasament.php">Clasament</a>
          <?php endif; ?>
        </nav>

        <div class="topbar-actions">
          <button class="theme-toggle" type="button" data-theme-toggle aria-label="Schimbă tema">🌗</button>
          <?php if ($user !== null): $level = compute_level((int) $user['id']); ?>
            <div class="user-pill">
              <span><?php echo $level['icon']; ?></span>
              <div>
                <strong><?php echo e($user['full_name']); ?></strong>
                <small><?php echo e(ucfirst($user['role'])); ?> · <?php echo e($level['title']); ?></small>
              </div>
            </div>
            <a class="btn ghost small" href="logout.php">Ieșire</a>
          <?php else: ?>
            <a class="btn ghost small" href="login.php">Autentificare</a>
            <a class="btn primary small" href="demo.php">Demo instant</a>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <?php $flashes = take_flashes(); if ($flashes): ?>
    <div class="container flash-stack">
      <?php foreach ($flashes as $f): ?>
        <div class="flash <?php echo e($f['type']); ?>"><?php echo e($f['message']); ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <main id="mainContent">
<?php
}

function page_footer(): void
{
    ?>
    </main>

    <footer class="site-footer">
      <div class="container footer-grid">
        <div>
          <div class="footer-title">Chimie Academy</div>
          <p>Platformă educațională pregătită pentru lucru clar, rapid și organizat.</p>
        </div>
        <div>
          <div class="footer-title">Compatibilitate</div>
          <p>Telefon, laptop și desktop, direct din browser. Funcționează pe orice hosting cu PHP, fără instalări suplimentare.</p>
        </div>
        <div>
          <div class="footer-title">Acces rapid</div>
          <p>
            <a href="laborator.html">Laborator</a> ·
            <a href="timer.php">Timer prezentare</a> ·
            <a href="clasament.php">Clasament</a>
          </p>
        </div>
      </div>
    </footer>
  </div>

  <script src="app.js"></script>
  <script src="chem-editor.js"></script>
  <script src="premium.js"></script>
</body>
</html>
<?php
}
