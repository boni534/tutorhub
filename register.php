<?php
// register.php - User registration view
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>
<div style="max-width: 550px; margin: 30px auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 24px; color: var(--primary);">Create TutorHub Account</h2>
        <form id="registerForm" action="actions/register-action.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Rahim Ahmed" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="name@domain.com" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="017XXXXXXXX" required>
                </div>
            </div>

            <div class="form-group">
                <label for="role">I am registering as:</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="student">Student / Parent (Seeking Tutor)</option>
                    <option value="teacher">Teacher / Tutor (Offering Tuition)</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-type password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Register Account</button>
        </form>
        <p style="text-align: center; margin-top: 18px; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600;">Sign In</a>
        </p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>