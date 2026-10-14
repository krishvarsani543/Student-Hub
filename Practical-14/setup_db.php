<?php
/**
 * Practical 14: Setup & Verification Tool
 */

require_once __DIR__ . "/db.php";

$studCount  = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE role = 'student'")->fetchColumn();
$eventCount = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$regCount   = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$logCount   = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 14 Setup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; padding: 40px; color: #333; line-height: 1.6; }
        .card { max-width: 680px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #123B5D; margin-top: 0; }
        .success { color: #16a34a; font-weight: bold; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 15px 0; font-size: 14px; }
        a { display: inline-block; background: #123B5D; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 10px; }
        .stat-badge { display: inline-block; background: #e2e8f0; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Practical 14: Admin Dashboard & Audit Log Setup</h2>
        <p class="success">Database `studenthub` and tables verified successfully!</p>

        <div class="info-box">
            <p><strong>Students Loaded:</strong> <span class="stat-badge"><?= $studCount ?></span></p>
            <p><strong>Events Loaded:</strong> <span class="stat-badge"><?= $eventCount ?></span></p>
            <p><strong>Registrations Loaded:</strong> <span class="stat-badge"><?= $regCount ?></span> (Seeded across months for Chart.js analytics)</p>
            <p><strong>Audit Logs Recorded:</strong> <span class="stat-badge"><?= $logCount ?></span></p>
            <p><strong>Features Implemented:</strong> Dynamic Role Menu, RBAC Guard, Status Toggle (Intermediate), Chart.js Monthly Trend (Advanced).</p>
        </div>

        <a href="index.php">&rarr; Open Admin Dashboard</a>
    </div>
</body>
</html>
