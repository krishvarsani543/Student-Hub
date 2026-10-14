<?php
/**
 * Practical 10: Student Dashboard
 * Strictly protected by authentication and role-based access control (RBAC).
 */

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Enforce role: Student only
requireRole("student");

// Fetch fresh details of student from database
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? LIMIT 1");
$stmt->execute([$_SESSION["student_id"]]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

$accessDeniedAlert = false;
if (($_GET["msg"] ?? "") === "access_denied") {
    $accessDeniedAlert = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Dashboard (Practical 10)</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .security-panel {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 24px;
            margin: 20px 0;
            border-left: 6px solid #123B5D;
        }
        .security-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .security-header h3 {
            margin: 0;
            color: #123B5D;
            display: flex;
            align-items: center;
            gap: 10px;
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
            font-size: 13px;
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
        .role-pill {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
        }
        .actions-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
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
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        .btn-warning:hover {
            background: #d97706;
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
            color: white;
        }
        .btn-primary {
            background: #123B5D;
            color: white;
        }
        .btn-primary:hover {
            background: #0e2e49;
            color: white;
        }
        .alert-unauthorized-banner {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 15px;
            animation: pulse 1.5s infinite alternate;
        }
        @keyframes pulse {
            from { box-shadow: 0 0 5px rgba(239, 68, 68, 0.4); }
            to { box-shadow: 0 0 15px rgba(239, 68, 68, 0.8); }
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
                <p><b>Practical 10: Secure Role-Based Student Portal</b></p>
            </div>
        </div>

        <nav>
            <center>
                <a href="student_dashboard.php">Dashboard</a>
                <a href="../Practical-2/events.html">Events</a>
                <a href="../Practical-2/profile.html">Profile</a>
                <a href="../Practical-2/feedback.html">Feedback</a>
                <a href="logout.php" style="color: #f87171;"><i class="fa-solid fa-power-off"></i> Logout</a>
            </center>
        </nav>
    </header>

    <div class="container" style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">

        <?php if ($accessDeniedAlert): ?>
            <div class="alert-unauthorized-banner">
                <i class="fa-solid fa-shield-halved" style="font-size: 32px;"></i>
                <div>
                    <strong style="font-size: 17px;">[RBAC ACCESS DENIED] Unauthorized Dashboard Access Blocked!</strong><br>
                    You are logged in with the <strong>Student</strong> role. You attempted to access the <strong>Admin Dashboard</strong>, which is restricted exclusively to Administrators. The system blocked this request and redirected you here safely.
                </div>
            </div>
        <?php endif; ?>

        <!-- Practical 10 Verification & Security Inspection Panel -->
        <div class="security-panel">
            <div class="security-header">
                <h3>
                    <i class="fa-solid fa-lock"></i>
                    Practical 10: Session & RBAC Verification Panel
                </h3>
                <span class="role-pill">
                    <i class="fa-solid fa-user-graduate"></i> Role: STUDENT
                </span>
            </div>

            <div class="security-grid">
                <div class="metric-card">
                    <div class="metric-label">Logged-In Student</div>
                    <div class="metric-value"><?= htmlspecialchars($student["name"] ?? $_SESSION["name"], ENT_QUOTES, "UTF-8") ?></div>
                    <small style="color: #64748b;"><?= htmlspecialchars($student["email"] ?? $_SESSION["email"], ENT_QUOTES, "UTF-8") ?></small>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Active PHP Session ID</div>
                    <div class="metric-value" style="font-family: monospace; font-size: 13px;">
                        <?= htmlspecialchars(session_id(), ENT_QUOTES, "UTF-8") ?>
                    </div>
                    <small style="color: #10b981;"><i class="fa-solid fa-check-circle"></i> Regenerated on Login</small>
                </div>

                <div class="metric-card">
                    <div class="metric-label">Last Login Timestamp (DB)</div>
                    <div class="metric-value" style="color: #0369a1;">
                        <?= !empty($student["last_login"]) ? htmlspecialchars($student["last_login"], ENT_QUOTES, "UTF-8") : "First Session" ?>
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

            <!-- Testing controls for Evaluation & Screenshots -->
            <div class="actions-toolbar">
                <span style="font-weight: 600; font-size: 14px; color: #334155;">Lab Evaluation Actions:</span>

                <!-- 1. Test RBAC: Student trying to open Admin Dashboard -->
                <a href="admin_dashboard.php" class="btn-action btn-warning" title="Click to test unauthorized access blocking">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    Test Unauthorized Access (Try Admin Panel)
                </a>

                <!-- 2. Test Timeout: Instantly simulates 15 mins inactivity -->
                <a href="simulate_timeout.php" class="btn-action btn-primary" title="Click to simulate 15 mins timeout">
                    <i class="fa-solid fa-hourglass-end"></i>
                    Simulate Session Timeout (Screenshot #4)
                </a>

                <!-- 3. Secure Logout -->
                <a href="logout.php" class="btn-action btn-danger" title="Securely destroy session">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout (Screenshot #3)
                </a>
            </div>
        </div>

        <!-- Student Profile Overview Card -->
        <div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h3 style="color: #123B5D; margin-bottom: 15px;">
                <i class="fa-solid fa-id-card"></i> Student Information
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px; font-weight: bold; width: 200px;">Full Name:</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($student["name"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px; font-weight: bold;">University Email:</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($student["email"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px; font-weight: bold;">Course / Department:</td>
                    <td style="padding: 10px;"><?= htmlspecialchars($student["course"] ?? "Information Technology", ENT_QUOTES, "UTF-8") ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px; font-weight: bold;">Account Role:</td>
                    <td style="padding: 10px;"><span class="role-pill">Student</span></td>
                </tr>
                <tr>
                    <td style="padding: 10px; font-weight: bold;">Remember-Me Token:</td>
                    <td style="padding: 10px;">
                        <?= isset($_COOKIE["remember_me"]) ? "<span style='color: green;'><i class='fa-solid fa-check'></i> Active (SHA-256 Hashed Selector+Token)</span>" : "<span style='color: #64748b;'>Not set (Session only)</span>" ?>
                    </td>
                </tr>
            </table>
        </div>

    </div>

    <!-- Live Inactivity Countdown -->
    <script>
        let timeLeft = 900; // 15 minutes
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
