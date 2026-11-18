-- TutorHub Database Schema & Sample Data
-- Database Name: tutorhub_db

CREATE DATABASE IF NOT EXISTS `tutorhub_db1` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tutorhub_db1`;

DROP TABLE IF EXISTS `admin_actions`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `tutor_assignments`;
DROP TABLE IF EXISTS `applications`;
DROP TABLE IF EXISTS `tuition_subjects`;
DROP TABLE IF EXISTS `tuition_posts`;
DROP TABLE IF EXISTS `teacher_availability`;
DROP TABLE IF EXISTS `teacher_subjects`;
DROP TABLE IF EXISTS `teacher_profiles`;
DROP TABLE IF EXISTS `student_profiles`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'teacher', 'admin') NOT NULL,
  `account_status` ENUM('active', 'suspended') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Student Profiles
CREATE TABLE `student_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `institution_name` VARCHAR(150),
  `preferred_location` VARCHAR(100),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Teacher Profiles
CREATE TABLE `teacher_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `university_name` VARCHAR(150),
  `department` VARCHAR(100),
  `qualification` VARCHAR(100),
  `current_year_or_semester` VARCHAR(50),
  `graduation_year` INT NULL,
  `experience_years` INT DEFAULT 0,
  `bio` TEXT,
  `expected_salary_min` DECIMAL(10,2) DEFAULT 0.00,
  `expected_salary_max` DECIMAL(10,2) DEFAULT 0.00,
  `preferred_location` VARCHAR(100),
  `teaching_method` ENUM('Home Tutoring', 'Online Tutoring', 'Both') DEFAULT 'Both',
  `verification_status` ENUM('unverified', 'verified', 'rejected') DEFAULT 'unverified',
  `profile_photo` VARCHAR(255) DEFAULT 'default-avatar.png',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Teacher Subjects
CREATE TABLE `teacher_subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  `class_level` VARCHAR(50) NOT NULL,
  FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Teacher Availability
CREATE TABLE `teacher_availability` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `teacher_id` INT NOT NULL,
  `available_day` VARCHAR(20) NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Tuition Posts
CREATE TABLE `tuition_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `student_name` VARCHAR(100) NOT NULL,
  `student_class` VARCHAR(50) NOT NULL,
  `institution_name` VARCHAR(150),
  `location` VARCHAR(100) NOT NULL,
  `tutoring_days_per_week` INT NOT NULL,
  `preferred_days` VARCHAR(150) NOT NULL,
  `preferred_time` VARCHAR(100) NOT NULL,
  `teaching_method` ENUM('Home Tutoring', 'Online Tutoring', 'Both') DEFAULT 'Home Tutoring',
  `monthly_salary` DECIMAL(10,2) NOT NULL,
  `preferred_tutor_gender` ENUM('Any', 'Male', 'Female') DEFAULT 'Any',
  `additional_requirements` TEXT,
  `approval_status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `tuition_status` ENUM('open', 'assigned', 'closed') DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Tuition Subjects
CREATE TABLE `tuition_subjects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tuition_id` INT NOT NULL,
  `subject_name` VARCHAR(100) NOT NULL,
  FOREIGN KEY (`tuition_id`) REFERENCES `tuition_posts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Applications
CREATE TABLE `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tuition_id` INT NOT NULL,
  `teacher_id` INT NOT NULL,
  `application_message` TEXT NOT NULL,
  `relevant_experience` TEXT,
  `proposed_salary` DECIMAL(10,2) NOT NULL,
  `available_days` VARCHAR(150) NOT NULL,
  `application_status` ENUM('Pending Review', 'Shortlisted', 'Approved', 'Rejected', 'Withdrawn') DEFAULT 'Pending Review',
  `admin_note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_tuition_teacher` (`tuition_id`, `teacher_id`),
  FOREIGN KEY (`tuition_id`) REFERENCES `tuition_posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Tutor Assignments
CREATE TABLE `tutor_assignments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tuition_id` INT NOT NULL,
  `teacher_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `assigned_by` INT NOT NULL,
  `agreed_monthly_salary` DECIMAL(10,2) NOT NULL,
  `assignment_status` ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tuition_id`) REFERENCES `tuition_posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Notifications
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `notification_type` VARCHAR(50) NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 11. Admin Actions (Audit Log)
CREATE TABLE `admin_actions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `action_type` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(50) NOT NULL,
  `target_id` INT NOT NULL,
  `action_note` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample Seed Data (Password: password123)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `account_status`) VALUES
(1, 'System Administrator', 'admin@tutorhub.com', '01700000001', '$2y$10$5UGOLBPke7m.Q.3Svpffq.MjIHvjZod7Xyigen3ogKFPaRBKvyzDW', 'admin', 'active'),
(2, 'Tanvir Hasan (Parent)', 'student@tutorhub.com', '01800000002', '$2y$10$5UGOLBPke7m.Q.3Svpffq.MjIHvjZod7Xyigen3ogKFPaRBKvyzDW', 'student', 'active'),
(3, 'Rahim Ahmed (Tutor)', 'teacher@tutorhub.com', '01900000003', '$2y$10$5UGOLBPke7m.Q.3Svpffq.MjIHvjZod7Xyigen3ogKFPaRBKvyzDW', 'teacher', 'active'),
(4, 'Nusrat Jahan (Tutor)', 'nusrat@tutorhub.com', '01600000004', '$2y$10$5UGOLBPke7m.Q.3Svpffq.MjIHvjZod7Xyigen3ogKFPaRBKvyzDW', 'teacher', 'active');

INSERT INTO `student_profiles` (`id`, `user_id`, `institution_name`, `preferred_location`) VALUES
(1, 2, 'Viqarunnisa Noon School & College', 'Mirpur, Dhaka');

INSERT INTO `teacher_profiles` (`id`, `user_id`, `university_name`, `department`, `qualification`, `current_year_or_semester`, `graduation_year`, `experience_years`, `bio`, `expected_salary_min`, `expected_salary_max`, `preferred_location`, `teaching_method`, `verification_status`) VALUES
(1, 3, 'Dhaka University', 'CSE', 'BSc in CSE', 'Final Year', 2026, 3, 'Passionate tutor with strong math & coding foundation.', 5000.00, 8000.00, 'Mirpur, Dhaka', 'Home Tutoring', 'verified'),
(2, 4, 'BUET', 'EEE', 'BSc in EEE', '3rd Year', 2027, 2, 'Experienced in teaching O-Level and SSC science batches.', 6000.00, 9000.00, 'Dhanmondi, Dhaka', 'Both', 'verified');

INSERT INTO `teacher_subjects` (`id`, `teacher_id`, `subject_name`, `class_level`) VALUES
(1, 1, 'Mathematics', 'Class 9-10'),
(2, 1, 'Physics', 'Class 9-10'),
(3, 2, 'Chemistry', 'Class 9-12');

INSERT INTO `tuition_posts` (`id`, `student_id`, `title`, `student_name`, `student_class`, `institution_name`, `location`, `tutoring_days_per_week`, `preferred_days`, `preferred_time`, `teaching_method`, `monthly_salary`, `preferred_tutor_gender`, `additional_requirements`, `approval_status`, `tuition_status`) VALUES
(1, 2, 'Need a Mathematics & Physics Tutor for Class 9', 'Saadman Hasan', 'Class 9', 'Monipur High School', 'Mirpur, Dhaka', 4, 'Sat, Mon, Wed, Fri', '5:00 PM - 7:00 PM', 'Home Tutoring', 6000.00, 'Any', 'Must be strong in Higher Mathematics fundamentals.', 'approved', 'open'),
(2, 2, 'Urgent: English & ICT Tutor for HSC Exam', 'Ayesha Hasan', 'HSC 2nd Year', 'Mirpur Girls Ideal College', 'Mirpur-10, Dhaka', 3, 'Sun, Tue, Thu', '6:30 PM - 8:00 PM', 'Home Tutoring', 5500.00, 'Female', 'Focus on test papers and grammatical foundations.', 'pending', 'open');

INSERT INTO `tuition_subjects` (`id`, `tuition_id`, `subject_name`) VALUES
(1, 1, 'Mathematics'),
(2, 1, 'Physics'),
(3, 2, 'English'),
(4, 2, 'ICT');

INSERT INTO `applications` (`id`, `tuition_id`, `teacher_id`, `application_message`, `relevant_experience`, `proposed_salary`, `available_days`, `application_status`) VALUES
(1, 1, 3, 'I have been mentoring Class 9 students from Monipur High School for 2 years with top GPA results.', '3 years private tuition', 6000.00, 'Sat, Mon, Wed, Fri', 'Pending Review');