<?php
// Database Credentials
$host = "localhost"; // Server name (localhost in XAMPP)
$dbname = "expense_tracker"; // Change if using a different DB name
$username = "root"; // Default user in XAMPP
$password = ""; // No password in XAMPP by default

// Create Connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Character Encoding
$conn->set_charset("utf8");

// Uncomment for debugging
// echo "Database Connected Successfully!";
?>
