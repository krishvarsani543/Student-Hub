
<?php
session_start();

require_once "db.php";
require_once "remember.php";

if (!isset($_SESSION["student_id"])) {
    restoreRememberedSession($pdo);

    if (isset($_SESSION["student_id"])) {
        if (($_SESSION["role"] ?? "") === "admin") {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit;
    }
}

if (isset($_SESSION["student_id"])) {
    if (($_SESSION["role"] ?? "") === "admin") {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT student_id, name, email, password, role, course FROM students WHERE email = :email LIMIT 1"
        );

        $stmt->execute(["email" => $email]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($password, $student["password"])) {
            session_regenerate_id(true);

            $_SESSION["student_id"] = $student["student_id"];
            $_SESSION["name"] = $student["name"];
            $_SESSION["email"] = $student["email"];
            $_SESSION["role"] = $student["role"];
            $_SESSION["course"] = $student["course"] ?? "B.Tech Student";

            if (isset($_POST["remember"]) && $_POST["remember"] === "1") {
                createRememberToken($pdo, (int) $student["student_id"]);
            } else {
                clearRememberToken($pdo);
            }

            $redirect = trim($_POST["redirect"] ?? $_GET["redirect"] ?? "");
            if ($student["role"] === "admin") {
                header("Location: admin_dashboard.php");
            } else {
                if (!empty($redirect) && (strpos($redirect, "deshboard") !== false || strpos($redirect, "dashboard") !== false)) {
                    header("Location: " . $redirect);
                } else {
                    header("Location: student_dashboard.php?login=success");
                }
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
    <title>StudentHub Login</title>

    <link rel="stylesheet" href="../Practical-3/login.css">
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
        <h2>Login</h2>

        <?php if ($error !== ""): ?>
            <p id="errormessage" style="color: red;">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php else: ?>
            <p id="errormessage"></p>
        <?php endif; ?>

        <table>
            <tr>
                <td><label for="email">Email</label></td>
                <td>
                    <input type="email" id="email" name="email"
                           placeholder="Enter Your Email" required>
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
                           name="remember" value="1">
                    <label for="remember">Remember Me</label>
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

    <div class="bottom">
        <p>New User?
            <a href="../Practical-2/register.html">Create an Account</a>
        </p>
        <br>
        <small>© 2026 StudentHub. All Rights Reserved.</small>
    </div>

    <script src="../Practical-4&5/login.js"></script>

</body>
</html>