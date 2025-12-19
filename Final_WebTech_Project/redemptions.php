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
  $id = (int)($_POST['id'] ?? 0);
  $action = $_POST['action'] ?? '';

  if ($id > 0 && in_array($action, ['fulfill','cancel'], true)) {
    try {
      db()->beginTransaction();

      $st = db()->prepare("SELECT * FROM redemptions WHERE id=? AND status='requested' FOR UPDATE");
      $st->execute([$id]);
      $red = $st->fetch();

      if (!$red) { db()->rollBack(); header("Location: redemptions.php"); exit; }

      if ($action === 'fulfill') {
        db()->prepare("UPDATE redemptions SET status='fulfilled', fulfilled_at=NOW() WHERE id=?")->execute([$id]);
        db()->commit();
      } else {
        $rwSt = db()->prepare("SELECT points_cost FROM rewards WHERE id=? FOR UPDATE");
        $rwSt->execute([(int)$red['reward_id']]);
        $rw = $rwSt->fetch();

        db()->prepare("SELECT id FROM users WHERE id=? FOR UPDATE")->execute([(int)$red['user_id']]);

        if (!$rw) throw new RuntimeException("Invalid state");

        db()->prepare("UPDATE redemptions SET status='cancelled' WHERE id=?")->execute([$id]);
        db()->prepare("UPDATE rewards SET stock = stock + 1 WHERE id=?")->execute([(int)$red['reward_id']]);
        db()->prepare("UPDATE users SET points_balance = points_balance + ? WHERE id=?")
          ->execute([(int)$rw['points_cost'], (int)$red['user_id']]);

        db()->commit();
      }
    } catch (Throwable $t) {
      if (db()->inTransaction()) db()->rollBack();
      $errors[] = "Failed to update redemption.";
    }
  }
}

$rows = db()->query("
  SELECT rd.id, rd.status, rd.created_at, rd.fulfilled_at,
         u.full_name, u.email,
         r.name AS reward_name, r.points_cost
  FROM redemptions rd
  JOIN users u ON u.id = rd.user_id
  JOIN rewards r ON r.id = rd.reward_id
  ORDER BY rd.created_at DESC
  LIMIT 80
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Admin: Redemptions</h2>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href="rewards.php">Manage Rewards</a>
    <a class="btn btn-outline-secondary" href="index.php">Back</a>
  </div>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Reward</th>
            <th>Cost</th>
            <th>Status</th>
            <th>Requested</th>
            <th>Fulfilled</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= (int)$r['id'] ?></td>
              <td>
                <div class="fw-semibold"><?= e($r['full_name']) ?></div>
                <div class="text-muted small"><?= e($r['email']) ?></div>
              </td>
              <td><?= e($r['reward_name']) ?></td>
              <td><?= (int)$r['points_cost'] ?></td>
              <td>
                <span class="badge bg-<?= $r['status']==='fulfilled'?'success':($r['status']==='cancelled'?'secondary':'warning text-dark') ?>">
                  <?= e($r['status']) ?>
                </span>
              </td>
              <td><?= e($r['created_at']) ?></td>
              <td><?= e($r['fulfilled_at'] ?? '-') ?></td>
              <td class="text-end">
                <?php if ($r['status'] === 'requested'): ?>
                  <form method="post" class="d-inline">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-success" name="action" value="fulfill">Fulfill</button>
                  </form>
                  <form method="post" class="d-inline" onsubmit="return confirm('Cancel this request? Points will be refunded.');">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" name="action" value="cancel">Cancel</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="8" class="text-muted">No redemptions yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
