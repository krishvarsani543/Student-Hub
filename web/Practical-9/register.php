<?php

session_start();

require_once "db.php";


/* =====================================================
   1. REQUEST METHOD CHECK
   ===================================================== */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}


/* =====================================================
   2. CSRF TOKEN CHECK
   ===================================================== */

if (
    !isset($_POST["csrf_token"]) ||
    !isset($_SESSION["csrf_token"])
) {
    die("Invalid CSRF request.");
}

if (!hash_equals(
    $_SESSION["csrf_token"],
    $_POST["csrf_token"]
)) {
    die("CSRF token validation failed.");
}


/* =====================================================
   3. GET FORM DATA
   ===================================================== */

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");

$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm"] ?? "";

$dob = trim($_POST["dob"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$course = trim($_POST["course"] ?? "");

$country = trim($_POST["country"] ?? "");
$state = trim($_POST["state"] ?? "");
$city = trim($_POST["city"] ?? "");

$address = trim($_POST["address"] ?? "");

$terms = isset($_POST["terms"]);

$skills = $_POST["skill"] ?? [];


/* =====================================================
   4. SKILLS
   ===================================================== */

if (!is_array($skills)) {
    $skills = [$skills];
}

$cleanSkills = [];

foreach ($skills as $skill) {

    $skill = trim($skill);

    if ($skill !== "") {
        $cleanSkills[] = $skill;
    }
}

$skillsString = implode(", ", $cleanSkills);


/* =====================================================
   5. BACKEND VALIDATION
   ===================================================== */

/* Name */

if ($name === "") {
    die("Error: Name is required.");
}

if (!preg_match("/^[A-Za-z ]{2,50}$/", $name)) {
    die("Error: Name should contain only letters and spaces.");
}


/* Email */

if ($email === "") {
    die("Error: Email is required.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format.");
}


/* Mobile */

if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    die("Error: Please enter a valid 10-digit mobile number.");
}


/* Password */

if ($password === "") {
    die("Error: Password is required.");
}

if (!preg_match(
    "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d]{8,}$/",
    $password
)) {
    die(
        "Error: Password must contain at least 8 characters, "
        . "1 uppercase letter, 1 lowercase letter and 1 number."
    );
}


/* Confirm Password */

if ($password !== $confirmPassword) {
    die("Error: Password and Confirm Password do not match.");
}


/* Date of Birth */

if ($dob === "") {
    die("Error: Date of Birth is required.");
}


/* Gender */

if ($gender === "") {
    die("Error: Please select gender.");
}


/* Course */

if ($course === "") {
    die("Error: Please select a course.");
}


/* Country */

if ($country === "") {
    die("Error: Please select a country.");
}


/* State */

if ($state === "") {
    die("Error: Please select a state.");
}


/* City */

if ($city === "") {
    die("Error: Please select a city.");
}


/* Skills */

if (empty($cleanSkills)) {
    die("Error: Please select at least one skill.");
}


/* Address */

if ($address === "") {
    die("Error: Address is required.");
}


/* Terms */

if (!$terms) {
    die("Error: You must agree to the Terms & Conditions.");
}


/* =====================================================
   6. DUPLICATE EMAIL CHECK
   ===================================================== */

$stmt = $conn->prepare(
    "SELECT student_id
     FROM students
     WHERE email = ?"
);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $stmt->close();

    die("Error: This email is already registered.");

}

$stmt->close();


/* =====================================================
   7. PASSWORD HASHING
   ===================================================== */

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

if ($hashedPassword === false) {
    die("Error: Password hashing failed.");
}


/* =====================================================
   8. INSERT INTO students TABLE
   ===================================================== */

$stmt = $conn->prepare(
    "INSERT INTO students
    (
        name,
        email,
        mobile,
        password,
        dob,
        gender,
        course,
        country,
        state,
        city,
        skills,
        address,
        terms_accepted
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


/* =====================================================
   9. PREPARED STATEMENT
   ===================================================== */

$termsAccepted = 1;

$stmt->bind_param(
    "ssssssssssssi",
    $name,
    $email,
    $mobile,
    $hashedPassword,
    $dob,
    $gender,
    $course,
    $country,
    $state,
    $city,
    $skillsString,
    $address,
    $termsAccepted
);


/* =====================================================
   10. EXECUTE INSERT
   ===================================================== */

if ($stmt->execute()) {

    $stmt->close();
    $conn->close();

    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta name="viewport"
              content="width=device-width, initial-scale=1.0">

        <title>Registration Successful</title>

        <style>

            body {
                font-family: Arial, sans-serif;
                background: #f5f5f5;
                text-align: center;
                padding-top: 100px;
            }

            .success-box {
                width: 500px;
                max-width: 90%;
                margin: auto;
                background: white;
                padding: 35px;
                border-radius: 12px;
                box-shadow: 0 0 15px rgba(0,0,0,0.15);
            }

            h1 {
                color: green;
            }

            a {
                display: inline-block;
                margin-top: 20px;
                text-decoration: none;
                background: #5265f9;
                color: white;
                padding: 10px 20px;
                border-radius: 6px;
            }

        </style>

    </head>

    <body>

        <div class="success-box">

            <h1>Registration Successful!</h1>

            <p>
                Your StudentHub account has been created successfully.
            </p>

            <p>
                Your information has been securely stored in MySQL.
            </p>

            <a href="../Practical-2/register.php">
                Register Another Student
            </a>

        </div>

    </body>

    </html>

    <?php

} else {

    /*
       In case database UNIQUE constraint
       also catches duplicate email.
    */

    if ($conn->errno == 1062) {

        echo "<h2>Error: This email is already registered.</h2>";

    } else {

        echo "<h2>Registration Failed</h2>";

        echo "<p>Database Error: "
            . htmlspecialchars($stmt->error)
            . "</p>";

    }

    $stmt->close();
    $conn->close();
}

?>