<?php
// actions/apply-tuition-action.php - Handles teacher applications for open tuition posts
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_role('teacher');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header("Location: ../tuition-feed.php?error=Security verification failed");
    exit();
}

$tuition_id = (int)($_POST['tuition_id'] ?? 0);
$teacher_id = $_SESSION['user_id'];
$application_message = sanitize($_POST['application_message'] ?? '');
$relevant_experience = sanitize($_POST['relevant_experience'] ?? '');
$proposed_salary = (float)($_POST['proposed_salary'] ?? 0);
$available_days = sanitize($_POST['available_days'] ?? '');

$t_stmt = $conn->prepare("SELECT student_id, title FROM tuition_posts WHERE id = ? AND approval_status = 'approved' AND tuition_status = 'open'");
$t_stmt->bind_param("i", $tuition_id);
$t_stmt->execute();
$tuition = $t_stmt->get_result()->fetch_assoc();
$t_stmt->close();

if (!$tuition) {
    header("Location: ../tuition-feed.php?error=This tuition is not currently open for application");
    exit();
}

$c_stmt = $conn->prepare("SELECT id FROM applications WHERE tuition_id = ? AND teacher_id = ?");
$c_stmt->bind_param("ii", $tuition_id, $teacher_id);
$c_stmt->execute();
if ($c_stmt->get_result()->num_rows > 0) {
    $c_stmt->close();
    header("Location: ../tuition-details.php?id={$tuition_id}&error=You have already applied for this tuition");
    exit();
}
$c_stmt->close();

$stmt = $conn->prepare("INSERT INTO applications (tuition_id, teacher_id, application_message, relevant_experience, proposed_salary, available_days) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissds", $tuition_id, $teacher_id, $application_message, $relevant_experience, $proposed_salary, $available_days);

if ($stmt->execute()) {
    $stmt->close();
    create_notification($conn, $tuition['student_id'], "A tutor has applied for your post: {$tuition['title']}", 'application');
    create_notification($conn, $teacher_id, "Application submitted for: {$tuition['title']}", 'application');
    header("Location: ../applications.php?success=Application submitted successfully!");
    exit();
} else {
    $stmt->close();
    header("Location: ../tuition-details.php?id={$tuition_id}&error=Unable to process your application.");
    exit();
}