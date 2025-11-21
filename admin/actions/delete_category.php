<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db_connect.php';

$id = $_POST['category_id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

// Check for existing products in this category
$check = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
$check->bind_param("i", $id);
$check->execute();
$res = $check->get_result()->fetch_assoc();

if ($res['count'] > 0) {
    // If it has products, it definitely can't be deleted (safest logic)
    echo json_encode([
        'success' => false, 
        'message' => 'Cannot delete: This category contains ' . $res['count'] . ' items. Please delete or move the items first.'
    ]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Category deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting category']);
}
?>