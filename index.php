<?php
// index.php - Modern landing homepage
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$tutors_query = "SELECT u.full_name, p.university_name, p.department, p.experience_years, p.expected_salary_min, p.expected_salary_max 
                 FROM teacher_profiles p 
                 JOIN users u ON p.user_id = u.id 
                 WHERE p.verification_status = 'verified' AND u.account_status = 'active' 
                 LIMIT 3";
$featured_tutors = $conn->query($tutors_query);

$tuition_query = "SELECT t.*, GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subject_list 
                  FROM tuition_posts t 
                  LEFT JOIN tuition_subjects s ON t.id = s.tuition_id 
                  WHERE t.approval_status = 'approved' AND t.tuition_status = 'open' 
                  GROUP BY t.id 
                  ORDER BY t.created_at DESC 
                  LIMIT 3";
$recent_tuitions = $conn->query($tuition_query);
?>

<section class="hero">
    <h1>Find the Right Tutor for Your Future</h1>
    <p>Bridging the gap between ambitious students and experienced tutors. Verified educators, personalized learning schedules, and hassle-free arrangements.</p>
    <div style="display: flex; justify-content: center; gap: 14px; margin-bottom: 30px;">
        <a href="create-tuition.php" class="btn btn-primary">Post Tuition Requirement</a>
        <a href="tuition-feed.php" class="btn btn-outline">Browse Tuition Posts</a>
    </div>

    <div class="search-bar-box">
        <form action="tuition-feed.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
            <input type="text" name="class" class="form-control" placeholder="Class / Level (e.g. Class 9)" style="flex: 1; min-width: 150px;">
            <input type="text" name="subject" class="form-control" placeholder="Subject (e.g. Math)" style="flex: 1; min-width: 150px;">
            <input type="text" name="location" class="form-control" placeholder="Area (e.g. Mirpur)" style="flex: 1; min-width: 150px;">
            <button type="submit" class="btn btn-primary">Search Feed</button>
        </form>
    </div>
</section>

<h2 style="text-align: center; margin-bottom: 24px;">How TutorHub Works</h2>
<div class="steps-container">
    <div class="step-card">
        <div class="step-num">1</div>
        <h3>Post Requirements</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 8px;">Students specify subjects, class, weekly days, location, and monthly budget.</p>
    </div>
    <div class="step-card">
        <div class="step-num">2</div>
        <h3>Compare Tutors</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 8px;">Qualified educators review posts and apply. Admins shortlist verified profiles.</p>
    </div>
    <div class="step-card">
        <div class="step-num">3</div>
        <h3>Start Learning</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 8px;">Review credentials, accept the tutor, and confirm your private tutoring journey.</p>
    </div>
</div>

<h2 style="margin: 36px 0 16px;">Verified Featured Tutors</h2>
<div class="grid-cols-4">
    <?php if ($featured_tutors && $featured_tutors->num_rows > 0): ?>
        <?php while($t = $featured_tutors->fetch_assoc()): ?>
            <div class="card" style="margin-bottom: 0;">
                <h3 style="color: var(--primary);"><?= htmlspecialchars($t['full_name']); ?></h3>
                <p style="font-weight: 500; font-size: 0.9rem;"><?= htmlspecialchars($t['university_name']); ?></p>
                <p style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($t['department']); ?></p>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 12px 0;">
                <p style="font-size: 0.85rem;"><strong>Experience:</strong> <?= (int)$t['experience_years']; ?> years</p>
                <p style="font-size: 0.85rem;"><strong>Expected:</strong> <?= format_taka($t['expected_salary_min']); ?>+</p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: var(--text-muted);">No verified tutors available at the moment.</p>
    <?php endif; ?>
</div>

<h2 style="margin: 36px 0 16px;">Latest Tuition Posts</h2>
<div class="grid-cols-4">
    <?php if ($recent_tuitions && $recent_tuitions->num_rows > 0): ?>
        <?php while($post = $recent_tuitions->fetch_assoc()): ?>
            <div class="card" style="margin-bottom: 0;">
                <span class="badge badge-approved" style="margin-bottom: 8px;">Active</span>
                <h4 style="margin-bottom: 8px;"><?= htmlspecialchars($post['title']); ?></h4>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px;"><strong>Class:</strong> <?= htmlspecialchars($post['student_class']); ?></p>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 6px;"><strong>Subjects:</strong> <?= htmlspecialchars($post['subject_list'] ?? 'General'); ?></p>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;"><strong>Location:</strong> <?= htmlspecialchars($post['location']); ?></p>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong style="color: var(--secondary);"><?= format_taka($post['monthly_salary']); ?>/mo</strong>
                    <a href="tuition-details.php?id=<?= $post['id']; ?>" class="btn btn-outline btn-sm">Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="color: var(--text-muted);">No tuition posts published recently.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>