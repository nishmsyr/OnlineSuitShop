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
    echo json_encode(['error' => 'Product ID required']);
    exit();
}

$product_id = intval($_GET['id']);

// Get product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit();
}

// Get product sizes
$stmt = $conn->prepare("SELECT size, stock FROM product_sizes WHERE product_id = ? ORDER BY 
                       CASE size 
                           WHEN 'S' THEN 1 
                           WHEN 'M' THEN 2 
                           WHEN 'L' THEN 3 
                           WHEN 'XL' THEN 4 
                           ELSE 5 
                       END");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$sizes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    'product' => $product,
    'sizes' => $sizes
]);

$conn->close();
?>