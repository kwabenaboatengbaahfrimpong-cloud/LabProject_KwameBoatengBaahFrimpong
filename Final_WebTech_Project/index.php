<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

$u = require_admin();
?>

<h2 class="mb-3">Admin Panel</h2>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Manage Materials</h5>
        <p class="text-muted mb-3">Point rates and daily caps.</p>
        <a class="btn btn-outline-primary" href="materials.php">Open</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Manage Bins</h5>
        <p class="text-muted mb-3">Locations around campus.</p>
        <a class="btn btn-outline-primary" href="bins.php">Open</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Review Submissions</h5>
        <p class="text-muted mb-3">Approve/reject logs (credits points on approval).</p>
        <a class="btn btn-success" href="review_submissions.php">Review</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5>Manage Rewards</h5>
        <p class="text-muted mb-3">Rewards inventory and redemption requests.</p>
        <a class="btn btn-outline-primary" href="rewards.php">Rewards</a>
        <a class="btn btn-outline-primary ms-2" href="redemptions.php">Redemptions</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
