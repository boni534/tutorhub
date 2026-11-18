<?php
// my-tuitions.php - Student's tuition management table
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('student');
$student_id = $_SESSION['user_id'];
require_once 'includes/header.php';

$stmt = $conn->prepare("SELECT t.*, 
                       (SELECT COUNT(*) FROM applications WHERE tuition_id = t.id) as app_count
                       FROM tuition_posts t 
                       WHERE t.student_id = ? 
                       ORDER BY t.created_at DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$my_posts = $stmt->get_result();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>My Tuition Posts</h2>
    <a href="create-tuition.php" class="btn btn-primary">+ Post Another Requirement</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Location</th>
                    <th>Salary</th>
                    <th>Approval</th>
                    <th>Status</th>
                    <th>Applicants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_posts->num_rows > 0): ?>
                    <?php while($post = $my_posts->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($post['title']); ?></strong></td>
                            <td><?= htmlspecialchars($post['student_class']); ?></td>
                            <td><?= htmlspecialchars($post['location']); ?></td>
                            <td><?= format_taka($post['monthly_salary']); ?></td>
                            <td><?= get_status_badge($post['approval_status']); ?></td>
                            <td><?= get_status_badge($post['tuition_status']); ?></td>
                            <td><span class="badge badge-shortlisted"><?= $post['app_count']; ?> Applicants</span></td>
                            <td>
                                <a href="tuition-details.php?id=<?= $post['id']; ?>" class="btn btn-outline btn-sm">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 20px;">You have not published any tuition posts yet.</td>
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