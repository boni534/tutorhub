<?php
// tuition-details.php - Detailed post view and tutor application submission form
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<div class='alert alert-danger'>Invalid tuition ID requested.</div>";
    require_once 'includes/footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT t.*, u.full_name as poster_name, u.phone as poster_phone, GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subject_list 
                        FROM tuition_posts t
                        JOIN users u ON t.student_id = u.id
                        LEFT JOIN tuition_subjects s ON t.id = s.tuition_id
                        WHERE t.id = ?
                        GROUP BY t.id");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "<div class='alert alert-danger'>Tuition post not found.</div>";
    require_once 'includes/footer.php';
    exit();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['user_role'] : null;
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

$has_applied = false;
if ($user_role === 'teacher') {
    $chk = $conn->prepare("SELECT id FROM applications WHERE tuition_id = ? AND teacher_id = ?");
    $chk->bind_param("ii", $id, $user_id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) $has_applied = true;
    $chk->close();
}
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 18px;">
        <div>
            <h2><?= htmlspecialchars($post['title']); ?></h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Posted on <?= date('F j, Y', strtotime($post['created_at'])); ?></p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 1.6rem; font-weight: 800; color: var(--secondary);"><?= format_taka($post['monthly_salary']); ?></div>
            <span style="font-size: 0.85rem; color: var(--text-muted);">per month</span>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div><strong>Student Name:</strong> <?= htmlspecialchars($post['student_name']); ?></div>
        <div><strong>Class / Grade:</strong> <?= htmlspecialchars($post['student_class']); ?></div>
        <div><strong>Institution:</strong> <?= htmlspecialchars($post['institution_name'] ?: 'Not stated'); ?></div>
        <div><strong>Required Subjects:</strong> <?= htmlspecialchars($post['subject_list'] ?? 'None specified'); ?></div>
        <div><strong>Tutoring Days:</strong> <?= $post['tutoring_days_per_week']; ?> days/week</div>
        <div><strong>Preferred Days:</strong> <?= htmlspecialchars($post['preferred_days']); ?></div>
        <div><strong>Schedule Time:</strong> <?= htmlspecialchars($post['preferred_time']); ?></div>
        <div><strong>Tutoring Location:</strong> <?= htmlspecialchars($post['location']); ?></div>
        <div><strong>Teaching Method:</strong> <?= htmlspecialchars($post['teaching_method']); ?></div>
        <div><strong>Preferred Gender:</strong> <?= htmlspecialchars($post['preferred_tutor_gender']); ?></div>
    </div>

    <div style="margin-bottom: 24px;">
        <h4>Additional Requirements</h4>
        <p style="margin-top: 6px; color: #334155; line-height: 1.5;"><?= nl2br(htmlspecialchars($post['additional_requirements'] ?: 'None')); ?></p>
    </div>

    <div style="background: #f8fafc; padding: 14px; border-radius: var(--radius); border: 1px dashed var(--border);">
        <?php if ($user_role === 'admin' || $user_id == $post['student_id']): ?>
            <p><strong>Direct Contact:</strong> <?= htmlspecialchars($post['poster_phone']); ?> (Student / Guardian)</p>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 0.875rem;">🔒 <em>Direct contact details are protected and made available once a tutor is officially assigned.</em></p>
        <?php endif; ?>
    </div>
</div>

<?php if ($user_role === 'teacher' && $post['tuition_status'] === 'open' && $post['approval_status'] === 'approved'): ?>
    <div class="card" id="apply">
        <h3>Apply for This Tuition Post</h3>
        <?php if ($has_applied): ?>
            <div class="alert alert-success" style="margin-top: 14px;">You have already submitted an application for this tuition post. Track its progress in your dashboard.</div>
        <?php else: ?>
            <form action="actions/apply-tuition-action.php" method="POST" style="margin-top: 16px;">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="tuition_id" value="<?= $post['id']; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="proposed_salary">Proposed Monthly Salary (৳)</label>
                        <input type="number" step="100" id="proposed_salary" name="proposed_salary" class="form-control" value="<?= $post['monthly_salary']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="available_days">Your Available Days</label>
                        <input type="text" id="available_days" name="available_days" class="form-control" placeholder="e.g. Sat, Mon, Wed, Fri" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="relevant_experience">Relevant Teaching Experience</label>
                    <textarea id="relevant_experience" name="relevant_experience" class="form-control" rows="2" placeholder="Briefly describe your experience teaching this class and subject..."></textarea>
                </div>

                <div class="form-group">
                    <label for="application_message">Application Pitch / Cover Note</label>
                    <textarea id="application_message" name="application_message" class="form-control" rows="4" placeholder="Explain why you are the best match for this student..." required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Application</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>