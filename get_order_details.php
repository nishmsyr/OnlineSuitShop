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
    echo json_encode(['error' => 'Order ID required']);
    exit();
}

$order_id = intval($_GET['id']);

// Get order details
$stmt = $conn->prepare("SELECT o.*, c.name as customer_name, c.email as customer_email 
                       FROM orders o 
                       JOIN customers c ON o.customer_id = c.id 
                       WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
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
