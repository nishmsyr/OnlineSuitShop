<?php
session_start();
include 'db.php';

// Allow only logged-in admins
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query - Fixed the column reference issue
$query = "SELECT c.* FROM customers c WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

// Fixed: Use the correct column name for ordering
$query .= " ORDER BY c.id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-bar input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            width: 300px;
            color: #333;
        }

        .search-bar button {
            padding: 10px 20px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .customers-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f8f9fa;
        }

        .view-details-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .view-details-btn:hover {
            background: #0056b3;
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
            max-width: 900px;
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

        .customer-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section h4 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .info-item {
            margin-bottom: 10px;
        }

        .info-item strong {
            color: #333;
        }

        .orders-section {
            margin-top: 30px;
        }

        .order-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .order-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 768px) {
            .customer-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- Admin Navigation Bar -->
    <div class="navbar">
        <div class="left">
            <h1 class="logo">BLACKTIE</h1>
            <a href="adminhome.html" class="nav-link">Home</a>
        </div>
        <div class="right">
            <button class="logout-btn" onclick="window.location.href='login.php'">Logout</button>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h2><i class="fas fa-users"></i> Customer Management</h2>

            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search by name, email, or phone..."
                    value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
                <a href="customer.php" style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">Clear</a>
            </form>
        </div>

        <div class="customers-table">
            <div class="table-responsive">
                <?php if (count($customers) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td>#<?= $customer['id'] ?></td>
                                    <td><?= htmlspecialchars($customer['name']) ?></td>
                                    <td><?= htmlspecialchars($customer['email']) ?></td>
                                    <td><?= htmlspecialchars($customer['phone']) ?></td>
                                    <td>
                                        <button class="view-details-btn" onclick="viewCustomerDetails(<?= $customer['id'] ?>)">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #666;">
                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>No customers found</h3>
                        <p>No customers match your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Customer Details Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent">
                <!-- Customer details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function viewCustomerDetails(customerId) {
            fetch(`get_customer_details.php?id=${customerId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    const customer = data.customer;
                    const orders = data.orders;

                    let ordersHtml = '';
                    if (orders.length > 0) {
                        orders.forEach(order => {
                            ordersHtml += `
                                <div class="order-item">
                                    <div class="order-header">
                                        <strong>Order #${order.id}</strong>
                                        <span class="status-badge status-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                                    </div>
                                    <div class="order-meta">
                                        <div><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</div>
                                        <div><strong>Items:</strong> ${order.item_count} item(s)</div>
                                        <div><strong>Total:</strong> RM ${parseFloat(order.total_amount).toFixed(2)}</div>
                                        <div><strong>Payment:</strong> ${order.payment_method.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        ordersHtml = '<p style="color: #666; text-align: center; padding: 20px;">No orders found for this customer.</p>';
                    }

                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <h2>Customer Details - ${customer.name}</h2>
                        
                        <div class="customer-info">
                            <div class="info-section">
                                <h4><i class="fas fa-user"></i> Personal Information</h4>
                                <div class="info-item">
                                    <strong>Name:</strong> ${customer.name}
                                </div>
                                <div class="info-item">
                                    <strong>Email:</strong> ${customer.email}
                                </div>
                                <div class="info-item">
                                    <strong>Phone:</strong> ${customer.phone}
                                </div>
                            </div>
                            <div class="info-section">
                                <h4><i class="fas fa-map-marker-alt"></i> Address</h4>
                                <div class="info-item">
                                    ${customer.address.replace(/\n/g, '<br>')}
                                </div>
                            </div>
                        </div>
                        
                        <div class="orders-section">
                            <h4><i class="fas fa-shopping-bag"></i> Order History (${orders.length} orders)</h4>
                            ${ordersHtml}
                        </div>
                    `;

                    document.getElementById('customerModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading customer details');
                });
        }

        function closeModal() {
            document.getElementById('customerModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('customerModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>