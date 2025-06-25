<?php
include_once 'functions.php';

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
                <a href="shop.php" class="nav-link active">Shop</a>
            </nav>
            <div class="nav-actions">
                <!-- Search Box -->
                <div class="search-box" style="margin-right: 1rem;">
                    <input type="text" id="searchInput" placeholder="Search products..." 
                           style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                </div>
                
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

    <!-- Shop Section -->
    <section class="shop">
        <div class="container">
            <h2 class="section-title">Our Collection</h2>
            
            <!-- Filter and Sort Section -->
            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterProducts('all')">All</button>
                    <?php foreach($categories as $category): ?>
                        <button class="filter-btn" onclick="filterProducts('<?php echo htmlspecialchars($category); ?>')">
                            <?php echo htmlspecialchars($category); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Sort Options -->
                <div class="sort-section" style="margin-top: 1rem; text-align: center;">
                    <label for="sortSelect" style="margin-right: 0.5rem;">Sort by:</label>
                    <select id="sortSelect" onchange="sortProducts(this.value)" 
                            style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Default</option>
                        <option value="name">Name A-Z</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="category">Category</option>
                    </select>
                </div>
            </div>

            <div class="products-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card" data-category="<?php echo htmlspecialchars($product['product_category']); ?>">
                    <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                         class="product-image"
                         onclick="quickViewProduct(<?php echo $product['product_id']; ?>)"
                         style="cursor: pointer;">
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
                                <span class="in-stock">✅ In Stock (<?php echo $product['product_quantity']; ?>)</span>
                            <?php else: ?>
                                <span class="out-of-stock">❌ Out of Stock</span>
                            <?php endif; ?>
                        </p>
                        <div class="product-actions" style="display: flex; gap: 0.5rem;">
                            <button class="add-to-cart-btn" 
                                    onclick="addToCart(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['product_name']); ?>')"
                                    <?php echo $product['product_quantity'] <= 0 ? 'disabled' : ''; ?>
                                    style="flex: 1;">
                                <?php echo $product['product_quantity'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                            <button class="quick-view-btn" 
                                    onclick="quickViewProduct(<?php echo $product['product_id']; ?>)"
                                    style="padding: 0.75rem; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <script>
        // Shop-specific JavaScript functions
        
        // Add item to cart
        function addToCart(productId, productName) {
            const btn = event.target
            const originalText = btn.textContent
            btn.disabled = true
            btn.textContent = "Adding..."

            fetch("api/cart_operations.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `action=add&product_id=${productId}&quantity=1`,
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    updateCartCount(data.cart_count)
                    showNotification(data.message)
                } else {
                    showNotification(data.message, "error")
                }
            })
            .catch((error) => {
                console.error("Error:", error)
                showNotification("An error occurred", "error")
            })
            .finally(() => {
                btn.disabled = false
                btn.textContent = originalText
            })
        }

        // Filter products by category
        function filterProducts(category) {
            const products = document.querySelectorAll(".product-card")
            const filterBtns = document.querySelectorAll(".filter-btn")

            // Update active button
            filterBtns.forEach((btn) => btn.classList.remove("active"))
            event.target.classList.add("active")

            // Filter products with animation
            products.forEach((product, index) => {
                setTimeout(() => {
                    if (category === "all" || product.dataset.category === category) {
                        product.style.display = "block"
                        product.style.opacity = "0"
                        product.style.transform = "translateY(20px)"
                        
                        setTimeout(() => {
                            product.style.transition = "all 0.3s ease"
                            product.style.opacity = "1"
                            product.style.transform = "translateY(0)"
                        }, 50)
                    } else {
                        product.style.transition = "all 0.3s ease"
                        product.style.opacity = "0"
                        product.style.transform = "translateY(-20px)"
                        
                        setTimeout(() => {
                            product.style.display = "none"
                        }, 300)
                    }
                }, index * 50) // Stagger animation
            })
        }

        // Search products
        function searchProducts(searchTerm) {
            const products = document.querySelectorAll('.product-card')
            
            if (searchTerm.length < 2) {
                // Show all products if search term is too short
                products.forEach(product => {
                    product.style.display = 'block'
                })
                return
            }

            searchTerm = searchTerm.toLowerCase()
            
            products.forEach(product => {
                const productName = product.querySelector('.product-name').textContent.toLowerCase()
                const productCategory = product.querySelector('.product-category').textContent.toLowerCase()
                const productColor = product.querySelector('.product-colour').textContent.toLowerCase()
                
                if (productName.includes(searchTerm) || 
                    productCategory.includes(searchTerm) || 
                    productColor.includes(searchTerm)) {
                    product.style.display = 'block'
                } else {
                    product.style.display = 'none'
                }
            })
        }

        // Sort products
        function sortProducts(sortBy) {
            const products = Array.from(document.querySelectorAll('.product-card'))
            const productsGrid = document.querySelector('.products-grid')
            
            products.sort((a, b) => {
                switch(sortBy) {
                    case 'name':
                        return a.querySelector('.product-name').textContent.localeCompare(
                            b.querySelector('.product-name').textContent
                        )
                    case 'price-low':
                        return parseFloat(a.querySelector('.product-price').textContent.replace('$', '')) - 
                               parseFloat(b.querySelector('.product-price').textContent.replace('$', ''))
                    case 'price-high':
                        return parseFloat(b.querySelector('.product-price').textContent.replace('$', '')) - 
                               parseFloat(a.querySelector('.product-price').textContent.replace('$', ''))
                    case 'category':
                        return a.querySelector('.product-category').textContent.localeCompare(
                            b.querySelector('.product-category').textContent
                        )
                    default:
                        return 0
                }
            })
            
            // Clear and re-append sorted products
            productsGrid.innerHTML = ''
            products.forEach(product => productsGrid.appendChild(product))
        }

        // Quick view product
        function quickViewProduct(productId) {
            // Create a simple modal for product quick view
            const modal = document.createElement('div')
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2000;
            `

            // Find the product data from the current page
            const productCard = document.querySelector(`[onclick*="${productId}"]`).closest('.product-card')
            const productName = productCard.querySelector('.product-name').textContent
            const productPrice = productCard.querySelector('.product-price').textContent
            const productCategory = productCard.querySelector('.product-category').textContent
            const productColor = productCard.querySelector('.product-colour').textContent
            const productSize = productCard.querySelector('.product-size').textContent
            const productImage = productCard.querySelector('.product-image').src
            const stockInfo = productCard.querySelector('.product-stock').textContent

            const modalContent = document.createElement('div')
            modalContent.style.cssText = `
                background: white;
                padding: 2rem;
                border-radius: 8px;
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            `

            modalContent.innerHTML = `
                <h3 style="margin-bottom: 1rem; color: #333;">${productName}</h3>
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <img src="${productImage}" alt="${productName}" 
                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px;">
                    <div>
                        <p><strong>Category:</strong> ${productCategory}</p>
                        <p><strong>Color:</strong> ${productColor}</p>
                        <p><strong>Size:</strong> ${productSize}</p>
                        <p><strong>Price:</strong> ${productPrice}</p>
                        <p><strong>Stock:</strong> ${stockInfo}</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button onclick="addToCart(${productId}, '${productName}'); document.body.removeChild(document.querySelector('.quick-view-modal'))" 
                            style="padding: 0.5rem 1rem; border: none; background: #000; color: white; border-radius: 4px; cursor: pointer;">
                        Add to Cart
                    </button>
                    <button onclick="document.body.removeChild(document.querySelector('.quick-view-modal'))" 
                            style="padding: 0.5rem 1rem; border: 1px solid #ddd; background: white; color: #333; border-radius: 4px; cursor: pointer;">
                        Close
                    </button>
                </div>
            `

            modal.className = 'quick-view-modal'
            modal.appendChild(modalContent)
            document.body.appendChild(modal)

            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    document.body.removeChild(modal)
                }
            })
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

        // Show notification function
        function showNotification(message, type = "success") {
            // Remove existing notifications
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

        // Debounce function for search
        function debounce(func, wait) {
            let timeout
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout)
                    func(...args)
                }
                clearTimeout(timeout)
                timeout = setTimeout(later, wait)
            }
        }

        // Initialize shop page
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount()
            
            // Setup search with debounce
            const searchInput = document.getElementById('searchInput')
            const debouncedSearch = debounce(searchProducts, 300)
            searchInput.addEventListener('input', (e) => {
                debouncedSearch(e.target.value)
            })
            
            // Add smooth animations to product cards
            const productCards = document.querySelectorAll('.product-card')
            productCards.forEach((card, index) => {
                card.style.opacity = '0'
                card.style.transform = 'translateY(20px)'
                setTimeout(() => {
                    card.style.transition = 'all 0.3s ease'
                    card.style.opacity = '1'
                    card.style.transform = 'translateY(0)'
                }, index * 50)
            })
        })
    </script>
</body>
</html>
