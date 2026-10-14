<?php
/**
 * Practical 12: Database Setup & Asset Verification
 */

require_once __DIR__ . "/db.php";

$count = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$logCount = (int)$pdo->query("SELECT COUNT(*) FROM event_audit_logs")->fetchColumn();
$uploadDir = __DIR__ . "/uploads/posters";
$hasHtaccess = file_exists($uploadDir . "/.htaccess");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 12 Setup</title>
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
        <h2>Practical 12: Event Management & Poster Upload Setup</h2>
        <p class="success">Database `studenthub` and tables verified successfully!</p>

        <div class="info-box">
            <p><strong>Total Seeded Events:</strong> <?= $count ?> events</p>
            <p><strong>Total Audit Logs:</strong> <?= $logCount ?> logs</p>
            <p><strong>Upload Folder:</strong> <code>uploads/posters/</code> (Directory Protected with .htaccess: <?= $hasHtaccess ? "Yes" : "No" ?>)</p>
            <p><strong>File Validation:</strong> MIME Type (finfo), Max 2MB Size, Extension (.jpg, .png, .webp), Image Integrity (getimagesize).</p>
            <p><strong>Advanced Extensions:</strong> Cryptographic file renaming, upload folder script execution lockdown, and audit trail.</p>
        </div>

        <a href="index.php">&rarr; Open Event Management Module</a>
    </div>
</body>
</html>
