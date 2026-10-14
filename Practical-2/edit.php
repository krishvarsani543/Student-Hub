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

$studentId = $_SESSION["student_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $dob = trim($_POST["dob"] ?? "");
    $gender = trim($_POST["gender"] ?? "Male");
    $course = trim($_POST["course"] ?? "");
    $address = trim($_POST["address"] ?? "");

    // Sanitize mobile if user entered +91
    $mobile = preg_replace('/^\+91\s*/', '', $mobile);

    if ($name !== "") {
        $updateStmt = $pdo->prepare(
            "UPDATE students SET name = ?, mobile = ?, dob = ?, gender = ?, course = ?, address = ? WHERE student_id = ?"
        );
        $updateStmt->execute([$name, $mobile, $dob, $gender, $course, $address, $studentId]);

        $_SESSION["name"] = $name;
        $_SESSION["course"] = $course;

        header("Location: profile.php?updated=1");
        exit;
    } else {
        $message = "Please enter your name.";
    }
}

$stmt = $pdo->prepare("SELECT student_id, name, email, mobile, dob, gender, course, address FROM students WHERE student_id = ? LIMIT 1");
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: login.html");
    exit;
}

$studentRoll = "";
if (preg_match('/^([0-9]{2}d[a-zA-Z]{2}[0-9]{3})@/i', $student["email"] ?? "", $matches)) {
    $studentRoll = strtoupper($matches[1]);
} else {
    $studentRoll = "STU-" . str_pad((string)($student["student_id"] ?? "1"), 4, "0", STR_PAD_LEFT);
}

$currentGender = strtolower($student["gender"] ?? "male");
$currentCourse = $student["course"] ?? "Computer Science";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../Practical-3/edit.css">
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
</header>

<h1 align="center" style="color:black">
    Edit Profile
</h1>

<?php if ($message !== ""): ?>
    <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($message, ENT_QUOTES, "UTF-8") ?></p>
<?php endif; ?>

<form action="edit.php" method="POST">
    <table cellpadding="8">
        <tr>
            <td>Full Name : </td>
            <td>
                <input type="text" name="name" value="<?= htmlspecialchars($student["name"] ?? "", ENT_QUOTES, "UTF-8") ?>" required>
            </td>
        </tr>

        <tr>
            <td>Student ID : </td>
            <td>
                <input type="text" value="<?= htmlspecialchars($studentRoll, ENT_QUOTES, "UTF-8") ?>" readonly>
            </td>
        </tr>

        <tr>
            <td>Email : </td>
            <td>
                <input type="email" value="<?= htmlspecialchars($student["email"] ?? "", ENT_QUOTES, "UTF-8") ?>" readonly style="background-color: #f0f0f0;">
            </td>
        </tr>

        <tr>
            <td>Phone Number :</td>
            <td>
                <input type="tel" name="mobile" value="<?= htmlspecialchars(!empty($student["mobile"]) ? "+91 " . $student["mobile"] : "", ENT_QUOTES, "UTF-8") ?>">
            </td>
        </tr>

        <tr>
            <td>Department / Course : </td>
            <td>
                <select name="course">
                    <option <?= ($currentCourse === "Computer Science" || stripos($currentCourse, "Computer Science") !== false) ? "selected" : "" ?>>Computer Science</option>
                    <option <?= (stripos($currentCourse, "CE") !== false || stripos($currentCourse, "Computer Engineering") !== false) ? "selected" : "" ?>>Computer Engineering</option>
                    <option <?= (stripos($currentCourse, "Information Technology") !== false || stripos($currentCourse, "IT") !== false) ? "selected" : "" ?>>Information Technology</option>
                    <option <?= (stripos($currentCourse, "CSE") !== false) ? "selected" : "" ?>>B.Tech CSE</option>
                    <option <?= (stripos($currentCourse, "Electronics") !== false) ? "selected" : "" ?>>Electronics</option>
                    <option <?= (stripos($currentCourse, "Mechanical") !== false) ? "selected" : "" ?>>Mechanical</option>
                </select>
            </td>
        </tr>

        <tr>
            <td>Semester : </td>
            <td>
                <select name="semester">
                    <option selected>1</option>
                    <option>2</option>
                    <option>3</option>
                    <option>4</option>
                    <option>5</option>
                    <option>6</option>
                    <option>7</option>
                    <option>8</option>
                </select>
            </td>
        </tr>

        <tr>
            <td>Gender : </td>
            <td>
                <input type="radio" name="gender" value="Male" <?= ($currentGender === "male") ? "checked" : "" ?>> Male
                <input type="radio" name="gender" value="Female" <?= ($currentGender === "female") ? "checked" : "" ?>> Female
            </td>
        </tr>

        <tr>
            <td>Date of Birth : </td>
            <td>
                <input type="date" name="dob" value="<?= htmlspecialchars($student["dob"] ?? "", ENT_QUOTES, "UTF-8") ?>">
            </td>
        </tr>

        <tr>
            <td>Address :</td>
            <td>
                <textarea name="address" rows="4" cols="30"><?= htmlspecialchars($student["address"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
            </td>
        </tr>

        <tr>
            <td>Upload Photo : </td>
            <td>
                <input type="file" name="photo">
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <br>
                <input type="submit" value="Save Changes">
                <input type="reset" value="Reset">
            </td>
        </tr>
    </table>
</form>

<br>

<a href="profile.php">
    Back to Profile
</a>

</body>
</html>
