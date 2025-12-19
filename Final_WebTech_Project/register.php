<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validate.php';
require_once __DIR__ . '/includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_400();

    $full_name = clean_str($_POST['full_name'] ?? '');
    $email = strtolower(clean_str($_POST['email'] ?? ''));
    $phone = clean_str($_POST['phone'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if ($full_name === '' || strlen($full_name) < 3) $errors[] = "Full name is required (min 3 chars).";
    if (!is_valid_email($email)) $errors[] = "Invalid email.";
    if (!is_strong_password($password)) $errors[] = "Password must be 8+ chars and include at least 1 letter and 1 number.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (!$errors) {
        $stmt = db()->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = "That email is already registered. Please login.";
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare("INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone ?: null, $hash]);
        $success = true;
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Create your EcoPoints account</h3>

        <?php if ($success): ?>
          <div class="alert alert-success">
            Registration successful. <a href="login.php">Login now</a>.
          </div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form id="registerForm" method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Full name</label>
            <input class="form-control" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Phone (optional)</label>
            <input class="form-control" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input class="form-control" type="password" name="password" required>
            <div class="form-text">8+ chars, include 1 letter & 1 number.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input class="form-control" type="password" name="confirm_password" required>
          </div>

          <button class="btn btn-success w-100" type="submit">Register</button>
          <div class="mt-3 text-center">
            Already registered? <a href="login.php">Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
