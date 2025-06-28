<?php
session_start();
include 'db.php';

// Check if user is logged in as customer
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "customer") {
    header("Location: login.php");
    exit();
}

// Handle search and category filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query - get products with total stock from all sizes
$query = "SELECT p.*, SUM(ps.stock) as total_stock 
          FROM products p 
          LEFT JOIN product_sizes ps ON p.id = ps.product_id 
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND p.name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

if (!empty($category)) {
    $query .= " AND p.category = ?";
    $params[] = $category;
    $types .= "s";
}

$query .= " GROUP BY p.id HAVING total_stock > 0 ORDER BY p.name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get categories for dropdown
$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM products ORDER BY category");
if ($result->num_rows > 0) {
    $categories = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Blacktie Suit Shop</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            position: relative;
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
        .product-details {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .product-image {
            flex: 1;
        }
        .product-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-info {
            flex: 1;
        }
        .product-info h2 {
            margin-bottom: 10px;
            color: #333;
        }
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 15px;
        }
        .size-selector {
            margin-bottom: 20px;
        }
        .size-selector label {
            color: #333;
            font-weight: bold;
        }
        .size-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .size-btn {
            padding: 8px 16px;
            border: 2px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 5px;
            color: #333;
            position: relative;
        }
        .size-btn.selected {
            border-color: #000;
            background: #000;
            color: white;
        }
        .size-btn:disabled {
            background: #f5f5f5;
            color: #999;
            cursor: not-allowed;
            border-color: #ddd;
        }
        .size-stock {
            font-size: 10px;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            color: #666;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .quantity-selector label {
            color: #333;
            font-weight: bold;
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
        .add-to-cart-btn {
            width: 100%;
            padding: 15px;
            background: #000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .add-to-cart-btn:hover {
            background: #333;
        }
        .add-to-cart-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .stock-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .product-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .product-details {
                flex-direction: column;
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

    <!-- Shop Content -->
    <div class="shop-hero">
        <div class="shop-content">
            <!-- Search Area -->
            <div class="search-area">
                <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" id="searchInput" name="search" placeholder="Search products..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <select id="categorySelect" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                    <?= $category === $cat['category'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="view-btn" style="width: auto; margin: 0;">Search</button>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="product-grid">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <img src="<?= !empty($product['image']) ? htmlspecialchars($product['image']) : '/placeholder.svg?height=250&width=240' ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="price">RM <?= number_format($product['price'], 2) ?></p>
                            <p style="margin: 0 15px; color: #666; font-size: 14px;">
                                Total Stock: <?= $product['total_stock'] ?>
                                <?php if ($product['total_stock'] <= 10): ?>
                                    <span style="color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>
                                <?php endif; ?>
                            </p>
                            <button class="view-btn" onclick="showProductDetails(<?= $product['id'] ?>)">
                                View Details
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: white; font-size: 18px; grid-column: 1/-1;">
                        No products found. Try adjusting your search criteria.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Product Details Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent">
                <!-- Product details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        let selectedSize = null;
        let selectedSizeStock = 0;

        function showProductDetails(productId) {
            fetch(`get_product_with_sizes.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    const product = data.product;
                    const sizes = data.sizes;
                    
                    let sizesHtml = '';
                    sizes.forEach(size => {
                        const disabled = size.stock === 0 ? 'disabled' : '';
                        sizesHtml += `
                            <button class="size-btn" onclick="selectSize('${size.size}', ${size.stock}, this)" ${disabled}>
                                ${size.size}
                                <div class="size-stock">${size.stock} left</div>
                            </button>
                        `;
                    });
                    
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = `
                        <div class="product-details">
                            <div class="product-image">
                                <img src="${product.image || '/placeholder.svg?height=300&width=300'}" alt="${product.name}">
                            </div>
                            <div class="product-info">
                                <h2>${product.name}</h2>
                                <div class="product-price">RM ${parseFloat(product.price).toFixed(2)}</div>
                                
                                ${product.description ? `<div class="product-description">${product.description}</div>` : ''}
                                
                                <div class="size-selector">
                                    <label>Size:</label>
                                    <div class="size-options">
                                        ${sizesHtml}
                                    </div>
                                </div>
                                
                                <div class="stock-info" id="stockInfo" style="display: none;">
                                    Stock: <span id="selectedStock">0</span> available
                                </div>
                                
                                <div class="quantity-selector">
                                    <label>Quantity:</label>
                                    <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                                    <input type="number" id="quantity" class="qty-input" value="1" min="1" max="1">
                                    <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                                </div>
                                
                                <button class="add-to-cart-btn" onclick="addToCart(${product.id})" id="addToCartBtn" disabled>
                                    Select Size to Add to Cart
                                </button>
                            </div>
                        </div>
                    `;
                    
                    // Reset selection
                    selectedSize = null;
                    selectedSizeStock = 0;
                    
                    document.getElementById('productModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product details');
                });
        }

        function selectSize(size, stock, element) {
            // Remove selected class from all size buttons
            document.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('selected'));
            
            // Add selected class to clicked button
            element.classList.add('selected');
            
            // Update global variables
            selectedSize = size;
            selectedSizeStock = stock;
            
            // Update UI
            document.getElementById('stockInfo').style.display = 'block';
            document.getElementById('selectedStock').textContent = stock;
            
            const quantityInput = document.getElementById('quantity');
            quantityInput.max = stock;
            quantityInput.value = Math.min(quantityInput.value, stock);
            
            const addToCartBtn = document.getElementById('addToCartBtn');
            addToCartBtn.disabled = false;
            addToCartBtn.textContent = 'Add to Cart';
        }

        function changeQuantity(change) {
            const input = document.getElementById('quantity');
            const newValue = parseInt(input.value) + change;
            const max = parseInt(input.max);
            
            if (newValue >= 1 && newValue <= max) {
                input.value = newValue;
            }
        }

        function addToCart(productId) {
            if (!selectedSize) {
                alert('Please select a size');
                return;
            }
            
            const quantity = document.getElementById('quantity').value;
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('size', selectedSize);
            formData.append('quantity', quantity);
            
            fetch('add_to_cart_with_sizes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    closeModal();
                } else {
                    alert('Error adding to cart: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding to cart');
            });
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>