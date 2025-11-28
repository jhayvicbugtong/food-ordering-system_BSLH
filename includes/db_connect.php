<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- NEW DYNAMIC BASE URL (MORE ROBUST) ---
// 1. Get filesystem paths
$project_root_fs = dirname(__DIR__); // e.g., 'C:\xampp\htdocs\food ordering system BSLH'
$document_root_fs = $_SERVER['DOCUMENT_ROOT']; // e.g., 'C:/xampp/htdocs'

// 2. Normalize slashes to forward slashes (web standard)
$project_root_norm = str_replace('\\', '/', $project_root_fs);
$document_root_norm = str_replace('\\', '/', $document_root_fs);

// 3. Remove trailing slashes for clean comparison
$project_root_norm = rtrim($project_root_norm, '/');
$document_root_norm = rtrim($document_root_norm, '/');

// 4. Get the sub-path
// This is now safe because both paths use the same slash type
$base_path = str_replace($document_root_norm, '', $project_root_norm); // e.g., '/food ordering system BSLH'

// 5. Final assignment (will be '' on root, or '/subfolder' in a subfolder)
$BASE_URL = rtrim($base_path, '/');
// --- END NEW DYNAMIC BASE URL ---


// // Database configuration in local development
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "online_food_ordering_db";

// Database configuration in infinityfree hosting
// $host = "sql204.infinityfree.com";
// $user = "if0_40429008";
// $pass = "bentesais3102";
// $dbname = "if0_40429008_online_food_ordering_db";

// Hosting configuration for mysqli
// $hostname = "localhost";
// $user = "u920374553_bentesais";
// $pass = "Bentesais.26";
// $dbname = "u920374553_bentesais_db";

// // Create connection
// $conn = mysqli_connect($servername, $username, $password, $database);


$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8mb4");
?>