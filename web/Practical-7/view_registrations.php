<?php

$file = __DIR__ . "/registrations.json";

if (!file_exists($file)) {
    die("Registration file not found.");
}

$jsonData = file_get_contents($file);
$registrations = json_decode($jsonData, true);

if (!is_array($registrations)) {
    die("Invalid registration data.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Students</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #123B5D;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 30px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #123B5D;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }
    </style>
</head>
<body>

<h1>Registered Students</h1>

<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Course</th>
        <th>Country</th>
        <th>State</th>
        <th>City</th>
        <th>Skills</th>
        <th>Date of Birth</th>
        <th>Gender</th>
        <th>Address</th>
        <th>Registered At</th>
    </tr>

    <?php foreach ($registrations as $student): ?>
        <tr>
            <td><?= htmlspecialchars($student["name"]) ?></td>
            <td><?= htmlspecialchars($student["email"]) ?></td>
            <td><?= htmlspecialchars($student["mobile"]) ?></td>
            <td><?= htmlspecialchars($student["course"]) ?></td>
            <td><?= htmlspecialchars($student["country"]) ?></td>
            <td><?= htmlspecialchars($student["state"]) ?></td>
            <td><?= htmlspecialchars($student["city"]) ?></td>
            <td><?= htmlspecialchars(implode(", ", $student["skills"])) ?></td>
            <td><?= htmlspecialchars($student["dob"]) ?></td>
            <td><?= htmlspecialchars($student["gender"]) ?></td>
            <td><?= htmlspecialchars($student["address"]) ?></td>
            <td><?= htmlspecialchars($student["registeredAt"]) ?></td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>