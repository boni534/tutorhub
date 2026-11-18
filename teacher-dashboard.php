<?php
// teacher-dashboard.php - Instructor overview and metrics
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('teacher');
$teacher_id = $_SESSION['user_id'];
require_once 'includes/header.php';

$total_applied = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE teacher_id = {$teacher_id}")->fetch_assoc()['total'];
$total_shortlisted = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE teacher_id = {$teacher_id} AND application_status = 'Shortlisted'")->fetch_assoc()['total'];
$total_approved = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE teacher_id = {$teacher_id} AND application_status = 'Approved'")->fetch_assoc()['total'];
$available_feed = $conn->query("SELECT COUNT(*) AS total FROM tuition_posts WHERE approval_status = 'approved' AND tuition_status = 'open'")->fetch_assoc()['total'];

$stmt = $conn->prepare("SELECT a.*, t.title as tuition_title, t.location, t.monthly_salary as base_salary 
                        FROM applications a
                        JOIN tuition_posts t ON a.tuition_id = t.id
                        WHERE a.teacher_id = ?
                        ORDER BY a.created_at DESC LIMIT 5");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$recent_apps = $stmt->get_result();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Teacher / Tutor Dashboard</h2>
    <a href="tuition-feed.php" class="btn btn-primary">Browse Tuition Feed</a>
</div>

<div class="grid-cols-4">
    <div class="stat-card">
        <h3>Available Openings</h3>
        <p><?= $available_feed; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--secondary);">
        <h3>My Applications</h3>
        <p><?= $total_applied; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--warning);">
        <h3>Shortlisted</h3>
        <p><?= $total_shortlisted; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <h3>Confirmed Tuitions</h3>
        <p><?= $total_approved; ?></p>
    </div>
</div>

<div class="card">
    <h3>Recent Applications</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tuition Title</th>
                    <th>Location</th>
                    <th>Proposed Salary</th>
                    <th>Applied On</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_apps->num_rows > 0): ?>
                    <?php while($row = $recent_apps->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['tuition_title']); ?></strong></td>
                            <td><?= htmlspecialchars($row['location']); ?></td>
                            <td><?= format_taka($row['proposed_salary']); ?></td>
                            <td><?= date('d M, Y', strtotime($row['created_at'])); ?></td>
                            <td><?= get_status_badge($row['application_status']); ?></td>
                            <td>
                                <?php if ($row['application_status'] === 'Pending Review'): ?>
                                    <form action="actions/application-action.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="application_id" value="<?= $row['id']; ?>">
                                        <input type="hidden" name="action" value="withdraw">
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-confirm-action" data-prompt="Withdraw this application?">Withdraw</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">You have not applied for any tuition posts yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$stmt->close();
require_once 'includes/footer.php'; 
?>