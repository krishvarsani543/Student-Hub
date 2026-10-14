<?php
session_start();
header("Content-Type: application/json");

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/remember.php";

if (!isset($_SESSION["student_id"])) {
    restoreRememberedSession($pdo);
}

if (isset($_SESSION["student_id"])) {
    $stmt = $pdo->prepare("SELECT student_id, name, email, mobile, dob, gender, course, country, state, city, address, skills, role FROM students WHERE student_id = ? LIMIT 1");
    $stmt->execute([$_SESSION["student_id"]]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["name"] = $row["name"];
        if (!empty($row["course"])) {
            $_SESSION["course"] = $row["course"];
        }

        // Parse skills from json or csv
        $skillsList = [];
        if (!empty($row["skills"])) {
            $decoded = json_decode($row["skills"], true);
            if (is_array($decoded)) {
                $skillsList = $decoded;
            } else {
                $skillsList = array_map("trim", explode(",", $row["skills"]));
            }
        }

        echo json_encode([
            "loggedIn"   => true,
            "student_id" => $row["student_id"],
            "name"       => $row["name"],
            "email"      => $row["email"],
            "mobile"     => $row["mobile"],
            "dob"        => $row["dob"],
            "gender"     => $row["gender"],
            "course"     => $row["course"] ?? "B.Tech Student",
            "country"    => $row["country"],
            "state"      => $row["state"],
            "city"       => $row["city"],
            "address"    => $row["address"],
            "skills"     => $skillsList,
            "role"       => $row["role"] ?? "student"
        ]);
        exit;
    }
}

echo json_encode(["loggedIn" => false]);
