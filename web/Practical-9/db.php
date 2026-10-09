<?php

$host = "127.0.0.1";
$port = 3307;
$username = "root";
$password = "";
$database = "studenthub";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    $port
);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>