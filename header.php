<?php
// includes/header.php - Global navigation bar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : null;
$user_name = $is_logged_in ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorHub - Home Tutor Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="nav-brand">
            <span class="brand-tutor">Tutor</span><span class="brand-hub">Hub</span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>
        <div class="nav-links" id="navLinks">
            <a href="index.php">Home</a>
            <a href="tuition-feed.php">Tuition Feed</a>
            
            <?php if ($is_logged_in): ?>
                <?php if ($user_role === 'student'): ?>
                    <a href="student-dashboard.php">Dashboard</a>
                    <a href="create-tuition.php">Post Tuition</a>
                    <a href="my-tuitions.php">My Tuitions</a>
                <?php elseif ($user_role === 'teacher'): ?>
                    <a href="teacher-dashboard.php">Dashboard</a>
                    <a href="teacher-profile.php">My Profile</a>
                    <a href="applications.php">My Applications</a>
                <?php elseif ($user_role === 'admin'): ?>
                    <a href="admin-dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <a href="notifications.php">Notifications</a>
                <span class="user-greeting">Hi, <?= htmlspecialchars(explode(' ', $user_name)[0]); ?></span>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="main-wrapper">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>