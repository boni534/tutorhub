<?php
// create-tuition.php - Form for publishing student tuition requirements
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_role('student');
require_once 'includes/header.php';
?>

<div style="max-width: 780px; margin: 20px auto;">
    <div class="card">
        <h2 style="color: var(--primary); margin-bottom: 8px;">Post Tuition Requirement</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">Please provide accurate student details so qualified tutors can submit their offers.</p>

        <form action="actions/create-tuition-action.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label for="title">Tuition Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Need a Mathematics & Physics Tutor for Class 9" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="student_name">Student Name *</label>
                    <input type="text" id="student_name" name="student_name" class="form-control" placeholder="Student's name" required>
                </div>
                <div class="form-group">
                    <label for="student_class">Class / Education Level *</label>
                    <input type="text" id="student_class" name="student_class" class="form-control" placeholder="e.g. Class 9 / HSC 1st Year" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="institution_name">Institution Name (Optional)</label>
                    <input type="text" id="institution_name" name="institution_name" class="form-control" placeholder="School or college name">
                </div>
                <div class="form-group">
                    <label for="location">Location / Area *</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Mirpur-10, Dhaka" required>
                </div>
            </div>

            <div class="form-group">
                <label for="subjects">Subjects Requiring Tutor (Comma separated) *</label>
                <input type="text" id="subjects" name="subjects" class="form-control" placeholder="e.g. Higher Math, Physics, Chemistry" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tutoring_days_per_week">Days per Week *</label>
                    <input type="number" id="tutoring_days_per_week" name="tutoring_days_per_week" class="form-control" min="1" max="7" value="3" required>
                </div>
                <div class="form-group">
                    <label for="preferred_days">Preferred Days *</label>
                    <input type="text" id="preferred_days" name="preferred_days" class="form-control" placeholder="e.g. Saturday, Monday, Wednesday" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="preferred_time">Preferred Tutoring Time *</label>
                    <input type="text" id="preferred_time" name="preferred_time" class="form-control" placeholder="e.g. 5:00 PM - 7:00 PM" required>
                </div>
                <div class="form-group">
                    <label for="monthly_salary">Monthly Salary Budget (৳) *</label>
                    <input type="number" step="100" id="monthly_salary" name="monthly_salary" class="form-control" placeholder="e.g. 6000" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="teaching_method">Teaching Method *</label>
                    <select id="teaching_method" name="teaching_method" class="form-control" required>
                        <option value="Home Tutoring">Home Tutoring</option>
                        <option value="Online Tutoring">Online Tutoring</option>
                        <option value="Both">Both (Flexible)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="preferred_tutor_gender">Preferred Tutor Gender</label>
                    <select id="preferred_tutor_gender" name="preferred_tutor_gender" class="form-control">
                        <option value="Any">Any Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="additional_requirements">Additional Requirements</label>
                <textarea id="additional_requirements" name="additional_requirements" class="form-control" rows="3" placeholder="Specific expectations, syllabus coverage, or tutor qualifications..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Publish Tuition Requirement</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>