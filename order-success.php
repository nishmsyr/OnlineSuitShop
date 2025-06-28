<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];

// Get the latest order for this customer
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If no order found, redirect to shop
if (!$order) {
    header("Location: shop.php");
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name, p.image, ps.size 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       LEFT JOIN product_sizes ps ON oi.product_size_id = ps.id
                       WHERE oi.order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();
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
            text-align: center;
        }
        
        .success-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .success-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .success-subtitle {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: bold;
            color: #333;
        }
        
        .order-items {
            margin-top: 20px;
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
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .item-details {
            flex: 1;
            text-align: left;
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
        
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            min-width: 200px;
            justify-content: center;
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
            background: white;
            color: #000;
            border: 2px solid #000;
        }
        
        .btn-secondary:hover {
            background: #000;
            color: white;
            transform: translateY(-2px);
        }
        
        .estimated-delivery {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .estimated-delivery h4 {
            color: #155724;
            margin-bottom: 5px;
        }
        
        .estimated-delivery p {
            color: #155724;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .success-container {
                margin: 20px auto;
            }
            
            .success-card {
                padding: 30px 20px;
            }
            
            .success-title {
                font-size: 24px;
            }
            
            .success-icon {
                font-size: 60px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
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

<div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="success-title">Order Successful!</h1>
            <p class="success-subtitle">
                Thank you <?= htmlspecialchars($customer['name']) ?>! Your order has been placed successfully.
            </p>
            
            <div class="order-details">
                <div class="order-info">
                    <div class="info-item">
                        <span class="info-label">Order Number</span>
                        <span class="info-value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Date</span>
                        <span class="info-value"><?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Amount</span>
                        <span class="info-value">RM <?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method</span>
                        <span class="info-value"><?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></span>
                    </div>
                </div>
                
                <div class="estimated-delivery">
                    <h4><i class="fas fa-truck"></i> Estimated Delivery</h4>
                    <p>Your order will be delivered within 3-5 business days to your specified address.</p>
                </div>
                
                <?php if (!empty($order_items)): ?>
                <div class="order-items">
                    <h4 style="margin-bottom: 15px; color: #333;">Order Items (<?= count($order_items) ?> items)</h4>
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item">
                            <img src="<?= !empty($item['image']) ? htmlspecialchars($item['image']) : '/placeholder.svg?height=50&width=50' ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-size">Size: <?= htmlspecialchars($item['size']) ?> | Qty: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-price">RM <?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-history"></i>
                    View Order History
                </a>
                <a href="shop.php" class="btn btn-secondary">
                    <i class="fas fa-shopping-bag"></i>
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>

<script>
        // Auto-redirect after 30 seconds if no action taken
        setTimeout(function() {
            if (confirm('Would you like to continue shopping?')) {
                window.location.href = 'shop.php';
            } else {
                window.location.href = 'profile.php';
            }
        }, 30000);
        
        // Add some celebration animation
        document.addEventListener('DOMContentLoaded', function() {
            const successIcon = document.querySelector('.success-icon');
            successIcon.style.animation = 'bounce 1s ease-in-out';
        });
    </script>
    
    <style>
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    </style>
</body>
</html>