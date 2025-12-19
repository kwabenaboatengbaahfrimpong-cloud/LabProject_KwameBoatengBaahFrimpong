<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validate.php';
require_once __DIR__ . '/../includes/header.php';

$u = require_admin();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_400();

    $name = clean_str($_POST['name'] ?? '');
    $loc = clean_str($_POST['location_text'] ?? '');
    if ($name === '') $errors[] = "Name required.";
    if ($loc === '') $errors[] = "Location required.";

    if (!$errors) {
        $stmt = db()->prepare("INSERT INTO bins (name, location_text, status) VALUES (?, ?, 'active')");
        $stmt->execute([$name, $loc]);
        header("Location: bins.php");
        exit;
    }
}

$rows = db()->query("SELECT * FROM bins ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Bins</h2>
  <a class="btn btn-outline-secondary" href="index.php">Back</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="mb-3">Add bin</h5>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="row g-2">
        <div class="col-md-4"><input class="form-control" name="name" placeholder="e.g., Bin D" required></div>
        <div class="col-md-8"><input class="form-control" name="location_text" placeholder="e.g., Engineering block" required></div>
      </div>
      <button class="btn btn-primary mt-3">Add</button>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr><th>Name</th><th>Location</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['name']) ?></td>
              <td><?= e($r['location_text']) ?></td>
              <td><?= e($r['status']) ?></td>
              <td><?= e($r['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
