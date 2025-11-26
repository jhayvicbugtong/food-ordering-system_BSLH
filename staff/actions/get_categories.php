<?php
// staff/actions/get_categories.php

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json');

// Allow 'staff' and 'admin'
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $search = $_GET['search'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $query = "SELECT * FROM categories WHERE 1=1";
    $countQuery = "SELECT COUNT(*) as total FROM categories WHERE 1=1";
    
    $params = [];
    $types = '';
    
    if (!empty($search)) {
        $query .= " AND (category_name LIKE ? OR description LIKE ?)";
        $countQuery .= " AND (category_name LIKE ? OR description LIKE ?)";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm];
        $types = 'ss';
    }
    
    $query .= " ORDER BY display_order, category_name LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    // Total count
    $stmt = $conn->prepare($countQuery);
    if (!empty($search)) {
        $stmt->bind_param('ss', $searchTerm, $searchTerm);
    }
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $totalRows = $totalResult->fetch_assoc()['total'];
    $stmt->close();

    // Data
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    $totalPages = ceil($totalRows / $limit);

    echo json_encode([
        'success' => true,
        'categories' => $categories,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalRows
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching categories: ' . $e->getMessage()
    ]);
}
?>