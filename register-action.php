<?php
// actions/register-action.php - Handles new user account registrations
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php");
    exit();
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("Security verification failed. Invalid CSRF token.");
}

$full_name = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = sanitize($_POST['role'] ?? '');

if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
    header("Location: ../register.php?error=All fields are required");
    exit();
}

if ($password !== $confirm_password) {
    header("Location: ../register.php?error=Passwords do not match");
    exit();
}

if (!in_array($role, ['student', 'teacher'])) {
    header("Location: ../register.php?error=Invalid role selected");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    header("Location: ../register.php?error=Email address already registered");
    exit();
}
$stmt->close();

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $full_name, $email, $phone, $password_hash, $role);

if ($stmt->execute()) {
    $new_user_id = $stmt->insert_id;
    $stmt->close();

    if ($role === 'student') {
        $p_stmt = $conn->prepare("INSERT INTO student_profiles (user_id) VALUES (?)");
        $p_stmt->bind_param("i", $new_user_id);
        $p_stmt->execute();
        $p_stmt->close();
    } else {
        $t_stmt = $conn->prepare("INSERT INTO teacher_profiles (user_id) VALUES (?)");
        $t_stmt->bind_param("i", $new_user_id);
        $t_stmt->execute();
        $t_stmt->close();
    }

    create_notification($conn, $new_user_id, "Welcome to TutorHub! Your account has been created.");
    header("Location: ../login.php?success=Account registered successfully. Please sign in.");
    exit();
} else {
    $stmt->close();
    header("Location: ../register.php?error=Registration failed. Please try again.");
    exit();
}