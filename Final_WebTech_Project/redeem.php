<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/validate.php';
require_once __DIR__ . '/includes/header.php';

$u = require_login();
$errors = [];
$success = false;

$reward_id = (int)($_GET['reward_id'] ?? ($_POST['reward_id'] ?? 0));
if ($reward_id <= 0) { http_response_code(400); exit('Invalid reward.'); }

$stmt = db()->prepare("SELECT id, name, points_cost, stock, is_active FROM rewards WHERE id=? LIMIT 1");
$stmt->execute([$reward_id]);
$reward = $stmt->fetch();
if (!$reward) { http_response_code(404); exit('Reward not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check_or_400();

  try {
    db()->beginTransaction();

    $rStmt = db()->prepare("SELECT id, points_cost, stock, is_active FROM rewards WHERE id=? FOR UPDATE");
    $rStmt->execute([$reward_id]);
    $r = $rStmt->fetch();

    $uStmt = db()->prepare("SELECT id, points_balance, is_active FROM users WHERE id=? FOR UPDATE");
    $uStmt->execute([(int)$u['id']]);
    $uu = $uStmt->fetch();

    if (!$r || !$uu || (int)$uu['is_active'] !== 1) throw new RuntimeException('Invalid state');

    if ((int)$r['is_active'] !== 1) $errors[] = "This reward is not active.";
    if ((int)$r['stock'] <= 0) $errors[] = "This reward is out of stock.";
    if ((int)$uu['points_balance'] < (int)$r['points_cost']) $errors[] = "You do not have enough points.";

    if ($errors) {
      db()->rollBack();
    } else {
      db()->prepare("INSERT INTO redemptions (user_id, reward_id, status) VALUES (?, ?, 'requested')")
        ->execute([(int)$u['id'], $reward_id]);

      db()->prepare("UPDATE users SET points_balance = points_balance - ? WHERE id=?")
        ->execute([(int)$r['points_cost'], (int)$u['id']]);

      db()->prepare("UPDATE rewards SET stock = stock - 1 WHERE id=?")->execute([$reward_id]);

      db()->commit();
      $success = true;
    }
  } catch (Throwable $t) {
    if (db()->inTransaction()) db()->rollBack();
    $errors[] = "Something went wrong. Please try again.";
  }
}
?>

<div class="row justify-content-center">
  <div class="col-md-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h3 class="mb-2">Redeem Reward</h3>
        <p class="text-muted mb-3">Confirm your redemption request.</p>

        <?php if ($success): ?>
          <div class="alert alert-success">
            Redemption requested! <a href="my_redemptions.php">View status</a>.
          </div>
        <?php endif; ?>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
          </div>
        <?php endif; ?>

        <div class="mb-3"><div class="fw-semibold">Reward</div><div><?= e($reward['name']) ?></div></div>
        <div class="mb-3"><div class="fw-semibold">Cost</div><div><?= (int)$reward['points_cost'] ?> points</div></div>
        <div class="mb-3"><div class="fw-semibold">Your balance</div><div><?= (int)$u['points_balance'] ?> points</div></div>

        <form method="post" onsubmit="return confirm('Confirm redeem? Points will be deducted immediately.');">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="reward_id" value="<?= (int)$reward['id'] ?>">
          <button class="btn btn-primary w-100" type="submit">Confirm Redemption</button>
          <a class="btn btn-outline-secondary w-100 mt-2" href="rewards.php">Back to Rewards</a>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
