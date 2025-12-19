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
  $action = $_POST['action'] ?? '';

  if ($action === 'add') {
    $name = clean_str($_POST['name'] ?? '');
    $cost = (int)($_POST['points_cost'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);

    if ($name === '') $errors[] = "Name required.";
    if ($cost <= 0) $errors[] = "Points cost must be > 0.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";

    if (!$errors) {
      db()->prepare("INSERT INTO rewards (name, points_cost, stock, is_active) VALUES (?,?,?,1)")
        ->execute([$name, $cost, $stock]);
      header("Location: rewards.php"); exit;
    }
  }

  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($id <= 0) $errors[] = "Invalid reward.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";

    if (!$errors) {
      db()->prepare("UPDATE rewards SET stock=?, is_active=? WHERE id=?")
        ->execute([$stock, $active, $id]);
      header("Location: rewards.php"); exit;
    }
  }
}

$rows = db()->query("SELECT * FROM rewards ORDER BY id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Admin: Rewards</h2>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="redemptions.php">View Redemptions</a>
    <a class="btn btn-outline-secondary" href="index.php">Back</a>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <h5 class="mb-3">Add reward</h5>
    <form method="post" class="row g-2">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="action" value="add">
      <div class="col-md-6"><input class="form-control" name="name" placeholder="Reward name" required></div>
      <div class="col-md-3"><input class="form-control" type="number" min="1" name="points_cost" placeholder="Points cost" required></div>
      <div class="col-md-3"><input class="form-control" type="number" min="0" name="stock" placeholder="Stock" required></div>
      <div class="col-12"><button class="btn btn-primary">Add</button></div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Reward</th><th>Cost</th><th>Stock</th><th>Active</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <form method="post">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <td class="fw-semibold"><?= e($r['name']) ?></td>
                <td><?= (int)$r['points_cost'] ?></td>
                <td style="max-width:140px;"><input class="form-control form-control-sm" type="number" min="0" name="stock" value="<?= (int)$r['stock'] ?>"></td>
                <td><input class="form-check-input" type="checkbox" name="is_active" <?= ((int)$r['is_active']===1)?'checked':'' ?>></td>
                <td class="text-end"><button class="btn btn-sm btn-outline-primary">Save</button></td>
              </form>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="5" class="text-muted">No rewards yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
