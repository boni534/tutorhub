# TutorHub — Home Tutor Management System

A beginner-friendly, secure, procedural PHP and MySQL web application designed for university software engineering capstone and viva presentations.

---

## 1. System Overview
TutorHub bridges the communication gap between students/parents seeking private academic tutors and educators looking for tutoring opportunities. It implements a strict 3-tier Role-Based Access Control (RBAC) model:
- **Students/Parents**: Post tuition needs, track applications, compare candidates, and accept tutors.
- **Teachers/Tutors**: Build a comprehensive CV, search matching tuition posts, and apply with custom pitches and salary proposals.
- **Administrators**: Moderate posts, verify tutor profiles, audit proposals, and maintain platform security.

---

## 2. Requirements & Setup Guide

### Software Needed
- **XAMPP** (PHP 7.4+ or 8.x, Apache, MySQL)
- Any modern web browser

### Step-by-Step Installation
1. Start **XAMPP Control Panel** and click **Start** for both **Apache** and **MySQL**.
2. Extract the `tutorhub` folder into your web root:
   ```text
   C:\xampp\htdocs\tutorhub
   ```
3. Open your browser and navigate to phpMyAdmin:
   ```text
   http://localhost/phpmyadmin/
   ```
4. Click on **Databases**, create a database named `tutorhub_db`, and set collation to `utf8mb4_unicode_ci`.
5. Click **Import**, choose the file `database.sql` from the `tutorhub` folder, and click **Go**.
6. Check database credentials in `config/database.php` (default `root` with no password).
7. Access the application in your browser:
   ```text
   http://localhost/tutorhub/
   ```

---

## 3. Demo Credentials

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@tutorhub.com` | `password123` |
| **Student / Parent** | `student@tutorhub.com` | `password123` |
| **Teacher / Tutor** | `teacher@tutorhub.com` | `password123` |
| **Teacher / Tutor 2** | `nusrat@tutorhub.com` | `password123` |