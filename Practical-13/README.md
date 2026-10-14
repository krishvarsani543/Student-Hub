# Practical 13: PHP JSON API and AJAX/Fetch-Based CRUD without Page Reload

## 📌 Problem Definition
Develop a RESTful PHP JSON API endpoint and consume it asynchronously on the frontend using the modern **JavaScript Fetch API**. Perform list, add, update, and delete operations asynchronously with **zero full page reloads**, visual state indicators, input validation, debounced search, and standard HTTP response status codes.

---

## 🔑 Key Questions / Analysis / Interpretation

### 1. Do PHP endpoints return valid JSON responses?
- `api.php` sets the response header:
  ```php
  header("Content-Type: application/json; charset=UTF-8");
  ```
- All responses strictly adhere to a unified JSON schema:
  ```json
  {
      "success": true,
      "status": 200,
      "message": "Operation completed successfully.",
      "data": { ... },
      "errors": null,
      "timestamp": "2026-10-13T03:30:00+05:30"
  }
  ```

### 2. Are GET, POST, PUT, and DELETE requests handled properly?
- `api.php` routes actions based on `$_SERVER['REQUEST_METHOD']`:
  - `GET`: Reads list of events (with search & category/status filters) or fetches single record via `?id=X`.
  - `POST`: Parses JSON body (`php://input`) or form data, validates inputs, and inserts a new event record (`201 Created`).
  - `PUT`: Parses JSON body, validates fields, and updates existing event (`200 OK`).
  - `DELETE`: Deletes event by ID without reloading the browser (`200 OK`).
- Supports preflight CORS requests (`OPTIONS` with `204 No Content`).

### 3. Are loading, success, and error states shown on the frontend?
- **Loading State**: An animated spinner overlay (`#loadingOverlay`) displays whenever an API request is pending. Action buttons display spinning icons with *"Saving..."* and are disabled during in-flight requests.
- **Success State**: Floating green toast notifications (`.toast-success`) slide in from top-right upon successful addition, modification, or deletion. The table and metrics update immediately.
- **Error State**: Red toast notifications (`.toast-danger`) and inline form highlights (`.is-invalid` with `.invalid-feedback`) appear if server-side validation fails or network errors occur.

### 4. Is input validation performed before database operations?
- Rigorous server-side validation is performed before executing SQL queries:
  - `event_name`: Required, minimum 3 characters.
  - `category`: Must belong to allowed whitelist (`Technical`, `Workshop`, `Cultural`, `Sports`, `Seminar`).
  - `event_date`: Must be valid `YYYY-MM-DD` date.
  - `venue`: Required non-empty string.
  - `status`: Restricted to `open` or `closed`.
- If validation fails, `api.php` returns HTTP status **`422 Unprocessable Entity`** with a structured `errors` mapping object.

---

## 🚀 Extensions Implemented

### Intermediate Extension: AJAX Search with Debounce
- In `index.php`, a 350ms debounce timer is attached to `#searchInput`.
- As the user types, rapid keystrokes reset the timer while displaying a subtle search indicator (`#searchSpinner`).
- The API call triggers only after typing pauses for 350ms, saving server bandwidth and preventing race conditions.

### Advanced Extension: Standard HTTP Status Codes & Centralized Error Handling
- Implemented standard REST status codes:
  - `200 OK`: Successful fetch, update, or deletion.
  - `201 Created`: Resource successfully created.
  - `400 Bad Request`: Malformed ID or missing parameters.
  - `404 Not Found`: Event ID does not exist in database.
  - `422 Unprocessable Entity`: Form validation errors.
  - `500 Internal Server Error`: Server exception.
- Centralized exception handler:
  ```php
  set_exception_handler(function (Throwable $e) {
      sendJsonResponse(false, 500, "Internal Server Error: " . $e->getMessage());
  });
  ```
- Includes a live **Network Activity Bar** at the bottom of the UI logging method, status, URL, and latency in ms!

---

## 📡 REST API Endpoint Documentation

| Method | Endpoint | Description | Status Code |
| :--- | :--- | :--- | :--- |
| **GET** | `/api.php` | List all events (supports `?search=`, `?category=`, `?status=`) | `200 OK` |
| **GET** | `/api.php?id={id}` | Retrieve single event details | `200 OK` / `404 Not Found` |
| **POST** | `/api.php` | Create a new event record | `201 Created` / `422 Unproc.` |
| **PUT** | `/api.php?id={id}` | Update existing event record | `200 OK` / `422 Unproc.` |
| **DELETE** | `/api.php?id={id}` | Permanently delete an event | `200 OK` / `404 Not Found` |

---

## 📸 Post Laboratory Screenshot Guide (Browser Network Tab)

1. **GET Request & List View**:
   - Open Developer Tools (`F12` &rarr; **Network** tab &rarr; filter by **Fetch/XHR**).
   - Load `index.php` &rarr; observe `api.php` `GET` request returning `200 OK` with JSON preview.
2. **Debounced Search**:
   - Type in the search box &rarr; observe in Network tab that requests fire only after pausing typing.
3. **POST Request (Create Event)**:
   - Click `+ Add Event`, fill modal, and click Save.
   - Capture Network tab showing `api.php` `POST` request with status `201 Created` and green toast.
4. **Validation Error (HTTP 422)**:
   - Clear event title in modal and submit &rarr; observe `POST` response with status `422 Unprocessable Entity` and field error messages.
5. **PUT Request (Edit Event)**:
   - Edit an event &rarr; capture `api.php?id=...` `PUT` request with status `200 OK`.
6. **DELETE Request**:
   - Delete an event via modal &rarr; capture `api.php?id=...` `DELETE` request with status `200 OK` and table updating instantly.

---

## 📂 File Architecture

```
Practical-13/
├── db.php             # PDO database connection & schema bootstrapping
├── api.php            # RESTful JSON API with status codes & centralized error handling
├── index.php          # SPA frontend consuming Fetch API with zero full page reload
├── setup_db.php       # Standalone database tester
├── schema.sql         # Standalone SQL dump
└── README.md          # Full API specification, viva answers & screenshot guide
```
