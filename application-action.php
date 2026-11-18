<?php
// actions/application-action.php - Handles approvals, shortlists, assignments, and withdrawals
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

check_logged_in();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    header("Location: ../index.php?error=Invalid CSRF Token");
    exit();
}

$action = sanitize($_POST['action'] ?? '');
$app_id = (int)($_POST['application_id'] ?? 0);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

$query = "SELECT a.*, t.student_id, t.monthly_salary, t.title FROM applications a 
          JOIN tuition_posts t ON a.tuition_id = t.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $app_id);
$stmt->execute();
$app = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$app) {
    header("Location: ../index.php?error=Application record not found");
    exit();
}

if ($action === 'withdraw' && $user_role === 'teacher' && $app['teacher_id'] == $user_id) {
    $u_stmt = $conn->prepare("UPDATE applications SET application_status = 'Withdrawn' WHERE id = ?");
    $u_stmt->bind_param("i", $app_id);
    $u_stmt->execute();
    $u_stmt->close();
    header("Location: ../applications.php?success=Application withdrawn");
    exit();
}

if (in_array($action, ['shortlist', 'admin_reject']) && $user_role === 'admin') {
    $status = ($action === 'shortlist') ? 'Shortlisted' : 'Rejected';
    $note = sanitize($_POST['admin_note'] ?? '');
    
    $u_stmt = $conn->prepare("UPDATE applications SET application_status = ?, admin_note = ? WHERE id = ?");
    $u_stmt->bind_param("ssi", $status, $note, $app_id);
    $u_stmt->execute();
    $u_stmt->close();

    create_notification($conn, $app['teacher_id'], "Your application status for '{$app['title']}' changed to: {$status}");
    header("Location: ../admin-dashboard.php?success=Application updated to {$status}");
    exit();
}

if ($action === 'accept_tutor' && $user_role === 'student' && $app['student_id'] == $user_id) {
    $conn->begin_transaction();
    try {
        $u1 = $conn->prepare("UPDATE applications SET application_status = 'Approved' WHERE id = ?");
        $u1->bind_param("i", $app_id);
        $u1->execute();
        $u1->close();

        $u2 = $conn->prepare("UPDATE tuition_posts SET tuition_status = 'assigned' WHERE id = ?");
        $u2->bind_param("i", $app['tuition_id']);
        $u2->execute();
        $u2->close();

        $ins = $conn->prepare("INSERT INTO tutor_assignments (tuition_id, teacher_id, student_id, assigned_by, agreed_monthly_salary, assignment_status) VALUES (?, ?, ?, ?, ?, 'active')");
        $ins->bind_param("iiiid", $app['tuition_id'], $app['teacher_id'], $user_id, $user_id, $app['proposed_salary']);
        $ins->execute();
        $ins->close();

        $u3 = $conn->prepare("UPDATE applications SET application_status = 'Rejected' WHERE tuition_id = ? AND id != ?");
        $u3->bind_param("ii", $app['tuition_id'], $app_id);
        $u3->execute();
        $u3->close();

        create_notification($conn, $app['teacher_id'], "Congratulations! You have been accepted for: {$app['title']}");
        $conn->commit();
        header("Location: ../student-dashboard.php?success=Tutor accepted and assigned successfully!");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../student-dashboard.php?error=Failed to finalize assignment");
        exit();
    }
}

header("Location: ../index.php?error=Unauthorized operation");
exit();