# Practical 11: Student Management CRUD with Search and Filter

## 📌 Problem Definition
Create a student management module to add, view, edit, delete, search, and filter student records. All database operations must use **MySQLi prepared statements** and clear success/failure feedback messages.

---

## 🔑 Key Questions / Analysis / Interpretation

### 1. Are SELECT, INSERT, UPDATE, and DELETE operations working?
- **SELECT**: Handled in `index.php` with dynamic MySQLi prepared queries supporting pagination, multi-field search, and filtering by course and status.
- **INSERT**: Handled in `student_action.php` (`action=add`) using `INSERT INTO students (...) VALUES (?, ?, ?, ?, ?, 'student', ?)`, with duplicate email checks and validation.
- **UPDATE**: Handled in `student_action.php` (`action=edit`) using `UPDATE students SET name = ?, email = ?, mobile = ?, course = ?, status = ? WHERE student_id = ?`.
- **DELETE**: 
  - **Permanent Delete**: Executed via `DELETE FROM students WHERE student_id = ?`.
  - **Soft Delete (Advanced Extension)**: Executed via `UPDATE students SET status = 'deleted' WHERE student_id = ?`, keeping student history intact.
- **Clear Messages**: Every operation stores a flash message in `$_SESSION['flash']` (e.g. green for success, red for error, yellow for warning).

### 2. Is search by name/email/course implemented?
- Implemented in `index.php` using prepared statements:
  ```php
  WHERE (name LIKE ? OR email LIKE ? OR course LIKE ?)
  ```
- Combined with dropdown filters for:
  - **Course**: `B.Tech IT`, `B.Tech CE`, `B.Tech CSE`, `B.Tech AI&DS`, etc.
  - **Status**: `Active`, `Inactive`, `Soft Deleted`.

### 3. Are delete operations confirmed before execution?
- Clicking the Delete button opens a dedicated modal dialog (`deleteModal`) displaying the student's name and ID.
- The user is provided with clear choices:
  - **Soft Delete (Recommended)**: Marks status as `deleted` without destroying the record.
  - **Permanent Delete**: Completely deletes the row from MySQL.
  - **Cancel**: Dismisses the dialog safely.

---

## 🚀 Extensions Implemented

### Intermediate Extension: Export Student List to CSV
- File: `export_csv.php`.
- Respects the current search and filter parameters.
- Sends CSV headers (`Content-Type: text/csv; charset=UTF-8`, `Content-Disposition: attachment; filename="students_export_..."`) and streams UTF-8 BOM formatted data for Excel.

### Advanced Extension: Soft Delete with Status Column
- The database schema includes a `status` column (`active`, `inactive`, `deleted`).
- When soft-deleted, status becomes `deleted`.
- Soft-deleted students display a red pill badge in the table.
- A **Restore** button appears for deleted students to easily revert them to `active` status.

---

## 🧪 Seeded Test Dataset
- Over 20 realistic student records seeded with CHARUSAT university emails (`22dit001@charusat.edu.in` to `22dit022@charusat.edu.in`), courses, and phone numbers.

---

## 📸 Required Screenshot Submission Guide

1. **View Records & Search/Filter**:
   - Open `index.php` &rarr; Take screenshot of the student table showing stats, search bar, and records.
   - Enter search term (e.g. `Patel` or `Computer`) and click Filter &rarr; Take screenshot of filtered results.
2. **Add Student (INSERT)**:
   - Click `+ Add Student`, fill modal form, and click Save &rarr; Take screenshot of green success alert.
3. **Edit Student (UPDATE)**:
   - Click the Edit button on a record, modify details in modal, and save &rarr; Take screenshot of success alert and updated values.
4. **Delete Confirmation & Soft Delete (DELETE)**:
   - Click the Delete button &rarr; Take screenshot of the **Confirm Delete Modal** showing confirmation dialog.
   - Click Soft Delete &rarr; Take screenshot showing student marked as *Soft Deleted* with restore button.
5. **CSV Export**:
   - Click `Export CSV` &rarr; Take screenshot of downloaded `.csv` file opened in Excel/Notepad.

---

## 📂 File Architecture

```
Practical-11/
├── db.php             # MySQLi connection & auto-seeder (>= 20 students)
├── index.php          # Main responsive UI with CRUD table, search, filter, pagination & modals
├── student_action.php # MySQLi prepared statement handler for Add, Edit, Delete & Restore
├── get_student.php    # AJAX endpoint returning student record in JSON
├── export_csv.php     # Dynamic CSV export respecting active filters
├── setup_db.php       # Standalone database tester
├── schema.sql         # Standalone SQL schema and 20+ test records dump
└── README.md          # Complete viva answers, evaluation criteria & test guide
```
