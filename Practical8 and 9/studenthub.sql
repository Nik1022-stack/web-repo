CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;

CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    department VARCHAR(60) NOT NULL,
    year_of_study INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(120) NOT NULL,
    organizer VARCHAR(80) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL DEFAULT 'Registered',
    CONSTRAINT fk_reg_student FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id)
        REFERENCES events(event_id)
        ON DELETE CASCADE,
    CONSTRAINT uq_registration UNIQUE (student_id, event_id)
);

INSERT INTO students (first_name, last_name, email, phone, department, year_of_study)
VALUES
('Aarav', 'Patel', 'aarav.patel@studenthub.edu', '9876543210', 'Computer Science', 2),
('Meera', 'Sharma', 'meera.sharma@studenthub.edu', '9988776655', 'Information Technology', 3),
('Rohit', 'Verma', 'rohit.verma@studenthub.edu', '9123456780', 'Electronics', 1),
('Sana', 'Iqbal', 'sana.iqbal@studenthub.edu', '9765432109', 'Mechanical', 2),
('Karan', 'Singh', 'karan.singh@studenthub.edu', '9898989898', 'Civil', 4);

INSERT INTO events (title, event_date, venue, organizer, description)
VALUES
('Tech Fest 2026', '2026-10-05', 'Main Auditorium', 'Student Council', 'Annual technology festival with coding and project exhibitions.'),
('Career Guidance Seminar', '2026-10-12', 'Conference Hall', 'Placement Cell', 'Seminar on campus placements and skill development.'),
('Cultural Night', '2026-10-18', 'Open Stage', 'Cultural Committee', 'Performances, dance, drama and music events.'),
('Hackathon Bootcamp', '2026-10-22', 'Innovation Lab', 'Computer Science Dept.', 'Hands-on coding challenge and team-building workshop.');

INSERT INTO registrations (student_id, event_id, status)
VALUES
(1, 1, 'Registered'),
(1, 2, 'Confirmed'),
(2, 1, 'Registered'),
(2, 3, 'Confirmed'),
(3, 4, 'Registered'),
(4, 2, 'Confirmed'),
(5, 3, 'Registered');

DELIMITER //
CREATE PROCEDURE GetStudentEventReport()
BEGIN
    SELECT s.student_id, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
           e.title AS event_title, r.status, r.registration_date
    FROM registrations r
    INNER JOIN students s ON r.student_id = s.student_id
    INNER JOIN events e ON r.event_id = e.event_id
    ORDER BY s.student_id, e.event_id;
END //
DELIMITER ;

SELECT 'StudentHub schema created successfully.' AS status;
