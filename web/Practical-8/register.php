<?php

session_start();

require_once "db.php";


/* =========================
   CSRF CHECK
   ========================= */

if (!isset($_POST["csrf_token"]) || !isset($_SESSION["csrf_token"])) {
    die("Invalid CSRF request.");
}

if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
    die("CSRF token validation failed.");
}


/* =========================
   REQUEST METHOD
   ========================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}


/* =========================
   CLEAN INPUT
   ========================= */

function cleanInput($data)
{
    return trim($data);
}


/* =========================
   GET FORM DATA
   ========================= */

$name = cleanInput($_POST["name"] ?? "");
$email = cleanInput($_POST["email"] ?? "");
$mobile = cleanInput($_POST["mobile"] ?? "");

$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm"] ?? "";

$dob = cleanInput($_POST["dob"] ?? "");
$gender = cleanInput($_POST["gender"] ?? "");
$course = cleanInput($_POST["course"] ?? "");

$country = cleanInput($_POST["country"] ?? "");
$state = cleanInput($_POST["state"] ?? "");
$city = cleanInput($_POST["city"] ?? "");

$address = cleanInput($_POST["address"] ?? "");

$terms = isset($_POST["terms"]);

$skills = $_POST["skill"] ?? [];


/* =========================
   SKILLS
   ========================= */

if (!is_array($skills)) {
    $skills = [$skills];
}

$cleanSkills = [];

foreach ($skills as $skill) {
    $cleanSkills[] = cleanInput($skill);
}


/* =========================
   VALIDATION
   ========================= */

if ($name === "") {
    die("Error: Name is required.");
}

if (!preg_match("/^[A-Za-z ]+$/", $name)) {
    die("Error: Name should contain only letters and spaces.");
}


if (!preg_match(
    "/^[0-9]{2}d[a-zA-Z]{2}[0-9]{3}@charusat\.edu\.in$/",
    $email
)) {
    die("Error: Please enter a valid CHARUSAT email.");
}


if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    die("Error: Mobile number must contain exactly 10 digits.");
}


if (!preg_match(
    "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d]{8,}$/",
    $password
)) {
    die("Error: Password must contain at least 8 characters, 1 uppercase letter, 1 lowercase letter and 1 number.");
}


if ($password !== $confirmPassword) {
    die("Error: Password and Confirm Password do not match.");
}


if ($dob === "") {
    die("Error: Date of Birth is required.");
}


if ($gender === "") {
    die("Error: Please select gender.");
}


if ($course === "") {
    die("Error: Please select a course.");
}


if ($country === "") {
    die("Error: Please select a country.");
}


if ($state === "") {
    die("Error: Please select a state.");
}


if ($city === "") {
    die("Error: Please select a city.");
}


if (empty($cleanSkills)) {
    die("Error: Please select at least one skill.");
}


if ($address === "") {
    die("Error: Address is required.");
}


if (!$terms) {
    die("Error: You must agree to the Terms & Conditions.");
}


/* =========================
   HASH PASSWORD
   ========================= */

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* =========================
   CONVERT SKILLS TO STRING
   ========================= */

$skillsString = implode(", ", $cleanSkills);


/* =========================
   INSERT INTO MYSQL
   ========================= */

try {

    $stmt = $pdo->prepare("
        INSERT INTO students
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
        (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");


    $stmt->execute([
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
        true
    ]);


    /* =========================
       SUCCESS
       ========================= */

    ?>

    <!DOCTYPE html>

    <html>

    <head>

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
                padding: 10px 25px;
                background: #123B5D;
                color: white;
                text-decoration: none;
                border-radius: 6px;
            }

        </style>

    </head>


    <body>

        <div class="success-box">

            <h1>Registration Successful!</h1>

            <p>
                Your registration data has been successfully
                saved in MySQL.
            </p>

            <a href="../Practical-2/login.html">
                Go to Login
            </a>

        </div>

    </body>

    </html>

    <?php


} catch (PDOException $e) {


    /* DUPLICATE EMAIL */

    if ($e->getCode() == 23000) {

        die(
            "Error: This email is already registered."
        );

    }


    /* OTHER DATABASE ERROR */

    die(
        "Database Error: " .
        $e->getMessage()
    );

}

?>