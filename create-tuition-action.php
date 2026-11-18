<?php
// actions/create-tuition-action.php - Student tuition requirement post processing
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header("Location: ../create-tuition.php?error=Security check failed");
    exit();
}

$student_id = $_SESSION['user_id'];
$title = sanitize($_POST['title'] ?? '');
$student_name = sanitize($_POST['student_name'] ?? '');
$student_class = sanitize($_POST['student_class'] ?? '');
$institution_name = sanitize($_POST['institution_name'] ?? '');
$location = sanitize($_POST['location'] ?? '');
$tutoring_days = (int)($_POST['tutoring_days_per_week'] ?? 0);
$preferred_days = sanitize($_POST['preferred_days'] ?? '');
$preferred_time = sanitize($_POST['preferred_time'] ?? '');
$teaching_method = sanitize($_POST['teaching_method'] ?? 'Home Tutoring');
$monthly_salary = (float)($_POST['monthly_salary'] ?? 0.00);
$preferred_gender = sanitize($_POST['preferred_tutor_gender'] ?? 'Any');
$additional_req = sanitize($_POST['additional_requirements'] ?? '');
$subjects_raw = sanitize($_POST['subjects'] ?? '');

if (empty($title) || empty($student_name) || empty($student_class) || empty($location) || $monthly_salary <= 0) {
    header("Location: ../create-tuition.php?error=Please fill in all mandatory fields with valid values");
    exit();
}

$stmt = $conn->prepare("INSERT INTO tuition_posts (student_id, title, student_name, student_class, institution_name, location, tutoring_days_per_week, preferred_days, preferred_time, teaching_method, monthly_salary, preferred_tutor_gender, additional_requirements, approval_status, tuition_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'open')");
$stmt->bind_param("isssssisssdss", $student_id, $title, $student_name, $student_class, $institution_name, $location, $tutoring_days, $preferred_days, $preferred_time, $teaching_method, $monthly_salary, $preferred_gender, $additional_req);

if ($stmt->execute()) {
    $tuition_id = $stmt->insert_id;
    $stmt->close();

    $subjects = array_map('trim', explode(',', $subjects_raw));
    $s_stmt = $conn->prepare("INSERT INTO tuition_subjects (tuition_id, subject_name) VALUES (?, ?)");
    foreach ($subjects as $sub) {
        if (!empty($sub)) {
            $s_stmt->bind_param("is", $tuition_id, $sub);
            $s_stmt->execute();
        }
    }
    $s_stmt->close();

    create_notification($conn, $student_id, "Your tuition post '{$title}' was submitted and is pending admin approval.");
    header("Location: ../student-dashboard.php?success=Tuition requirement submitted successfully. Awaiting approval.");
    exit();
} else {
    header("Location: ../create-tuition.php?error=Failed to publish post.");
    exit();
}