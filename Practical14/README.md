# Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log

## Problem Definition
Develop an admin dashboard that displays user statistics, student count, event count, and latest registrations. Restrict administrative access to admin users only and show dynamic menu options based on user role.

---

## Key Analysis & Viva Questions

### 1. Are dashboard values loaded dynamically from the database?
- Yes. Aggregation queries retrieve live counts using SQL:
  ```php
  $user_count = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
  $student_count = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
  $event_count = $conn->query("SELECT COUNT(*) AS c FROM events")->fetch_assoc()['c'];
  $reg_count = $conn->query("SELECT COUNT(*) AS c FROM registrations")->fetch_assoc()['c'];
  ```

### 2. Is access restricted to admin users?
- Yes. Authenticated session check redirects unauthenticated visitors to `login.php`.
- If a logged-in user does not have the `admin` role, an access restriction banner is shown, and the user management table and audit logs are completely hidden.

### 3. Are role-based menus working?
- Yes. The navigation menu array is determined dynamically by the role stored in `$_SESSION['role']`:
  - **Admin**: Shows Dashboard, Users, Registrations, Analytics, and Audit Logs.
  - **Student**: Shows Dashboard and My Registrations.

### 4. Are important admin actions recorded in audit_logs?
- Yes. The `audit_logs` table logs user sign-ins, logouts, and administrative status changes using `addAudit($conn, $user, $action)`.

---

## Extensions

### Intermediate Extension: Active/Inactive Status Toggle
- Admins can click **Toggle Status** next to any user account (`?toggle_user=id`).
- Inverts `Active` $\leftrightarrow$ `Inactive` in MySQL and records the event in the audit log.

### Advanced Extension: Monthly Event Registrations Analytics Chart
- A lightweight, pure-CSS responsive bar chart displays monthly registration trends using SQL aggregation:
  ```sql
  SELECT DATE_FORMAT(registered_at, '%b %Y') AS month, COUNT(*) AS count
  FROM registrations GROUP BY month ORDER BY MIN(registered_at) ASC;
  ```

---

## Demo Credentials

| Role | Username | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` | Full Admin Dashboard & User Management |
| **Student** | `student_aarav` | `student123` | Restricted Dashboard & Student Menu |

---

## How to Run
1. Start MySQL in XAMPP (port `3306`).
2. Run the PHP server:
   ```bash
   php -S localhost:8098
   ```
3. Open `http://localhost:8098/login.php` in your browser.
