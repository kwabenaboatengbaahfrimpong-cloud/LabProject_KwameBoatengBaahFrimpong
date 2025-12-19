<?php
// public/includes/auth.php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function current_user(): ?array {
    if (empty($_SESSION['uid'])) return null;

    $stmt = db()->prepare("SELECT id, full_name, email, role, points_balance, is_active FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['uid']]);
    $u = $stmt->fetch();
    if (!$u || (int)$u['is_active'] !== 1) return null;
    return $u;
}

function require_login(): array {
    $u = current_user();
    if (!$u) {
        header("Location: login.php");
        exit;
    }
    return $u;
}

function require_admin(): array {
    $u = require_login();
    if (($u['role'] ?? 'user') !== 'admin') {
        http_response_code(403);
        exit('Forbidden');
    }
    return $u;
}
