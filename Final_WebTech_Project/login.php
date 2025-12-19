<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validate.php';
require_once __DIR__ . '/includes/header.php';

$errors = [];

function too_many_attempts(string $email, string $ip): bool {
    $stmt = db()->prepare("
      SELECT COUNT(*) AS c
      FROM login_attempts
      WHERE email = ? AND ip_address = ? AND was_success = 0
        AND created_at >= (NOW() - INTERVAL 10 MINUTE)
    ");
    $stmt->execute([$email, $ip]);
    $row = $stmt->fetch();
    return (int)($row['c'] ?? 0) >= 5;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_400();

    $email = strtolower(clean_str($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if (!is_valid_email($email)) $errors[] = "Invalid email.";
    if ($password === '') $errors[] = "Password required.";

    if (!$errors && too_many_attempts($email, $ip)) {
        $errors[] = "Too many login attempts. Please wait 10 minutes and try again.";
    }

    if (!$errors) {
        $stmt = db()->prepare("SELECT id, password_hash, is_active FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $u = $stmt->fetch();

        $ok = $u && (int)$u['is_active'] === 1 && password_verify($password, $u['password_hash']);

        $stmt2 = db()->prepare("INSERT INTO login_attempts (email, ip_address, was_success) VALUES (?, ?, ?)");
        $stmt2->execute([$email, $ip, $ok ? 1 : 0]);

        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$u['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Incorrect login details.";
        }
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Login</h3>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
          </div>

          <button class="btn btn-dark w-100" type="submit">Login</button>

          <div class="mt-3 text-center">
            New user? <a href="register.php">Register first</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
