-- Practical 15: StudentHub Final Integration Portal
CREATE DATABASE IF NOT EXISTS studenthub_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub_portal;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- 1. Users Table (RBAC)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'
);

-- 2. Students Table (CRUD & Search/Filter)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    course VARCHAR(60) NOT NULL,
    status ENUM('Active', 'Inactive', 'Deleted') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Events Table (CRUD & Poster Upload)
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    poster VARCHAR(255) NULL,
    status ENUM('Open', 'Closed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Data
INSERT INTO users (username, password, role, status) VALUES
('admin', 'admin123', 'admin', 'Active'),
('student_aarav', 'student123', 'student', 'Active');

INSERT INTO students (student_code, name, email, course, status) VALUES
('STU2026001', 'Aarav Patel', 'aarav.patel@studenthub.edu', 'Computer Science', 'Active'),
('STU2026002', 'Meera Sharma', 'meera.sharma@studenthub.edu', 'Data Science', 'Active'),
('STU2026003', 'Rohit Verma', 'rohit.verma@studenthub.edu', 'Cyber Security', 'Active'),
('STU2026004', 'Sana Iqbal', 'sana.iqbal@studenthub.edu', 'Artificial Intelligence', 'Active');

INSERT INTO events (title, category, event_date, venue, poster, status) VALUES
('Annual Tech Fest 2026', 'Technical', '2026-11-15', 'Main Auditorium', NULL, 'Open'),
('AI & Deep Learning Workshop', 'Workshop', '2026-11-25', 'Lab 3, IT Dept', NULL, 'Open'),
('Inter-College Cultural Night', 'Cultural', '2026-12-10', 'University Amphitheatre', NULL, 'Open');

INSERT INTO audit_logs (user, action) VALUES
('admin', 'StudentHub integrated database seeded and verified.');
