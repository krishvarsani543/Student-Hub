<?php
/**
 * Practical 14: Login Page with Role-Based Redirection
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . "/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare("SELECT student_id, name, email, password, role FROM students WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"]    = (int)$user["student_id"];
            $_SESSION["user_name"]  = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["role"]       = $user["role"];

            recordAuditLog($pdo, $user["student_id"], $user["email"], "LOGIN", "User logged in with role '{$user['role']}'");

            if ($user["role"] === "admin") {
                header("Location: index.php");
            } else {
                header("Location: student_view.php");
            }
            exit;
        }

        $error = "Invalid credentials. For quick testing use: admin@charusat.edu.in / Admin@123";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Login - Practical 14</title>
    <link rel="stylesheet" href="../Practical-3/login.css">
    <style>
        .test-box {
            width: 500px;
            max-width: 90%;
            margin: 15px auto 0 auto;
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            font-size: 13px;
        }
        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            background: #e2e8f0;
            cursor: pointer;
            margin: 4px 0;
            font-family: monospace;
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
                <p><b>Practical 14: Authentication & Role-Based Dashboard Portal</b></p>
            </div>
        </div>
    </header>

    <form id="loginForm" method="POST" action="">
        <h2>Role Login</h2>

        <?php if ($error !== ""): ?>
            <p style="color: #dc2626; font-size: 14px; margin-bottom: 15px; background: #fee2e2; padding: 10px; border-radius: 6px;">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <table>
            <tr>
                <td><label for="email">Email</label></td>
                <td>
                    <input type="email" id="email" name="email" placeholder="Enter email" required>
                </td>
            </tr>
            <tr>
                <td><label for="password">Password</label></td>
                <td>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="buttons">
                    <button type="submit" id="loginBtn">Login</button>
                    <button type="reset">Clear</button>
                </td>
            </tr>
        </table>
    </form>

    <div class="test-box">
        <strong>Quick 1-Click Credentials for Viva:</strong><br>
        <div class="pill" onclick="set('admin@charusat.edu.in', 'Admin@123')">
            🛡️ Admin: admin@charusat.edu.in / Admin@123
        </div><br>
        <div class="pill" onclick="set('22dit045@charusat.edu.in', 'Student@123')">
            🎓 Student: 22dit045@charusat.edu.in / Student@123
        </div>
    </div>

    <script>
        function set(e, p) {
            document.getElementById('email').value = e;
            document.getElementById('password').value = p;
        }
    </script>
</body>
</html>
