<?php

// Database configuration in local development
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "online_food_ordering_db";

// Database configuration in infinityfree hosting
// $host = "sql101.infinityfree.com";
// $user = "if0_40429008";
// $pass = "bentesais3102";
// $dbname = "if0_40232593_online_food_ordering_db";


$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");
?>