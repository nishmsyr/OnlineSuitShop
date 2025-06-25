<?php
include_once 'functions.php';

$session_id = initializeSession();
$cart_items = getCartItems($conn, $session_id);
$cart_count = getCartCount($conn, $session_id);

// Calculate totals
$subtotal = 0;
foreach($cart_items as $item) {
    $subtotal += $item['product_price'] * $item['cart_quantity'];
}
$tax = calculateTax($subtotal);
$total = $subtotal + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | BLACKTIE</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1>BLACKTIE</h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="shop.php" class="nav-link">Shop</a>
            </nav>
            <div class="nav-actions">
                <button class="icon-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
                <button class="icon-btn cart-btn" onclick="window.location.href='cart.php'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="m1 1 4 4 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?php if($cart_count > 0): ?>
                        <span class="cart-count" id="cartCount"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </button>
                <button class="logout-btn" onclick="window.location.href='login.php'">Log Out</button>
            </div>
        </div>
    </header>

    <section class="cart-section">
        <div class="container">
            <h2 class="section-title">Shopping Cart</h2>
            
            <?php if(empty($cart_items)): ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p>Add some suits to get started!</p>
                <button class="cta-btn" onclick="window.location.href='shop.php'">Continue Shopping</button>
            </div>
            <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                             class="item-image">
                        <div class="item-details">
                            <h4 class="item-name">👔 <?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p class="item-specs">
                                <span>🎨 <?php echo htmlspecialchars($item['product_colour']); ?></span> • 
                                <span>Size <?php echo htmlspecialchars($item['product_size']); ?></span>
                            </p>
                            <p class="item-price">💰 <?php echo formatPrice($item['product_price']); ?></p>
                            <p class="stock-info">
                                <?php if($item['product_quantity'] >= $item['cart_quantity']): ?>
                                    <span class="in-stock">✅ In Stock</span>
                                <?php else: ?>
                                    <span class="low-stock">⚠️ Only <?php echo $item['product_quantity']; ?> left</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['cart_quantity'] - 1; ?>)">-</button>
                            <span class="quantity"><?php echo $item['cart_quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['cart_quantity'] + 1; ?>)">+</button>
                        </div>
                        <div class="item-total">
                            <p class="item-total-price">💰 <?php echo formatPrice($item['product_price'] * $item['cart_quantity']); ?></p>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">Remove</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>📋 Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>🚚 Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (8%):</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        <hr>
                        <div class="summary-row total">
                            <span><strong>Total:</strong></span>
                            <span><strong><?php echo formatPrice($total); ?></strong></span>
                        </div>
                        <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                        <button class="clear-cart-btn" onclick="clearCart()" 
                                style="width: 100%; background: #e74c3c; color: white; border: none; padding: 0.75rem; margin-top: 0.5rem; border-radius: 4px; cursor: pointer;">
                            Clear Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        
        // Update quantity
        function updateQuantity(productId, newQuantity) {
            fetch("api/cart_operations.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=update&product_id=${productId}&quantity=${newQuantity}`,
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    updateCartCount(data.cart_count)
                    location.reload()
                } else {
                    showNotification(data.message, "error")
                }
            })
            .catch((error) => {
                console.error("Error:", error)
                showNotification("An error occurred", "error")
            })
        }

        // Remove item from cart
        function removeFromCart(productId) {
            if (confirm("🗑️ Are you sure you want to remove this item?")) {
                fetch("api/cart_operations.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `action=remove&product_id=${productId}`,
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        updateCartCount(data.cart_count)
                        location.reload()
                    } else {
                        showNotification(data.message, "error")
                    }
                })
                .catch((error) => {
                    console.error("Error:", error)
                    showNotification("An error occurred", "error")
                })
            }
        }

        // Clear entire cart
        function clearCart() {
            if (confirm("🗑️ Are you sure you want to clear your entire cart?")) {
                fetch("api/cart_operations.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "action=clear",
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        updateCartCount(data.cart_count)
                        location.reload()
                    } else {
                        showNotification(data.message, "error")
                    }
                })
                .catch((error) => {
                    console.error("Error:", error)
                    showNotification("An error occurred", "error")
                })
            }
        }

        // Update cart count in header
        function updateCartCount(count) {
            const cartCountElement = document.getElementById("cartCount")
            if (cartCountElement) {
                cartCountElement.textContent = count
                if (count > 0) {
                    cartCountElement.style.display = "flex"
                } else {
                    cartCountElement.style.display = "none"
                }
            }
        }

        // Load cart count on page load
        function loadCartCount() {
            fetch("api/cart_operations.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "action=get_count",
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    updateCartCount(data.cart_count)
                }
            })
            .catch((error) => {
                console.error("Error loading cart count:", error)
            })
        }

        // Checkout function
        function checkout() {
            const cartItems = document.querySelectorAll('.cart-item')
            if (cartItems.length === 0) {
                showNotification("Your cart is empty", "error")
                return
            }

            if (confirm("Proceed to checkout? This will redirect you to the payment page.")) {
                showNotification("Checkout functionality would be implemented here!")
            }
        }

        // Show notification function
        function showNotification(message, type = "success") {
            const existingNotifications = document.querySelectorAll('.notification')
            existingNotifications.forEach(notification => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification)
                }
            })

            const notification = document.createElement("div")
            notification.className = `notification ${type}`
            notification.textContent = `${type === 'error' ? '❌' : '✅'} ${message}`

            // Add styles
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'error' ? '#e74c3c' : '#2ecc71'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 4px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                z-index: 1001;
                font-weight: 500;
                animation: slideIn 0.3s ease;
                max-width: 300px;
                word-wrap: break-word;
            `

            // Add animation keyframes
            if (!document.querySelector('#notification-animation')) {
                const style = document.createElement('style')
                style.id = 'notification-animation'
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `
                document.head.appendChild(style)
            }

            document.body.appendChild(notification)

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.style.animation = "slideIn 0.3s ease reverse"
                    setTimeout(() => {
                        if (document.body.contains(notification)) {
                            document.body.removeChild(notification)
                        }
                    }, 300)
                }
            }, 3000)
        }

        // Initialize cart page
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount()
            
            // Add smooth animations to cart items
            const cartItems = document.querySelectorAll('.cart-item')
            cartItems.forEach((item, index) => {
                item.style.opacity = '0'
                item.style.transform = 'translateY(20px)'
                setTimeout(() => {
                    item.style.transition = 'all 0.3s ease'
                    item.style.opacity = '1'
                    item.style.transform = 'translateY(0)'
                }, index * 100)
            })
        })
    </script>
</body>
</html>
