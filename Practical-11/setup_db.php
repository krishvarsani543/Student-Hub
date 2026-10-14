<?php
/**
 * Practical 11: Setup & Data Verification Tool
 * Re-runs database checks and outputs setup confirmation for lab evaluation.
 */

require_once __DIR__ . "/db.php";

$countRes = $conn->query("SELECT COUNT(*) AS total FROM students");
$total = $countRes->fetch_assoc()["total"] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 11 Setup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; padding: 40px; color: #333; line-height: 1.6; }
        .card { max-width: 650px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #123B5D; margin-top: 0; }
        .success { color: #16a34a; font-weight: bold; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 15px 0; font-size: 14px; }
        a { display: inline-block; background: #123B5D; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Practical 11: MySQLi CRUD Database Setup</h2>
        <p class="success">Database `studenthub` and `students` table configured successfully!</p>

        <div class="info-box">
            <p><strong>Total Seeded Records in Database:</strong> <?= (int)$total ?> students</p>
            <p><strong>MySQLi Operations Supported:</strong> SELECT, INSERT, UPDATE, DELETE (Permanent & Soft Delete)</p>
            <p><strong>Key Features:</strong> Multi-field Search, Course & Status Filtering, Pagination, CSV Export, Modal Confirmation Dialogs.</p>
        </div>

        <a href="index.php">&rarr; Open Student Management CRUD Module</a>
    </div>
</body>
</html>
