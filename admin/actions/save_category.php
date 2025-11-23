<?php
// admin/actions/save_category.php

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];
$id = $_POST['category_id'] ?? '';
$name = trim($_POST['category_name'] ?? '');
$desc = trim($_POST['description'] ?? '');
$order = intval($_POST['display_order'] ?? 0);
$active = intval($_POST['is_active'] ?? 1);

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

try {
    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE categories SET category_name=?, description=?, display_order=?, is_active=? WHERE category_id=?");
        $stmt->bind_param("ssiii", $name, $desc, $order, $active, $id);
        $msg = "Category updated successfully";
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO categories (category_name, description, display_order, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $name, $desc, $order, $active);
        $msg = "Category added successfully";
    }

    if ($stmt->execute()) {
        // Determine the ID (if insert, use insert_id; if update, use the POST id)
        $returnId = $id ? $id : $stmt->insert_id;

        $response = [
            'success' => true, 
            'message' => $msg,
            // Return these so JS can update the dropdown
            'category_id' => $returnId,
            'category_name' => $name
        ];
    } else {
        $response = ['success' => false, 'message' => 'Database Error: ' . $stmt->error];
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>