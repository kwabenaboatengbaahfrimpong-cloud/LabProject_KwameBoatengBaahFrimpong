<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$u = require_login();

$stmt = db()->prepare("
  SELECT COUNT(*) AS c, COALESCE(SUM(computed_points),0) AS pts
  FROM submissions
  WHERE user_id = ? AND status = 'approved'
");
$stmt->execute([$u['id']]);
$stats = $stmt->fetch();

$stmt2 = db()->prepare("
  SELECT s.id, s.quantity, s.computed_points, s.status, s.created_at,
         m.name AS material, b.name AS bin_name
  FROM submissions s
  JOIN materials m ON m.id = s.material_id
  JOIN bins b ON b.id = s.bin_id
  WHERE s.user_id = ?
  ORDER BY s.created_at DESC
  LIMIT 10
");
$stmt2->execute([$u['id']]);
$recent = $stmt2->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Welcome, <?= e($u['full_name']) ?></h2>
  <div>
    <?php if ($u['role'] === 'admin'): ?>
      <a class="btn btn-outline-primary" href="admin/index.php">Admin Panel</a>
    <?php endif; ?>
    <a class="btn btn-success" href="submit.php">Log Recycling</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Points balance</div>
        <div class="fs-2 fw-bold"><?= (int)$u['points_balance'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Approved logs</div>
        <div class="fs-2 fw-bold"><?= (int)($stats['c'] ?? 0) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted">Approved points earned</div>
        <div class="fs-2 fw-bold"><?= (int)($stats['pts'] ?? 0) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="mb-3">Recent submissions</h5>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Material</th>
            <th>Bin</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Points</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td><?= e($r['material']) ?></td>
              <td><?= e($r['bin_name']) ?></td>
              <td><?= e((string)$r['quantity']) ?></td>
              <td>
                <span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'secondary') ?>">
                  <?= e($r['status']) ?>
                </span>
              </td>
              <td><?= (int)$r['computed_points'] ?></td>
              <td><?= e($r['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recent): ?>
            <tr><td colspan="7" class="text-muted">No submissions yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
