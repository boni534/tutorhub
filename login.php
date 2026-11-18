<?php
// login.php - User authentication gateway
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>
<div style="max-width: 440px; margin: 40px auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 24px; color: var(--primary);">Sign In to TutorHub</h2>
        <form action="actions/login-action.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="example@domain.com" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Sign In</button>
        </form>
        <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600;">Register here</a>
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>