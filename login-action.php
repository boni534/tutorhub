<?php
// actions/login-action.php - Handles login credentials and session init
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Security verification failed. Invalid CSRF token.");
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: ../login.php?error=Email and password are required");
    exit();
}

$stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role, account_status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($user['account_status'] === 'suspended') {
        header("Location: ../login.php?error=Your account has been suspended. Contact support.");
        exit();
    }

    if (password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin-dashboard.php");
        } elseif ($user['role'] === 'teacher') {
            header("Location: ../teacher-dashboard.php");
        } else {
            header("Location: ../student-dashboard.php");
        }
        exit();
    }
}

header("Location: ../login.php?error=Invalid email or password");
exit();