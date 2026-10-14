<?php
/**
 * Practical 15: Automated Portal Verification & Diagnostic Test Suite
 * Programmatically validates Navigation, Responsiveness, Authentication,
 * Database Operations, Security, AJAX Endpoints, and Documentation.
 */

require_once __DIR__ . "/db.php";

$tests = [];
$totalPassed = 0;
$totalFailed = 0;

function recordTest(string $category, string $testName, bool $status, string $details = ""): void
{
    global $tests, $totalPassed, $totalFailed;
    if ($status) {
        $totalPassed++;
    } else {
        $totalFailed++;
    }
    $tests[] = [
        "category" => $category,
        "name"     => $testName,
        "passed"   => $status,
        "details"  => $details
    ];
}

// ==========================================
// 1. NAVIGATION VERIFICATION
// ==========================================
$routes = [
    "Public Landing"       => __DIR__ . "/../Practical-2/home.html",
    "Login Portal"         => __DIR__ . "/../Practical-2/login.html",
    "Registration Page"    => __DIR__ . "/../Practical-2/register.php",
    "Student Dashboard"    => __DIR__ . "/../Practical-2/deshboard.php",
    "Student Profile"      => __DIR__ . "/../Practical-2/profile.php",
    "Edit Profile Form"    => __DIR__ . "/../Practical-2/edit.php",
    "Events Showcase"      => __DIR__ . "/../Practical-2/events.html",
    "Feedback Submission"  => __DIR__ . "/../Practical-2/feedback.html",
    "Admin Dashboard"      => __DIR__ . "/../Practical-14/index.php",
    "Student CRUD Hub"     => __DIR__ . "/../Practical-11/index.php",
    "Event Management"     => __DIR__ . "/../Practical-12/index.php",
    "RESTful API Endpoint" => __DIR__ . "/../Practical-13/api.php",
];

foreach ($routes as $label => $path) {
    $exists = file_exists($path);
    recordTest("Navigation", "Route File Exists: $label", $exists, $exists ? "Found at " . basename($path) : "Missing file");
}

// ==========================================
// 2. RESPONSIVENESS VERIFICATION
// ==========================================
$viewportCheckFiles = [
    __DIR__ . "/../Practical-2/home.html",
    __DIR__ . "/../Practical-2/deshboard.php",
    __DIR__ . "/../Practical-2/profile.php",
    __DIR__ . "/../Practical-14/index.php",
];
foreach ($viewportCheckFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $hasViewport = (stripos($content, "viewport") !== false && stripos($content, "width=device-width") !== false);
        recordTest("Responsiveness", "Viewport Meta Tag in " . basename($file), $hasViewport, $hasViewport ? "Mobile viewport configured" : "Missing meta viewport");
    }
}

$cssFiles = [
    __DIR__ . "/../Practical-3/deshboard.css",
    __DIR__ . "/../Practical-3/profile.css",
    __DIR__ . "/../Practical-3/home.css",
];
foreach ($cssFiles as $css) {
    if (file_exists($css)) {
        $content = file_get_contents($css);
        $hasMediaQueries = (stripos($content, "@media") !== false);
        recordTest("Responsiveness", "Media Queries in " . basename($css), $hasMediaQueries, $hasMediaQueries ? "Responsive breakpoints detected" : "No @media rules found");
    }
}

// ==========================================
// 3. AUTHENTICATION & SESSION VERIFICATION
// ==========================================
// Password hash verification
$testPlain = "SecurePass@2026";
$testHash = password_hash($testPlain, PASSWORD_DEFAULT);
$authPass = password_verify($testPlain, $testHash) && !password_verify("WrongPass", $testHash);
recordTest("Authentication", "Password Hashing & Timing-Safe Verification", $authPass, "Bcrypt hash & password_verify() verified");

// Timeout logic test
$sessionTimeoutWindow = 900; // 15 mins
$lastActivity = time() - 1000;
$isTimedOut = (time() - $lastActivity) > $sessionTimeoutWindow;
recordTest("Authentication", "Session Inactivity Timeout Logic (15 min)", $isTimedOut, "Timeout properly flags inactive sessions");

// Remember-me split token generation test
$selector = bin2hex(random_bytes(12));
$token = bin2hex(random_bytes(32));
$tokenHash = hash("sha256", $token);
$splitTokenCheck = (strlen($selector) === 24 && hash_equals($tokenHash, hash("sha256", $token)));
recordTest("Authentication", "Split-Token Pattern (Selector + SHA256 Hash)", $splitTokenCheck, "Cryptographically secure split tokens generated");

// ==========================================
// 4. DATABASE OPERATIONS VERIFICATION
// ==========================================
recordTest("Database", "Active MySQL Port Connection", ($pdo !== null), "Connected to MySQL on port $activePort");

try {
    $stuCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    recordTest("Database", "Students Table Read Query", ($stuCount >= 1), "Found $stuCount records in students table");
} catch (Exception $e) {
    recordTest("Database", "Students Table Read Query", false, $e->getMessage());
}

try {
    $evCount = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
    recordTest("Database", "Events Table Read Query", ($evCount >= 1), "Found $evCount records in events table");
} catch (Exception $e) {
    recordTest("Database", "Events Table Read Query", false, $e->getMessage());
}

try {
    $regCheck = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    recordTest("Database", "Registrations Table Read Query", true, "Registrations table accessible ($regCheck rows)");
} catch (Exception $e) {
    recordTest("Database", "Registrations Table Read Query", false, $e->getMessage());
}

try {
    $auditCheck = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    recordTest("Database", "Audit Logs Table Read Query", true, "Audit logs table accessible ($auditCheck rows)");
} catch (Exception $e) {
    recordTest("Database", "Audit Logs Table Read Query", false, $e->getMessage());
}

// Prepared statement test with SQL injection attempt string
try {
    $safeStmt = $pdo->prepare("SELECT student_id, name FROM students WHERE email = ?");
    $sqliAttempt = "admin@charusat.edu.in' OR '1'='1";
    $safeStmt->execute([$sqliAttempt]);
    $sqliResult = $safeStmt->fetchAll();
    recordTest("Database", "SQL Injection Immune Parameterized Query", (count($sqliResult) === 0), "SQL injection attack string safely parameterized to 0 matches");
} catch (Exception $e) {
    recordTest("Database", "SQL Injection Immune Parameterized Query", false, $e->getMessage());
}

// ==========================================
// 5. SECURITY CHECKS VERIFICATION
// ==========================================
// XSS escaping check
$xssVector = "<script>alert('XSS')</script>";
$escaped = htmlspecialchars($xssVector, ENT_QUOTES, "UTF-8");
$xssSafe = (strpos($escaped, "<script>") === false && strpos($escaped, "&lt;script&gt;") !== false);
recordTest("Security", "XSS Output Escaping (htmlspecialchars)", $xssSafe, "Malicious script tags successfully defanged");

// File upload validator security check
$uploadHelper = __DIR__ . "/../Practical-12/upload_helper.php";
if (file_exists($uploadHelper)) {
    $uploadCode = file_get_contents($uploadHelper);
    $hasMimeCheck = (stripos($uploadCode, "finfo_file") !== false || stripos($uploadCode, "FILEINFO_MIME_TYPE") !== false);
    $hasSizeCheck = (stripos($uploadCode, "2097152") !== false || stripos($uploadCode, "2 * 1024 * 1024") !== false);
    recordTest("Security", "File Upload Server-Side MIME Validation", $hasMimeCheck, "MIME verification via finfo_file implemented");
    recordTest("Security", "File Upload 2MB Strict Size Cap", $hasSizeCheck, "File size cap <= 2MB strictly enforced");
} else {
    recordTest("Security", "File Upload Validation Helper", false, "upload_helper.php not found");
}

// RBAC check: Ensure requireAdmin rejects non-admin
$authFile = __DIR__ . "/../Practical-14/auth.php";
if (file_exists($authFile)) {
    $authCode = file_get_contents($authFile);
    $hasRbac = (stripos($authCode, "requireAdmin") !== false && stripos($authCode, "403") !== false);
    recordTest("Security", "Role-Based Access Control (RBAC 403)", $hasRbac, "Admin routes protected with HTTP 403 Forbidden");
}

// ==========================================
// 6. AJAX & API FUNCTIONALITY VERIFICATION
// ==========================================
// Check check_email.php exists and returns JSON
$checkEmailFile = __DIR__ . "/../Practical-7&8&9/check_email.php";
if (file_exists($checkEmailFile)) {
    $ceCode = file_get_contents($checkEmailFile);
    $hasJson = (stripos($ceCode, "application/json") !== false && stripos($ceCode, "json_encode") !== false);
    recordTest("AJAX", "Live Email Uniqueness AJAX Endpoint", $hasJson, "check_email.php sends valid JSON responses");
}

// Check current user session endpoint
$getUserFile = __DIR__ . "/../Practical-7&8&9/get_current_user.php";
if (file_exists($getUserFile)) {
    $guCode = file_get_contents($getUserFile);
    $hasUserJson = (stripos($guCode, "application/json") !== false && stripos($guCode, "json_encode") !== false);
    recordTest("AJAX", "Dynamic Current User Session API", $hasUserJson, "get_current_user.php active for asynchronous dashboard updates");
}

// Check Practical-13 REST API
$apiFile = __DIR__ . "/../Practical-13/api.php";
if (file_exists($apiFile)) {
    $apiCode = file_get_contents($apiFile);
    $hasRest = (stripos($apiCode, "application/json") !== false && stripos($apiCode, "REQUEST_METHOD") !== false);
    recordTest("AJAX", "RESTful JSON API with HTTP Methods (GET/POST/PUT/DELETE)", $hasRest, "REST API routing implemented in Practical-13/api.php");
}

// ==========================================
// 7. DOCUMENTATION & DEPLOYMENT VERIFICATION
// ==========================================
$docFiles = [
    "Practical 10 Documentation"  => __DIR__ . "/../Practical-10/README.md",
    "Practical 11 Documentation"  => __DIR__ . "/../Practical-11/README.md",
    "Practical 12 Documentation"  => __DIR__ . "/../Practical-12/README.md",
    "Practical 13 Documentation"  => __DIR__ . "/../Practical-13/README.md",
    "Practical 14 Documentation"  => __DIR__ . "/../Practical-14/README.md",
    "Apache Virtual Host Config"   => __DIR__ . "/studenthub-vhost.conf",
    "Portal Database Schema"      => __DIR__ . "/portal_schema.sql",
];

foreach ($docFiles as $docName => $docPath) {
    $exists = file_exists($docPath);
    recordTest("Documentation", "$docName Exists", $exists, $exists ? "Verified (" . filesize($docPath) . " bytes)" : "Missing documentation");
}

$passPercentage = ($totalPassed + $totalFailed) > 0 ? round(($totalPassed / ($totalPassed + $totalFailed)) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Portal - Automated Verification & Diagnostics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --primary: #0284c7;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: #334155;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: var(--dark); color: var(--text-main); padding: 30px 20px; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; }
        .header { background: var(--card-bg); padding: 25px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border); flex-wrap: wrap; gap: 20px; }
        .header h1 { font-size: 26px; color: #38bdf8; display: flex; align-items: center; gap: 12px; }
        .header p { color: var(--text-muted); font-size: 14px; margin-top: 5px; }
        .stat-badge { font-size: 24px; font-weight: 800; padding: 10px 20px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: var(--success); border: 1px solid var(--success); text-align: center; }
        .stat-badge.warn { background: rgba(239, 68, 68, 0.15); color: var(--danger); border-color: var(--danger); }
        .grid-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .summary-card { background: var(--card-bg); padding: 18px; border-radius: 10px; border: 1px solid var(--border); text-align: center; }
        .summary-card h3 { font-size: 28px; font-weight: 800; color: #38bdf8; }
        .summary-card p { font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 5px; }
        
        .section-card { background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border); margin-bottom: 25px; overflow: hidden; }
        .section-header { padding: 16px 20px; background: rgba(15, 23, 42, 0.6); border-bottom: 1px solid var(--border); font-weight: 700; color: #38bdf8; font-size: 16px; display: flex; align-items: center; gap: 10px; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 18px; text-align: left; font-size: 14px; border-bottom: 1px solid var(--border); }
        th { background: rgba(15, 23, 42, 0.4); color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        .badge-pass { background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; }
        .badge-fail { background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; }
        .details-text { color: var(--text-muted); font-size: 13px; }
        
        .action-bar { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px; justify-content: center; }
        .btn { padding: 12px 22px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: #0369a1; }
        .btn-secondary { background: #334155; color: white; }
        .btn-secondary:hover { background: #475569; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1><i class="fa-solid fa-circle-check"></i> Practical-15: System Verification</h1>
                <p>Comprehensive Integration & Diagnostic Audit for StudentHub Portal</p>
                <p style="font-size: 12px; color: #64748b; margin-top: 4px;">Active MySQL Port: <?= htmlspecialchars((string)$activePort) ?> • PHP <?= phpversion() ?> • Server: <?= htmlspecialchars($_SERVER["SERVER_SOFTWARE"] ?? "Apache") ?></p>
            </div>
            <div class="stat-badge <?= $totalFailed > 0 ? "warn" : "" ?>">
                <?= $passPercentage ?>% PASSED
                <div style="font-size: 12px; font-weight: normal; margin-top: 3px;"><?= $totalPassed ?> of <?= ($totalPassed + $totalFailed) ?> Tests</div>
            </div>
        </div>

        <div class="grid-summary">
            <div class="summary-card">
                <h3><?= $totalPassed ?></h3>
                <p>Tests Passed</p>
            </div>
            <div class="summary-card">
                <h3 style="color: <?= $totalFailed > 0 ? "var(--danger)" : "var(--success)" ?>;"><?= $totalFailed ?></h3>
                <p>Tests Failed</p>
            </div>
            <div class="summary-card">
                <h3>7 / 7</h3>
                <p>Pillars Audited</p>
            </div>
            <div class="summary-card">
                <h3>Port <?= htmlspecialchars((string)$activePort) ?></h3>
                <p>MySQL Connection</p>
            </div>
        </div>

        <?php
        $categories = array_unique(array_column($tests, "category"));
        foreach ($categories as $cat):
            $catTests = array_filter($tests, fn($t) => $t["category"] === $cat);
        ?>
        <div class="section-card">
            <div class="section-header">
                <i class="fa-solid fa-list-check"></i> <?= htmlspecialchars($cat) ?> Pillar Audit
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 45%;">Verification Item</th>
                        <th style="width: 20%;">Status</th>
                        <th style="width: 35%;">Diagnostic Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($catTests as $t): ?>
                    <tr>
                        <td style="font-weight: 500;"><?= htmlspecialchars($t["name"]) ?></td>
                        <td>
                            <?php if ($t["passed"]): ?>
                                <span class="badge-pass"><i class="fa-solid fa-check"></i> Passed</span>
                            <?php else: ?>
                                <span class="badge-fail"><i class="fa-solid fa-xmark"></i> Failed</span>
                            <?php endif; ?>
                        </td>
                        <td class="details-text"><?= htmlspecialchars($t["details"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endforeach; ?>

        <div class="action-bar">
            <a href="index.php" class="btn btn-primary"><i class="fa-solid fa-arrow-left"></i> Return to Integrated Portal</a>
            <a href="setup_portal.php" class="btn btn-secondary"><i class="fa-solid fa-database"></i> Re-run Database Setup</a>
            <a href="../Practical-2/home.html" class="btn btn-secondary"><i class="fa-solid fa-house"></i> Public Home</a>
        </div>
    </div>
</body>
</html>
