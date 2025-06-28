<?php
session_start();
include 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Customer ID required']);
    exit();
}

$customer_id = intval($_GET['id']);

// Get customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
    http_response_code(404);
    echo json_encode(['error' => 'Customer not found']);
    exit();
}

// Get customer's order history
$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.quantity * oi.price) as total_amount 
                       FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.customer_id = ? 
                       GROUP BY o.id 
                       ORDER BY o.created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    'customer' => $customer,
    'orders' => $orders
]);

$conn->close();
?>