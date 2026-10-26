<?php
require __DIR__ . '/db.php';

try {
    $students = fetchStudents($pdo);
    $registrations = fetchRegistrations($pdo);

    $sampleInsert = registerStudentForEvent($pdo, 1, 3, 'Registered');
} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Database Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f7fb;
            color: #222;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            max-width: 1000px;
            margin: auto;
        }
        h1, h2 {
            color: #1a3d6d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #d9dfe8;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #eaf2ff;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: #b00020;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>StudentHub - PDO Connection Test</h1>
        <p><a href="register.php">Create a StudentHub account</a></p>

        <?php if (isset($errorMessage)): ?>
            <p class="error">Database error: <?= htmlspecialchars($errorMessage) ?></p>
        <?php else: ?>
            <p class="success">Connection successful using PDO and prepared statements.</p>

            <?php if ($sampleInsert): ?>
                <p class="success">Sample registration inserted successfully.</p>
            <?php else: ?>
                <p>Sample registration already existed, so no new row was inserted.</p>
            <?php endif; ?>

            <h2>Students</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$student['student_id']) ?></td>
                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['department']) ?></td>
                            <td><?= htmlspecialchars((string)$student['year_of_study']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h2>Registrations</h2>
            <table>
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>Student</th>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $registration): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$registration['registration_id']) ?></td>
                            <td><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></td>
                            <td><?= htmlspecialchars($registration['event_title']) ?></td>
                            <td><?= htmlspecialchars($registration['registration_date']) ?></td>
                            <td><?= htmlspecialchars($registration['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
