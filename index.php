<?php
include_once 'functions.php';

$session_id = initializeSession();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLACKTIE - Where Confidence Wears a Suit</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1>BLACKTIE</h1>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link active">Home</a>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">BLACKTIE</h1>
            <p class="hero-subtitle">WHERE CONFIDENCE WEARS A SUIT</p>
            <button class="cta-btn" onclick="smoothScrollToShop()">Shop Now</button>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products" style="padding: 80px 0; background: white;">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <?php 
                // Get first 3 products for featured section
                $featured_products = array_slice(getAllProducts($conn), 0, 3);
                foreach($featured_products as $product): 
                ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                         class="product-image">
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                        <p class="product-price"><?php echo formatPrice($product['product_price']); ?></p>
                        <button class="add-to-cart-btn" onclick="window.location.href='shop.php'">
                            View Details
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
        
        // Smooth scroll to shop (or redirect to shop page)
        function smoothScrollToShop() {
            window.location.href = 'shop.php'
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

        // Hero animation
        function animateHero() {
            const heroTitle = document.querySelector('.hero-title')
            const heroSubtitle = document.querySelector('.hero-subtitle')
            const ctaBtn = document.querySelector('.cta-btn')

            // Animate elements with staggered timing
            setTimeout(() => {
                heroTitle.style.opacity = '1'
                heroTitle.style.transform = 'translateY(0)'
            }, 300)

            setTimeout(() => {
                heroSubtitle.style.opacity = '1'
                heroSubtitle.style.transform = 'translateY(0)'
            }, 600)

            setTimeout(() => {
                ctaBtn.style.opacity = '1'
                ctaBtn.style.transform = 'translateY(0)'
            }, 900)
        }

        // Initialize home page
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount()
            
            // Set initial styles for animation
            const heroTitle = document.querySelector('.hero-title')
            const heroSubtitle = document.querySelector('.hero-subtitle')
            const ctaBtn = document.querySelector('.cta-btn')

            heroTitle.style.cssText += 'opacity: 0; transform: translateY(30px); transition: all 0.8s ease;'
            heroSubtitle.style.cssText += 'opacity: 0; transform: translateY(30px); transition: all 0.8s ease;'
            ctaBtn.style.cssText += 'opacity: 0; transform: translateY(30px); transition: all 0.8s ease;'

            // Start animation
            animateHero()

            // Animate featured products
            const featuredCards = document.querySelectorAll('.featured-products .product-card')
            featuredCards.forEach((card, index) => {
                card.style.opacity = '0'
                card.style.transform = 'translateY(30px)'
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease'
                    card.style.opacity = '1'
                    card.style.transform = 'translateY(0)'
                }, 1200 + (index * 200))
            })
        })
    </script>
</body>
</html>
