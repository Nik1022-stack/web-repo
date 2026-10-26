<?php
session_start();

$baseDir = __DIR__;
$dataDir = $baseDir . DIRECTORY_SEPARATOR . 'data';
$csvFile = $dataDir . DIRECTORY_SEPARATOR . 'records.csv';
$jsonFile = $dataDir . DIRECTORY_SEPARATOR . 'records.json';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

if (!file_exists($csvFile)) {
    $header = ['name', 'email', 'phone', 'course', 'message', 'submitted_at'];
    $handle = fopen($csvFile, 'w');
    if ($handle !== false) {
        fputcsv($handle, $header);
        fclose($handle);
    }
}

if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function sanitizeText($value)
{
    return trim(htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8'));
}

function getStoredRecords(): array
{
    global $jsonFile;

    if (!file_exists($jsonFile)) {
        return [];
    }

    $content = file_get_contents($jsonFile);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

$errors = [];
$successMessage = '';
$formValues = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'course' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrfToken)) {
        $errors[] = 'Invalid CSRF token. Please submit the form again.';
    }

    $formValues['name'] = sanitizeText($_POST['name'] ?? '');
    $formValues['email'] = sanitizeText($_POST['email'] ?? '');
    $formValues['phone'] = sanitizeText($_POST['phone'] ?? '');
    $formValues['course'] = sanitizeText($_POST['course'] ?? '');
    $formValues['message'] = sanitizeText($_POST['message'] ?? '');

    if ($formValues['name'] === '' || strlen($formValues['name']) < 2) {
        $errors[] = 'Name is required and must be at least 2 characters long.';
    }

    if (!filter_var($formValues['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!preg_match('/^[0-9+\-()\s]{10,15}$/', $formValues['phone'])) {
        $errors[] = 'Please enter a valid phone number with 10 to 15 characters.';
    }

    if ($formValues['course'] === '') {
        $errors[] = 'Please select a course.';
    }

    if ($formValues['message'] === '' || strlen($formValues['message']) < 10) {
        $errors[] = 'Message is required and must be at least 10 characters long.';
    }

    if (empty($errors)) {
        $record = [
            'name' => $formValues['name'],
            'email' => $formValues['email'],
            'phone' => $formValues['phone'],
            'course' => $formValues['course'],
            'message' => $formValues['message'],
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        $csvHandle = fopen($csvFile, 'a');
        if ($csvHandle !== false) {
            fputcsv($csvHandle, [
                $record['name'],
                $record['email'],
                $record['phone'],
                $record['course'],
                $record['message'],
                $record['submitted_at'],
            ]);
            fclose($csvHandle);
        }

        $records = getStoredRecords();
        $records[] = $record;
        file_put_contents($jsonFile, json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $successMessage = 'Form submitted successfully. Your record has been stored.';
        $formValues = ['name' => '', 'email' => '', 'phone' => '', 'course' => '', 'message' => ''];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
} else {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = generateCsrfToken();
$storedRecords = getStoredRecords();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        h1 {
            text-align: center;
            color: #1f2937;
        }
        form {
            display: grid;
            gap: 18px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #374151;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        button {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .error-list {
            margin: 0;
            padding-left: 18px;
        }
        .records {
            margin-top: 30px;
            border-top: 2px solid #e5e7eb;
            padding-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Registration Form</h1>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul class="error-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="form-row">
                <div>
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo $formValues['name']; ?>" required>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo $formValues['email']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo $formValues['phone']; ?>" required>
                </div>
                <div>
                    <label for="course">Course</label>
                    <select id="course" name="course" required>
                        <option value="">Select Course</option>
                        <option value="Web Development" <?php echo ($formValues['course'] === 'Web Development') ? 'selected' : ''; ?>>Web Development</option>
                        <option value="Database Management" <?php echo ($formValues['course'] === 'Database Management') ? 'selected' : ''; ?>>Database Management</option>
                        <option value="PHP Programming" <?php echo ($formValues['course'] === 'PHP Programming') ? 'selected' : ''; ?>>PHP Programming</option>
                        <option value="UI/UX Design" <?php echo ($formValues['course'] === 'UI/UX Design') ? 'selected' : ''; ?>>UI/UX Design</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="message">Message</label>
                <textarea id="message" name="message" required><?php echo $formValues['message']; ?></textarea>
            </div>

            <button type="submit">Submit Form</button>
        </form>

        <div class="records">
            <h2>Stored Records</h2>
            <?php if (!empty($storedRecords)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Course</th>
                            <th>Message</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($storedRecords as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['course'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['message'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($record['submitted_at'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No records stored yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
