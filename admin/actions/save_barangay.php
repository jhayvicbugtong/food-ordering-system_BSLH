<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['barangay_id'] ?? '';
$name = trim($_POST['barangay_name'] ?? '');
$fee = floatval($_POST['delivery_fee'] ?? 0);
$active = intval($_POST['is_active'] ?? 1);

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Barangay name is required']);
    exit;
}

try {
    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE deliverable_barangays SET barangay_name = ?, delivery_fee = ?, is_active = ? WHERE barangay_id = ?");
        $stmt->bind_param("sdii", $name, $fee, $active, $id);
        $msg = "Barangay updated successfully";
    } else {
        // Insert
        // Check for duplicate name first
        $check = $conn->prepare("SELECT 1 FROM deliverable_barangays WHERE LOWER(barangay_name) = LOWER(?)");
        $check->bind_param("s", $name);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
             echo json_encode(['success' => false, 'message' => 'Barangay name already exists']);
             exit;
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO deliverable_barangays (barangay_name, delivery_fee, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $name, $fee, $active);
        $msg = "Barangay added successfully";
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>