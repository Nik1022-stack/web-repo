# Practical 15: Final Integration, Testing, Documentation, GitHub Submission, and Local Deployment

## 1. Problem Definition
Integrate all developed modules into a unified, deployable **StudentHub Portal**. Verify end-to-end navigation, responsiveness, authentication, role-based access control (RBAC), database operations, security validations, and JSON API functionality. Document the installation and provide an Apache Virtual Host configuration for local deployment.

---

## 2. Key Analysis & Viva Questions

### Q1: Are all pages properly linked and navigable?
- Yes. The top navigation bar links to all core sections: **Overview**, **Students**, **Events**, **Deployment & Checklists**, the **JSON API** endpoint (`api.php`), and **Logout**.

### Q2: Are sessions, roles, validations, and database operations working end-to-end?
- **Sessions & Roles**: Users authenticate via `login.php`. Admin accounts have full read/write privileges (add/delete students and events), while student accounts have restricted view-only access.
- **Validations**: All required fields and file uploads (size $\le$ 2MB, MIME type check) are validated server-side.
- **Database Operations**: MySQLi prepared statements prevent SQL injection across all INSERT, SELECT, and DELETE operations.

### Q3: Are security and accessibility checklists completed?
- **Security Checklist**:
  1. Parameterized queries for SQL injection prevention.
  2. Output escaping via `htmlspecialchars()` for XSS protection.
  3. File upload restrictions (extensions, sizes, and `.htaccess` execution blocking).
  4. Role-based server-side privilege checks.
- **Accessibility Checklist (WCAG 2.1)**:
  1. Semantic HTML elements (`<header>`, `<nav>`, `<table>`, `<label>`).
  2. High-contrast typography for readability.
  3. Media alternative labels and form input associations.

### Q4: Is project documentation sufficient for installation and testing?
- Yes. Clear step-by-step instructions for database import, development server setup, and Apache Virtual Host configuration are provided below.

### Q5: Is the project repository clean and properly committed?
- Yes. Each practical is contained in its own modular folder, with clean separation of database scripts, stylesheets, and documentation.

---

## 3. Extensions

### Intermediate Extension: Local Apache Virtual Host Setup
Configure a custom domain like `http://studenthub.local` in XAMPP:

1. Open `C:\xampp\apache\conf\extra\httpd-vhosts.conf` and append:
   ```apache
   <VirtualHost *:80>
       ServerName studenthub.local
       DocumentRoot "C:/Users/jikad/OneDrive/Desktop/WDF precticals/Prectical15"
       <Directory "C:/Users/jikad/OneDrive/Desktop/WDF precticals/Prectical15">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
2. Open `C:\Windows\System32\drivers\etc\hosts` as Administrator and add:
   ```
   127.0.0.1 studenthub.local
   ```
3. Restart Apache in the XAMPP Control Panel and visit **`http://studenthub.local`**.

### Advanced Extension: Production Deployment Notes
- **Environment Separation**: Replace root database credentials with an environment configuration (`.env` or dedicated config file).
- **HTTPS Enforcement**: Enforce SSL/TLS certificates and set `session.cookie_secure = 1`.
- **Static Asset Caching**: Offload posters and CSS assets to a CDN or web server cache headers.

---

## 4. Test Report & Demo Credentials

| Role | Username | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `admin123` | Full access: Add/Delete Students, Add/Delete Events with Posters, Audit Logs |
| **Student** | `student_aarav` | `student123` | Restricted access: View overview, view students, view events |

---

## 5. How to Run Locally

### Step 1: Start MySQL in XAMPP
Ensure MySQL is active on port `3306`.

### Step 2: Import Database Schema
```bash
mysql -u root < schema.sql
```

### Step 3: Start PHP Development Server
```bash
php -S localhost:8099
```

### Step 4: Open in Web Browser
Navigate to: **`http://localhost:8099/login.php`**
