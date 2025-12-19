<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/validate.php';
require_once __DIR__ . '/../includes/header.php';

$u = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_400();

    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $note = clean_str($_POST['admin_note'] ?? '');

    $stmt = db()->prepare("SELECT * FROM submissions WHERE id = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$id]);
    $sub = $stmt->fetch();

    if ($sub) {
        if ($action === 'approve') {
            db()->beginTransaction();
            try {
                $stmt2 = db()->prepare("UPDATE submissions SET status='approved', admin_note=?, reviewed_at=NOW() WHERE id=?");
                $stmt2->execute([$note ?: null, $id]);

                $stmt3 = db()->prepare("UPDATE users SET points_balance = points_balance + ? WHERE id=?");
                $stmt3->execute([(int)$sub['computed_points'], (int)$sub['user_id']]);

                db()->commit();
            } catch (Throwable $t) {
                db()->rollBack();
                throw $t;
            }
        } elseif ($action === 'reject') {
            $stmt2 = db()->prepare("UPDATE submissions SET status='rejected', admin_note=?, reviewed_at=NOW() WHERE id=?");
            $stmt2->execute([$note ?: null, $id]);
        }
    }

    header("Location: review_submissions.php");
    exit;
}

$rows = db()->query("
  SELECT s.id, s.quantity, s.computed_points, s.created_at,
         u.full_name, u.email,
         m.name AS material, b.name AS bin_name
  FROM submissions s
  JOIN users u ON u.id = s.user_id
  JOIN materials m ON m.id = s.material_id
  JOIN bins b ON b.id = s.bin_id
  WHERE s.status = 'pending'
  ORDER BY s.created_at ASC
  LIMIT 50
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Review Submissions</h2>
  <a class="btn btn-outline-secondary" href="index.php">Back</a>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>User</th>
            <th>Material</th>
            <th>Bin</th>
            <th>Qty</th>
            <th>Points</th>
            <th>Submitted</th>
            <th>Action</th>
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
              <td><?= e($r['material']) ?></td>
              <td><?= e($r['bin_name']) ?></td>
              <td><?= e((string)$r['quantity']) ?></td>
              <td class="fw-bold"><?= (int)$r['computed_points'] ?></td>
              <td><?= e($r['created_at']) ?></td>
              <td>
                <form method="post" class="d-flex gap-2">
                  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <input class="form-control form-control-sm" name="admin_note" placeholder="Optional note" style="min-width:180px">
                  <button class="btn btn-sm btn-success" name="action" value="approve">Approve</button>
                  <button class="btn btn-sm btn-outline-danger" name="action" value="reject">Reject</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?>
            <tr><td colspan="8" class="text-muted">No pending submissions.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
