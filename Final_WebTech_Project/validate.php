<?php
// public/includes/validate.php
declare(strict_types=1);

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function is_valid_email(string $email): bool {
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_strong_password(string $pw): bool {
    // At least 8 chars, 1 letter, 1 number
    return (bool)preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/', $pw);
}

function clean_str(string $s): string {
    return trim(preg_replace('/\s+/', ' ', $s));
}
