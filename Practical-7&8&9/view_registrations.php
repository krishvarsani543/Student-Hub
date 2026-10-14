
<?php

require_once "db.php";

$sql = "SELECT name, email, mobile, course, country, state, city, skills, dob, gender, address FROM students ORDER BY student_id DESC";

try {
    $stmt = $pdo->query($sql);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Unable to fetch registrations. Please check the database table columns.");
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

<?php if (count($registrations) > 0): ?>
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
    </tr>

    <?php foreach ($registrations as $student): ?>
        <tr>
            <td><?= htmlspecialchars($student["name"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["email"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["mobile"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["course"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["country"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["state"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["city"] ?? "") ?></td>
            <td><?= htmlspecialchars(implode(", ", json_decode($student["skills"] ?? "[]", true) ?: [])) ?></td>
            <td><?= htmlspecialchars($student["dob"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["gender"] ?? "") ?></td>
            <td><?= htmlspecialchars($student["address"] ?? "") ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p style="text-align:center;">No registered students found.</p>
<?php endif; ?>

</body>
</html>