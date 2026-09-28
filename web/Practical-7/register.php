<?php

session_start();

if (!isset($_POST["csrf_token"]) || !isset($_SESSION["csrf_token"])) {
    die("Invalid CSRF request.");
}

if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
    die("CSRF token validation failed.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request method.");
}


function cleanInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, "UTF-8");
}



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

if (!is_array($skills)) {
    $skills = [$skills];
}

$cleanSkills = [];

foreach ($skills as $skill) {
    $cleanSkills[] = cleanInput($skill);
}




if ($name === "") {
    die("Error: Name is required.");
}

if (!preg_match("/^[A-Za-z ]+$/", $name)) {
    die("Error: Name should contain only letters and spaces.");
}


if (!preg_match("/^[0-9]{2}d[a-zA-Z]{2}[0-9]{3}@charusat\.edu\.in$/", $email)) {
    die("Error: Please enter a valid CHARUSAT email.");
}


if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    die("Error: Mobile number must contain exactly 10 digits.");
}


if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) {
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


$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


$student = [
    "name" => $name,
    "email" => $email,
    "mobile" => $mobile,
    "password" => $hashedPassword,
    "dob" => $dob,
    "gender" => $gender,
    "course" => $course,
    "country" => $country,
    "state" => $state,
    "city" => $city,
    "skills" => $cleanSkills,
    "address" => $address,
    "termsAccepted" => true,
    "registeredAt" => date("Y-m-d H:i:s")
];


$file = __DIR__ . "/registrations.json";


if (!file_exists($file)) {
    file_put_contents($file, "[]");
}


$jsonData = file_get_contents($file);

$registrations = json_decode($jsonData, true);


if (!is_array($registrations)) {
    $registrations = [];
}


$registrations[] = $student;



$newJsonData = json_encode(
    $registrations,
    JSON_PRETTY_PRINT
);



if (file_put_contents($file, $newJsonData, LOCK_EX) === false) {
    die("Error: Unable to save registration data.");
}


?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registration Success</title>

    <style>

        body {
            font-family: Arial, Helvetica, sans-serif;
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
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
        }

        h1 {
            color: green;
        }

        p {
            color: #333;
            font-size: 17px;
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

        a:hover {
            background: #0d2b43;
        }

    </style>

</head>

<body>

    <div class="success-box">

        <h1>Registration Successful!</h1>

        <p>
            Your registration data has been successfully saved.
        </p>

        <a href="../Practical-2/login.html">
            Go to Login
        </a>

    </div>

</body>

</html>