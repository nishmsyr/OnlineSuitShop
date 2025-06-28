<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
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

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $product = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($product);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
}

$stmt->close();
$conn->close();
?>