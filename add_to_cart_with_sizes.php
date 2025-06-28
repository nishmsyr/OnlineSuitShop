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

// Get product_size_id and check stock
$stmt = $conn->prepare("SELECT id, stock FROM product_sizes WHERE product_id = ? AND size = ?");
$stmt->bind_param("is", $product_id, $size);
$stmt->execute();
$size_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$size_data || $size_data['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Product not available or insufficient stock']);
    exit();
}

$product_size_id = $size_data['id'];

// Check if item already exists in cart
$stmt = $conn->prepare("SELECT * FROM cart WHERE customer_id = ? AND product_size_id = ?");
$stmt->bind_param("ii", $customer_id, $product_size_id);
$stmt->execute();
$existing_item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existing_item) {
    // Update quantity
    $new_quantity = $existing_item['quantity'] + $quantity;
    if ($new_quantity > $size_data['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
        exit();
    }

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_quantity, $existing_item['id']);
} else {
    // Add new item
    $stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, product_size_id, quantity, selected) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("iiii", $customer_id, $product_id, $product_size_id, $quantity);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding to cart']);
}

$stmt->close();
$conn->close();
