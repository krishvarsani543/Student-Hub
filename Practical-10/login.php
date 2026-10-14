<?php
/**
 * Practical 10: Secure Login Page
 * Features:
 * - password_verify() verification
 * - session_regenerate_id(true) against session fixation
 * - Role-based redirection (admin -> admin_dashboard.php, student -> student_dashboard.php)
 * - Session timeout notification handling
 * - Secure remember-me token integration
 * - Last login timestamp tracking
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (ini_get("session.use_cookies")) {
        $isSecure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
        session_set_cookie_params([
            "lifetime" => 0,
            "path"     => "/",
            "domain"   => "",
            "secure"   => $isSecure,
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }
    session_start();
}

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/remember.php";

// If not logged in, attempt restoring via Remember-Me token
if (!isset($_SESSION["student_id"])) {
    restoreRememberedSession($pdo);
}

// If already authenticated, redirect immediately based on role
if (isset($_SESSION["student_id"])) {
    if (($_SESSION["role"] ?? "") === "admin") {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit;
}

$error = "";
$successNotice = "";

// Check for feedback messages via GET query params
$msg = $_GET["msg"] ?? "";
if ($msg === "timeout") {
    $error = "Session timed out due to 15 minutes of inactivity. Please log in again.";
} elseif ($msg === "unauthorized") {
    $error = "Unauthorized access attempt blocked! Please log in first.";
} elseif ($msg === "access_denied") {
    $error = "Access Denied! You do not have permission to view the requested dashboard.";
} elseif ($msg === "logout") {
    $successNotice = "You have been logged out securely. Sessions and tokens cleared.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter both email and password.";
    } else {
        // Prepare statement to query student/user record securely
        $stmt = $pdo->prepare(
            "SELECT student_id, name, email, password, role, last_login 
             FROM students 
             WHERE email = :email 
             LIMIT 1"
        );
        $stmt->execute(["email" => $email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password hash
        if ($student && password_verify($password, $student["password"])) {
            // Prevent Session Fixation attack by regenerating session ID
            session_regenerate_id(true);

            // Populate session
            $_SESSION["student_id"]    = (int)$student["student_id"];
            $_SESSION["name"]          = $student["name"];
            $_SESSION["email"]         = $student["email"];
            $_SESSION["role"]          = $student["role"];
            $_SESSION["last_login"]    = $student["last_login"]; // previous login
            $_SESSION["last_activity"] = time();

            // Intermediate Extension: Update last_login timestamp in database
            $updateLogin = $pdo->prepare("UPDATE students SET last_login = NOW() WHERE student_id = ?");
            $updateLogin->execute([$student["student_id"]]);

            // Advanced Extension: Process Remember-Me checkbox
            if (isset($_POST["remember"]) && $_POST["remember"] === "1") {
                createRememberToken($pdo, (int)$student["student_id"]);
            } else {
                clearRememberToken($pdo);
            }

            // Role-based redirection
            if ($student["role"] === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit;
        }

        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Login - Practical 10</title>

    <link rel="stylesheet" href="../Practical-3/login.css">
    <style>
        .alert-box {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
            font-weight: 500;
            text-align: left;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .test-credentials {
            width: 500px;
            max-width: 90%;
            margin: 20px auto 0 auto;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 15px 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            font-size: 14px;
        }
        .test-credentials h4 {
            color: #123B5D;
            margin-bottom: 8px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .credential-pill {
            display: inline-block;
            background: #e2e8f0;
            padding: 6px 12px;
            border-radius: 6px;
            margin: 4px 4px 4px 0;
            cursor: pointer;
            transition: all 0.2s;
            font-family: monospace;
            font-size: 13px;
        }
        .credential-pill:hover {
            background: #cbd5e1;
            transform: translateY(-1px);
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-admin { background: #fee2e2; color: #b91c1c; }
        .badge-student { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">

            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <h3 style="color: white;">Welcome Back</h3>
                <p style="color: white;">
                    Don't have an account?
                    <a href="../Practical-2/register.html" style="color: chocolate;">
                        Register Here
                    </a>
                </p>
            </div>
        </div>
    </header>

    <form id="loginForm" action="" method="POST">
        <h2>Secure Login</h2>

        <?php if ($error !== ""): ?>
            <div class="alert-box alert-error">
                <strong>Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <?php if ($successNotice !== ""): ?>
            <div class="alert-box alert-success">
                <strong>Notice:</strong> <?= htmlspecialchars($successNotice, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <table>
            <tr>
                <td><label for="email">Email</label></td>
                <td>
                    <input type="email" id="email" name="email"
                           placeholder="Enter Your Email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </td>
            </tr>

            <tr>
                <td><label for="password">Password</label></td>
                <td>
                    <input type="password" id="password" name="password"
                           placeholder="Enter Your Password" required>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="forgot">
                    <a href="#">Forgot Password?</a>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="remember">
                    <input type="checkbox" id="remember"
                           name="remember" value="1"
                           <?= (isset($_POST["remember"]) ? "checked" : "") ?>>
                    <label for="remember">Remember Me (30 Days)</label>
                </td>
            </tr>

            <tr>
                <td colspan="2" class="buttons">
                    <button id="loginBtn" type="submit">Login</button>
                    <button type="reset">Clear</button>
                </td>
            </tr>
        </table>
    </form>

    <!-- Quick Credentials Helper for Lab Evaluation & Viva Testing -->
    <div class="test-credentials">
        <h4>
            <span>Quick Test Accounts</span>
            <small style="font-weight: normal; color: #6b7280;">(Click to auto-fill)</small>
        </h4>
        <div style="margin-bottom: 6px;">
            <span class="badge badge-admin">Admin</span>
            <span class="credential-pill" onclick="fillCreds('admin@charusat.edu.in', 'Admin@123')">
                admin@charusat.edu.in / Admin@123
            </span>
        </div>
        <div>
            <span class="badge badge-student">Student</span>
            <span class="credential-pill" onclick="fillCreds('22dit045@charusat.edu.in', 'Student@123')">
                22dit045@charusat.edu.in / Student@123
            </span>
        </div>
    </div>

    <div class="bottom">
        <p>New User?
            <a href="../Practical-2/register.html">Create an Account</a>
        </p>
        <br>
        <small>&copy; 2026 StudentHub. All Rights Reserved. | Practical 10 Authentication System</small>
    </div>

    <script>
        function fillCreds(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }
    </script>

</body>
</html>
