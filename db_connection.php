<?php
// db_connection.php

$servername = "localhost"; // Database server
$dbname = "miufashion"; // Database name
$username = "root"; // MySQL username
$password = ""; // MySQL password (leave empty if no password)

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set the character set to UTF-8
$conn->set_charset("utf8");

// You can also define a function to close the connection
function closeConnection($conn) {
    $conn->close();
}
?>