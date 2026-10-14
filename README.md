# 🎓 StudentHub — Enterprise Academic Web Portal
**Charusat University • Faculty of Technology**

StudentHub is a comprehensive, production-ready web application consolidating 15 laboratory modules into a unified campus management ecosystem. It provides role-based portals for enrolled students, campus administrators, and public visitors.

---

## 📌 Laboratory Practicals Index

| Practical | Title | Module Description |
| :--- | :--- | :--- |
| **[Practical-1](Practical-1/)** | Sitemap & Wireframing | Information architecture, page flow, and Draw.io system wireframes |
| **[Practical-2](Practical-2/)** | UI Structure & HTML5 Templates | Responsive pages for Home, About, Events, Feedback, Dashboard, and Profile |
| **[Practical-3](Practical-3/)** | CSS Design System | Responsive typography, Charusat brand colors, dark-mode styling, and cards |
| **[Practical-4&5](Practical-4&5/)** | JavaScript & Validations | Dynamic DOM manipulation, theme persistence, regex validation, and weather API |
| **[Practical-6](Practical-6/)** | JSON Data Schemas | Structured data fixtures for FAQs, event schedules, and user registration |
| **[Practical-7&8&9](Practical-7&8&9/)** | Database & Authentication | MySQL PDO connection, password hashing, active student session management |
| **[Practical-10](Practical-10/)** | Advanced Session Security | 15-minute session timeout, split remember-me tokens, and session regeneration |
| **[Practical-11](Practical-11/)** | Student Management CRUD | MySQLi prepared statements, multi-field search, soft-delete, and CSV export |
| **[Practical-12](Practical-12/)** | Event Management CRUD | Event administration with `finfo_file` MIME verification and strict 2MB poster limit |
| **[Practical-13](Practical-13/)** | REST API & Asynchronous CRUD | JavaScript Fetch API integration with zero page reloads and standard HTTP status codes |
| **[Practical-14](Practical-14/)** | Admin Dashboard & RBAC | Administrative console with Chart.js registration trends and HTTP 403 access control |
| **[Practical-15](Practical-15/)** | **Portal Integration & Deployment** | **Automated 7-pillar verification suite, Apache VirtualHost, and consolidated schema** |

---

## ⚡ Quick Start & Deployment Guide

### Prerequisites
- **XAMPP** (Apache 2.4+ and MySQL/MariaDB 10.4+)
- **PHP** 8.1+ with `pdo_mysql`, `mysqli`, and `fileinfo` extensions enabled

### 1. Database Initialization
1. Ensure MySQL is running in XAMPP (supporting auto-detection for ports **3307** and **3306**).
2. Run the one-click database installer via browser:
   ```text
   http://localhost/StudentHub/Practical-15/setup_portal.php
   ```
   Or import `Practical-15/portal_schema.sql` into phpMyAdmin.

### 2. Apache Virtual Host Deployment (Recommended)
1. Add to `C:\Windows\System32\drivers\etc\hosts` (as Administrator):
   ```text
   127.0.0.1    studenthub.local
   127.0.0.1    www.studenthub.local
   ```
2. Append the Virtual Host configuration from `Practical-15/studenthub-vhost.conf` to `C:\xampp\sstudenthub\apache\conf\extra\httpd-vhosts.conf`.
3. Restart Apache in the XAMPP Control Panel.
4. Access the portal at **`http://studenthub.local/`**.

### 3. Automated System Verification
Run the integrated diagnostic suite to verify navigation, responsiveness, authentication, database operations, security, and AJAX functionality:
```text
http://studenthub.local/Practical-15/verify_portal.php
```

---

## 🔑 Test Credentials for Evaluation & Viva

| Account Role | University Email | Password | Primary Destination |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@charusat.edu.in` | `Admin@123` | Admin Dashboard (`Practical-14`) |
| **Student (Demo 1)** | `25dce099@charusat.edu.in` | `Rohan@123` | Student Dashboard & Profile (`Practical-2`) |
| **Student (Demo 2)** | `25dce017@charusat.edu.in` | `Yaksh@123` | Student Dashboard & Profile (`Practical-2`) |

---

## 🛡️ Security Implementation Highlights
- **SQL Injection Prevention**: 100% prepared queries using parameterized statements (`?` or `:name`).
- **XSS Defense**: Strict `htmlspecialchars($input, ENT_QUOTES, 'UTF-8')` on all echoed data.
- **Role-Based Access Control**: Strict `requireAdmin()` gatekeeper returning HTTP 403 Forbidden to unauthorized accounts.
- **Session Security**: Session ID regeneration upon login, 15-minute inactivity timeout, and timing-attack immune remember-me split tokens.
- **Upload Defense**: Server-side binary MIME verification via `finfo_file()` and $\le 2\text{ MB}$ strict size enforcement.

---

## 📜 License
Developed as part of the Web Development curriculum at **CHARUSAT University**. All Rights Reserved © 2026.
