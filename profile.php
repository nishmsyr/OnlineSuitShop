<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];
$message = "";
$error_message = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $address = $_POST["address"];

    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $phone, $email, $address, $customer_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $_SESSION["user_name"] = $name;
    } else {
        $error_message = "Error updating profile.";
    }
    $stmt->close();
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Get current password
    $stmt = $conn->prepare("SELECT password FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($current_password === $result['password']) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_password, $customer_id);

                if ($stmt->execute()) {
                    $message = "Password changed successfully!";
                } else {
                    $error_message = "Error changing password.";
                }
                $stmt->close();
            } else {
                $error_message = "New password must be at least 6 characters long.";
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}

// Fetch customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch order history with more details
$orders = [];
$stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count, SUM(oi.quantity * oi.price) as total_amount 
                       FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.customer_id = ? 
                       GROUP BY o.id 
                       ORDER BY o.created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $orders = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .profile-tabs {
            display: flex;
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .tab-btn {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            color: #333;
        }

        .tab-btn.active {
            background: #000;
            color: white;
        }

        .tab-btn:hover {
            background: #666;
            color: #f8f9fa;
        }

        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
        }

        .btn-primary {
            background: #000;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary:hover {
            background: #333;
        }

        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .view-details-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }

        .view-details-btn:hover {
            background: #0056b3;
        }

        .password-form {
            max-width: 500px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            position: relative;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #333;
        }

        .order-items-table {
            margin-top: 20px;
        }

        .order-items-table table {
            font-size: 14px;
        }

        .order-items-table th {
            background: #f8f9fa;
            color: #333;
        }

        .order-items-table td {
            color: #333;
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

    <div class="profile-container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="profile-tabs">
            <button class="tab-btn active" onclick="showTab('profile')">My Profile</button>
            <button class="tab-btn" onclick="showTab('password')">Change Password</button>
            <button class="tab-btn" onclick="showTab('orders')">Order History</button>
        </div>

        <!-- Profile Tab -->
        <div id="profile" class="tab-content active">
            <h2>My Profile</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="4" required><?= htmlspecialchars($customer['address']) ?></textarea>
                </div>
                <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
            </form>
        </div>

        <!-- Password Tab -->
        <div id="password" class="tab-content">
            <h2>Change Password</h2>
            <form method="POST" class="password-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                </div>
                <button type="submit" name="change_password" class="btn-primary">Change Password</button>
            </form>
        </div>

        <!-- Orders Tab -->
        <div id="orders" class="tab-content">
            <h2>Order History</h2>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <strong>Order #<?= $order['id'] ?></strong>
                                <p style="color: #666;">Date: <?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div>
                                <span class="order-status status-<?= strtolower($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </div>
                        </div>
                        <p style="color: #333;"><strong>Items:</strong> <?= $order['item_count'] ?> item(s)</p>
                        <p style="color: #333;"><strong>Total:</strong> RM <?= number_format($order['total_amount'], 2) ?></p>
                        <p style="color: #333;"><strong>Payment:</strong> <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?></p>
                        <button class="view-details-btn" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                            View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666;">No orders found. <a href="shop.php">Start shopping!</a></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const contents = document.querySelectorAll('.tab-content');
            contents.forEach(content => content.classList.remove('active'));

            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected tab and activate button
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        function viewOrderDetails(orderId) {
            fetch(`get_customer_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    const order = data.order;
                    const items = data.items;

                    let itemsHtml = '';
                    items.forEach(item => {
                        itemsHtml += `
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="${item.image || '/placeholder.svg?height=50&width=50'}" 
                                             alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                        <div>
                                            <strong>${item.name}</strong><br>
                                            <small style="color: #666;">Size: ${item.size}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>${item.quantity}</td>
                                <td>RM ${parseFloat(item.price).toFixed(2)}</td>
                                <td><strong>RM ${(item.quantity * item.price).toFixed(2)}</strong></td>
                            </tr>
                        `;
                    });

                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <h2>Order Details - #${order.id}</h2>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <h4 style="color: #333;">Order Information</h4>
                                <p style="color: #333;"><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                                <p style="color: #333;"><strong>Status:</strong> <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span></p>
                                <p style="color: #333;"><strong>Payment Method:</strong> ${order.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                            </div>
                            <div>
                                <h4 style="color: #333;">Shipping Address</h4>
                                <p style="color: #333;">${order.shipping_address.replace(/\n/g, '<br>')}</p>
                            </div>
                        </div>
                        
                        <div class="order-items-table">
                            <h4 style="color: #333;">Order Items</h4>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Product</th>
                                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Quantity</th>
                                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Price</th>
                                        <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #000;">
                            <h3 style="color: #333;">Total: RM ${parseFloat(order.total_amount).toFixed(2)}</h3>
                        </div>
                    `;

                    document.getElementById('orderModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order details');
                });
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;

            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>

</html>