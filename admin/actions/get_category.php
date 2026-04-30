<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

if (!isset($_GET['category_id'])) {
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

$category_id = intval($_GET['category_id']);

try {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Category not found']);
        exit;
    }
    
    $category = $result->fetch_assoc();
    echo json_encode($category);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching category: ' . $e->getMessage()]);
}
?>