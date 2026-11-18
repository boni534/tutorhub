-- Run this in phpMyAdmin (SQL tab, database tutorhub_db) if you ALREADY imported database.sql.
-- It resets the demo accounts' password to: password123
UPDATE users SET password_hash = '$2y$10$5UGOLBPke7m.Q.3Svpffq.MjIHvjZod7Xyigen3ogKFPaRBKvyzDW'
WHERE email IN ('admin@tutorhub.com', 'student@tutorhub.com', 'teacher@tutorhub.com', 'nusrat@tutorhub.com');
