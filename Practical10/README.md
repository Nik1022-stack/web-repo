# Practical 10: Secure Login

Import `schema.sql` in phpMyAdmin, then open `login.php` through XAMPP. Demo accounts: `admin` / `Admin@12345` and `student` / `Student@12345`.

Sessions use HttpOnly/SameSite cookies, rotate the session ID at login, and expire after 15 minutes idle. Role checks protect each dashboard. Remember-me uses a random validator stored only as a SHA-256 hash and rotates on use. Logout clears the session and remember token.

Test failed login, both role dashboards, opening the other role's page directly, sign out, inactivity timeout, and remember-me. Change demo passwords before deployment; use HTTPS so cookies are Secure.