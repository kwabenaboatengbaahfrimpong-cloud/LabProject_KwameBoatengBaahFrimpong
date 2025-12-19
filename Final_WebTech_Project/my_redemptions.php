<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$u = require_login();

$st = db()->prepare("
  SELECT r.name AS reward, rd.status, rd.created_at, rd.fulfilled_at
  FROM redemptions rd
  JOIN rewards r ON r.id = rd.reward_id
  WHERE rd.user_id = ?
  ORDER BY rd.created_at DESC
  LIMIT 50
");
$st->execute([(int)$u['id']]);
$data = $st->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">My Redemptions</h2>
  <a class="btn btn-outline-primary" href="rewards.php">Browse Rewards</a>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Reward</th>
            <th>Status</th>
            <th>Requested</th>
            <th>Fulfilled</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $r): ?>
            <tr>
              <td class="fw-semibold"><?= e($r['reward']) ?></td>
              <td>
                <span class="badge bg-<?= $r['status'] === 'fulfilled' ? 'success' : ($r['status'] === 'cancelled' ? 'secondary' : 'warning text-dark') ?>">
                  <?= e($r['status']) ?>
                </span>
              </td>
              <td><?= e($r['created_at']) ?></td>
              <td><?= e($r['fulfilled_at'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$data): ?>
            <tr><td colspan="4" class="text-muted">No redemptions yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
