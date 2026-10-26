# Practical 8 and 9: StudentHub Database and Secure Registration

## Problem Statement
Design a database for StudentHub with students, events, and registrations. The system should support student enrollment in multiple events and display event registration data securely using PHP and MySQL.

## ER Model Summary
The database is normalized to the 3rd Normal Form (3NF):

- Students table stores only student-related attributes.
- Events table stores only event-related attributes.
- Registrations table stores the many-to-many relationship between students and events.

### Relationships
- One student can register for many events.
- One event can have many students.
- A registration row connects both entities using foreign keys.

### Primary and Foreign Keys
- `students.student_id` is the primary key.
- `events.event_id` is the primary key.
- `registrations.registration_id` is the primary key.
- `registrations.student_id` is a foreign key referencing `students.student_id`.
- `registrations.event_id` is a foreign key referencing `events.event_id`.
- `UNIQUE (student_id, event_id)` prevents duplicate registrations.

## Database Security and Good Practice
This implementation follows secure database handling practices:

- PDO is used instead of legacy MySQL functions.
- Prepared statements are used for all SQL queries.
- `PDO::ATTR_EMULATE_PREPARES` is disabled.
- MySQL exceptions are handled with `try/catch` blocks.
- The application does not use a production root account; it uses a controlled database user.

## Files Included
- `studenthub.sql` — database schema and seed data.
- `db.php` — PDO database connection and helper functions.
- `index.php` — connection test page and example queries.

## Steps to Run in XAMPP
1. Start Apache and MySQL from the XAMPP Control Panel.
2. Open phpMyAdmin.
3. Create a database named `studenthub`.
4. Import the file `studenthub.sql`.
5. Copy the practical folder to `C:/xampp/htdocs/`.
6. Open the browser and visit: `http://localhost/Prectical8/index.php`

## Expected Output
- A successful database connection message.
- A list of students.
- A list of event registrations.
- A sample insertion into the `registrations` table using a prepared statement.

## Practical Viva Questions
1. What is the purpose of a foreign key?
2. Why is the `registrations` table needed instead of storing event IDs inside students?
3. Why is PDO preferred over MySQL extension in PHP?
4. How do prepared statements protect against SQL injection?
5. What is normalization and why is it important?

## Advanced Extension
The schema also includes a MySQL stored procedure named `GetStudentEventReport()` to retrieve student registration details in a reusable way.

Example usage in phpMyAdmin or MySQL Workbench:

```sql
CALL GetStudentEventReport();
```

This demonstrates the advanced stored-procedure concept mentioned in the practical.

## Practical 9: Secure User Registration

`register.php` adds account registration using MySQLi. It validates the submitted name, username, email, password, and confirmation on the server; the HTML form also provides required fields and matching length/format constraints. Usernames and email addresses are normalized and checked for duplicates, and unique database indexes protect against concurrent duplicate submissions. Passwords are stored only as `password_hash()` output. SQL uses prepared statements, and a CSRF token is checked on every submission. Successful registrations and their audit entries are committed together.

### Setup
1. Start Apache and MySQL in XAMPP.
2. Import `studenthub.sql` into phpMyAdmin as before.
3. Import `registration.sql` into the same MySQL server. It creates the account and audit tables without dropping or changing Practical 8 tables.
4. Open `http://localhost/Prectical8And9/register.php` (adjust the URL to the folder name under `htdocs`).

### Registration test report
| Test | Example | Expected result |
| --- | --- | --- |
| Valid registration | `sam_01`, `sam@example.com`, matching password of 8+ characters | Account and audit row inserted; success message shown |
| Invalid email | `not-an-email` | Rejected; no rows inserted |
| Invalid username | `sam!` | Rejected; no rows inserted |
| Short/mismatched password | Fewer than 8 characters or confirmation differs | Rejected; no rows inserted |
| Duplicate email | Submit an existing email with another username | Rejected; no rows inserted |
| Duplicate username | Submit an existing username with another email | Rejected; no rows inserted |
| Missing/invalid CSRF token | Submit without the current session token | Rejected; no rows inserted |

Use a new email and username for the valid case; repeat it to exercise the duplicate checks. Verify that `app_users.password_hash` contains a hash rather than the submitted password and that each successful registration has one row in `registration_audit_log`.

### Viva questions
1. Why should an application store a password hash instead of a password?
2. Why are duplicate checks backed by unique database constraints?
3. How do prepared statements prevent SQL injection?
4. What does a CSRF token protect against?
