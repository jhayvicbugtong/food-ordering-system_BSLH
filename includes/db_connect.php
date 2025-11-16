<?php
$host = "localhost";
$user = "root";
$pass = "";
// --- MODIFIED ---
// Changed from "food_ordering_db" to your new database name
$dbname = "online_food_ordering_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");
?>