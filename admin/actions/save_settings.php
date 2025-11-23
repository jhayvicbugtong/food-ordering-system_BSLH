<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Loop through ALL POST data and update settings
    // This automatically catches 'store_status' from your new dropdown
    foreach ($_POST as $key => $value) {
        // Basic sanitization
        $key = $conn->real_escape_string($key);
        $value = $conn->real_escape_string($value);
        
        // Only update keys that actually exist in the DB
        $sql = "UPDATE system_settings SET setting_value = '$value', updated_at = NOW() WHERE setting_key = '$key'";
        $conn->query($sql);
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Settings updated successfully']);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>