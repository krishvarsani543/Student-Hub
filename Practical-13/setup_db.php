<?php
/**
 * Practical 13: Database & API Setup Tool
 */

require_once __DIR__ . "/db.php";

$count = (int)$pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 13 Setup</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; padding: 40px; color: #333; line-height: 1.6; }
        .card { max-width: 650px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #123B5D; margin-top: 0; }
        .success { color: #16a34a; font-weight: bold; }
        .info-box { background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 15px 0; font-size: 14px; }
        a { display: inline-block; background: #123B5D; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 10px; }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Practical 13: JSON API & Fetch CRUD Setup</h2>
        <p class="success">Database `studenthub` and tables verified successfully!</p>

        <div class="info-box">
            <p><strong>Available Event Records:</strong> <?= $count ?> events</p>
            <p><strong>JSON API Endpoint:</strong> <code>api.php</code> (Supports GET, POST, PUT, DELETE)</p>
            <p><strong>RESTful Status Codes:</strong> <code>200 OK</code>, <code>201 Created</code>, <code>404 Not Found</code>, <code>422 Unprocessable Entity</code>, <code>500 Internal Server Error</code></p>
            <p><strong>Frontend:</strong> JavaScript <code>fetch()</code> API consuming JSON asynchronously without full page reload, debounced search (350ms), and live toast state alerts.</p>
        </div>

        <a href="index.php">&rarr; Open Fetch API CRUD Dashboard</a>
    </div>
</body>
</html>
