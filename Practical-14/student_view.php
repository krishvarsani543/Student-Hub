<?php
/**
 * Practical 14: Student Portal View (To verify Dynamic Menu & RBAC)
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/dynamic_menu.php";

$currentRole = $_SESSION["role"] ?? "student";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Portal (Practical 14 Dynamic Menu)</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .layout-wrapper {
            display: flex;
            max-width: 1320px;
            margin: 25px auto;
            padding: 0 20px;
            gap: 25px;
        }
        .sidebar {
            width: 270px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 20px;
            height: fit-content;
        }
        .content-area {
            flex: 1;
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
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 24px;
            margin-bottom: 20px;
        }
        .role-banner {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            color: #1e40af;
            padding: 14px 18px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .btn-test {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
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
                <p><b>Practical 14: Dynamic Role-Based Menu (Student View)</b></p>
            </div>
        </div>
    </header>

    <div class="layout-wrapper">

        <!-- Dynamic Sidebar Menu (Student Context) -->
        <aside class="sidebar">
            <div style="padding-bottom: 15px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-graduation-cap" style="font-size: 26px; color: #3b82f6;"></i>
                <div>
                    <strong style="color: #123B5D; display: block;">Student Portal</strong>
                    <small style="color: #64748b;">Dynamic Menu Loaded</small>
                </div>
            </div>

            <!-- Renders Student Menu -->
            <?php renderDynamicMenu($currentRole, "student_home"); ?>

            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px dashed #cbd5e1;">
                <a href="switch_role.php?role=admin" style="font-size: 13px; color: #123B5D; font-weight: bold; text-decoration: none;">
                    <i class="fa-solid fa-user-shield"></i> Switch to Admin Role
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="content-area">

            <div class="role-banner">
                <div>
                    <i class="fa-solid fa-info-circle"></i>
                    <strong>RBAC Active:</strong> You are currently viewing the system with the <strong>Student</strong> role. Notice that the sidebar loaded the Student Dynamic Menu.
                </div>
                <a href="index.php" class="btn-test" style="background: #ef4444;" title="Click to test unauthorized access blocking">
                    <i class="fa-solid fa-lock"></i> Test Admin Access (Triggers 403)
                </a>
            </div>

            <div class="card">
                <h3 style="color: #123B5D; margin-top: 0;">Welcome, <?= htmlspecialchars($_SESSION["user_name"] ?? "Student", ENT_QUOTES, "UTF-8") ?>!</h3>
                <p style="color: #475569; line-height: 1.6;">
                    This screen proves <strong>Key Question #3 (Are role-based menus working?)</strong>. Because you are logged in as a student, administrative tools (audit logs, student status toggles, moderation) are completely hidden, and student options are presented dynamically.
                </p>

                <div style="background: #f8fafc; border-left: 4px solid #3b82f6; padding: 14px; border-radius: 6px; margin-top: 15px; font-size: 14px;">
                    <strong>Next Viva Step:</strong> Click the red <strong>"Test Admin Access"</strong> button above to verify that attempting to visit <code>index.php</code> triggers the HTTP 403 Forbidden screen.
                </div>
            </div>

        </main>
    </div>

</body>
</html>
