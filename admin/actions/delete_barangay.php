<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['barangay_id'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

// Optional: Check if used in orders (not strictly necessary if constraints cascade, but good for UX)
// For now, we'll just delete.

$stmt = $conn->prepare("DELETE FROM deliverable_barangays WHERE barangay_id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Barangay deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting barangay']);
}
?>