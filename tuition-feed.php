<?php
// tuition-feed.php - Social-style tuition newsfeed with search & filtering
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

$class_filter = sanitize($_GET['class'] ?? '');
$subject_filter = sanitize($_GET['subject'] ?? '');
$location_filter = sanitize($_GET['location'] ?? '');
$method_filter = sanitize($_GET['method'] ?? '');

$sql = "SELECT t.*, GROUP_CONCAT(s.subject_name SEPARATOR ', ') AS subject_list 
        FROM tuition_posts t
        LEFT JOIN tuition_subjects s ON t.id = s.tuition_id
        WHERE t.approval_status = 'approved' AND t.tuition_status = 'open'";

$params = [];
$types = "";

if (!empty($class_filter)) {
    $sql .= " AND t.student_class LIKE ?";
    $params[] = "%" . $class_filter . "%";
    $types .= "s";
}
if (!empty($location_filter)) {
    $sql .= " AND t.location LIKE ?";
    $params[] = "%" . $location_filter . "%";
    $types .= "s";
}
if (!empty($method_filter)) {
    $sql .= " AND t.teaching_method = ?";
    $params[] = $method_filter;
    $types .= "s";
}

$sql .= " GROUP BY t.id";

if (!empty($subject_filter)) {
    $sql .= " HAVING subject_list LIKE ?";
    $params[] = "%" . $subject_filter . "%";
    $types .= "s";
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$feed_posts = $stmt->get_result();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Live Tuition Feed</h2>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student'): ?>
        <a href="create-tuition.php" class="btn btn-primary">+ Post New Tuition</a>
    <?php endif; ?>
</div>

<div class="card" style="padding: 16px; margin-bottom: 24px;">
    <form action="tuition-feed.php" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <input type="text" name="class" value="<?= htmlspecialchars($class_filter); ?>" class="form-control" placeholder="Filter Class..." style="flex: 1; min-width: 140px;">
        <input type="text" name="subject" value="<?= htmlspecialchars($subject_filter); ?>" class="form-control" placeholder="Filter Subject..." style="flex: 1; min-width: 140px;">
        <input type="text" name="location" value="<?= htmlspecialchars($location_filter); ?>" class="form-control" placeholder="Filter Location..." style="flex: 1; min-width: 140px;">
        <select name="method" class="form-control" style="flex: 1; min-width: 140px;">
            <option value="">All Methods</option>
            <option value="Home Tutoring" <?= $method_filter === 'Home Tutoring' ? 'selected' : ''; ?>>Home Tutoring</option>
            <option value="Online Tutoring" <?= $method_filter === 'Online Tutoring' ? 'selected' : ''; ?>>Online Tutoring</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="tuition-feed.php" class="btn btn-outline">Clear</a>
    </form>
</div>

<div style="display: flex; flex-direction: column; gap: 18px;">
    <?php if ($feed_posts->num_rows > 0): ?>
        <?php while($row = $feed_posts->fetch_assoc()): ?>
            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h3 style="color: var(--primary); font-size: 1.25rem; margin-bottom: 4px;"><?= htmlspecialchars($row['title']); ?></h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem;">Posted on <?= date('d M, Y', strtotime($row['created_at'])); ?> • Location: <strong><?= htmlspecialchars($row['location']); ?></strong></p>
                    </div>
                    <span style="font-size: 1.25rem; font-weight: 800; color: var(--secondary);"><?= format_taka($row['monthly_salary']); ?>/mo</span>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-bottom: 14px; font-size: 0.9rem;">
                    <div><strong>Class:</strong> <?= htmlspecialchars($row['student_class']); ?></div>
                    <div><strong>Subjects:</strong> <?= htmlspecialchars($row['subject_list'] ?? 'None specified'); ?></div>
                    <div><strong>Days:</strong> <?= $row['tutoring_days_per_week']; ?> days/week (<?= htmlspecialchars($row['preferred_days']); ?>)</div>
                    <div><strong>Timing:</strong> <?= htmlspecialchars($row['preferred_time']); ?></div>
                    <div><strong>Method:</strong> <?= htmlspecialchars($row['teaching_method']); ?></div>
                    <div><strong>Preferred Gender:</strong> <?= htmlspecialchars($row['preferred_tutor_gender']); ?></div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border); padding-top: 12px;">
                    <a href="tuition-details.php?id=<?= $row['id']; ?>" class="btn btn-outline">View Details</a>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'teacher'): ?>
                        <a href="tuition-details.php?id=<?= $row['id']; ?>#apply" class="btn btn-primary">Apply Now</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="card" style="text-align: center; color: var(--text-muted); padding: 40px;">
            <h3>No open tuition posts matched your criteria</h3>
            <p>Try clearing filters or check back later.</p>
        </div>
    <?php endif; ?>
</div>

<?php 
$stmt->close();
require_once 'includes/footer.php'; 
?>