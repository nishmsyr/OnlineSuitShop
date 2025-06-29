
<?php
session_start();
include 'db.php';

// Allow only logged-in admins
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$message = "";
$error_message = "";

// Handle product operations
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = floatval($_POST['price']);
        $description = $_POST['description'];
        $image = $_POST['image'];
        
        // Insert product
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, description, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $name, $category, $price, $description, $image);
        
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            
            // Add default sizes with stock
            $sizes = ['S', 'M', 'L', 'XL'];
            foreach ($sizes as $size) {
                $stock = intval($_POST["stock_$size"]);
                $size_stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock) VALUES (?, ?, ?)");
                $size_stmt->bind_param("isi", $product_id, $size, $stock);
                $size_stmt->execute();
                $size_stmt->close();
            }
            
            $message = "Product added successfully!";
        } else {
            $error_message = "Error adding product.";
        }
        $stmt->close();
        
    } elseif (isset($_POST['update_product'])) {
        $id = intval($_POST['id']);
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = floatval($_POST['price']);
        $description = $_POST['description'];
        $image = $_POST['image'];
        
        // Update product
        $stmt = $conn->prepare("UPDATE products SET name = ?, category = ?, price = ?, description = ?, image = ? WHERE id = ?");
        $stmt->bind_param("ssdssi", $name, $category, $price, $description, $image, $id);
        
        if ($stmt->execute()) {
            // Update sizes stock
            $sizes = ['S', 'M', 'L', 'XL'];
            foreach ($sizes as $size) {
                $stock = intval($_POST["stock_$size"]);
                
                // Check if size exists
                $check_stmt = $conn->prepare("SELECT id FROM product_sizes WHERE product_id = ? AND size = ?");
                $check_stmt->bind_param("is", $id, $size);
                $check_stmt->execute();
                $exists = $check_stmt->get_result()->fetch_assoc();
                $check_stmt->close();
                
                if ($exists) {
                    // Update existing size
                    $size_stmt = $conn->prepare("UPDATE product_sizes SET stock = ? WHERE product_id = ? AND size = ?");
                    $size_stmt->bind_param("iis", $stock, $id, $size);
                } else {
                    // Insert new size
                    $size_stmt = $conn->prepare("INSERT INTO product_sizes (product_id, size, stock) VALUES (?, ?, ?)");
                    $size_stmt->bind_param("isi", $id, $size, $stock);
                }
                $size_stmt->execute();
                $size_stmt->close();
            }
            
            $message = "Product updated successfully!";
        } else {
            $error_message = "Error updating product.";
        }
        $stmt->close();
        
    } elseif (isset($_POST['delete_product'])) {
        $id = intval($_POST['id']);
        
        // Delete product (sizes will be deleted automatically due to foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $message = "Product deleted successfully!";
        } else {
            $error_message = "Error deleting product.";
        }
        $stmt->close();
    }
}

// Fetch all products with their sizes
$products = [];
$result = $conn->query("SELECT p.*, GROUP_CONCAT(CONCAT(ps.size, ':', ps.stock) ORDER BY CASE ps.size WHEN 'S' THEN 1 
                               WHEN 'M' THEN 2 
                               WHEN 'L' THEN 3 
                               WHEN 'XL' THEN 4 
                               ELSE 5 
                           END SEPARATOR '|') as size_stock_info,
                       SUM(ps.stock) as total_stock
                       FROM products p 
                       LEFT JOIN product_sizes ps ON p.id = ps.product_id 
                       GROUP BY p.id 
                       ORDER BY p.name");
if ($result->num_rows > 0) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

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
    <title>Product Management - Blacktie Suit Shop</title>
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .add-product-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
        }
        .size-stock-inputs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        .size-input {
            text-align: center;
        }
        .size-input label {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .size-input input {
            text-align: center;
        }
        .products-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
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
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
        .size-stock-display {
            font-size: 12px;
            color: #666;
        }
        .size-badge {
            display: inline-block;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            margin: 2px;
            border: 1px solid #ddd;
        }
        .low-stock {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }
        .out-of-stock {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-edit {
            background: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
        }
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
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .size-stock-inputs {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation Bar -->
    <div class="navbar">
        <div class="left">
            <h1 class="logo">BLACKTIE</h1>
            <a href="admin_home.php" class="nav-link">Home</a>
        </div>
        <div class="right">
            <button class="logout-btn" onclick="window.location.href='login.php'">Logout</button>
        </div>
    </div>

    <div class="admin-container">
        <div class="admin-header">
            <h2><i class="fas fa-box"></i> Product Management</h2>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

<!-- Add Product Form -->
        <div class="add-product-form">
            <h3>Add New Product</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" list="categories" required>
                        <datalist id="categories">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (RM)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Image URL</label>
                        <input type="url" id="image" name="image">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Stock by Size</label>
                    <div class="size-stock-inputs">
                        <div class="size-input">
                            <label for="stock_S">Size S</label>
                            <input type="number" id="stock_S" name="stock_S" min="0" value="0" required>
                        </div>
                        <div class="size-input">
                            <label for="stock_M">Size M</label>
                            <input type="number" id="stock_M" name="stock_M" min="0" value="0" required>
                        </div>
                        <div class="size-input">
                            <label for="stock_L">Size L</label>
                            <input type="number" id="stock_L" name="stock_L" min="0" value="0" required>
                        </div>
                        <div class="size-input">
                            <label for="stock_XL">Size XL</label>
                            <input type="number" id="stock_XL" name="stock_XL" min="0" value="0" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="add_product" class="btn-primary">Add Product</button>
            </form>
        </div>

<!-- Products Table -->
        <div class="products-table">
            <div class="table-responsive">
                <?php if (count($products) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock by Size</th>
                                <th>Total Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?= !empty($product['image']) ? htmlspecialchars($product['image']) : '/placeholder.svg?height=60&width=60' ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                                    </td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= htmlspecialchars($product['category']) ?></td>
                                    <td>RM <?= number_format($product['price'], 2) ?></td>
                                    <td>
                                        <div class="size-stock-display">
                                            <?php 
                                            if ($product['size_stock_info']) {
                                                $sizes = explode('|', $product['size_stock_info']);
                                                foreach ($sizes as $size_info) {
                                                    list($size, $stock) = explode(':', $size_info);
                                                    $class = '';
                                                    if ($stock == 0) $class = 'out-of-stock';
                                                    elseif ($stock <= 5) $class = 'low-stock';
                                                    echo "<span class='size-badge $class'>$size: $stock</span>";
                                                }
                                            } else {
                                                echo "<span class='size-badge out-of-stock'>No sizes</span>";
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= $product['total_stock'] ?: 0 ?></strong>
                                        <?php if ($product['total_stock'] <= 10): ?>
                                            <i class="fas fa-exclamation-triangle" style="color: #dc3545;" title="Low stock"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick="editProduct(<?= $product['id'] ?>)">Edit</button>
                                            <button class="btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>')">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; color: #666;">
                        <i class="fas fa-box" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>No products found</h3>

<p>Add your first product using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="editModalContent">
                <!-- Edit form will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="id" id="deleteProductId">
        <input type="hidden" name="delete_product" value="1">
    </form>

    <script>
        function editProduct(productId) {
            fetch(`get_product_for_edit.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    
                    const product = data.product;
                    const sizes = data.sizes;
                    
                    // Create size inputs
                    let sizeInputs = '';
                    ['S', 'M', 'L', 'XL'].forEach(size => {
                        const sizeData = sizes.find(s => s.size === size);
                        const stock = sizeData ? sizeData.stock : 0;
                        sizeInputs += `
                            <div class="size-input">
                                <label for="edit_stock_${size}">Size ${size}</label>
                                <input type="number" id="edit_stock_${size}" name="stock_${size}" min="0" value="${stock}" required>
                            </div>
                        `;
                    });
                    
                    const modalContent = document.getElementById('editModalContent');
                    modalContent.innerHTML = `
                        <h3>Edit Product</h3>
                        <form method="POST">
                            <input type="hidden" name="id" value="${product.id}">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_name">Product Name</label>
                                    <input type="text" id="edit_name" name="name" value="${product.name}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_category">Category</label>
                                    <input type="text" id="edit_category" name="category" value="${product.category}" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_price">Price (RM)</label>
                                    <input type="number" id="edit_price" name="price" step="0.01" min="0" value="${product.price}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_image">Image URL</label>
                                    <input type="url" id="edit_image" name="image" value="${product.image || ''}">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_description">Description</label>
                                <textarea id="edit_description" name="description" rows="3">${product.description || ''}</textarea>
                            </div>
                            
                            <div class="form-group">
<label>Stock by Size</label>
                                <div class="size-stock-inputs">
                                    ${sizeInputs}
                                </div>
                            </div>
                            
                            <button type="submit" name="update_product" class="btn-primary">Update Product</button>
                        </form>
                    `;
                    
                    document.getElementById('editModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product details');
                });
        }

        function deleteProduct(productId, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                document.getElementById('deleteProductId').value = productId;
                document.getElementById('deleteForm').submit();
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>