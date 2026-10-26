# Practical 11: Student Management CRUD with Search and Filter

## Problem Definition
Create a student management module to **add (INSERT)**, **view (SELECT)**, **edit (UPDATE)**, **delete (DELETE)**, **search**, and **filter** student records. All database operations use **MySQLi prepared statements** with clear success/failure messages.

---

## Key Analysis & Viva Questions

### 1. Are SELECT, INSERT, UPDATE, and DELETE operations working?
- **SELECT**: Retrieves all students or filtered records with `$conn->prepare()` and `$stmt->execute()`.
- **INSERT**: Form submission with `add_student` executes `INSERT INTO students (...) VALUES (?, ?, ...)`.
- **UPDATE**: Clicking **Edit** populates the student's details in the form. Submitting executes `UPDATE students SET ... WHERE id = ?`.
- **DELETE**: Clicking **Delete** executes `DELETE FROM students WHERE id = ?`.

### 2. Is search by name/email/course implemented?
- Yes. The search query uses SQL `LIKE ?` across `first_name`, `last_name`, `email`, and `course` with prepared parameter binding:
  ```php
  $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR course LIKE ?)";
  ```
- Dropdowns for **Course** and **Status** allow filtering alongside search keywords.

### 3. Are delete operations confirmed before execution?
- Yes. Every delete button uses JavaScript confirmation before proceeding:
  ```html
  <a href="index.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete?');">Delete</a>
  ```

---

## Extensions

### Intermediate Extension: Export to CSV
- Clicking **Export to CSV** calls `index.php?export=csv`.
- Uses PHP headers (`Content-Type: text/csv`) and `fputcsv()` to stream the student dataset as a downloadable CSV file.

### Advanced Extension: Soft Delete via Status Column
- Clicking **Soft Delete** sets `status = 'Deleted'` instead of permanently removing the row.
- Soft-deleted records can be viewed under the "Deleted Only" status filter and restored using the **Restore** button (`status = 'Active'`).

---

## Database Setup
1. Database Name: `practical11`
2. Table: `students`
3. Import `schema.sql` into MySQL/phpMyAdmin:
   ```sql
   mysql -u root < schema.sql
   ```
4. Contains 25 seeded student records.

---

## How to Run
1. Start Apache and MySQL in XAMPP.
2. Run the PHP server:
   ```bash
   php -S localhost:8095
   ```
3. Open `http://localhost:8095` in your browser.
