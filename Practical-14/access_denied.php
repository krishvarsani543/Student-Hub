<?php
/**
 * Practical 14: Access Denied (HTTP 403) Screen
 * Rendered when a student attempts to access admin-restricted areas.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - Access Denied (Practical 14)</title>
    <link rel="stylesheet" href="../Practical-3/deshboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f8fafc; font-family: Arial, sans-serif; }
        .denied-box {
            max-width: 650px;
            margin: 60px auto;
            background: white;
            border-radius: 12px;
            padding: 35px 30px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-top: 6px solid #ef4444;
        }
        .denied-icon {
            font-size: 64px;
            color: #ef4444;
            margin-bottom: 15px;
        }
        .denied-title {
            color: #991b1b;
            font-size: 26px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        .denied-msg {
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .role-indicator {
            background: #fee2e2;
            color: #991b1b;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .btn-link {
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-blue { background: #123B5D; color: white; }
        .btn-blue:hover { background: #0c273e; }
        .btn-yellow { background: #f59e0b; color: white; }
        .btn-yellow:hover { background: #d97706; }
    </style>
</head>
<body>

    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">
            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p><b>Practical 14: Role-Based Access Control Security Guard</b></p>
            </div>
        </div>
    </header>

    <div class="denied-box">
        <div class="denied-icon">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h2 class="denied-title">403 Forbidden: Access Restricted</h2>
        
        <div class="role-indicator">
            <i class="fa-solid fa-user-lock"></i> Current Role: <?= htmlspecialchars(strtoupper($_SESSION["role"] ?? "STUDENT"), ENT_QUOTES, "UTF-8") ?>
        </div>

        <p class="denied-msg">
            You do not have administrative privileges to access this dashboard.<br>
            The system detected your role as <strong><?= htmlspecialchars($_SESSION["role"] ?? "student", ENT_QUOTES, "UTF-8") ?></strong> and blocked access to the Administrator Console.
        </p>

        <div class="btn-group">
            <a href="student_view.php" class="btn-link btn-blue">
                <i class="fa-solid fa-graduation-cap"></i> Go to Student View
            </a>
            <a href="switch_role.php?role=admin" class="btn-link btn-yellow">
                <i class="fa-solid fa-key"></i> Switch to Admin Role (Testing)
            </a>
        </div>
    </div>

</body>
</html>
