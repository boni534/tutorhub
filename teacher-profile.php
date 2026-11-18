<?php
// teacher-profile.php - Profile details and CV management
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('teacher');
$user_id = $_SESSION['user_id'];
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die("CSRF validation failed");
    }

    $university = sanitize($_POST['university_name'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $qualification = sanitize($_POST['qualification'] ?? '');
    $year_semester = sanitize($_POST['current_year_or_semester'] ?? '');
    $experience = (int)($_POST['experience_years'] ?? 0);
    $salary_min = (float)($_POST['expected_salary_min'] ?? 0);
    $salary_max = (float)($_POST['expected_salary_max'] ?? 0);
    $location = sanitize($_POST['preferred_location'] ?? '');
    $teaching_method = sanitize($_POST['teaching_method'] ?? 'Both');
    $bio = sanitize($_POST['bio'] ?? '');

    $upd = $conn->prepare("UPDATE teacher_profiles SET university_name = ?, department = ?, qualification = ?, current_year_or_semester = ?, experience_years = ?, expected_salary_min = ?, expected_salary_max = ?, preferred_location = ?, teaching_method = ?, bio = ? WHERE user_id = ?");
    $upd->bind_param("ssssiddsssi", $university, $department, $qualification, $year_semester, $experience, $salary_min, $salary_max, $location, $teaching_method, $bio, $user_id);
    $upd->execute();
    $upd->close();

    echo "<div class='alert alert-success'>Profile details updated successfully!</div>";
}

$stmt = $conn->prepare("SELECT u.full_name, u.email, u.phone, tp.* FROM users u JOIN teacher_profiles tp ON u.id = tp.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div style="max-width: 800px; margin: 20px auto;">
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Tutor Profile & Credentials</h2>
            <div>Verification: <?= get_status_badge($profile['verification_status']); ?></div>
        </div>

        <form action="teacher-profile.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($profile['full_name']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($profile['email']); ?>" disabled>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="university_name">University / College</label>
                    <input type="text" id="university_name" name="university_name" class="form-control" value="<?= htmlspecialchars($profile['university_name'] ?? ''); ?>" placeholder="e.g. Dhaka University" required>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" class="form-control" value="<?= htmlspecialchars($profile['department'] ?? ''); ?>" placeholder="e.g. CSE" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="qualification">Degree / Qualification</label>
                    <input type="text" id="qualification" name="qualification" class="form-control" value="<?= htmlspecialchars($profile['qualification'] ?? ''); ?>" placeholder="e.g. BSc in CSE">
                </div>
                <div class="form-group">
                    <label for="current_year_or_semester">Current Year / Semester</label>
                    <input type="text" id="current_year_or_semester" name="current_year_or_semester" class="form-control" value="<?= htmlspecialchars($profile['current_year_or_semester'] ?? ''); ?>" placeholder="e.g. 3rd Year / 6th Semester">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="experience_years">Years of Experience</label>
                    <input type="number" id="experience_years" name="experience_years" class="form-control" value="<?= (int)($profile['experience_years'] ?? 0); ?>" min="0">
                </div>
                <div class="form-group">
                    <label for="preferred_location">Preferred Tutoring Area</label>
                    <input type="text" id="preferred_location" name="preferred_location" class="form-control" value="<?= htmlspecialchars($profile['preferred_location'] ?? ''); ?>" placeholder="e.g. Badda, Rampura">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="expected_salary_min">Min Expected Salary (৳)</label>
                    <input type="number" id="expected_salary_min" name="expected_salary_min" class="form-control" value="<?= (float)($profile['expected_salary_min'] ?? 0); ?>">
                </div>
                <div class="form-group">
                    <label for="expected_salary_max">Max Expected Salary (৳)</label>
                    <input type="number" id="expected_salary_max" name="expected_salary_max" class="form-control" value="<?= (float)($profile['expected_salary_max'] ?? 0); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="teaching_method">Teaching Method</label>
                <select id="teaching_method" name="teaching_method" class="form-control">
                    <option value="Home Tutoring" <?= ($profile['teaching_method'] ?? '') === 'Home Tutoring' ? 'selected' : ''; ?>>Home Tutoring</option>
                    <option value="Online Tutoring" <?= ($profile['teaching_method'] ?? '') === 'Online Tutoring' ? 'selected' : ''; ?>>Online Tutoring</option>
                    <option value="Both" <?= ($profile['teaching_method'] ?? '') === 'Both' ? 'selected' : ''; ?>>Both</option>
                </select>
            </div>

            <div class="form-group">
                <label for="bio">Bio & Academic Highlights</label>
                <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Mention your teaching philosophy, top subjects, and major achievements..."><?= htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Profile Changes</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>