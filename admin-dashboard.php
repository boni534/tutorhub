<?php
// admin-dashboard.php - Central administration & management portal
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('admin');
$admin_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    $admin_task = sanitize($_POST['admin_task'] ?? '');

    if ($admin_task === 'update_post_status') {
        $post_id = (int)$_POST['post_id'];
        $new_status = $_POST['approval_status'] ?? '';
        if (!in_array($new_status, ['approved', 'rejected', 'pending'], true)) {
            header("Location: admin-dashboard.php?error=Invalid post status");
            exit();
        }

        $stmt = $conn->prepare("UPDATE tuition_posts SET approval_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $post_id);
        $stmt->execute();
        $stmt->close();

        $owner = $conn->query("SELECT student_id, title FROM tuition_posts WHERE id = {$post_id}")->fetch_assoc();
        if ($owner) {
            create_notification($conn, $owner['student_id'], "Your tuition post '{$owner['title']}' has been {$new_status} by admin.", 'tuition');
        }
        header("Location: admin-dashboard.php?success=Tuition post updated to " . $new_status);
        exit();
    }

    if ($admin_task === 'toggle_user_status') {
        $target_user_id = (int)$_POST['target_user_id'];
        $new_state = $_POST['account_status'] ?? '';
        if (!in_array($new_state, ['active', 'suspended'], true)) {
            header("Location: admin-dashboard.php?error=Invalid account status");
            exit();
        }

        if ($target_user_id === $admin_id) {
            header("Location: admin-dashboard.php?error=Cannot alter your own admin account status");
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET account_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_state, $target_user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-dashboard.php?success=User account status updated");
        exit();
    }

    if ($admin_task === 'verify_teacher') {
        $teacher_profile_id = (int)$_POST['teacher_profile_id'];
        $v_status = $_POST['verification_status'] ?? '';
        if (!in_array($v_status, ['unverified', 'verified', 'rejected'], true)) {
            header("Location: admin-dashboard.php?error=Invalid verification status");
            exit();
        }

        $stmt = $conn->prepare("UPDATE teacher_profiles SET verification_status = ? WHERE id = ?");
        $stmt->bind_param("si", $v_status, $teacher_profile_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin-dashboard.php?success=Teacher verification status updated");
        exit();
    }
}

require_once 'includes/header.php';

$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_students = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'student'")->fetch_assoc()['total'];
$total_teachers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'teacher'")->fetch_assoc()['total'];
$pending_posts_count = $conn->query("SELECT COUNT(*) AS total FROM tuition_posts WHERE approval_status = 'pending'")->fetch_assoc()['total'];
$pending_apps_count = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE application_status = 'Pending Review'")->fetch_assoc()['total'];
$active_assignments_count = $conn->query("SELECT COUNT(*) AS total FROM tutor_assignments WHERE assignment_status = 'active'")->fetch_assoc()['total'];

$pending_posts = $conn->query("SELECT t.*, u.full_name as student_username FROM tuition_posts t JOIN users u ON t.student_id = u.id WHERE t.approval_status = 'pending' ORDER BY t.created_at ASC");
$users_list = $conn->query("SELECT id, full_name, email, phone, role, account_status, created_at FROM users WHERE role != 'admin' ORDER BY id DESC LIMIT 10");
$pending_apps = $conn->query("SELECT a.*, u.full_name as tutor_name, t.title as tuition_title, tp.university_name FROM applications a JOIN users u ON a.teacher_id = u.id LEFT JOIN teacher_profiles tp ON u.id = tp.user_id JOIN tuition_posts t ON a.tuition_id = t.id WHERE a.application_status = 'Pending Review' ORDER BY a.created_at ASC");
$teachers_list = $conn->query("SELECT tp.id AS profile_id, tp.university_name, tp.department, tp.qualification, tp.verification_status, u.full_name, u.email FROM teacher_profiles tp JOIN users u ON tp.user_id = u.id ORDER BY FIELD(tp.verification_status, 'unverified', 'rejected', 'verified'), tp.id DESC");
?>

<h2>Administrator Control Center</h2>

<div class="grid-cols-4" style="margin-top: 18px;">
    <div class="stat-card">
        <h3>Total Users</h3>
        <p><?= $total_users; ?></p>
        <small style="color: var(--text-muted);"><?= $total_students; ?> Students | <?= $total_teachers; ?> Tutors</small>
    </div>
    <div class="stat-card" style="border-left-color: var(--warning);">
        <h3>Pending Tuition Posts</h3>
        <p><?= $pending_posts_count; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--secondary);">
        <h3>Pending Tutor Apps</h3>
        <p><?= $pending_apps_count; ?></p>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <h3>Confirmed Tuitions</h3>
        <p><?= $active_assignments_count; ?></p>
    </div>
</div>

<div class="card">
    <h3>Pending Tuition Posts Awaiting Moderation</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Poster</th>
                    <th>Class</th>
                    <th>Location</th>
                    <th>Salary</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pending_posts->num_rows > 0): ?>
                    <?php while($p = $pending_posts->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['title']); ?></strong></td>
                            <td><?= htmlspecialchars($p['student_username']); ?></td>
                            <td><?= htmlspecialchars($p['student_class']); ?></td>
                            <td><?= htmlspecialchars($p['location']); ?></td>
                            <td><?= format_taka($p['monthly_salary']); ?></td>
                            <td><?= date('d M, Y', strtotime($p['created_at'])); ?></td>
                            <td>
                                <form action="admin-dashboard.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="admin_task" value="update_post_status">
                                    <input type="hidden" name="post_id" value="<?= $p['id']; ?>">
                                    <input type="hidden" name="approval_status" value="approved">
                                    <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                </form>
                                <form action="admin-dashboard.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="admin_task" value="update_post_status">
                                    <input type="hidden" name="post_id" value="<?= $p['id']; ?>">
                                    <input type="hidden" name="approval_status" value="rejected">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 16px;">No posts pending approval.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Tutor Applications Moderation</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tuition Post</th>
                    <th>Applicant Tutor</th>
                    <th>Institution</th>
                    <th>Proposed Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pending_apps->num_rows > 0): ?>
                    <?php while($app = $pending_apps->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['tuition_title']); ?></td>
                            <td><strong><?= htmlspecialchars($app['tutor_name']); ?></strong></td>
                            <td><?= htmlspecialchars($app['university_name'] ?? '—'); ?></td>
                            <td><?= format_taka($app['proposed_salary']); ?></td>
                            <td>
                                <form action="actions/application-action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="application_id" value="<?= $app['id']; ?>">
                                    <input type="hidden" name="action" value="shortlist">
                                    <button type="submit" class="btn btn-primary btn-sm">Recommend / Shortlist</button>
                                </form>
                                <form action="actions/application-action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="application_id" value="<?= $app['id']; ?>">
                                    <input type="hidden" name="action" value="admin_reject">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Decline</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 16px;">No tutor applications requiring administrative attention.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Tutor Verification</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tutor</th>
                    <th>Email</th>
                    <th>University</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($teachers_list && $teachers_list->num_rows > 0): ?>
                    <?php while($t = $teachers_list->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['full_name']); ?></strong></td>
                            <td><?= htmlspecialchars($t['email']); ?></td>
                            <td><?= htmlspecialchars($t['university_name'] ?? '—'); ?></td>
                            <td><?= htmlspecialchars($t['department'] ?? '—'); ?></td>
                            <td><?= get_status_badge($t['verification_status']); ?></td>
                            <td>
                                <?php if ($t['verification_status'] !== 'verified'): ?>
                                <form action="admin-dashboard.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="admin_task" value="verify_teacher">
                                    <input type="hidden" name="teacher_profile_id" value="<?= $t['profile_id']; ?>">
                                    <input type="hidden" name="verification_status" value="verified">
                                    <button type="submit" class="btn btn-primary btn-sm">Verify</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($t['verification_status'] !== 'rejected'): ?>
                                <form action="admin-dashboard.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="admin_task" value="verify_teacher">
                                    <input type="hidden" name="teacher_profile_id" value="<?= $t['profile_id']; ?>">
                                    <input type="hidden" name="verification_status" value="rejected">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Reject</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 16px;">No tutor profiles yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3>Registered Users Management</h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users_list->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user['full_name']); ?></strong></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? ''); ?></td>
                        <td><span class="badge badge-shortlisted"><?= ucfirst($user['role']); ?></span></td>
                        <td><?= get_status_badge($user['account_status']); ?></td>
                        <td>
                            <form action="admin-dashboard.php" method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="admin_task" value="toggle_user_status">
                                <input type="hidden" name="target_user_id" value="<?= $user['id']; ?>">
                                <input type="hidden" name="account_status" value="<?= $user['account_status'] === 'active' ? 'suspended' : 'active'; ?>">
                                <button type="submit" class="btn btn-sm <?= $user['account_status'] === 'active' ? 'btn-outline-danger' : 'btn-primary'; ?>">
                                    <?= $user['account_status'] === 'active' ? 'Suspend' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>