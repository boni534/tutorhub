<?php
// student-dashboard.php - Student & parent command center
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('student');
$student_id = $_SESSION['user_id'];
require_once 'includes/header.php';

$total_posts = $conn->query("SELECT COUNT(*) AS total FROM tuition_posts WHERE student_id = {$student_id}")->fetch_assoc()['total'];
$active_posts = $conn->query("SELECT COUNT(*) AS total FROM tuition_posts WHERE student_id = {$student_id} AND tuition_status = 'open'")->fetch_assoc()['total'];
$total_assigned = $conn->query("SELECT COUNT(*) AS total FROM tutor_assignments WHERE student_id = {$student_id} AND assignment_status = 'active'")->fetch_assoc()['total'];

$apps_stmt = $conn->prepare("SELECT a.*, u.full_name as teacher_name, tp.university_name, tp.department, tp.experience_years, t.title as tuition_title
                             FROM applications a
                             JOIN users u ON a.teacher_id = u.id
                             JOIN teacher_profiles tp ON u.id = tp.user_id
                             JOIN tuition_posts t ON a.tuition_id = t.id
                             WHERE t.student_id = ? AND a.application_status IN ('Pending Review', 'Shortlisted')
                             ORDER BY a.created_at DESC");
$apps_stmt->bind_param("i", $student_id);
$apps_stmt->execute();
$applications = $apps_stmt->get_result();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Student / Parent Dashboard</h2>
    <a href="create-tuition.php" class="btn btn-primary">+ Post New Tuition</a>
</div>

<div class="grid-cols-4">
    <div class="stat-card">
        <h3>Total Posts</h3>
        <p><?= $total_posts; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--secondary);">
        <h3>Open Tuitions</h3>
        <p><?= $active_posts; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <h3>Active Tutors Assigned</h3>
        <p><?= $total_assigned; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--warning);">
        <h3>Applicant Proposals</h3>
        <p><?= $applications->num_rows; ?></p>
    </div>
</div>

<div class="card">
    <h3>Tutor Applications Awaiting Your Decision</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 16px;">Review teacher profiles and accept your preferred candidate.</p>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tuition Title</th>
                    <th>Tutor Name</th>
                    <th>Background</th>
                    <th>Experience</th>
                    <th>Proposed Salary</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($applications->num_rows > 0): ?>
                    <?php while($app = $applications->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($app['tuition_title']); ?></strong></td>
                            <td><?= htmlspecialchars($app['teacher_name']); ?></td>
                            <td><?= htmlspecialchars($app['university_name']); ?> (<?= htmlspecialchars($app['department']); ?>)</td>
                            <td><?= (int)$app['experience_years']; ?> yrs</td>
                            <td><?= format_taka($app['proposed_salary']); ?></td>
                            <td><?= get_status_badge($app['application_status']); ?></td>
                            <td>
                                <form action="actions/application-action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="application_id" value="<?= $app['id']; ?>">
                                    <input type="hidden" name="action" value="accept_tutor">
                                    <button type="submit" class="btn btn-primary btn-sm btn-confirm-action" data-prompt="Confirm selection: This will assign this tutor to your tuition and close applications.">Accept Tutor</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">No pending tutor applications at this time.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$apps_stmt->close();
require_once 'includes/footer.php'; 
?>