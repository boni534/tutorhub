<?php
// includes/auth.php - Session protection and Role-based Access Control (RBAC)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function check_logged_in() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=Please login first");
        exit();
    }
}

function check_role($allowed_roles) {
    check_logged_in();
    if (!in_array($_SESSION['user_role'], (array)$allowed_roles)) {
        header("Location: index.php?error=Unauthorized access");
        exit();
    }
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}