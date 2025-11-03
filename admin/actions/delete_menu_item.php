<?php
// Start session and check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated and has admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// 1. FIXED: Correct path to db_connect.php
require_once __DIR__ . '/../../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    // 2. FIXED: Use PREPARED STATEMENTS
    // First, get the image path to delete the file
    $stmt_find = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
    $stmt_find->bind_param("i", $product_id);
    $stmt_find->execute();
    $result = $stmt_find->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        if ($product['image_url'] && file_exists(__DIR__ . '/../../' . $product['image_url'])) {
            unlink(__DIR__ . '/../../' . $product['image_url']);
        }
    }
    $stmt_find->close();
    
    // Now, delete the item from the database
    $stmt_delete = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt_delete->bind_param("i", $product_id);
    
    if ($stmt_delete->execute()) {
        $response['success'] = true;
        $response['message'] = 'Menu item deleted successfully';
    } else {
        $response['message'] = 'Error deleting menu item: ' . $stmt_delete->error;
    }
    $stmt_delete->close();
} else {
    $response['message'] = 'No product ID provided';
}

$conn->close();
echo json_encode($response);
?>