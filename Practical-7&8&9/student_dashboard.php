
<?php
require_once "auth.php";
require_once "db.php";

if (($_SESSION["role"] ?? "") !== "student") {
    header("Location: login.php");
    exit;
}

// Fetch fresh details of student from database
if (isset($_SESSION["student_id"])) {
    $stmt = $pdo->prepare("SELECT name, course FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$_SESSION["student_id"]]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row["name"])) {
            $_SESSION["name"] = $row["name"];
        }
        if (!empty($row["course"])) {
            $_SESSION["course"] = $row["course"];
        }
    }
}

$studentName = $_SESSION["name"] ?? "Student";
$studentCourse = $_SESSION["course"] ?? "B.Tech Student";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Dashboard</title>

    <link rel="stylesheet" href="../Practical-3/deshboard.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                 alt="StudentHub Logo">

            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p><b>Empowering Students Through Technology</b></p>
            </div>
        </div>

        <nav>
            <center>
                <a href="student_dashboard.php">Dashboard</a>
                <a href="../Practical-2/events.html">Events</a>
                <a href="../Practical-2/profile.php">Profile</a>
                <a href="../Practical-2/feedback.html">Feedback</a>
                <a href="logout.php">Logout</a>
            </center>
        </nav>

        <marquee>
            Admission Open 2026 • AI Workshop Registration Started •
            National Hackathon Registration Open • Semester Examination Timetable Released
        </marquee>

        <button id="themeToggle">🌙 Dark Mode</button>
        <button id="weatherButton">🌦️ Weather</button>
    </header>

    <div class="container">
        <div class="main">
            <div class="topbar">
                <div class="search">
                    <input type="text" placeholder="Search...">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <div class="user">
                    <i class="fa-solid fa-bell"></i>
                    <a href="../Practical-2/profile.php" title="View Profile" style="display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;cursor:pointer;">
                        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                             alt="User">
                        <span id="topbarUserName">
                            <?= htmlspecialchars($studentName, ENT_QUOTES, "UTF-8") ?>
                        </span>
                    </a>
                </div>
            </div>

            <div class="welcome">
                <h1 id="welcomeHeading">
                    Welcome Back
                    <?= htmlspecialchars($studentName, ENT_QUOTES, "UTF-8") ?> 👋
                </h1>
                <p id="welcomeCourse"><?= htmlspecialchars($studentCourse, ENT_QUOTES, "UTF-8") ?></p>
            </div>

            <div class="cards">
                <div class="card">
                    <i class="fa-solid fa-user-check"></i>
                    <h2>96%</h2>
                    <p>Attendance</p>
                </div>

                <div class="card">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <h2>8.75</h2>
                    <p>CGPA</p>
                </div>

                <div class="card">
                    <i class="fa-solid fa-book"></i>
                    <h2>7</h2>
                    <p>Assignments</p>
                </div>

                <div class="card">
                    <i class="fa-solid fa-diagram-project"></i>
                    <h2>3</h2>
                    <p>Projects</p>
                </div>
            </div>

            <div class="dashboard-content">
                <div class="left-panel">
                    <div class="box">
                        <h2>Quick Links</h2>
                        <a href="../Practical-2/profile.php">
                            <i class="fa-solid fa-user"></i> My Profile
                        </a>
                        <a href="../Practical-2/attendance.html">
                            <i class="fa-solid fa-calendar-check"></i> Attendance
                        </a>
                        <a href="../Practical-2/result.html">
                            <i class="fa-solid fa-square-poll-vertical"></i> Results
                        </a>
                        <a href="../Practical-2/fees.html">
                            <i class="fa-solid fa-wallet"></i> Fees
                        </a>
                        <a href="../Practical-2/assignment.html">
                            <i class="fa-solid fa-book"></i> Assignments
                        </a>
                    </div>
                </div>

                <div class="right-panel">
                    <div class="box">
                        <h2>Latest Notices</h2>
                        <ul>
                            <li>📢 Internal Exam starts from 10 August.</li>
                            <li>📢 Semester Project Submission: 20 August.</li>
                            <li>📢 Hackathon Registration is Open.</li>
                            <li>📢 Library will remain closed on Sunday.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="activity">
                <h2>Recent Activity</h2>
                <ul>
                    <li>✅ Assignment 7 Submitted Successfully</li>
                    <li>✅ Attendance Updated</li>
                    <li>✅ Semester Fees Paid</li>
                    <li>✅ New Result Published</li>
                    <li>✅ New Notice Added</li>
                </ul>
            </div>

            <footer>
                <p>© 2026 StudentHub | All Rights Reserved</p>
            </footer>
        </div>
    </div>

    <div id="successPopup">
        <h2>Login Successful 🎉</h2>
        <p>You are logged in successfully</p>
    </div>

    <div id="weatherPopup">
        <div id="weatherPopupContent">
            <button id="closeWeather">×</button>
            <h2>🌦️ Current Weather</h2>
            <div id="weatherInfo">
                <p>Click Weather to get your current location and weather.</p>
            </div>
            <div id="weatherMap"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../Practical-4&5/deshboard.js"></script>
</body>
</html>