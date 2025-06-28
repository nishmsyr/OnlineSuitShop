<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Get order ID from URL parameter
if (!isset($_GET['order_id'])) {
    header("Location: shop.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION["user_id"];

// Verify this order belongs to the logged-in user
$stmt = $conn->prepare("SELECT o.*, c.name as customer_name, c.email, c.phone, c.address 
                       FROM orders o 
                       JOIN customers c ON o.customer_id = c.id 
                       WHERE o.id = ? AND o.customer_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: shop.php");
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .success-header {
            text-align: center;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 40px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
        }
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
            animation: checkmark 0.6s ease-in-out;
        }
        @keyframes checkmark {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .success-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .success-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        .info-item {
            text-align: center;
        }
        .info-item .label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .info-item .value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }
        .order-items {
            margin-top: 20px;
        }
        .order-items h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }
        .item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #fafafa;
        }
        .item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
        }

.item-details {
            flex: 1;
        }
        .item-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .item-meta {
            font-size: 0.9rem;
            color: #666;
        }
        .item-price {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
        .shipping-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .shipping-info h4 {
            color: #333;
            margin-bottom: 15px;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #000;
            color: white;
        }
        .btn-primary:hover {
            background: #333;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }
        .total-amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
        @media (max-width: 768px) {
            .success-header h1 {
                font-size: 2rem;
            }
            .success-header p {
                font-size: 1rem;
            }
            .order-info {
                grid-template-columns: 1fr;
            }
            .item {
                flex-direction: column;
                text-align: center;
            }
            .item img {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="left">
            <h1 class="logo">BLACKTIE</h1>
            <a href="shop.php" class="nav-link">Shop</a>
        </div>
        <div class="right">
            <a href="profile.php" class="nav-link">Profile</a>
            <a href="cart.php" class="nav-link">Cart</a>
            <button class="logout-btn" onclick="window.location.href='login.php'">Logout</button>
        </div>
    </div>

    <div class="success-container">
        <!-- Success Header -->
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Successful!</h1>
            <p>Thank you for your purchase. Your order has been confirmed.</p>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <div class="order-info">
                <div class="info-item">
                    <div class="label">Order Number</div>
                    <div class="value">#<?= $order['id'] ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Order Date</div>
                    <div class="value"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Payment Method</div>
                    <div class="value"><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="label">Total Amount</div>
                    <div class="value total-amount">RM <?= number_format($order['total_amount'], 2) ?></div>
                </div>
            </div>

<!-- Order Items -->
            <div class="order-items">
                <h3><i class="fas fa-shopping-bag"></i> Items Ordered</h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="item">
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                        <div class="item-details">
                            <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div class="item-meta">
                                Size: <?= htmlspecialchars($item['size']) ?> | 
                                Quantity: <?= $item['quantity'] ?>
                            </div>
                        </div>
                        <div class="item-price">
                            RM <?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Shipping Information -->
            <div class="shipping-info">
                <h4><i class="fas fa-truck"></i> Shipping Information</h4>
                <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
                <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                <p>Phone: <?= htmlspecialchars($order['phone']) ?></p>
                <p>Email: <?= htmlspecialchars($order['email']) ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="shop.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i>
                Continue Shopping
            </a>
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-user"></i>
                View Profile
            </a>
        </div>
    </div>

    <script>
        // Add some celebration effects
        document.addEventListener('DOMContentLoaded', function() {
            // Create confetti effect (optional)
            setTimeout(() => {
                // You can add confetti library here if desired
                console.log('Order completed successfully!');
            }, 500);
        });
    </script>
</body>
</html>