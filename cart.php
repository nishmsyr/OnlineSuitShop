<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION["user_id"];

// Handle cart updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantities'] as $cart_id => $quantity) {
            $quantity = intval($quantity);
            $selected = isset($_POST['selected'][$cart_id]) ? 1 : 0;

            if ($quantity > 0) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ?, selected = ? WHERE id = ? AND customer_id = ?");
                $stmt->bind_param("iiii", $quantity, $selected, $cart_id, $customer_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $cart_id, $customer_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['select_all'])) {
        $select_all = $_POST['select_all'] === '1' ? 1 : 0;
        $stmt = $conn->prepare("UPDATE cart SET selected = ? WHERE customer_id = ?");
        $stmt->bind_param("ii", $select_all, $customer_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: cart.php");
    exit();
}

// Fetch cart items with size information
$cart_items = [];
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, ps.size, ps.stock as size_stock 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       LEFT JOIN product_sizes ps ON c.product_size_id = ps.id
                       WHERE c.customer_id = ? 
                       ORDER BY c.created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

// Calculate totals for selected items only
$subtotal = 0;
$selected_count = 0;
foreach ($cart_items as $item) {
    if ($item['selected']) {
        $subtotal += $item['price'] * $item['quantity'];
        $selected_count++;
    }
}
$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over RM100
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .cart-items {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .select-all-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .select-all-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-checkbox {
            margin-right: 15px;
        }

        .item-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
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
            margin-bottom: 5px;
        }

        .item-price {
            font-weight: bold;
            color: #000;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 20px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 3px;
            color: #333;
        }

        .qty-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            padding: 5px;
            border-radius: 3px;
            color: #333;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
        }

        .remove-btn:hover {
            background: #c82333;
        }

        .cart-summary {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            height: fit-content;
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

        .checkout-btn {
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

        .checkout-btn:hover {
            background: #333;
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .continue-shopping {
            background: #000;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }

        .update-cart-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .update-cart-btn:hover {
            background: #218838;
        }

        .selected-items-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stock-warning {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .quantity-controls {
                margin: 10px 0;
            }
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

    <?php if (count($cart_items) > 0): ?>
        <div class="cart-container">
            <div class="cart-items">
                <div class="cart-header">
                    <h2>Shopping Cart (<?= count($cart_items) ?> items)</h2>
                    <div class="select-all-container">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()"
                            <?= $selected_count === count($cart_items) ? 'checked' : '' ?>>
                        <label for="selectAll">Select All</label>
                    </div>
                </div>

                <form method="POST" id="cartForm">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-checkbox">
                                <input type="checkbox" name="selected[<?= $item['id'] ?>]" value="1"
                                    <?= $item['selected'] ? 'checked' : '' ?>
                                    onchange="updateTotals()">
                            </div>

                            <img src="<?= !empty($item['image']) ? htmlspecialchars($item['image']) : '/placeholder.svg?height=80&width=80' ?>"
                                alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">

                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="item-size">Size: <?= htmlspecialchars($item['size']) ?></div>
                                <div class="item-price">RM <?= number_format($item['price'], 2) ?></div>
                                <?php if ($item['quantity'] > $item['size_stock']): ?>
                                    <div class="stock-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Only <?= $item['size_stock'] ?> left in stock
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="quantity-controls">
                                <button type="button" class="qty-btn" onclick="changeQuantity(<?= $item['id'] ?>, -1)">-</button>
                                <input type="number" name="quantities[<?= $item['id'] ?>]"
                                    value="<?= $item['quantity'] ?>" min="1" max="<?= $item['size_stock'] ?>"
                                    class="qty-input" id="qty-<?= $item['id'] ?>" onchange="updateTotals()">
                                <button type="button" class="qty-btn" onclick="changeQuantity(<?= $item['id'] ?>, 1)">+</button>
                            </div>

                            <div style="text-align: center;">
                                <div style="font-weight: bold; margin-bottom: 10px; color: #333;">
                                    RM <span id="item-total-<?= $item['id'] ?>"><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                                <button type="button" class="remove-btn" onclick="removeItem(<?= $item['id'] ?>)">
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" name="update_cart" class="update-cart-btn">
                        Update Cart
                    </button>
                </form>
            </div>

            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="selected-items-info">
                    <span id="selected-count"><?= $selected_count ?></span> of <?= count($cart_items) ?> items selected
                </div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>RM <span id="subtotal"><?= number_format($subtotal, 2) ?></span></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span id="shipping-cost"><?= $shipping > 0 ? 'RM ' . number_format($shipping, 2) : 'FREE' ?></span>
                </div>
                <?php if ($subtotal < 100 && $shipping > 0): ?>
                    <div style="font-size: 12px; color: #666; margin: 10px 0;">
                        Free shipping on orders over RM100
                    </div>
                <?php endif; ?>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span>RM <span id="total"><?= number_format($total, 2) ?></span></span>
                </div>

                <button class="checkout-btn" onclick="proceedToCheckout()"
                    <?= $selected_count === 0 ? 'disabled' : '' ?> id="checkoutBtn">
                    Proceed to Checkout (<?= $selected_count ?> items)
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart" style="font-size: 64px; color: #ddd; margin-bottom: 20px;"></i>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="shop.php" class="continue-shopping">Continue Shopping</a>
        </div>
    <?php endif; ?>

    <!-- Hidden forms for actions -->
    <form id="removeForm" method="POST" style="display: none;">
        <input type="hidden" name="cart_id" id="removeCartId">
        <input type="hidden" name="remove_item" value="1">
    </form>

    <form id="selectAllForm" method="POST" style="display: none;">
        <input type="hidden" name="select_all" id="selectAllValue">
    </form>

    <script>
        // Store original cart data for calculations
        const cartItems = <?= json_encode($cart_items) ?>;

        function changeQuantity(cartId, change) {
            const input = document.getElementById(`qty-${cartId}`);
            const newValue = parseInt(input.value) + change;
            const max = parseInt(input.max);

            if (newValue >= 1 && newValue <= max) {
                input.value = newValue;
                updateTotals();
            }
        }

        function removeItem(cartId) {
            if (confirm('Remove this item from cart?')) {
                document.getElementById('removeCartId').value = cartId;
                document.getElementById('removeForm').submit();
            }
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('input[name^="selected["]');

            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });

            // Submit form to update database
            document.getElementById('selectAllValue').value = selectAll.checked ? '1' : '0';
            document.getElementById('selectAllForm').submit();
        }

        function updateTotals() {
            let subtotal = 0;
            let selectedCount = 0;

            cartItems.forEach(item => {
                const checkbox = document.querySelector(`input[name="selected[${item.id}]"]`);
                const quantityInput = document.getElementById(`qty-${item.id}`);
                const itemTotalSpan = document.getElementById(`item-total-${item.id}`);

                if (checkbox && quantityInput && itemTotalSpan) {
                    const quantity = parseInt(quantityInput.value);
                    const itemTotal = item.price * quantity;

                    // Update individual item total
                    itemTotalSpan.textContent = itemTotal.toFixed(2);

                    // Add to subtotal if selected
                    if (checkbox.checked) {
                        subtotal += itemTotal;
                        selectedCount++;
                    }
                }
            });

            // Update summary
            const shipping = subtotal > 100 ? 0 : 10;
            const total = subtotal + shipping;

            document.getElementById('selected-count').textContent = selectedCount;
            document.getElementById('subtotal').textContent = subtotal.toFixed(2);
            document.getElementById('shipping-cost').textContent = shipping > 0 ? `RM ${shipping.toFixed(2)}` : 'FREE';
            document.getElementById('total').textContent = total.toFixed(2);

            // Update checkout button
            const checkoutBtn = document.getElementById('checkoutBtn');
            checkoutBtn.disabled = selectedCount === 0;
            checkoutBtn.textContent = `Proceed to Checkout (${selectedCount} items)`;

            // Update select all checkbox
            const totalItems = cartItems.length;
            const selectAllCheckbox = document.getElementById('selectAll');
            selectAllCheckbox.checked = selectedCount === totalItems;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalItems;
        }

        function proceedToCheckout() {
            const selectedItems = [];
            const checkboxes = document.querySelectorAll('input[name^="selected["]:checked');

            if (checkboxes.length === 0) {
                alert('Please select at least one item to checkout.');
                return;
            }

            // Update cart first, then redirect
            document.getElementById('cartForm').addEventListener('submit', function(e) {
                e.preventDefault();

                // Submit form data
                const formData = new FormData(this);
                formData.append('update_cart', '1');

                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    window.location.href = 'payment.php';
                });
            });

            document.getElementById('cartForm').dispatchEvent(new Event('submit'));
        }

        // Initialize totals on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateTotals();
        });
    </script>
</body>

</html>