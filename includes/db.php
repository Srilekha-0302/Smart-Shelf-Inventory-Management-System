<?php
$servername = "localhost";
$username = "root";
$password = ""; // Change if needed
$dbname = "inventory_db"; // Use your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

