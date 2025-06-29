<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header("Location: shop.php");
    exit();
}

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION["user_id"]);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: shop.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .success-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .btn-primary {
            background: #000;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="left">
            <h1 class="logo">BLACKTIE</h1>
            <a href="index.php" class="nav-link">Home</a>
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

    <div class="success-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1>Order Confirmed!</h1>
        <p>Thank you for your purchase. Your order has been successfully placed.</p>

        <div class="order-details">
            <h3>Order Details</h3>
            <div class="detail-row">
                <span><strong>Order Number:</strong></span>
                <span>#<?= $order['id'] ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Order Date:</strong></span>
                <span><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Total Amount:</strong></span>
                <span>RM <?= number_format($order['total_amount'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Payment Method:</strong></span>
                <span><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></span>
            </div>
            <div class="detail-row">
                <span><strong>Status:</strong></span>
                <span><?= ucfirst($order['status']) ?></span>
            </div>
        </div>

        <p>We'll send you an email confirmation shortly. You can track your order status in your profile.</p>

        <div class="action-buttons">
            <a href="profile.php" class="btn btn-primary">View Order History</a>
            <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</body>

</html>