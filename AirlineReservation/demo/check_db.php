<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "airline2";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if database exists
$db_selected = $conn->select_db($dbname);

if ($db_selected) {
    echo "Database '$dbname' exists and connection is successful.";
} else {
    echo "Database '$dbname' does not exist or cannot be accessed.";
}

$conn->close();
?>
