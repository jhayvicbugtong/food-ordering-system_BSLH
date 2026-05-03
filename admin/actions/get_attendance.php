    <?php
session_start();
require_once '../../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$query = "
    SELECT 
        a.attendance_id,
        a.attendance_date,
        a.time_in,
        a.time_out,
        a.total_hours,
        a.status,
        u.first_name,
        u.last_name
    FROM staff_attendance a
    INNER JOIN users u ON a.staff_id = u.user_id
    ORDER BY a.attendance_date DESC
";

$result = $conn->query($query);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    'success' => true,
    'attendance' => $data
]);
?>