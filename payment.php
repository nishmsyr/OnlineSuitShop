<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];

// Fetch ONLY SELECTED cart items
$cart_items = [];
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, ps.size, ps.stock as size_stock 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       LEFT JOIN product_sizes ps ON c.product_size_id = ps.id
                       WHERE c.customer_id = ? AND c.selected = 1
                       ORDER BY c.created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

// If no selected items, redirect back to cart
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate totals for SELECTED items only
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = $subtotal > 100 ? 0 : 10;
$total = $subtotal + $shipping;

// Get customer details for shipping
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = "";
$error_message = "";

// Handle order submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check stock availability for all selected items
        foreach ($cart_items as $item) {
            $stmt = $conn->prepare("SELECT stock FROM product_sizes WHERE id = ?");
            $stmt->bind_param("i", $item['product_size_id']);
            $stmt->execute();
            $current_stock = $stmt->get_result()->fetch_assoc()['stock'];
            $stmt->close();

            if ($current_stock < $item['quantity']) {
                throw new Exception("Insufficient stock for " . $item['name'] . " (Size: " . $item['size'] . ")");
            }
        }

        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, shipping_address, payment_method, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idss", $customer_id, $total, $shipping_address, $payment_method);
        $stmt->execute();
        $order_id = $conn->insert_id;
        $stmt->close();

        // Add order items and update stock for SELECTED items only
        foreach ($cart_items as $item) {
            // Add to order_items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_size_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiid", $order_id, $item['product_id'], $item['product_size_id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();

            // Update stock
            $stmt = $conn->prepare("UPDATE product_sizes SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['product_size_id']);
            $stmt->execute();
            $stmt->close();
        }

        // Remove ONLY SELECTED items from cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND selected = 1");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        // Redirect to success page or show success message
        header("Location: order-success.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .order-summary {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
        }

        .payment-methods {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .payment-method {
            flex: 1;
            min-width: 150px;
        }

        .payment-method input[type="radio"] {
            width: auto;
            margin-right: 8px;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.3s;
            font-weight: normal;
        }

        .payment-method input[type="radio"]:checked+label {
            border-color: #000;
            background: #f8f9fa;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .item-size {
            color: #666;
            font-size: 14px;
        }

        .item-price {
            font-weight: bold;
            color: #000;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            color: #333;
        }

        .summary-total {
            border-top: 2px solid #000;
            padding-top: 10px;
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .place-order-btn {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
        }

        .place-order-btn:hover {
            background: #333;
        }

        .selected-items-info {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .selected-items-info h4 {
            color: #155724;
            margin-bottom: 10px;
        }

        .selected-items-info p {
            color: #155724;
            margin: 0;
        }

        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="left">
            <h1 class="logo">BLACKTIE</h1>
            <a href="home.html" class="nav-link">Home</a>
            <a href="shop.php" class="nav-link">Shop</a>
        </div>
        <div class="right">
            <button class="icon-btn" title="Profile" onclick="window.location.href='profile.php'">
                <i class="fas fa-user"></i>
            </button>
            <button class="icon-btn" title="Cart" onclick="window.location.href='cart.php'">
                <i class="fas fa-shopping-cart"></i>
            </button>
            <button class="logout-btn" onclick="window.location.href='login.php'">Logout</button>
        </div>
    </div>

    <div class="checkout-container">
        <div class="checkout-form">
            <h2>Checkout</h2>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div class="selected-items-info">
                <h4><i class="fas fa-check-circle"></i> Selected Items for Checkout</h4>
                <p>You are purchasing <?= count($cart_items) ?> selected item(s) from your cart.</p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="shipping_address">Shipping Address</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-methods">
                        <div class="payment-method">
                            <input type="radio" id="credit_card" name="payment_method" value="credit_card" required>
                            <label for="credit_card">
                                <i class="fas fa-credit-card"></i> Credit Card
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" required>
                            <label for="bank_transfer">
                                <i class="fas fa-university"></i> Bank Transfer
                            </label>
                        </div>
                        <div class="payment-method">
                            <input type="radio" id="cash_on_delivery" name="payment_method" value="cash_on_delivery" required>
                            <label for="cash_on_delivery">
                                <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" name="place_order" class="place-order-btn">
                    Place Order - RM <?= number_format($total, 2) ?>
                </button>
            </form>
        </div>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <p style="color: #666; margin-bottom: 20px;">
                <?= count($cart_items) ?> selected item(s)
            </p>

            <?php foreach ($cart_items as $item): ?>
                <div class="order-item">
                    <img src="<?= !empty($item['image']) ? htmlspecialchars($item['image']) : '/placeholder.svg?height=60&width=60' ?>"
                        alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                    <div class="item-details">
                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="item-size">Size: <?= htmlspecialchars($item['size']) ?> | Qty: <?= $item['quantity'] ?></div>
                    </div>
                    <div class="item-price">RM <?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 20px;">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>RM <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span><?= $shipping > 0 ? 'RM ' . number_format($shipping, 2) : 'FREE' ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span>RM <?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>