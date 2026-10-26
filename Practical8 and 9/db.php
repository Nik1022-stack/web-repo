<?php
declare(strict_types=1);

$host = '127.0.0.1';
$dbname = 'studenthub';
$dbuser = 'root';
$dbpass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $options);
} catch (PDOException $e) {
    throw new RuntimeException('Database connection failed. Please start MySQL in XAMPP and import studenthub.sql first.');
}

function fetchStudents(PDO $pdo): array
{
    $sql = 'SELECT student_id, first_name, last_name, email, department, year_of_study
            FROM students
            ORDER BY student_id ASC';

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function fetchRegistrations(PDO $pdo): array
{
    $sql = 'SELECT r.registration_id, s.first_name, s.last_name, e.title AS event_title,
                   r.registration_date, r.status
            FROM registrations r
            INNER JOIN students s ON r.student_id = s.student_id
            INNER JOIN events e ON r.event_id = e.event_id
            ORDER BY r.registration_id ASC';

    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function registerStudentForEvent(PDO $pdo, int $studentId, int $eventId, string $status = 'Registered'): bool
{
    try {
        $sql = 'INSERT INTO registrations (student_id, event_id, status)
                VALUES (:student_id, :event_id, :status)';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':student_id' => $studentId,
            ':event_id' => $eventId,
            ':status' => $status,
        ]);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            return false;
        }
        throw $e;
    }
}
