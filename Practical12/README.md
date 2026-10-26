# Practical 12: Event Management CRUD with Poster Upload and File Validation

## Problem Definition
Develop event management functionality for admin users. Admin can **add**, **view**, **update**, and **delete** events, and **upload event posters** with strict file type and file size validation using PHP and MySQLi.

---

## Key Analysis & Viva Questions

### 1. Are add, update, and delete functionalities implemented correctly?
- **Add (INSERT)**: Form submits title, category, date, venue, status, description, and an optional poster file using `multipart/form-data`.
- **Update (UPDATE)**: Populates the form when clicking **Edit**. Keeps the existing poster if a new one is not uploaded, or safely replaces the old poster file with the new one.
- **Delete (DELETE)**: Deletes the event record from MySQL and removes the physical poster image file from the `uploads/` directory via `unlink()`.

### 2. Is the UI linked with the database correctly?
- Yes. Event records are retrieved using parameterized MySQLi queries (`$conn->prepare()`, `$stmt->execute()`) and displayed in a formatted responsive table with thumbnails and status badges.

### 3. Are poster uploads validated for type and size?
Yes, using three strict server-side validation layers:
1. **File Size Check**: Enforces a maximum limit of **2MB** (`$file['size'] <= 2 * 1024 * 1024`).
2. **MIME Type Inspection**: Uses PHP's `finfo_file(FILEINFO_MIME_TYPE)` to verify the actual file content (only `image/jpeg`, `image/png`, `image/webp`).
3. **Extension Whitelist**: Verifies the extension against `['jpg', 'jpeg', 'png', 'webp']`.

### 4. Are success and failure messages displayed?
- Yes. Clear feedback banners (`alert-success` and `alert-danger`) are displayed for validation errors, successful inserts, updates, file upload failures, and deletions.

---

## Extensions

### Intermediate Extension: Category & Status Filter
- Dropdown filters for **Category** (`Technical`, `Workshop`, `Cultural`, `Sports`, `Seminar`) and **Status** (`Open`, `Closed`).
- Allows filtering scheduled events alongside keyword search (title, venue, description).

### Advanced Extension: Image Renaming, Upload Directory Protection & Audit Log
1. **Safe Image Renaming**: Files are renamed with unique hashes (`poster_timestamp_random.ext`) to avoid filename collisions and prevent directory traversal.
2. **Upload Directory Protection**: An `.htaccess` file inside `uploads/` blocks script execution (disallowing `.php`, `.cgi`, `.py` from executing).
3. **Event Audit Log**: All state changes (`CREATED`, `UPDATED`, `DELETED`) are tracked in the `event_audit_log` database table with timestamps.

---

## Database Setup
1. Database Name: `practical12`
2. Tables: `events`, `event_audit_log`
3. Import `schema.sql`:
   ```bash
   mysql -u root < schema.sql
   ```

---

## How to Run
1. Start Apache and MySQL in XAMPP.
2. Run the PHP server:
   ```bash
   php -S localhost:8096
   ```
3. Open `http://localhost:8096` in your browser.
