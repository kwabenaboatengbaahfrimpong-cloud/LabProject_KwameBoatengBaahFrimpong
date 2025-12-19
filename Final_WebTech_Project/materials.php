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
    $unit = clean_str($_POST['unit'] ?? 'kg');
    $ppu = (int)($_POST['points_per_unit'] ?? 0);
    $cap = (float)($_POST['max_units_per_day'] ?? 0);

    if ($name === '') $errors[] = "Name required.";
    if ($ppu <= 0) $errors[] = "Points per unit must be > 0.";
    if ($cap <= 0) $errors[] = "Daily cap must be > 0.";

    if (!$errors) {
        $stmt = db()->prepare("INSERT INTO materials (name, unit, points_per_unit, max_units_per_day) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $unit ?: 'kg', $ppu, $cap]);
        header("Location: materials.php");
        exit;
    }
}

$rows = db()->query("SELECT * FROM materials ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Materials</h2>
  <a class="btn btn-outline-secondary" href="index.php">Back</a>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="mb-3">Add material</h5>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="row g-2">
        <div class="col-md-4"><input class="form-control" name="name" placeholder="e.g., Glass" required></div>
        <div class="col-md-2"><input class="form-control" name="unit" placeholder="kg" value="kg"></div>
        <div class="col-md-3"><input class="form-control" name="points_per_unit" type="number" min="1" placeholder="Points/unit" required></div>
        <div class="col-md-3"><input class="form-control" name="max_units_per_day" type="number" step="0.01" min="0.01" placeholder="Max units/day" required></div>
      </div>
      <button class="btn btn-primary mt-3">Add</button>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr><th>Name</th><th>Unit</th><th>Pts/unit</th><th>Max/day</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['name']) ?></td>
              <td><?= e($r['unit']) ?></td>
              <td><?= (int)$r['points_per_unit'] ?></td>
              <td><?= e((string)$r['max_units_per_day']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
