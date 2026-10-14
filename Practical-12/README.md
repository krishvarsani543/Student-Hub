# Practical 12: Event Management CRUD with Poster Upload and File Validation

## 📌 Problem Definition
Develop event management functionality for admin users. Admin can add, view, update, delete, and upload event posters with strict file type and file size validation.

---

## 🔑 Key Questions / Analysis / Interpretation

### 1. Are add, update, and delete functionalities implemented correctly?
- **Add**: Implemented in `event_action.php` (`action=add`) using PDO prepared statements to insert title, category, date, time, venue, status, description, and uploaded poster filename.
- **Update**: Implemented in `event_action.php` (`action=edit`) to modify event details. If a new poster is uploaded, the existing poster is automatically replaced and deleted from disk to prevent storage bloat.
- **Delete**: Implemented in `event_action.php` (`action=delete`) with confirmation dialog. When an event is removed, its associated poster file is also deleted from disk.

### 2. Is the UI linked with the database correctly?
- The UI in `index.php` dynamically loads all events from MySQL.
- Search queries and dropdown filters (Category & Open/Closed status) are bound to prepared statements.
- Poster images are previewed dynamically with thumbnail and enlarged modal views.

### 3. Are poster uploads validated for type and size?
- Validation is implemented in `upload_helper.php` with 4 layers of security:
  1. **File Size Validation**: Strict check for files $\le 2\text{ MB}$ (`2097152 bytes`). Files $> 2\text{ MB}$ are rejected with clear error: *"File size exceeds 2MB limit."*
  2. **Extension Whitelist**: Only `.jpg`, `.jpeg`, `.png`, and `.webp` extensions are allowed.
  3. **Server-Side MIME Verification**: Uses PHP `finfo_file(finfo_open(FILEINFO_MIME_TYPE), ...)` to verify real file bytes (`image/jpeg`, `image/png`, `image/webp`), preventing extension spoofing.
  4. **Image Integrity Check**: Validated using `getimagesize()` to block corrupted or disguised PHP/executable polyglot files.

### 4. Are success and failure messages displayed?
- A session-based flash messaging system displays prominent colored alert banners:
  - 🟢 **Success**: Green alert on successful creation, update, or deletion.
  - 🔴 **Danger**: Red alert if file exceeds 2MB, invalid MIME type, or database error.
  - 🟡 **Warning**: Yellow alert on event deletion or status changes.

---

## 🚀 Extensions Implemented

### Intermediate Extension: Event Category & Open/Closed Filter
- Events categorized as: `Technical`, `Workshop`, `Cultural`, `Sports`, and `Seminar`.
- Quick filters allow viewing events by specific category and registration status (`Open` vs `Closed`).

### Advanced Extension: Security & Audit Logging
1. **Cryptographic Image Renaming**:
   - Files are renamed to `poster_YYYYMMDD_HHMMSS_[random_12_hex].[ext]`. Original user filenames are never executed or stored directly.
2. **Upload Directory Protection**:
   - The `uploads/posters/` folder includes an `.htaccess` rule that blocks PHP script execution (`Require all denied` for script extensions) and disables directory indexing.
3. **Audit Log for Event Changes**:
   - Every `CREATE`, `UPDATE`, `DELETE`, and `STATUS_CHANGE` action is logged into table `event_audit_logs` with event ID, action details, timestamp, user, and IP address.
   - An interactive "Audit Logs" modal is available in the admin UI.

---

## 📸 Required Screenshot Submission Guide

1. **Event List & Posters (READ)**:
   - Open `index.php` &rarr; Take screenshot of events table with poster thumbnails, categories, and statistics cards.
2. **Add Event with Valid Poster (CREATE & UPLOAD)**:
   - Click `+ Add Event`, fill details, select a valid image ($< 2\text{ MB}$), and save &rarr; Take screenshot of success alert and uploaded poster.
3. **Upload Validation Test (ERROR CASE)**:
   - Attempt to upload an invalid file (e.g. a `.txt` or `.php` file, or an image $> 2\text{ MB}$) &rarr; Take screenshot of the red rejection error alert.
4. **Edit Event & Poster Replacement (UPDATE)**:
   - Click Edit on an existing event, replace poster or change status &rarr; Take screenshot of the update confirmation.
5. **Delete Confirmation & Execution (DELETE)**:
   - Click Delete on an event &rarr; Take screenshot of the **Confirm Delete Modal**, then confirm to see the deletion banner.
6. **Audit Trail (ADVANCED)**:
   - Click `Audit Logs` button &rarr; Take screenshot of the log drawer showing history of actions.

---

## 📂 File Architecture

```
Practical-12/
├── db.php             # PDO database connection, migrations & seeder
├── upload_helper.php  # 4-layer file validator (MIME, size, ext, integrity) & renamer
├── index.php          # Admin UI with event CRUD, poster previews, filters & modals
├── event_action.php   # Backend controller for Add, Edit, Delete, Upload & Audit
├── get_event.php      # AJAX endpoint for single event data
├── get_audit_logs.php # AJAX endpoint for audit log history
├── uploads/posters/   # Protected poster directory with .htaccess
├── setup_db.php       # Standalone setup and verification tool
├── schema.sql         # SQL dump of events and audit logs
└── README.md          # Complete documentation, analysis answers & screenshot guide
```
