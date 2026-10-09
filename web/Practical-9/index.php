<!DOCTYPE html>
<html>
<head>
    <title>Practical 9 - User Registration</title>
</head>
<body>

<h2>User Registration</h2>

<form action="register.php" method="POST">

    <label>Name:</label>
    <input type="text" name="name" required>
    <br><br>

    <label>Email:</label>
    <input type="email" name="email" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" required>
    <br><br>

    <button type="submit">Register</button>

</form>

</body>
</html>