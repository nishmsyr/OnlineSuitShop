<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID required']);
    exit();
}

$order_id = intval($_GET['id']);
$customer_id = $_SESSION["user_id"];

// Get order details (only for the logged-in customer)
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit();
}

// Get order items with product details and size information
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image, ps.size 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       LEFT JOIN product_sizes ps ON oi.product_size_id = ps.id
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    'order' => $order,
    'items' => $items
]);

$conn->close();
