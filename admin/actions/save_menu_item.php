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

// Path to db_connect.php
require_once __DIR__ . '/../../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

try {
    $product_id = $_POST['product_id'] ?? '';
    $name = trim($_POST['name']);
    $category_id = trim($_POST['category_id']);
    $base_price = trim($_POST['base_price']);
    $is_available = trim($_POST['is_available']);
    $description = trim($_POST['description']);

    // --- Basic Validation ---
    if (empty($name) || empty($category_id) || empty($base_price) || $is_available === '') {
        throw new Exception('Please fill in all required fields: Name, Category, Price, and Availability.');
    }

    // Handle file upload
    $image_url = '';
    $newImageUploaded = false;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $upload_dir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;

        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            throw new Exception('Only JPG, JPEG, PNG, and GIF files are allowed.');
        }

        if ($_FILES['product_image']['size'] > 2 * 1024 * 1024) {
            throw new Exception('File size must be less than 2MB.');
        }

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $file_path)) {
            $image_url = 'uploads/products/' . $file_name;
            $newImageUploaded = true;
            
            // Delete old image if updating
            if ($product_id) {
                $stmt_old_img = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
                $stmt_old_img->bind_param("i", $product_id);
                $stmt_old_img->execute();
                $result_old_img = $stmt_old_img->get_result();
                if ($result_old_img->num_rows > 0) {
                    $old_data = $result_old_img->fetch_assoc();
                    if ($old_data['image_url'] && file_exists(__DIR__ . '/../../' . $old_data['image_url'])) {
                        unlink(__DIR__ . '/../../' . $old_data['image_url']);
                    }
                }
                $stmt_old_img->close();
            }
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    // Insert or Update
    if ($product_id) {
        // Update existing product
        if ($newImageUploaded) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, base_price = ?, description = ?, is_available = ?, image_url = ? WHERE product_id = ?");
            // "s" for string, "d" for double, "i" for integer
            $stmt->bind_param("ssdsssi", $name, $category_id, $base_price, $description, $is_available, $image_url, $product_id);
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, base_price = ?, description = ?, is_available = ? WHERE product_id = ?");
            $stmt->bind_param("ssdssi", $name, $category_id, $base_price, $description, $is_available, $product_id);
        }
        $message = 'Menu item updated successfully';
    } else {
        // Insert new product
        if (empty($image_url)) {
             // throw new Exception('A product image is required for new items.');
        }
        $stmt = $conn->prepare("INSERT INTO products (name, category_id, base_price, description, is_available, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $name, $category_id, $base_price, $description, $is_available, $image_url);
        $message = 'Menu item added successfully';
    }

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = $message;
    } else {
        throw new Exception('Database error: ' . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>