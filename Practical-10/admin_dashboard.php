<?php
/**
 * Practical 10: Admin Dashboard
 * Strictly protected by authentication and role-based access control (RBAC).
 * Only accessible to users with the 'admin' role.
 */

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Enforce role: Admin only
requireRole("admin");

// Fetch admin details
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
$stmt->execute([$_SESSION["student_id"]]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all registered users from database to demonstrate administrative visibility
$usersStmt = $pdo->query("SELECT student_id, name, email, role, last_login FROM students ORDER BY student_id ASC");
$allUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard (Practical 10)</title>

    <link rel="stylesheet" href="../Practical-3/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            font-size: 16px;
        }
        .admin-header-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 24px;
            margin-bottom: 25px;
            border-left: 6px solid #b91c1c;
        }
        .header-title-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-title-row h2 {
            margin: 0;
            color: #123B5D;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
        }
        .role-badge-admin {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .security-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .metric-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
        }
        .metric-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .metric-value {
            font-size: 15px;
            font-weight: bold;
            color: #1e293b;
            word-break: break-all;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-primary { background: #123B5D; color: white; }
        .btn-primary:hover { background: #0e2e49; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; color: white; }

        .users-table-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 24px;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }
        .users-table th, .users-table td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .users-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }
        .users-table tr:hover {
            background-color: #f8fafc;
        }
        .badge-role-admin {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-role-student {
            background: #dbeafe;
            color: #1e40af;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">

            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p><b>Practical 10: Secure Role-Based Administration Console</b></p>
            </div>
        </div>

        <nav>
            <center>
                <a href="admin_dashboard.php">Admin Panel</a>
                <a href="logout.php" style="color: #f87171;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </center>
        </nav>
    </header>

    <div class="admin-container">

        <!-- Admin Inspection Card -->
        <div class="admin-header-card">
            <div class="header-title-row">
                <h2>
                    <i class="fa-solid fa-user-shield" style="color: #b91c1c;"></i>
                    Administrator Control Panel
                </h2>
                <span class="role-badge-admin">
                    <i class="fa-solid fa-shield"></i> Role: ADMIN
                </span>
            </div>

            <div class="security-grid">
                <div class="metric-card">
                    <div class="metric-label">Logged-In Administrator</div>
                    <div class="metric-value"><?= htmlspecialchars($admin["name"] ?? $_SESSION["name"], ENT_QUOTES, "UTF-8") ?></div>
                    <small style="color: #64748b;"><?= htmlspecialchars($admin["email"] ?? $_SESSION["email"], ENT_QUOTES, "UTF-8") ?></small>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Admin Session ID</div>
                    <div class="metric-value" style="font-family: monospace; font-size: 13px;">
                        <?= htmlspecialchars(session_id(), ENT_QUOTES, "UTF-8") ?>
                    </div>
                    <small style="color: #10b981;"><i class="fa-solid fa-check-circle"></i> Regenerated on Login</small>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Last Login Timestamp (DB)</div>
                    <div class="metric-value" style="color: #b91c1c;">
                        <?= !empty($admin["last_login"]) ? htmlspecialchars($admin["last_login"], ENT_QUOTES, "UTF-8") : "Active Session" ?>
                    </div>
                    <small style="color: #64748b;">Intermediate Extension</small>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Session Inactivity Timeout</div>
                    <div class="metric-value" id="timeoutCountdown" style="color: #d97706;">
                        15m 00s remaining
                    </div>
                    <small style="color: #64748b;">Threshold: 900 seconds</small>
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
                <a href="simulate_timeout.php" class="btn-action btn-primary">
                    <i class="fa-solid fa-hourglass-end"></i> Simulate Timeout
                </a>
                <a href="logout.php" class="btn-action btn-danger">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Secure Logout
                </a>
            </div>
        </div>

        <!-- RBAC User Directory Card -->
        <div class="users-table-card">
            <h3 style="color: #123B5D; margin: 0 0 10px 0; font-size: 20px;">
                <i class="fa-solid fa-users-gear"></i> System User Accounts & Roles (RBAC Inspection)
            </h3>
            <p style="color: #64748b; margin-bottom: 15px; font-size: 14px;">
                Demonstrates role-based data retrieval. Non-admin users cannot access this view.
            </p>

            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login (DB)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allUsers as $u): ?>
                        <tr>
                            <td><?= (int)$u["student_id"] ?></td>
                            <td><strong><?= htmlspecialchars($u["name"], ENT_QUOTES, "UTF-8") ?></strong></td>
                            <td><?= htmlspecialchars($u["email"], ENT_QUOTES, "UTF-8") ?></td>
                            <td>
                                <?php if ($u["role"] === "admin"): ?>
                                    <span class="badge-role-admin"><i class="fa-solid fa-shield"></i> Admin</span>
                                <?php else: ?>
                                    <span class="badge-role-student"><i class="fa-solid fa-graduation-cap"></i> Student</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= !empty($u["last_login"]) ? htmlspecialchars($u["last_login"], ENT_QUOTES, "UTF-8") : "<span style='color: #94a3b8;'>Never</span>" ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        let timeLeft = 900;
        const countdownEl = document.getElementById("timeoutCountdown");

        setInterval(function() {
            if (timeLeft <= 0) {
                countdownEl.innerText = "Expired! Redirecting...";
                window.location.href = "login.php?msg=timeout";
            } else {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownEl.innerText = `${minutes}m ${seconds < 10 ? '0' : ''}${seconds}s remaining`;
            }
        }, 1000);
    </script>
</body>
</html>
