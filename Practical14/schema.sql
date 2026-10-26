-- Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log
CREATE DATABASE IF NOT EXISTS practical14 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical14;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- 1. Users Table (with RBAC role and status toggle)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff', 'student') NOT NULL DEFAULT 'student',
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active'
);

-- 2. Students Table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL
);

-- 3. Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL
);

-- 4. Registrations Table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    event_title VARCHAR(100) NOT NULL,
    registered_at DATE NOT NULL
);

-- 5. Audit Logs Table
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50) NOT NULL,
    action VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed Users (Passwords: admin123, student123)
INSERT INTO users (username, password, role, status) VALUES
('admin', 'admin123', 'admin', 'Active'),
('staff_user', 'staff123', 'staff', 'Active'),
('student_aarav', 'student123', 'student', 'Active'),
('student_meera', 'student123', 'student', 'Inactive');

-- Seed Students
INSERT INTO students (name, email, course) VALUES
('Aarav Patel', 'aarav@example.com', 'Computer Science'),
('Meera Sharma', 'meera@example.com', 'Data Science'),
('Rohit Verma', 'rohit@example.com', 'Cyber Security'),
('Sana Iqbal', 'sana@example.com', 'AI & ML');

-- Seed Events
INSERT INTO events (title, event_date) VALUES
('Tech Fest 2026', '2026-11-15'),
('AI & Robotics Workshop', '2026-11-25'),
('Annual Cultural Fest', '2026-12-10');

-- Seed Registrations (Spread across months for analytics chart)
INSERT INTO registrations (student_name, event_title, registered_at) VALUES
('Aarav Patel', 'Tech Fest 2026', '2026-09-10'),
('Meera Sharma', 'Tech Fest 2026', '2026-09-18'),
('Rohit Verma', 'Tech Fest 2026', '2026-09-22'),
('Sana Iqbal', 'AI & Robotics Workshop', '2026-10-05'),
('Aarav Patel', 'AI & Robotics Workshop', '2026-10-15'),
('Karan Singh', 'Annual Cultural Fest', '2026-11-01');

-- Seed Audit Log
INSERT INTO audit_logs (user, action) VALUES
('admin', 'System initialized and seeded.'),
('admin', 'Admin logged in.');
