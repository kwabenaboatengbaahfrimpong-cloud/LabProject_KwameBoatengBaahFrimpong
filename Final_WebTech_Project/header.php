<?php
// public/includes/header.php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/validate.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php"><?= e(APP_NAME) ?></a>
    <div class="ms-auto d-flex gap-2">
      <?php if (!empty($_SESSION['uid'])): ?>
        <a class="btn btn-outline-light btn-sm" href="dashboard.php">Dashboard</a>
        <a class="btn btn-outline-light btn-sm" href="leaderboard.php">Leaderboard</a>
        <a class="btn btn-outline-light btn-sm" href="rewards.php">Rewards</a>
        <a class="btn btn-outline-light btn-sm" href="my_redemptions.php">My Redemptions</a>
        <a class="btn btn-warning btn-sm" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-outline-light btn-sm" href="login.php">Login</a>
        <a class="btn btn-success btn-sm" href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container py-4">
