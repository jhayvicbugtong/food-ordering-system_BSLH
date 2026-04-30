<?php
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

// Allow admin or staff
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$status = $_POST['status'] ?? '';

if ($status !== 'open' && $status !== 'closed') {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    // Check if key exists, if not insert it
    $check = $conn->query("SELECT 1 FROM system_settings WHERE setting_key = 'store_status'");
    if ($check->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_type) VALUES ('store_status', ?, 'string')");
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'store_status'");
        $stmt->bind_param("s", $status);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Store is now ' . ucfirst($status)]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>