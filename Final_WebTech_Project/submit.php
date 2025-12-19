<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validate.php';
require_once __DIR__ . '/includes/header.php';

$u = require_login();
$errors = [];
$success = false;

$materials = db()->query("SELECT id, name, unit, points_per_unit, max_units_per_day FROM materials ORDER BY name ASC")->fetchAll();
$bins = db()->query("SELECT id, name, location_text FROM bins WHERE status='active' ORDER BY name ASC")->fetchAll();

function units_logged_today(int $user_id, int $material_id): float {
    $stmt = db()->prepare("
      SELECT COALESCE(SUM(quantity),0) AS q
      FROM submissions
      WHERE user_id = ? AND material_id = ?
        AND DATE(created_at) = CURDATE()
        AND status IN ('pending','approved')
    ");
    $stmt->execute([$user_id, $material_id]);
    $row = $stmt->fetch();
    return (float)($row['q'] ?? 0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_400();

    $material_id = (int)($_POST['material_id'] ?? 0);
    $bin_id = (int)($_POST['bin_id'] ?? 0);
    $quantity = (float)($_POST['quantity'] ?? 0);

    if ($material_id <= 0) $errors[] = "Select a material.";
    if ($bin_id <= 0) $errors[] = "Select a bin location.";
    if ($quantity <= 0 || $quantity > 50) $errors[] = "Quantity must be between 0 and 50.";

    $stmt = db()->prepare("SELECT id, points_per_unit, max_units_per_day FROM materials WHERE id = ?");
    $stmt->execute([$material_id]);
    $mat = $stmt->fetch();

    if (!$mat) $errors[] = "Invalid material.";

    if (!$errors) {
        $already = units_logged_today((int)$u['id'], $material_id);
        $cap = (float)$mat['max_units_per_day'];
        if ($already + $quantity > $cap) {
            $errors[] = "Daily limit exceeded for this material. Try a smaller quantity.";
        }
    }

    if (!$errors) {
        $points = (int)round($quantity * (int)$mat['points_per_unit']);
        $stmt2 = db()->prepare("
          INSERT INTO submissions (user_id, material_id, bin_id, quantity, computed_points, status)
          VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt2->execute([(int)$u['id'], $material_id, $bin_id, $quantity, $points]);
        $success = true;
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-3">Log Recycling</h3>

        <?php if ($success): ?>
          <div class="alert alert-success">Submitted! An admin will review it.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form id="submissionForm" method="post" novalidate>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

          <div class="mb-3">
            <label class="form-label">Material</label>
            <select class="form-select" name="material_id" required>
              <option value="">Select...</option>
              <?php foreach ($materials as $m): ?>
                <option value="<?= (int)$m['id'] ?>" <?= ((int)($_POST['material_id'] ?? 0) === (int)$m['id']) ? 'selected' : '' ?>>
                  <?= e($m['name']) ?> (<?= e($m['points_per_unit']) ?> pts/<?= e($m['unit']) ?>, max <?= e($m['max_units_per_day']) ?>/day)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Bin location</label>
            <select class="form-select" name="bin_id" required>
              <option value="">Select...</option>
              <?php foreach ($bins as $b): ?>
                <option value="<?= (int)$b['id'] ?>" <?= ((int)($_POST['bin_id'] ?? 0) === (int)$b['id']) ? 'selected' : '' ?>>
                  <?= e($b['name']) ?> — <?= e($b['location_text']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input class="form-control" name="quantity" type="number" step="0.01" min="0.01" max="50" required value="<?= e($_POST['quantity'] ?? '') ?>">
            <div class="form-text">You’ll earn points after admin approval.</div>
          </div>

          <button class="btn btn-success w-100" type="submit">Submit log</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
