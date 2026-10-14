<?php
session_start();

if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub Registration</title>

    <link rel="stylesheet" href="../Practical-3/register.css">

</head>


<body>




<header>

    <div class="header-content">

        <img src="https://admission.charusat.ac.in/View%20Assets/MainPage/assets/charusat%20images/logo.png"
            alt="StudentHub Logo">

        <div class="header-text">

            <h1>StudentHub Portal</h1>

            <h3 style="color: white;">Create Your Account</h3>

            <p style="color: white;">
                Already have an account?
                <a href="login.html" style="color: chocolate;">Login Here</a>
            </p>

        </div>

    </div>

</header>


<form id="registerForm" action="../Practical-7&8&9/register.php" method="POST">

    <input type="hidden" name="registered" value="success">

    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <!-- <input type="hidden" name="csrf_token" value="wrong_token"> -->

    <h2>Registration Form</h2>

    <table>

        

        <tr>

            <td>
                <label for="name">Full Name</label>
            </td>

            <td>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter Full Name"
                    required>
            </td>

        </tr>


        

        <tr>

            <td>
                <label for="email">Email</label>
            </td>

            <td>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter Email"
                    required>
            </td>

        </tr>


        

        <tr>

            <td>
                <label for="mobile">Mobile</label>
            </td>

            <td>
                <input
                    type="tel"
                    id="mobile"
                    name="mobile"
                    placeholder="Enter Mobile Number"
                    required>
            </td>

        </tr>



        <tr>

            <td>
                <label for="password">Password</label>
            </td>

            <td>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter Password"
                    required>
            </td>

        </tr>


        

        <tr>

            <td>
                <label for="confirm">Confirm Password</label>
            </td>

            <td>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    placeholder="Confirm Password"
                    required>
            </td>

        </tr>


       

        <tr>

            <td>
                <label for="dob">Date of Birth</label>
            </td>

            <td>
                <input
                    type="date"
                    id="dob"
                    name="dob"
                    required>
            </td>

        </tr>


        

        <tr>

            <td>
                Gender
            </td>

            <td>

                <input
                    type="radio"
                    name="gender"
                    id="male"
                    value="Male">

                <label for="male">Male</label>


                <input
                    type="radio"
                    name="gender"
                    id="female"
                    value="Female">

                <label for="female">Female</label>


                <input
                    type="radio"
                    name="gender"
                    id="other"
                    value="Other">

                <label for="other">Other</label>

            </td>

        </tr>


        

        <tr>

            <td>
                <label for="course">Course</label>
            </td>

            <td>

                <select id="course" name="course" required>

                    <option value="">Select Course</option>

                    <option>B.Tech CSE</option>

                    <option>B.Tech CE</option>

                    <option>B.Tech IT</option>

                </select>

            </td>

        </tr>

        <tr>

    <td>
        <label for="country">Country</label>
    </td>

    <td>
        <select id="country" name="country" required>
            <option value="">Select Country</option>
            <option value="India">India</option>
            <option value="USA">USA</option>
            <option value="Canada">Canada</option>
        </select>
    </td>

</tr>

<tr>

    <td>
        <label for="state">State</label>
    </td>

    <td>
        <select id="state" name="state" required disabled>
            <option value="">Select State</option>
        </select>
    </td>

</tr>

<tr>

    <td>
        <label for="city">City</label>
    </td>

    <td>
        <select id="city" name="city" required disabled>
            <option value="">Select City</option>
        </select>
    </td>

</tr>
        

        <tr>

            <td>
                Skills
            </td>

            <td>

                <label>
                    <input type="checkbox" name="skill[]" value="C">
                    C
                </label>

                <label>
                    <input type="checkbox" name="skill[]" value="C++">
                    C++
                </label>

                <label>
                    <input type="checkbox" name="skill[]" value="Java">
                    Java
                </label>

                <label>
                    <input type="checkbox" name="skill[]" value="Python">
                    Python
                </label>

            </td>

        </tr>


        

        <tr>

            <td>
                <label for="address">Address</label>
            </td>

            <td>

                <textarea
                    id="address"
                    name="address"
                    rows="4"
                    placeholder="Enter Your Address"></textarea>

            </td>

        </tr>


        

        <tr>

            <td colspan="2" class="terms">

                <input
                    type="checkbox"
                    id="terms"
                    name="terms"
                    required>

                <label for="terms">
                    I agree to the Terms & Conditions
                </label>

            </td>

        </tr>


        

        <tr>

            <td colspan="2" class="buttons">

                <button type="submit">
                    Register
                </button>

                <button type="reset">
                    Clear
                </button>

            </td>

        </tr>

    </table>



</form>




<div id="validationPopup" class="popup">

    <div class="popup-content">

        <button id="closePopup" class="close">&times;</button>

        <h2 id="popupTitle">Validation Error</h2>

        <p id="popupMessage"></p>

        <button id="popupOk">OK</button>

    </div>

</div>


<hr>



<hr>

<footer>

    <p>
        <b style="color: black;">
        &copy; 2026 StudentHub. All Rights Reserved.
        </b>
    </p>

</footer>

<script src="../Practical-4&5/register.js"></script>

</body>

</html> 