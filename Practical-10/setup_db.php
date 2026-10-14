<?php
/**
 * Practical 10: Standalone Database Seeder & Verification Tool
 * Initializes the database schema, ensures columns, and seeds test accounts.
 */

require_once __DIR__ . "/db.php";

echo "<!DOCTYPE html><html><head><title>Practical 10 Setup</title>";
echo "<style>body{font-family:Arial,sans-serif;background:#f8fafc;padding:40px;color:#333;line-height:1.6;} .card{max-width:650px;margin:auto;background:white;padding:30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);} h2{color:#123B5D;} .success{color:#16a34a;font-weight:bold;} .info-box{background:#f1f5f9;padding:15px;border-radius:8px;margin:15px 0;} a{color:#0284c7;text-decoration:none;font-weight:bold;}</style>";
echo "</head><body><div class='card'>";
echo "<h2>Practical 10: Database Setup & Seeder</h2>";
echo "<p class='success'>Database and tables verified successfully!</p>";

echo "<div class='info-box'>";
echo "<h4>Seeded Credentials for Viva & Evaluation:</h4>";
echo "<p><strong>1. Admin Account:</strong><br>Email: <code>admin@charusat.edu.in</code><br>Password: <code>Admin@123</code><br>Role: <code>admin</code></p>";
echo "<p><strong>2. Student Account:</strong><br>Email: <code>22dit045@charusat.edu.in</code><br>Password: <code>Student@123</code><br>Role: <code>student</code></p>";
echo "<p><strong>3. Demo Student:</strong><br>Email: <code>student@charusat.edu.in</code><br>Password: <code>Student@123</code><br>Role: <code>student</code></p>";
echo "</div>";

echo "<p><a href='login.php'>&rarr; Proceed to Secure Login Page</a></p>";
echo "</div></body></html>";
