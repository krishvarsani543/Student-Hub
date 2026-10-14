<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../Practical-7&8&9/db.php";
require_once __DIR__ . "/../Practical-7&8&9/remember.php";

if (!isset($_SESSION["student_id"])) {
    restoreRememberedSession($pdo);
}

if (!isset($_SESSION["student_id"])) {
    header("Location: login.html");
    exit;
}

$stmt = $pdo->prepare("SELECT student_id, name, email, mobile, dob, gender, course, country, state, city, address, skills, role FROM students WHERE student_id = ? LIMIT 1");
$stmt->execute([$_SESSION["student_id"]]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: login.html");
    exit;
}

// Student roll number / ID extraction
$studentRoll = "";
if (preg_match('/^([0-9]{2}d[a-zA-Z]{2}[0-9]{3})@/i', $student["email"] ?? "", $matches)) {
    $studentRoll = strtoupper($matches[1]);
} else {
    $studentRoll = "STU-" . str_pad((string)($student["student_id"] ?? "1"), 4, "0", STR_PAD_LEFT);
}

// Formatted Date of Birth
$formattedDob = !empty($student["dob"]) ? date("d-m-Y", strtotime($student["dob"])) : "Not Provided";

// Parse skills from database JSON array or comma separated
$skills = [];
if (!empty($student["skills"])) {
    $decoded = json_decode($student["skills"], true);
    if (is_array($decoded)) {
        $skills = $decoded;
    } else {
        $skills = array_filter(array_map("trim", explode(",", $student["skills"])));
    }
}
if (empty($skills)) {
    $skills = ["Problem Solving", "Web Technologies"];
}

// Department / Course
$department = !empty($student["course"]) ? $student["course"] : "Information Technology";

// Address composition
$addressText = "";
if (!empty($student["address"])) {
    $addressText = $student["address"];
    if (!empty($student["city"]) && stripos($addressText, $student["city"]) === false) {
        $addressText .= ", " . $student["city"];
    }
} else {
    $parts = array_filter([$student["city"] ?? "", $student["state"] ?? "", $student["country"] ?? ""]);
    $addressText = !empty($parts) ? implode(", ", $parts) : "Anand, Gujarat";
}

$badgeRole = ($student["role"] ?? "") === "admin" ? "Administrator" : "Enrolled Student";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>

    <link rel="stylesheet" href="../Practical-3/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <header>
        <div class="header-content">
            <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
                alt="StudentHub Logo">

            <div class="header-text">
                <h1>StudentHub Portal</h1>
                <p style="color: white;">Empowering Students Through Technology</p>
            </div>
        </div>

        <nav>
            <a href="deshboard.php">Dashboard</a>
            <a href="events.html">Events</a>
            <a href="profile.php">Profile</a>
            <a href="feedback.html">Feedback</a>
            <a href="../Practical-7&8&9/logout.php">Logout</a>
        </nav>

        <marquee>
            Admission Open 2026 • AI Workshop Registration Started •
            National Hackathon Registration Open •
            Semester Examination Timetable Released
        </marquee>
    </header>

    <div class="container">

        <div class="profile-header">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                alt="Student">

            <h1 id="profileName"><?= htmlspecialchars($student["name"] ?? "Student", ENT_QUOTES, "UTF-8") ?></h1>

            <p id="profileCourse"><?= htmlspecialchars($student["course"] ?? "B.Tech Student", ENT_QUOTES, "UTF-8") ?></p>

            <span class="badge" id="profileBadge"><?= htmlspecialchars($badgeRole, ENT_QUOTES, "UTF-8") ?></span>
        </div>

        <div class="stats">
            <div class="card">
                <i class="fa-solid fa-graduation-cap"></i>
                <h2>8.75</h2>
                <p>CGPA</p>
            </div>

            <div class="card">
                <i class="fa-solid fa-user-check"></i>
                <h2>96%</h2>
                <p>Attendance</p>
            </div>

            <div class="card">
                <i class="fa-solid fa-folder-open"></i>
                <h2>3</h2>
                <p>Projects</p>
            </div>

            <div class="card">
                <i class="fa-solid fa-book"></i>
                <h2>7</h2>
                <p>Assignments</p>
            </div>
        </div>

        <div class="section">
            <h2>Personal Information</h2>

            <div class="details">
                <div>
                    <h4>Student ID</h4>
                    <p id="profileStudentId"><?= htmlspecialchars($studentRoll, ENT_QUOTES, "UTF-8") ?></p>
                </div>

                <div>
                    <h4>Date of Birth</h4>
                    <p id="profileDob"><?= htmlspecialchars($formattedDob, ENT_QUOTES, "UTF-8") ?></p>
                </div>

                <div>
                    <h4>Email</h4>
                    <p id="profileEmail"><?= htmlspecialchars($student["email"] ?? "", ENT_QUOTES, "UTF-8") ?></p>
                </div>

                <div>
                    <h4>Phone</h4>
                    <p id="profilePhone"><?= htmlspecialchars(!empty($student["mobile"]) ? "+91 " . $student["mobile"] : "Not Provided", ENT_QUOTES, "UTF-8") ?></p>
                </div>

                <div>
                    <h4>Department</h4>
                    <p id="profileDepartment"><?= htmlspecialchars($department, ENT_QUOTES, "UTF-8") ?></p>
                </div>

                <div>
                    <h4>Address</h4>
                    <p id="profileAddress"><?= htmlspecialchars($addressText, ENT_QUOTES, "UTF-8") ?></p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Skills</h2>

            <div class="skills" id="profileSkills">
                <?php foreach ($skills as $skill): ?>
                    <span><?= htmlspecialchars($skill, ENT_QUOTES, "UTF-8") ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h2>Achievements</h2>

            <ul>
                <li>🏆 Winner - Coding Competition</li>
                <li>💻 Web Development Workshop Completed</li>
                <li>🚀 National Hackathon Participant</li>
                <li>📜 Technical Certification</li>
            </ul>
        </div>

        <div class="buttons">
            <a href="edit.php">Edit Profile</a>
            <a href="deshboard.php">Dashboard</a>
            <a href="../Practical-7&8&9/logout.php">Logout</a>
        </div>

    </div>

</body>
</html>
