<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$customer_id = $_SESSION["user_id"];
$product_id = intval($_POST['product_id']);
$size = $_POST['size'];
$quantity = intval($_POST['quantity']);

// Validate input
if ($product_id <= 0 || $quantity <= 0 || empty($size)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Check if product exists and has enough stock
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND stock >= ?");
$stmt->bind_param("ii", $product_id, $quantity);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not available or insufficient stock']);
    exit();
}

// Check if item already exists in cart
$stmt = $conn->prepare("SELECT * FROM cart WHERE customer_id = ? AND product_id = ? AND size = ?");
$stmt->bind_param("iis", $customer_id, $product_id, $size);
$stmt->execute();
$existing_item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing_item) {
    // Update quantity
    $new_quantity = $existing_item['quantity'] + $quantity;
    if ($new_quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_quantity, $existing_item['id']);
} else {
    // Add new item
    $stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $customer_id, $product_id, $size, $quantity);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding to cart']);
}

$stmt->close();
$conn->close();
