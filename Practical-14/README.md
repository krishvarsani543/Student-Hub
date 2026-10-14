# Practical 14: Admin Dashboard with Role-Based Access Control, Dynamic Menu, and Audit Log

## 📌 Problem Definition
Develop an admin dashboard that displays user statistics, student count, event count, and latest registrations. Restrict access strictly to admin users only and show dynamic menu options based on role.

---

## 🔑 Key Questions / Analysis / Interpretation

### 1. Are dashboard values loaded dynamically from the database?
- **Yes.** Every single KPI metric and table is calculated dynamically from MySQL queries in `index.php`:
  - **Student Count**: `SELECT COUNT(*), SUM(status='active'), SUM(status='inactive') FROM students WHERE role = 'student'`
  - **Event Count**: `SELECT COUNT(*), SUM(status='open'), SUM(status='closed') FROM events`
  - **Total Registrations**: `SELECT COUNT(*) FROM registrations`
  - **Audit Logs Count**: `SELECT COUNT(*) FROM audit_logs`
  - **Latest 5 Registrations**: `SELECT ... FROM registrations JOIN students ... JOIN events ... ORDER BY registration_id DESC LIMIT 5`
  - **Monthly Registration Trend**: `SELECT DATE_FORMAT(registration_date, '%b %Y') ... GROUP BY month ...`

### 2. Is access restricted to admin users?
- **Yes.** The admin dashboard (`index.php`) enforces access restriction using `requireAdmin()` in `auth.php`:
  ```php
  function requireAdmin(): void {
      if (($_SESSION["role"] ?? "guest") !== "admin") {
          http_response_code(403);
          include __DIR__ . "/access_denied.php";
          exit;
      }
  }
  ```
- If a student or unauthorized user attempts to open `index.php`, the server responds with **HTTP 403 Forbidden** and renders a red access denied warning.

### 3. Are role-based menus working?
- **Yes.** The dynamic menu generator in `dynamic_menu.php` loads navigation items dynamically:
  - **Admin Role Menu**:
    - 📊 Dashboard Overview
    - 👥 Student Moderation
    - 📈 Event Analytics
    - 📝 Recent Registrations
    - 📜 Security Audit Trail
  - **Student Role Menu** (visible in `student_view.php`):
    - 🏠 Student Portal
    - 📅 Browse Events
    - 🎫 My Registrations
    - 👤 Profile Settings
    - 🔒 Test Admin Dashboard (Triggers 403)

### 4. Are important admin actions recorded in `audit_logs`?
- **Yes.** The dedicated `recordAuditLog()` function in `db.php` records critical admin actions in table `audit_logs`:
  - `LOGIN`: When admin/user authenticates.
  - `STATUS_TOGGLE`: When admin toggles student active/inactive status.
  - `EVENT_CREATE` / `EVENT_DELETE`: When events are modified.
  - `AUDIT_PURGE`: When logs are reset.
- Every entry records `log_id`, `action`, `details`, `user_email`, `ip_address`, and `created_at` timestamp.

---

## 🚀 Extensions Implemented

### Intermediate Extension: Active/Inactive User Status Toggle
- Admin can toggle a student's status between `Active` and `Inactive` with a single click.
- Handled in `action.php` (`action=toggle_status`), which updates MySQL and writes a detailed traceability record into `audit_logs`.

### Advanced Extension: Chart.js Event Registrations by Month Analytics
- SQL aggregates registration dates grouped by year and month:
  ```sql
  SELECT DATE_FORMAT(registration_date, '%b %Y') as month_label, COUNT(*) as total_regs
  FROM registrations
  GROUP BY sort_key, month_label
  ORDER BY sort_key ASC
  ```
- Rendered on the dashboard as an interactive bar chart using **Chart.js** with university color gradients (`#123B5D` and `#8b5cf6`).

---

## 📸 Required Screenshot Submission Guide

1. **Admin Dashboard Overview (Metrics & Aggregations)**:
   - Open `index.php` as Admin &rarr; Take screenshot showing student count, event count, total registrations, and dynamic admin menu.
2. **Monthly Registration Analytics Chart (Advanced Extension)**:
   - Capture screenshot of the Chart.js monthly registrations trend chart.
3. **User Status Toggle (Intermediate Extension)**:
   - Click "Deactivate" / "Activate" on a student record &rarr; Capture screenshot showing updated status pill and green confirmation toast.
4. **Security Audit Log (Key Question #4)**:
   - Capture screenshot of the Audit Log table showing timestamped entries for user moderation and logins.
5. **RBAC Access Denied Screen (Key Question #2)**:
   - Click "Switch to Student Role", then attempt to open `index.php` &rarr; Capture screenshot of the red **403 Forbidden Access Restricted** screen.
6. **Dynamic Student Menu (Key Question #3)**:
   - Open `student_view.php` &rarr; Capture screenshot showing the dynamic student menu in the sidebar.

---

## 📂 File Architecture

```
Practical-14/
├── db.php               # PDO database connection, audit logger & monthly seeder
├── auth.php             # Session management & requireAdmin() RBAC guard
├── dynamic_menu.php     # Dynamic role-based navigation generator
├── action.php           # Status toggle controller with audit log recorder
├── index.php            # Main Admin Dashboard with KPIs, Chart.js & Audit Trail
├── access_denied.php    # 403 Forbidden screen for unauthorized role attempts
├── student_view.php     # Student portal demonstrating student dynamic menu
├── switch_role.php      # Viva testing utility to switch session roles
├── login.php            # Role login page matching StudentHub theme
├── setup_db.php         # Standalone database verification runner
├── schema.sql           # Database schema and sample data dump
└── README.md            # Complete documentation, analysis answers & screenshot guide
```
