-- Practical 11: Student Management CRUD with Search and Filter
-- Database Schema and Seed Data

CREATE DATABASE IF NOT EXISTS practical11 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical11;

DROP TABLE IF EXISTS students;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Male',
    course VARCHAR(60) NOT NULL,
    department VARCHAR(60) NOT NULL,
    year_of_study TINYINT UNSIGNED NOT NULL DEFAULT 1,
    gpa DECIMAL(3,2) NOT NULL DEFAULT 3.00,
    status ENUM('Active', 'Inactive', 'Deleted') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_course (course),
    INDEX idx_email (email),
    INDEX idx_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data (25 Realistic Student Records)
INSERT INTO students (student_code, first_name, last_name, email, phone, gender, course, department, year_of_study, gpa, status) VALUES
('STU2026001', 'Aarav', 'Patel', 'aarav.patel@studenthub.edu', '+91 9876543210', 'Male', 'Computer Science', 'Computer Engineering', 3, 3.85, 'Active'),
('STU2026002', 'Meera', 'Sharma', 'meera.sharma@studenthub.edu', '+91 9876543211', 'Female', 'Data Science', 'Computer Engineering', 2, 3.92, 'Active'),
('STU2026003', 'Rohit', 'Verma', 'rohit.verma@studenthub.edu', '+91 9876543212', 'Male', 'Cyber Security', 'Information Technology', 4, 3.65, 'Active'),
('STU2026004', 'Sana', 'Iqbal', 'sana.iqbal@studenthub.edu', '+91 9876543213', 'Female', 'Artificial Intelligence', 'Computer Engineering', 3, 3.98, 'Active'),
('STU2026005', 'Karan', 'Singh', 'karan.singh@studenthub.edu', '+91 9876543214', 'Male', 'Information Technology', 'Information Technology', 1, 3.40, 'Active'),
('STU2026006', 'Diya', 'Joshi', 'diya.joshi@studenthub.edu', '+91 9876543215', 'Female', 'Computer Science', 'Computer Engineering', 2, 3.78, 'Active'),
('STU2026007', 'Aditya', 'Deshmukh', 'aditya.deshmukh@studenthub.edu', '+91 9876543216', 'Male', 'Data Science', 'Computer Engineering', 3, 3.55, 'Inactive'),
('STU2026008', 'Ananya', 'Rao', 'ananya.rao@studenthub.edu', '+91 9876543217', 'Female', 'Cloud Computing', 'Information Technology', 4, 3.88, 'Active'),
('STU2026009', 'Varun', 'Nair', 'varun.nair@studenthub.edu', '+91 9876543218', 'Male', 'Cyber Security', 'Information Technology', 2, 3.25, 'Active'),
('STU2026010', 'Ishita', 'Gupta', 'ishita.gupta@studenthub.edu', '+91 9876543219', 'Female', 'Artificial Intelligence', 'Computer Engineering', 1, 3.70, 'Active'),
('STU2026011', 'Kabir', 'Mehta', 'kabir.mehta@studenthub.edu', '+91 9876543220', 'Male', 'Computer Science', 'Computer Engineering', 3, 3.60, 'Active'),
('STU2026012', 'Rhea', 'Menon', 'rhea.menon@studenthub.edu', '+91 9876543221', 'Female', 'Information Technology', 'Information Technology', 2, 3.82, 'Active'),
('STU2026013', 'Dev', 'Chopra', 'dev.chopra@studenthub.edu', '+91 9876543222', 'Male', 'Data Science', 'Computer Engineering', 4, 3.45, 'Active'),
('STU2026014', 'Pooja', 'Hegde', 'pooja.hegde@studenthub.edu', '+91 9876543223', 'Female', 'Cloud Computing', 'Information Technology', 1, 3.90, 'Active'),
('STU2026015', 'Siddharth', 'Bose', 'siddharth.bose@studenthub.edu', '+91 9876543224', 'Male', 'Computer Science', 'Computer Engineering', 2, 3.15, 'Inactive'),
('STU2026016', 'Tanvi', 'Shah', 'tanvi.shah@studenthub.edu', '+91 9876543225', 'Female', 'Cyber Security', 'Information Technology', 3, 3.75, 'Active'),
('STU2026017', 'Yash', 'Malhotra', 'yash.malhotra@studenthub.edu', '+91 9876543226', 'Male', 'Artificial Intelligence', 'Computer Engineering', 4, 3.95, 'Active'),
('STU2026018', 'Kavya', 'Reddy', 'kavya.reddy@studenthub.edu', '+91 9876543227', 'Female', 'Information Technology', 'Information Technology', 3, 3.62, 'Active'),
('STU2026019', 'Manish', 'Kulkarni', 'manish.kulkarni@studenthub.edu', '+91 9876543228', 'Male', 'Data Science', 'Computer Engineering', 1, 3.30, 'Active'),
('STU2026020', 'Neha', 'Pandey', 'neha.pandey@studenthub.edu', '+91 9876543229', 'Female', 'Computer Science', 'Computer Engineering', 2, 3.89, 'Active'),
('STU2026021', 'Arjun', 'Venkatesh', 'arjun.v@studenthub.edu', '+91 9876543230', 'Male', 'Cyber Security', 'Information Technology', 4, 3.52, 'Active'),
('STU2026022', 'Simran', 'Kaur', 'simran.kaur@studenthub.edu', '+91 9876543231', 'Female', 'Artificial Intelligence', 'Computer Engineering', 2, 3.80, 'Active'),
('STU2026023', 'Pranav', 'Bhat', 'pranav.bhat@studenthub.edu', '+91 9876543232', 'Male', 'Cloud Computing', 'Information Technology', 3, 3.48, 'Inactive'),
('STU2026024', 'Shreya', 'Ghosh', 'shreya.ghosh@studenthub.edu', '+91 9876543233', 'Female', 'Data Science', 'Computer Engineering', 1, 3.94, 'Active'),
('STU2026025', 'Nikhil', 'Saxena', 'nikhil.saxena@studenthub.edu', '+91 9876543234', 'Male', 'Computer Science', 'Computer Engineering', 3, 3.20, 'Deleted');
