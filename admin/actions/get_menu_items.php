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

// --- Pagination Logic ---
$items_per_page = 5; // Show 10 items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $items_per_page;

// --- Query 1: Get total count of items ---
$count_result = $conn->query("SELECT COUNT(*) as total FROM products");
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// --- Query 2: Get paged items ---
$stmt = $conn->prepare("SELECT p.*, c.category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.category_id 
                        ORDER BY p.product_id DESC 
                        LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $items_per_page, $offset);
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
                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px;">
                        <i class="bi bi-image text-muted"></i>
                    </div>
                <?php endif; ?>
            </td>
            <td>
                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                <small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
            </td>
            <td><?= htmlspecialchars($item['category_name']) ?></td>
            <td>₱<?= number_format($item['base_price'], 2) ?></td>
            <td>
                <span class="badge <?= $item['is_available'] ? 'badge-success' : 'badge-danger' ?>">
                    <?= $item['is_available'] ? 'Visible' : 'Hidden' ?>
                </span>
            </td>
            <td>
               
                    <button class="btn btn-outline-secondary btn-edit" data-id="<?= $item['product_id'] ?>">Edit</button>
                    <button class="btn btn-outline-danger btn-delete" data-id="<?= $item['product_id'] ?>">Delete</button>
              
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