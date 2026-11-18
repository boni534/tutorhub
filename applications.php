<?php
// applications.php - Full history of applications made by teacher
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('teacher');
$teacher_id = $_SESSION['user_id'];
require_once 'includes/header.php';

$stmt = $conn->prepare("SELECT a.*, t.title, t.student_class, t.location, t.tuition_status 
                        FROM applications a
                        JOIN tuition_posts t ON a.tuition_id = t.id
                        WHERE a.teacher_id = ?
                        ORDER BY a.created_at DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$applications = $stmt->get_result();
?>

<h2>My Submitted Applications</h2>
<div class="card" style="margin-top: 18px;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tuition Title</th>
                    <th>Class</th>
                    <th>Location</th>
                    <th>Proposed Salary</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                    <th>Admin Note</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($applications->num_rows > 0): ?>
                    <?php while($row = $applications->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['title']); ?></strong></td>
                            <td><?= htmlspecialchars($row['student_class']); ?></td>
                            <td><?= htmlspecialchars($row['location']); ?></td>
                            <td><?= format_taka($row['proposed_salary']); ?></td>
                            <td><?= date('d M, Y', strtotime($row['created_at'])); ?></td>
                            <td><?= get_status_badge($row['application_status']); ?></td>
                            <td><?= htmlspecialchars($row['admin_note'] ?: '—'); ?></td>
                            <td>
                                <?php if ($row['application_status'] === 'Pending Review'): ?>
                                    <form action="actions/application-action.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="application_id" value="<?= $row['id']; ?>">
                                        <input type="hidden" name="action" value="withdraw">
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-confirm-action" data-prompt="Withdraw this application?">Withdraw</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">No action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">You haven't submitted any applications.</td>
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