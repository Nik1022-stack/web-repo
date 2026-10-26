CREATE DATABASE IF NOT EXISTS practical10 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE practical10;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL,
    last_login DATETIME NULL,
    remember_selector CHAR(32) NULL UNIQUE,
    remember_hash CHAR(64) NULL,
    remember_expires DATETIME NULL
);

INSERT INTO users (username, password_hash, role) VALUES
('admin', '$2y$10$W/qM1DnZDtOGcw6WwvICIeY/CJtabHs7OhT7OlhTW79T7gAnZ5xly', 'admin'),
('student', '$2y$10$cLu6afL/JAARKJO4oUpt4..H8ONZ5IagMXAJRsGKC8/JDU7GpLkuK', 'student')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role);