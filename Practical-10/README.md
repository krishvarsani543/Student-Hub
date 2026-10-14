# Practical 10: Secure Login/Logout with Sessions, Role-Based Access, Timeout, and Remember-Me Token

## 📌 Problem Definition
Develop secure login and logout functionality using PHP sessions. Verify passwords using `password_verify()`, regenerate session ID after login to prevent session fixation, restrict dashboard access to authorized users, implement role-based redirection, handle automatic session timeouts, and implement an advanced secure remember-me token system.

---

## 🔑 Key Questions / Analysis / Interpretation

### 1. Are sessions started and destroyed securely?
- **Secure Session Start**: Sessions are initiated with secure cookie attributes (`HttpOnly = true`, `SameSite = Lax`, and conditional `Secure` flag on HTTPS) to defend against Cross-Site Scripting (XSS) and Cross-Site Request Forgery (CSRF).
- **Session Fixation Prevention**: Immediately upon verifying credentials in `login.php`, `session_regenerate_id(true)` is called, discarding the old session identifier and issuing a fresh one.
- **Secure Session Destruction**: In `logout.php`, session variables are emptied (`$_SESSION = []`), the session cookie is invalidated with an expired epoch timestamp (`time() - 3600`), and `session_destroy()` is invoked. Additionally, the remember-me token is deleted from both the database and the client's cookie jar.

### 2. Is `password_verify()` used correctly?
- Passwords are encrypted using PHP's native `password_hash($password, PASSWORD_DEFAULT)` (Bcrypt).
- In `login.php`, incoming user input is compared against the database hash using timing-attack safe `password_verify($password, $user['password'])`. Raw passwords are never stored or logged.

### 3. Is unauthorized dashboard access blocked?
- `auth.php` acts as a guard. If `$_SESSION['student_id']` is absent, execution terminates immediately, redirecting unauthenticated visitors to `login.php?msg=unauthorized`.
- **Role-Based Access Control (RBAC)**: 
  - `admin_dashboard.php` enforces `requireRole('admin')`. If a student attempts access, HTTP 403 Forbidden is sent and the user is redirected safely to `student_dashboard.php?msg=access_denied`.
  - Direct URL tampering without an active session is completely blocked.

### 4. Is session timeout implemented?
- Implemented in `auth.php` with a 900-second (15 minutes) inactivity window.
- On each user request, `time() - $_SESSION['last_activity']` is evaluated:
  - If the threshold is exceeded: the session and cookies are destroyed, and the user is redirected to `login.php?msg=timeout`.
  - If within the limit: `$_SESSION['last_activity'] = time()` is refreshed.
- An interactive simulation button (`simulate_timeout.php`) is included to facilitate screenshot capture without waiting 15 minutes.

### 5. Are admin and student users redirected to appropriate dashboards?
- Upon authentication in `login.php`:
  - Role `admin` &rarr; redirects to `admin_dashboard.php`
  - Role `student` &rarr; redirects to `student_dashboard.php`
- If an already authenticated user revisits `login.php`, the system detects their session and routes them directly to their appropriate dashboard.

---

## 🚀 Extensions Implemented

### Intermediate Extension: Last-Login Timestamp
- On successful authentication, the database column `last_login` in table `students` is updated with `NOW()`.
- The dashboard displays both the prior recorded login and the current session details.

### Advanced Extension: Secure Remember-Me Token (Split-Token Pattern)
- Adheres to the industry-standard **Selector + Authenticator (Validator)** pattern:
  - **Selector**: 12 random bytes (hexadecimal) used to query the database row in $O(1)$ time without exposing identity.
  - **Validator Token**: 32 cryptographically secure random bytes generated via `random_bytes(32)`.
  - **Database Storage**: The database only stores `token_hash = hash('sha256', $token)`. Even if the database is dumped, plain tokens cannot be recovered.
  - **Verification**: Validated using constant-time `hash_equals()`.
  - **Cookie**: Transmitted as `selector:token` with `HttpOnly` and `SameSite=Lax`.

---

## 🧪 Test Accounts for Evaluation & Viva

| Account Role | University Email | Password | Target Dashboard |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@charusat.edu.in` | `Admin@123` | `admin_dashboard.php` |
| **Student** | `22dit045@charusat.edu.in` | `Student@123` | `student_dashboard.php` |
| **Demo Student** | `student@charusat.edu.in` | `Student@123` | `student_dashboard.php` |

*(Note: The login page includes 1-click test fill buttons for fast testing)*

---

## 📸 Required Screenshot Submission Guide

1. **Login Success**:
   - Log in as Student (`22dit045@charusat.edu.in` / `Student@123`) &rarr; view `student_dashboard.php`.
   - Log in as Admin (`admin@charusat.edu.in` / `Admin@123`) &rarr; view `admin_dashboard.php`.
2. **Failed Login**:
   - Enter invalid password (e.g. `wrongpass`) &rarr; observe red error message *"Invalid email or password."* on `login.php`.
3. **Logout**:
   - Click "Logout" &rarr; verify redirection to `login.php` showing green notification *"You have been logged out securely. Sessions and tokens cleared."*
4. **Session Timeout**:
   - Click "Simulate Session Timeout" from the dashboard &rarr; observe automatic redirect to `login.php` with notice *"Session timed out due to 15 minutes of inactivity."*
5. **Unauthorized Access Attempt**:
   - Log in as Student and click "Test Unauthorized Access (Try Admin Panel)" or navigate directly to `admin_dashboard.php`.
   - Observe immediate blocking and red alert *"Unauthorized Dashboard Access Blocked!"*.
   - Also test visiting `student_dashboard.php` directly in an incognito window without logging in &rarr; notice redirect to `login.php?msg=unauthorized`.

---

## 📂 File Architecture

```
Practical-10/
├── db.php                 # Database connection & auto-bootstrapping
├── schema.sql             # SQL schema & test data dump
├── setup_db.php           # Browser-based verification tool
├── auth.php               # Session security, timeout & RBAC guard
├── remember.php           # Advanced selector+token hash remember-me logic
├── login.php              # Styled login interface matching StudentHub design
├── student_dashboard.php  # Student portal with RBAC & session inspection
├── admin_dashboard.php    # Admin portal with user management directory
├── logout.php             # Comprehensive session & token destruction
├── simulate_timeout.php   # Instant timeout simulation helper
└── README.md              # Documentation, viva guide & test procedure
```
