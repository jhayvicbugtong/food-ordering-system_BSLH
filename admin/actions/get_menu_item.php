<?php
// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated and has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// 1. FIXED: Correct path to db_connect.php
require_once __DIR__ . '/../../includes/db_connect.php';

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    // 2. FIXED: Use PREPARED STATEMENTS to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id); // "i" for integer
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
    $stmt->close();
}
$conn->close();
?>