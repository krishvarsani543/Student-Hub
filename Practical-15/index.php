<?php
/**
 * Practical 15: StudentHub Integrated Portal Gateway
 * Unifies all 15 modules into a single deployable, role-aware portal interface.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/../Practical-7&8&9/remember.php";

// Attempt to restore remember-me session if unauthenticated
if (!isset($_SESSION["student_id"])) {
    restoreRememberedSession($pdo);
}

$isLoggedIn = isset($_SESSION["student_id"]);
$userRole = $_SESSION["role"] ?? "guest";
$userName = $_SESSION["name"] ?? "Guest Visitor";
$userEmail = $_SESSION["email"] ?? "";
$userCourse = $_SESSION["course"] ?? "";

// Fetch live database statistics for the portal overview
$studentCount = 0;
$eventCount = 0;
$regCount = 0;
$auditCount = 0;

try {
    $studentCount = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE role = 'student'")->fetchColumn();
    $eventCount = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    $regCount = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $auditCount = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
} catch (Exception $e) {}

// Log access in audit trail
if ($isLoggedIn) {
    logPortalAudit($pdo, (int)$_SESSION["student_id"], "PORTAL_GATEWAY_ACCESS", "Accessed Practical-15 integrated gateway", "success");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Integrated Web Portal (Practical 15)</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --bg-card-alt: #162033;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --header-bg: #123B5D;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: var(--bg-body); color: var(--text-main); line-height: 1.6; }

        /* Portal Header */
        header {
            background: var(--header-bg);
            border-bottom: 3px solid #0284c7;
            padding: 15px 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .header-wrap {
            max-width: 1300px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }
        .branding {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .branding img {
            width: 140px;
            height: auto;
            background: white;
            padding: 6px;
            border-radius: 8px;
        }
        .branding-text h1 {
            font-size: 24px;
            color: #ffffff;
            font-weight: 700;
        }
        .branding-text p {
            font-size: 13px;
            color: #93c5fd;
        }
        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-chip {
            background: rgba(255,255,255,0.1);
            padding: 8px 16px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .user-chip img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        .role-badge {
            background: #38bdf8;
            color: #0f172a;
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 12px;
            text-transform: uppercase;
        }
        .role-badge.admin { background: #ef4444; color: white; }
        .role-badge.guest { background: #94a3b8; color: #0f172a; }

        /* Container & Grid */
        .container {
            max-width: 1300px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Banner */
        .hero-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 35px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .hero-banner h2 {
            font-size: 28px;
            color: #38bdf8;
            margin-bottom: 10px;
        }
        .hero-banner p {
            color: var(--text-muted);
            max-width: 850px;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover { background: #059669; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-main); }
        .btn-outline:hover { background: rgba(255,255,255,0.05); }

        /* Live KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: rgba(2, 132, 199, 0.15);
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .kpi-info h3 {
            font-size: 26px;
            font-weight: 700;
        }
        .kpi-info p {
            font-size: 13px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Modules Matrix Section */
        .section-title {
            font-size: 20px;
            color: #38bdf8;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .module-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            position: relative;
            transition: transform 0.2s, border-color 0.2s;
        }
        .module-card:hover {
            transform: translateY(-3px);
            border-color: #0284c7;
        }
        .module-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .module-tag {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .tag-public { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .tag-student { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .tag-admin { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .tag-api { background: rgba(168, 85, 247, 0.2); color: #c084fc; }

        .module-card h4 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #f1f5f9;
        }
        .module-card p {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 16px;
        }
        .module-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-top: 1px solid var(--border);
            padding-top: 14px;
        }
        .module-links a {
            font-size: 13px;
            color: #38bdf8;
            text-decoration: none;
            background: rgba(2, 132, 199, 0.1);
            padding: 6px 12px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .module-links a:hover {
            background: #0284c7;
            color: white;
        }

        /* Deployment & Accounts Card */
        .info-split {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .info-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }
        .info-card h4 {
            font-size: 17px;
            color: #38bdf8;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 10px;
        }
        .table-custom th, .table-custom td {
            padding: 10px;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        .table-custom th { color: var(--text-muted); background: rgba(15,23,42,0.4); }

        /* Footer */
        footer {
            background: var(--header-bg);
            border-top: 1px solid var(--border);
            padding: 25px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-wrap">
            <div class="branding">
                <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png" alt="StudentHub Logo">
                <div class="branding-text">
                    <h1>StudentHub Portal (Practical 15)</h1>
                    <p>Unified Enterprise Academic Portal • CHARUSAT University</p>
                </div>
            </div>

            <div class="header-user">
                <?php if ($isLoggedIn): ?>
                    <div class="user-chip">
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Avatar">
                        <div>
                            <div style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($userName) ?></div>
                            <span class="role-badge <?= htmlspecialchars($userRole) ?>"><?= htmlspecialchars(strtoupper($userRole)) ?></span>
                        </div>
                    </div>
                    <a href="../Practical-7&8&9/logout.php" class="btn btn-outline" style="padding: 7px 14px; font-size: 12px;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
                <?php else: ?>
                    <div class="user-chip">
                        <span class="role-badge guest">GUEST</span>
                        <span style="font-size: 13px; color: #94a3b8;">Not Logged In</span>
                    </div>
                    <a href="../Practical-2/login.html" class="btn btn-primary" style="padding: 7px 16px; font-size: 13px;"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">

        <!-- Hero Section -->
        <div class="hero-banner">
            <h2>🎓 Deployable StudentHub Portal Gateway</h2>
            <p>
                Welcome to the complete, integrated capstone portal consolidating all 15 laboratory modules into a single production-ready web application. Verified with role-based authentication, dynamic database persistence, AJAX asynchronous communication, and local Apache Virtual Host deployment.
            </p>
            <div class="hero-actions">
                <a href="verify_portal.php" class="btn btn-success"><i class="fa-solid fa-clipboard-check"></i> Run Verification Test Suite</a>
                <a href="setup_portal.php" class="btn btn-outline"><i class="fa-solid fa-database"></i> Database Setup &amp; Seeder</a>
                <a href="../Practical-2/home.html" class="btn btn-outline"><i class="fa-solid fa-globe"></i> Public Landing Page</a>
                <?php if ($userRole === "admin"): ?>
                    <a href="../Practical-14/index.php" class="btn btn-primary"><i class="fa-solid fa-gauge-high"></i> Admin Dashboard</a>
                <?php elseif ($userRole === "student"): ?>
                    <a href="../Practical-2/deshboard.php" class="btn btn-primary"><i class="fa-solid fa-graduation-cap"></i> Student Dashboard</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Live KPI Metrics -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                <div class="kpi-info">
                    <h3><?= $studentCount ?></h3>
                    <p>Enrolled Students</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fa-solid fa-calendar-star"></i></div>
                <div class="kpi-info">
                    <h3><?= $eventCount ?></h3>
                    <p>Campus Events</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fa-solid fa-ticket"></i></div>
                <div class="kpi-info">
                    <h3><?= $regCount ?></h3>
                    <p>Total Registrations</p>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <div class="kpi-info">
                    <h3><?= $auditCount ?></h3>
                    <p>Security Audit Logs</p>
                </div>
            </div>
        </div>

        <!-- Integrated Modules Matrix -->
        <h3 class="section-title"><i class="fa-solid fa-cubes"></i> Integrated Modules Directory</h3>
        <div class="modules-grid">

            <!-- Module 1: Public Web Portal -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-public">Practicals 1–6</span>
                    <i class="fa-solid fa-globe" style="color: #60a5fa;"></i>
                </div>
                <h4>Public Web Portal &amp; Showcase</h4>
                <p>Semantic HTML5 structure, responsive CSS styles, dark-mode toggle, client-side validation, and weather widget.</p>
                <div class="module-links">
                    <a href="../Practical-2/home.html"><i class="fa-solid fa-house"></i> Home</a>
                    <a href="../Practical-2/about.html"><i class="fa-solid fa-circle-info"></i> About</a>
                    <a href="../Practical-2/events.html"><i class="fa-solid fa-calendar"></i> Events</a>
                    <a href="../Practical-2/contact.html"><i class="fa-solid fa-envelope"></i> Contact</a>
                    <a href="../Practical-2/faq.html"><i class="fa-solid fa-question"></i> FAQ</a>
                </div>
            </div>

            <!-- Module 2: Authentication & Sessions -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-student">Practicals 7–10</span>
                    <i class="fa-solid fa-lock" style="color: #34d399;"></i>
                </div>
                <h4>Authentication &amp; Session Engine</h4>
                <p>AJAX email check, Bcrypt password hashing, session regeneration, 15-minute inactivity timeout, and remember-me split tokens.</p>
                <div class="module-links">
                    <a href="../Practical-2/login.html"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                    <a href="../Practical-2/register.php"><i class="fa-solid fa-user-plus"></i> Register</a>
                    <a href="../Practical-7&8&9/student_dashboard.php"><i class="fa-solid fa-table-columns"></i> Dashboard (PHP)</a>
                    <a href="../Practical-10/simulate_timeout.php"><i class="fa-solid fa-clock"></i> Test Timeout</a>
                </div>
            </div>

            <!-- Module 3: Student Management CRUD -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-admin">Practical 11</span>
                    <i class="fa-solid fa-user-graduate" style="color: #f87171;"></i>
                </div>
                <h4>Student CRUD with Search &amp; Filter</h4>
                <p>Complete student management with prepared MySQLi statements, multi-field search, status filtering, soft-delete, and CSV export.</p>
                <div class="module-links">
                    <a href="../Practical-11/index.php"><i class="fa-solid fa-table"></i> Student Records</a>
                    <a href="../Practical-11/export_csv.php"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
                </div>
            </div>

            <!-- Module 4: Event Management & Uploads -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-admin">Practical 12</span>
                    <i class="fa-solid fa-cloud-arrow-up" style="color: #f87171;"></i>
                </div>
                <h4>Event Management &amp; Poster Upload</h4>
                <p>Event CRUD operations featuring multi-layer file upload validation: MIME checking via finfo_file, 2MB size cap, and safe storage.</p>
                <div class="module-links">
                    <a href="../Practical-12/index.php"><i class="fa-solid fa-list-check"></i> Manage Events</a>
                </div>
            </div>

            <!-- Module 5: RESTful API & AJAX CRUD -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-api">Practical 13</span>
                    <i class="fa-solid fa-bolt" style="color: #c084fc;"></i>
                </div>
                <h4>RESTful JSON API &amp; AJAX Operations</h4>
                <p>Asynchronous CRUD utilizing JavaScript Fetch API with zero page reloads, debounced live search, and standard HTTP REST status codes.</p>
                <div class="module-links">
                    <a href="../Practical-13/index.php"><i class="fa-solid fa-laptop-code"></i> Live AJAX GUI</a>
                    <a href="../Practical-13/api.php" target="_blank"><i class="fa-solid fa-code"></i> JSON Endpoint</a>
                </div>
            </div>

            <!-- Module 6: Admin Dashboard & RBAC -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-admin">Practical 14</span>
                    <i class="fa-solid fa-shield" style="color: #f87171;"></i>
                </div>
                <h4>Admin Dashboard &amp; Role-Based Access</h4>
                <p>Protected administrative console with live Chart.js registration trends, role-specific dynamic navigation, and audit trail.</p>
                <div class="module-links">
                    <a href="../Practical-14/index.php"><i class="fa-solid fa-gauge"></i> Admin Console</a>
                    <a href="../Practical-14/switch_role.php?role=student"><i class="fa-solid fa-repeat"></i> Role Switcher</a>
                </div>
            </div>

            <!-- Module 7: Student Personal Hub -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-student">Student Hub</span>
                    <i class="fa-solid fa-id-badge" style="color: #34d399;"></i>
                </div>
                <h4>Student Profile &amp; Dashboard Hub</h4>
                <p>Personal student dashboard dynamically synchronized with MySQL profile data, roll number extraction, skills badges, and edit form.</p>
                <div class="module-links">
                    <a href="../Practical-2/deshboard.php"><i class="fa-solid fa-gauge-simple"></i> Dashboard</a>
                    <a href="../Practical-2/profile.php"><i class="fa-solid fa-id-card"></i> My Profile</a>
                    <a href="../Practical-2/edit.php"><i class="fa-solid fa-user-pen"></i> Edit Profile</a>
                    <a href="../Practical-2/feedback.html"><i class="fa-solid fa-comment-dots"></i> Feedback</a>
                </div>
            </div>

            <!-- Module 8: Verification & Capstone -->
            <div class="module-card">
                <div class="module-header">
                    <span class="module-tag tag-public">Practical 15</span>
                    <i class="fa-solid fa-check-double" style="color: #38bdf8;"></i>
                </div>
                <h4>Portal Verification &amp; Virtual Host</h4>
                <p>Automated verification diagnostics covering all 7 pillars, consolidated schema setup, and Apache VirtualHost configuration.</p>
                <div class="module-links">
                    <a href="verify_portal.php"><i class="fa-solid fa-stethoscope"></i> Self Diagnostics</a>
                    <a href="studenthub-vhost.conf" target="_blank"><i class="fa-solid fa-server"></i> VHost Config</a>
                    <a href="setup_portal.php"><i class="fa-solid fa-wrench"></i> Setup Portal</a>
                </div>
            </div>

        </div>

        <!-- Deployment and Test Accounts -->
        <div class="info-split">
            <div class="info-card">
                <h4><i class="fa-solid fa-key"></i> Evaluation &amp; Viva Test Accounts</h4>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Target Portal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b style="color: #ef4444;">Administrator</b></td>
                            <td>admin@charusat.edu.in</td>
                            <td><code>Admin@123</code></td>
                            <td>Admin Dashboard</td>
                        </tr>
                        <tr>
                            <td><b style="color: #34d399;">Student</b></td>
                            <td>25dce099@charusat.edu.in</td>
                            <td><code>Rohan@123</code></td>
                            <td>Student Dashboard</td>
                        </tr>
                        <tr>
                            <td><b style="color: #60a5fa;">Student</b></td>
                            <td>25dce017@charusat.edu.in</td>
                            <td><code>Yaksh@123</code></td>
                            <td>Student Dashboard</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="info-card">
                <h4><i class="fa-solid fa-network-wired"></i> Apache Virtual Host Deployment</h4>
                <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 10px;">
                    Follow these 3 quick steps to serve the portal on <code>http://studenthub.local</code>:
                </p>
                <ol style="font-size: 13px; color: #cbd5e1; margin-left: 20px; line-height: 1.8;">
                    <li>Open <code>C:\Windows\System32\drivers\etc\hosts</code> as Administrator and add:
                        <br><code>127.0.0.1 studenthub.local</code>
                    </li>
                    <li>Add the VirtualHost configuration from <a href="studenthub-vhost.conf" style="color: #38bdf8;">Practical-15/studenthub-vhost.conf</a> to <code>C:\xampp\sstudenthub\apache\conf\extra\httpd-vhosts.conf</code>.</li>
                    <li>Restart Apache in XAMPP Control Panel and navigate to <b>http://studenthub.local/</b>.</li>
                </ol>
            </div>
        </div>

    </div>

    <footer>
        <p>© 2026 StudentHub Enterprise Portal • CHARUSAT University. All 15 Practical Modules Integrated.</p>
    </footer>

</body>
</html>
