<?php
/**
 * Practical 14: Admin Dashboard with RBAC, Dynamic Menu, and Audit Log
 * Features:
 * - Access Restricted to Admin only via requireAdmin()
 * - Dynamic Role-Based Sidebar Navigation
 * - Real-time SQL Aggregation for Students, Events, Registrations, and Logs
 * - Intermediate Extension: Active/Inactive User Status Toggle with audit logging
 * - Advanced Extension: Chart.js Event Registrations by Month Analytics
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/dynamic_menu.php";

// 1. RBAC Guard: Restrict access to admin users only (Key Question #2)
requireAdmin();

// 2. Process Flash Messages
$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);

// 3. Dynamic SQL Aggregation Queries (Key Question #1)
// Student Metrics
$studentStats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive
    FROM students 
    WHERE role = 'student'
")->fetch();

// Event Metrics
$eventStats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_events,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_events
    FROM events
")->fetch();

// Registrations & Audit Logs Count
$totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$totalAuditLogs     = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

// 4. Advanced Extension: Event Registrations by Month for Analytics Chart
$monthlyQuery = $pdo->query("
    SELECT 
        DATE_FORMAT(registration_date, '%b %Y') as month_label,
        DATE_FORMAT(registration_date, '%Y-%m') as sort_key,
        COUNT(*) as total_regs
    FROM registrations
    GROUP BY sort_key, month_label
    ORDER BY sort_key ASC
");
$chartData = $monthlyQuery->fetchAll();

$chartLabels = [];
$chartCounts = [];
foreach ($chartData as $cd) {
    $chartLabels[] = $cd["month_label"];
    $chartCounts[] = (int)$cd["total_regs"];
}

// 5. Latest 5 Event Registrations
$recentRegsQuery = $pdo->query("
    SELECT 
        r.registration_id,
        r.registration_date,
        s.name as student_name,
        s.email as student_email,
        e.event_name,
        e.category
    FROM registrations r
    JOIN students s ON r.student_id = s.student_id
    JOIN events e ON r.event_id = e.event_id
    ORDER BY r.registration_id DESC
    LIMIT 5
");
$recentRegistrations = $recentRegsQuery->fetchAll();

// 6. Students Directory for Status Toggle (Intermediate Extension)
$studentsList = $pdo->query("
    SELECT student_id, name, email, course, status 
    FROM students 
    WHERE role = 'student' 
    ORDER BY student_id DESC 
    LIMIT 6
")->fetchAll();

// 7. Security Audit Trail (Key Question #4)
$auditLogs = $pdo->query("
    SELECT log_id, action, details, user_email, ip_address, created_at 
    FROM audit_logs 
    ORDER BY log_id DESC 
    LIMIT 8
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard (Practical 14)</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Chart.js for Advanced Analytics Extension -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .layout-wrapper {
            display: flex;
            max-width: 1360px;
            margin: 25px auto;
            padding: 0 20px;
            gap: 25px;
        }

        /* Dynamic Sidebar */
        .sidebar {
            width: 270px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 22px 18px;
            height: fit-content;
        }
        .sidebar-header {
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .dynamic-nav-list {
            list-style: none;
            padding: 0;
            margin: 15px 0 0 0;
        }
        .nav-item {
            margin-bottom: 6px;
        }
        .nav-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .nav-item a:hover {
            background: #f1f5f9;
            color: #123B5D;
        }
        .nav-item.active a {
            background: #123B5D;
            color: white;
        }
        .nav-badge {
            margin-left: auto;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            color: white;
            font-weight: bold;
        }

        /* Main Dashboard Content Area */
        .content-area {
            flex: 1;
            min-width: 0;
        }

        /* Top Bar */
        .top-action-bar {
            background: white;
            border-radius: 12px;
            padding: 16px 22px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .role-pill-admin {
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Flash Message */
        .flash-alert {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 500;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .flash-success { background: #dcfce7; color: #166534; border-left: 5px solid #22c55e; }
        .flash-danger  { background: #fee2e2; color: #991b1b; border-left: 5px solid #ef4444; }
        .flash-warning { background: #fef3c7; color: #92400e; border-left: 5px solid #f59e0b; }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-left: 5px solid #123B5D;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .stat-card.card-events { border-left-color: #8b5cf6; }
        .stat-card.card-regs   { border-left-color: #22c55e; }
        .stat-card.card-audit  { border-left-color: #ef4444; }
        .stat-val { font-size: 28px; font-weight: bold; color: #1e293b; }
        .stat-lbl { font-size: 13px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .stat-sub { font-size: 12px; color: #64748b; margin-top: 4px; }

        /* Section Cards */
        .dashboard-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 24px;
            margin-bottom: 25px;
        }
        .card-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 12px;
        }
        .card-header-row h3 {
            margin: 0;
            color: #123B5D;
            font-size: 19px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Tables */
        .dash-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .dash-table th {
            background: #f8fafc;
            color: #475569;
            padding: 12px 14px;
            text-align: left;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            font-size: 13px;
        }
        .dash-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .dash-table tr:hover { background: #f8fafc; }

        /* Status Pills */
        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-active   { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .btn-toggle {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-toggle-deactivate { background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5; }
        .btn-toggle-deactivate:hover { background: #dc2626; color: white; }
        .btn-toggle-activate   { background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; }
        .btn-toggle-activate:hover { background: #16a34a; color: white; }

        /* Audit Log Badges */
        .badge-audit {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            font-family: monospace;
        }
        .badge-login   { background: #e0f2fe; color: #0369a1; }
        .badge-toggle  { background: #fef3c7; color: #92400e; }
        .badge-event   { background: #ede9fe; color: #6d28d9; }
        .badge-default { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">
            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p><b>Practical 14: Admin Dashboard with RBAC, Dynamic Menu & Audit Log</b></p>
            </div>
        </div>
    </header>

    <div class="layout-wrapper">

        <!-- Dynamic Role-Based Sidebar Navigation (Key Question #3) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-user-shield" style="font-size: 28px; color: #b91c1c;"></i>
                <div>
                    <strong style="color: #123B5D; display: block;">Admin Control Panel</strong>
                    <span class="role-pill-admin"><i class="fa-solid fa-shield"></i> Role: ADMIN</span>
                </div>
            </div>

            <!-- Dynamic Menu Loaded Based on Role -->
            <?php renderDynamicMenu($_SESSION["role"] ?? "admin", "dashboard"); ?>

            <!-- Lab Testing Role Switcher Widget -->
            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
                <small style="color: #64748b; font-weight: bold; display: block; margin-bottom: 6px;">VIVA TESTING SHORTCUT:</small>
                <a href="switch_role.php?role=student" style="font-size: 13px; color: #d97706; font-weight: bold; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-arrows-spin"></i> Switch to Student Role (Verify RBAC)
                </a>
            </div>
        </aside>

        <!-- Main Dashboard Content Area -->
        <main class="content-area">

            <!-- Top Notification & Quick Info Bar -->
            <div class="top-action-bar">
                <div>
                    <h2 style="margin: 0; color: #123B5D; font-size: 22px;">
                        <i class="fa-solid fa-gauge-high"></i> Administrative Monitoring Console
                    </h2>
                    <small style="color: #64748b;">Live dynamic database monitoring | Session ID: <?= htmlspecialchars(session_id(), ENT_QUOTES, "UTF-8") ?></small>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span class="role-pill-admin">
                        <i class="fa-solid fa-user-check"></i> Authenticated Admin
                    </span>
                    <a href="switch_role.php?role=student" class="btn-toggle btn-toggle-deactivate" style="text-decoration: none; padding: 7px 14px;">
                        <i class="fa-solid fa-right-from-bracket"></i> Test Student View
                    </a>
                </div>
            </div>

            <!-- Flash Alert Notice -->
            <?php if ($flash): ?>
                <div class="flash-alert flash-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
                    <div>
                        <i class="fa-solid fa-circle-check"></i> &nbsp;<?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <span style="cursor: pointer;" onclick="this.parentElement.style.display='none';">&times;</span>
                </div>
            <?php endif; ?>

            <!-- Key SQL Aggregated Metric Cards (Key Question #1) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div>
                        <div class="stat-val"><?= (int)$studentStats["total"] ?></div>
                        <div class="stat-lbl">Student Count</div>
                        <div class="stat-sub">
                            <span style="color: #16a34a; font-weight: bold;"><?= (int)$studentStats["active"] ?> Active</span> • 
                            <span style="color: #ef4444;"><?= (int)$studentStats["inactive"] ?> Inactive</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-users" style="font-size: 34px; color: #123B5D;"></i>
                </div>

                <div class="stat-card card-events">
                    <div>
                        <div class="stat-val"><?= (int)$eventStats["total"] ?></div>
                        <div class="stat-lbl">Event Count</div>
                        <div class="stat-sub">
                            <span style="color: #16a34a;"><?= (int)$eventStats["open_events"] ?> Open</span> • 
                            <span style="color: #64748b;"><?= (int)$eventStats["closed_events"] ?> Closed</span>
                        </div>
                    </div>
                    <i class="fa-solid fa-calendar-check" style="font-size: 34px; color: #8b5cf6;"></i>
                </div>

                <div class="stat-card card-regs">
                    <div>
                        <div class="stat-val"><?= $totalRegistrations ?></div>
                        <div class="stat-lbl">Total Registrations</div>
                        <div class="stat-sub">Across all university events</div>
                    </div>
                    <i class="fa-solid fa-ticket" style="font-size: 34px; color: #22c55e;"></i>
                </div>

                <div class="stat-card card-audit">
                    <div>
                        <div class="stat-val"><?= $totalAuditLogs ?></div>
                        <div class="stat-lbl">Audit Log Entries</div>
                        <div class="stat-sub">Security & traceability logs</div>
                    </div>
                    <i class="fa-solid fa-fingerprint" style="font-size: 34px; color: #ef4444;"></i>
                </div>
            </div>

            <!-- Advanced Extension: Event Registrations by Month Analytics Chart -->
            <div class="dashboard-card" id="chartSection">
                <div class="card-header-row">
                    <h3>
                        <i class="fa-solid fa-chart-line" style="color: #8b5cf6;"></i>
                        Event Registrations by Month (Advanced Analytics Extension)
                    </h3>
                    <span style="font-size: 13px; color: #64748b;">Dynamic SQL Group By Month</span>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="monthlyRegistrationsChart"></canvas>
                </div>
            </div>

            <!-- Intermediate Extension: Student Directory with Active/Inactive Status Toggle -->
            <div class="dashboard-card" id="studentsSection">
                <div class="card-header-row">
                    <h3>
                        <i class="fa-solid fa-user-gear" style="color: #123B5D;"></i>
                        User Moderation & Status Toggle (Intermediate Extension)
                    </h3>
                    <span style="font-size: 13px; color: #64748b;">Click toggle to change status & trigger audit log</span>
                </div>

                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Current Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($studentsList as $st): ?>
                            <tr>
                                <td><strong>#<?= (int)$st["student_id"] ?></strong></td>
                                <td><strong><?= htmlspecialchars($st["name"], ENT_QUOTES, "UTF-8") ?></strong></td>
                                <td><?= htmlspecialchars($st["email"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($st["course"] ?? "Information Technology", ENT_QUOTES, "UTF-8") ?></td>
                                <td>
                                    <?php if ($st["status"] === "active"): ?>
                                        <span class="status-pill status-active"><i class="fa-solid fa-check"></i> Active</span>
                                    <?php else: ?>
                                        <span class="status-pill status-inactive"><i class="fa-solid fa-pause"></i> Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="action.php" style="margin: 0; display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="student_id" value="<?= (int)$st['student_id'] ?>">
                                        <?php if ($st["status"] === "active"): ?>
                                            <button type="submit" class="btn-toggle btn-toggle-deactivate" title="Deactivate user account">
                                                <i class="fa-solid fa-ban"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn-toggle btn-toggle-activate" title="Activate user account">
                                                <i class="fa-solid fa-circle-check"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Event Registrations Section -->
            <div class="dashboard-card" id="registrationsSection">
                <div class="card-header-row">
                    <h3>
                        <i class="fa-solid fa-ticket" style="color: #22c55e;"></i>
                        Latest Event Registrations
                    </h3>
                    <span style="font-size: 13px; color: #64748b;">SQL JOIN across students and events</span>
                </div>

                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Reg #</th>
                            <th>Student</th>
                            <th>Event Enrolled</th>
                            <th>Category</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentRegistrations)): ?>
                            <?php foreach ($recentRegistrations as $rg): ?>
                                <tr>
                                    <td><strong>#<?= (int)$rg["registration_id"] ?></strong></td>
                                    <td>
                                        <strong><?= htmlspecialchars($rg["student_name"], ENT_QUOTES, "UTF-8") ?></strong>
                                        <small style="display: block; color: #64748b;"><?= htmlspecialchars($rg["student_email"], ENT_QUOTES, "UTF-8") ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($rg["event_name"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td>
                                        <span style="font-size: 12px; font-weight: bold; background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 12px;">
                                            <?= htmlspecialchars($rg["category"], ENT_QUOTES, "UTF-8") ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fa-regular fa-clock" style="color: #64748b;"></i> 
                                        <?= date("d M Y, h:i A", strtotime($rg["registration_date"])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">No registrations recorded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Security Audit Trail Section (Key Question #4) -->
            <div class="dashboard-card" id="auditSection">
                <div class="card-header-row">
                    <h3>
                        <i class="fa-solid fa-fingerprint" style="color: #ef4444;"></i>
                        Security Audit Log (Key Question #4)
                    </h3>
                    <span style="font-size: 13px; color: #64748b;">Records user moderation, logins & event updates</span>
                </div>

                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>Admin / User</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditLogs as $al): ?>
                            <?php
                                $badgeCls = "badge-default";
                                if ($al["action"] === "LOGIN") $badgeCls = "badge-login";
                                elseif ($al["action"] === "STATUS_TOGGLE") $badgeCls = "badge-toggle";
                                elseif (str_contains($al["action"], "EVENT")) $badgeCls = "badge-event";
                            ?>
                            <tr>
                                <td>#<?= (int)$al["log_id"] ?></td>
                                <td><span class="badge-audit <?= $badgeCls ?>"><?= htmlspecialchars($al["action"], ENT_QUOTES, "UTF-8") ?></span></td>
                                <td><?= htmlspecialchars($al["details"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($al["user_email"] ?? "admin@charusat.edu.in", ENT_QUOTES, "UTF-8") ?></td>
                                <td><code><?= htmlspecialchars($al["ip_address"] ?? "127.0.0.1", ENT_QUOTES, "UTF-8") ?></code></td>
                                <td><small style="color: #64748b;"><?= htmlspecialchars($al["created_at"], ENT_QUOTES, "UTF-8") ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- Chart.js Script for Advanced Extension -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById("monthlyRegistrationsChart").getContext("2d");
            
            const labels = <?= json_encode($chartLabels) ?>;
            const dataCounts = <?= json_encode($chartCounts) ?>;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Event Registrations',
                        data: dataCounts,
                        backgroundColor: 'rgba(18, 59, 93, 0.75)',
                        borderColor: '#123B5D',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: '#8b5cf6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { family: 'Arial' }
                            },
                            grid: { color: '#f1f5f9' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 6
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
