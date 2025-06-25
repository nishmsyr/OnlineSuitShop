<?php
include_once 'function.php';
include_once 'cartOperation.php';

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
    <title>Shopping Cart - BLACKTIE</title>
    <link rel="stylesheet" href="assets/css/styles.css">
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
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
                <button class="icon-btn cart-btn" onclick="window.location.href='cart.php'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z"></path>
                        <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z"></path>
                        <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6"></path>
                    </svg>
                    <?php if($cart_count > 0): ?>
                        <span class="cart-count" id="cartCount"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </button>
                <button class="logout-btn">Log Out</button>
            </div>
        </div>
    </header>

    <!-- Cart Section -->
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
                        <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="item-image">
                        <div class="item-details">
                            <h4 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p class="item-specs">
                                <span><?php echo htmlspecialchars($item['product_colour']); ?></span> • 
                                <span>Size <?php echo htmlspecialchars($item['product_size']); ?></span>
                            </p>
                            <p class="item-price"><?php echo formatPrice($item['product_price']); ?></p>
                            <p class="stock-info">
                                <?php if($item['product_quantity'] >= $item['cart_quantity']): ?>
                                    <span class="in-stock">✓ In Stock</span>
                                <?php else: ?>
                                    <span class="low-stock">⚠ Only <?php echo $item['product_quantity']; ?> left</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['cart_quantity'] - 1; ?>)">-</button>
                            <span class="quantity"><?php echo $item['cart_quantity']; ?></span>
                            <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, <?php echo $item['cart_quantity'] + 1; ?>)">+</button>
                        </div>
                        <div class="item-total">
                            <p class="item-total-price"><?php echo formatPrice($item['product_price'] * $item['cart_quantity']); ?></p>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">Remove</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax (8%):</span>
                            <span><?php echo formatPrice($tax); ?></span>
                        </div>
                        <hr>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                        <button class="clear-cart-btn" onclick="clearCart()">Clear Cart</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="assets/js/script.js"></script>
</body>
</html>
