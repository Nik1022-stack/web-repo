# Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload

## Problem Definition
Create simple PHP JSON API endpoints for events and consume them using JavaScript's modern **Fetch API**. Perform **List**, **Add**, **Update**, and **Delete** operations asynchronously without a full page reload.

---

## Key Analysis & Viva Questions

### 1. Do PHP endpoints return valid JSON responses?
- Yes. Every API response sets `header('Content-Type: application/json; charset=utf-8');` and outputs JSON encoded data using `json_encode()`:
  ```json
  {
      "success": true,
      "message": "Event created successfully!",
      "data": { "id": 6 }
  }
  ```

### 2. Are GET and POST requests handled properly?
- **GET**: Handled for retrieving the event list (`api.php`), searching (`api.php?search=keyword`), and fetching a single event by ID (`api.php?id=1`).
- **POST**: Handled for creating (`?action=create`), updating (`?action=update`), and deleting (`?action=delete`) event records. Supports both JSON body and URL encoded parameters.

### 3. Are loading, success, and error states shown on the frontend?
- **Loading State**: An indicator banner (`#loading-box`) is displayed whenever a network request is pending.
- **Success State**: A green notification banner (`.alert-success`) confirms successful additions, updates, or deletions.
- **Error State**: A red alert banner (`.alert-danger`) informs the user if validation fails or a server error occurs.

### 4. Is input validation performed before database operations?
- Yes. Server-side validation checks that required fields (`title`, `category`, `event_date`, `venue`) are non-empty. If validation fails, the API responds with HTTP **400 Bad Request** and an informative error message.

---

## Extensions

### Intermediate Extension: AJAX Search with Debounce
- Implemented in `index.html` using a 300ms debounce timer on `#search-input`:
  ```javascript
  searchInput.addEventListener('input', (e) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
          fetchEvents(e.target.value, categoryFilter.value);
      }, 300);
  });
  ```
- Prevents redundant API calls while user is typing and updates the table asynchronously.

### Advanced Extension: Response Status Codes & Centralized Error Handling
- Handled through `sendResponse($success, $message, $data, $statusCode)`:
  - `200 OK`: Successful query, update, or deletion.
  - `201 Created`: Successful event creation.
  - `400 Bad Request`: Validation failure or missing parameters.
  - `404 Not Found`: Target event not found.
  - `500 Internal Server Error`: Database query failures.

---

## API Endpoints Reference

| Method | Endpoint / Action | Purpose | Request Body / Params |
| :--- | :--- | :--- | :--- |
| **GET** | `api.php` | Fetch all events | Optional: `?search=...&category=...` |
| **GET** | `api.php?id={id}` | Fetch single event | `id` parameter in URL |
| **POST** | `api.php?action=create` | Add new event | `{ title, category, event_date, venue, status, description }` |
| **POST** | `api.php?action=update` | Update existing event | `{ id, title, category, event_date, venue, status, description }` |
| **POST** | `api.php?action=delete` | Delete event | `{ id }` |

---

## How to Run
1. Start MySQL in XAMPP (port `3306`).
2. Run the PHP server:
   ```bash
   php -S localhost:8097
   ```
3. Open `http://localhost:8097` in your browser.
