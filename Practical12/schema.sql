-- Practical 12: Event Management CRUD with Poster Upload and File Validation
-- Database Schema and Sample Data

CREATE DATABASE IF NOT EXISTS practical12 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical12;

DROP TABLE IF EXISTS event_audit_log;
DROP TABLE IF EXISTS events;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(150) NOT NULL,
    poster VARCHAR(255) NULL,
    status ENUM('Open', 'Closed') DEFAULT 'Open',
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS event_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Seed Data
INSERT INTO events (title, category, event_date, venue, poster, status, description) VALUES
('Tech Fest 2026', 'Technical', '2026-11-15', 'Main Auditorium', NULL, 'Open', 'Annual national-level technical symposium with coding and robotics competitions.'),
('AI & Machine Learning Workshop', 'Workshop', '2026-11-20', 'Seminar Hall B', NULL, 'Open', 'Hands-on practical workshop covering deep learning models and PyTorch basics.'),
('Cultural Night & Dance Fest', 'Cultural', '2026-12-05', 'University Amphitheatre', NULL, 'Open', 'Inter-college musical and dance performances showcasing cultural diversity.'),
('Annual Cricket Tournament', 'Sports', '2026-12-10', 'University Sports Complex', NULL, 'Closed', 'Intra-department 20-over knock-out cricket tournament.'),
('Career Guidance & Placement Seminar', 'Seminar', '2026-12-18', 'Conference Room 1', NULL, 'Open', 'Industry leaders speak about software engineering roles and interview preparation.');

-- Seed Audit Log
INSERT INTO event_audit_log (event_id, action, details) VALUES
(1, 'CREATED', 'Event "Tech Fest 2026" created with status Open.'),
(2, 'CREATED', 'Event "AI & Machine Learning Workshop" created with status Open.'),
(3, 'CREATED', 'Event "Cultural Night & Dance Fest" created with status Open.'),
(4, 'CREATED', 'Event "Annual Cricket Tournament" created with status Closed.'),
(5, 'CREATED', 'Event "Career Guidance & Placement Seminar" created with status Open.');
