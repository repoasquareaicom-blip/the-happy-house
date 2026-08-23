<?php
$servername = "thehappyhouse.au"; // Change if your DB is hosted elsewhere
$username = "thehappyhousedev"; // Change to your database username
$password = "T#2#@ppy#01$"; // Change to your database password
$dbname = "thehappyhouse"; // Replace with your actual database name



// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8 for proper encoding support
$conn->set_charset("utf8");
?>
