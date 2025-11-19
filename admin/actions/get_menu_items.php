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

require_once __DIR__ . '/../../includes/db_connect.php';

// --- NEW: Expanded Search Logic ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params_count = []; // For count query
$params_data = [];  // For data query
$types_count = "";  // For binding param types
$types_data = "";   // For binding param types

if (!empty($search)) {
    // Base WHERE clause for LIKE searches
    $where_clause = " WHERE (p.name LIKE ? OR c.category_name LIKE ? OR p.description LIKE ? OR p.base_price LIKE ?) ";
    
    // Add params for LIKE searches
    $like_param = "%" . $search . "%";
    $params_count = [$like_param, $like_param, $like_param, $like_param];
    $params_data = [$like_param, $like_param, $like_param, $like_param];
    $types_count = "ssss";
    $types_data = "ssss";

    // --- NEW: Check for availability keywords ---
    $search_lower = strtolower($search);
    if ($search_lower === 'hidden' || $search_lower === 'sold out') {
        // If user types "hidden", add `OR is_available = 0`
        $where_clause .= " OR p.is_available = 0 ";
    } elseif ($search_lower === 'visible' || $search_lower === 'orderable') {
        // If user types "visible", add `OR is_available = 1`
        $where_clause .= " OR p.is_available = 1 ";
    }
}

// --- Pagination Logic ---
$items_per_page = 5; // Show 5 items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $items_per_page;

// --- Query 1: Get total count of *filtered* items ---
$count_sql = "SELECT COUNT(*) as total 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.category_id
              $where_clause";
              
$count_stmt = $conn->prepare($count_sql);
if (!empty($search)) {
    // Use dynamic types string
    $count_stmt->bind_param($types_count, ...$params_count);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);
$count_stmt->close();


// --- Query 2: Get paged *filtered* items ---
$data_sql = "SELECT p.*, c.category_name 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.category_id 
             $where_clause

             LIMIT ? OFFSET ?";

// Add limit and offset to params
$params_data[] = $items_per_page;
$params_data[] = $offset;
$types_data .= "ii"; // Add integer types for LIMIT and OFFSET

$stmt = $conn->prepare($data_sql);

// Dynamically bind params
if (!empty($search)) {
    $stmt->bind_param($types_data, ...$params_data);
} else {
    $stmt->bind_param("ii", ...$params_data); // No search, just "ii" for LIMIT/OFFSET
}

$stmt->execute();
$result = $stmt->get_result();

// --- Build HTML for the table rows ---
$html = '';
if ($result->num_rows > 0) {
    while ($item = $result->fetch_assoc()):
        // Use output buffering to capture the HTML template
        ob_start();
    ?>
        <tr>
            <td>
                <?php if ($item['image_url']): ?>
                    <img src="../<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                         class="menu-table-img">
                <?php else: ?>
                    <div class="menu-table-img-placeholder">
                        <i class="bi bi-image"></i>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <div class="menu-item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="menu-item-desc" title="<?= htmlspecialchars($item['description']) ?>">
                    <?= htmlspecialchars($item['description']) ? htmlspecialchars($item['description']) : '<em>No description</em>' ?>
                </div>
            </td>
            <td><?= htmlspecialchars($item['category_name']) ?></td>
            <td class="menu-item-price">₱<?= number_format($item['base_price'], 2) ?></td>
            <td>
                <span class="badge <?= $item['is_available'] ? 'badge-success' : 'badge-danger' ?>">
                    <?= $item['is_available'] ? 'Visible' : 'Hidden' ?>
                </span>
            </td>
            <td>
                <div class="btn-group btn-group-sm" role="group">
                    <button class="btn btn-outline-secondary btn-edit" data-id="<?= $item['product_id'] ?>">
                        <i class="bi bi-pencil-fill"></i> Edit
                    </button>
                    <button class="btn btn-outline-danger btn-delete" data-id="<?= $item['product_id'] ?>">
                        <i class="bi bi-trash-fill"></i> Delete
                    </button>
                </div>
            </td>
        </tr>
    <?php 
    $html .= ob_get_clean();
    endwhile; 
} else {
    $html = '<tr><td colspan="6" class="text-center">No menu items found.</td></tr>';
}

// --- Prepare JSON Response ---
$response = [
    'success' => true,
    'html' => $html,
    'pagination' => [
        'currentPage' => $page,
        'totalPages' => $total_pages,
        'totalItems' => $total_items
    ]
];

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>