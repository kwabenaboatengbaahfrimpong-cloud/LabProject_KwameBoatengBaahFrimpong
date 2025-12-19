<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$u = require_login();

$stmt = db()->query("
  SELECT u.full_name, COALESCE(SUM(s.computed_points),0) AS pts
  FROM users u
  LEFT JOIN submissions s
    ON s.user_id = u.id
   AND s.status = 'approved'
   AND YEAR(s.created_at) = YEAR(CURDATE())
   AND MONTH(s.created_at) = MONTH(CURDATE())
  WHERE u.is_active = 1
  GROUP BY u.id
  ORDER BY pts DESC
  LIMIT 20
");
$rows = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Leaderboard (this month)</h2>
  <a class="btn btn-outline-success" href="submit.php">Log Recycling</a>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Points</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $r): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= e($r['full_name']) ?></td>
              <td class="fw-bold"><?= (int)$r['pts'] ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr><td colspan="3" class="text-muted">No data yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
