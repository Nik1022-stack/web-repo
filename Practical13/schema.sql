-- Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload
-- Database Schema and Sample Data

CREATE DATABASE IF NOT EXISTS practical13 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical13;

DROP TABLE IF EXISTS events;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    status ENUM('Open', 'Closed') DEFAULT 'Open',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Seed Data
INSERT INTO events (title, category, event_date, venue, status, description) VALUES
('Tech Fest 2026', 'Technical', '2026-11-15', 'Main Auditorium', 'Open', 'National level coding and robotics competition.'),
('Web Development BootCamp', 'Workshop', '2026-11-22', 'Lab 3, IT Department', 'Open', 'Hands-on session on PHP, REST APIs, and modern frontend tools.'),
('Cyber Security Seminar', 'Seminar', '2026-12-02', 'Conference Hall A', 'Open', 'Expert lecture on ethical hacking and secure application design.'),
('Annual Cultural Fest', 'Cultural', '2026-12-10', 'University Amphitheatre', 'Open', 'Music, dance, and drama performances across all departments.'),
('Inter-College Chess Championship', 'Sports', '2026-12-15', 'Indoor Sports Complex', 'Closed', 'Rapid and blitz chess tournament with trophy awards.');
