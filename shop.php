<?php
include_once 'function.php';

$session_id = initializeSession();
$products = getAllProducts($conn);
$cart_count = getCartCount($conn, $session_id);
$categories = getProductCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - BLACKTIE</title>
    <link rel="stylesheet" href="shop.css">
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
                <a href="shop.php" class="nav-link active">Shop</a>
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

    <!-- Shop Section -->
    <section class="shop">
        <div class="container">
            <h2 class="section-title">Our Collection</h2>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterProducts('all')">All</button>
                    <?php foreach($categories as $category): ?>
                        <button class="filter-btn" onclick="filterProducts('<?php echo htmlspecialchars($category); ?>')">
                            <?php echo htmlspecialchars($category); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="products-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card" data-category="<?php echo htmlspecialchars($product['product_category']); ?>">
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p class="product-details">
                            <span class="product-colour"><?php echo htmlspecialchars($product['product_colour']); ?></span> • 
                            <span class="product-size">Size <?php echo htmlspecialchars($product['product_size']); ?></span>
                        </p>
                        <p class="product-category"><?php echo htmlspecialchars($product['product_category']); ?></p>
                        <p class="product-price"><?php echo formatPrice($product['product_price']); ?></p>
                        <p class="product-stock">
                            <?php if($product['product_quantity'] > 0): ?>
                                <span class="in-stock">In Stock (<?php echo $product['product_quantity']; ?>)</span>
                            <?php else: ?>
                                <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                        <button class="add-to-cart-btn" 
                                onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')"
                                <?php echo $product['product_quantity'] <= 0 ? 'disabled' : ''; ?>>
                            <?php echo $product['product_quantity'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script src="script.js"></script>
</body>
</html>
