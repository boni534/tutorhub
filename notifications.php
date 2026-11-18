<?php
// notifications.php - Unified activity feed for all user roles
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_logged_in();
$user_id = $_SESSION['user_id'];

$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = {$user_id}");

require_once 'includes/header.php';

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<div style="max-width: 700px; margin: 20px auto;">
    <div class="card">
        <h2>System Notifications</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Stay informed on your tuition posts, application reviews, and approvals.</p>

        <?php if ($notifications->num_rows > 0): ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php while($n = $notifications->fetch_assoc()): ?>
                    <div style="padding: 12px 16px; border-radius: var(--radius); border-left: 4px solid var(--primary); background: #f8fafc; border-top: 1px solid var(--border); border-right: 1px solid var(--border); border-bottom: 1px solid var(--border);">
                        <p style="font-size: 0.95rem; margin-bottom: 4px;"><?= htmlspecialchars($n['message']); ?></p>
                        <small style="color: var(--text-muted);"><?= date('F j, Y, g:i A', strtotime($n['created_at'])); ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--text-muted); padding: 30px;">You have no notifications right now.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
require_once 'includes/footer.php'; 
?>