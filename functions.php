<?php
// Include database connection
include_once 'db.php';

// ==================== PRODUCT FUNCTIONS ====================

/**
 * Get all products from database
 */
function getAllProducts($conn) {
    $query = "SELECT product_id, product_name, product_category, product_size, product_colour, product_price, product_quantity FROM product ORDER BY product_name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getAllProducts: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    return $products;
}

/**
 * Get single product by ID
 */
function getProductById($conn, $product_id) {
    $query = "SELECT product_id, product_name, product_category, product_size, product_colour, product_price, product_quantity, product_image FROM product WHERE product_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getProductById: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $product ? $product : false;
}

/**
 * Get products by category
 */
function getProductsByCategory($conn, $category) {
    $query = "SELECT product_id, product_name, product_category, product_size, product_colour, product_price, product_quantity FROM product WHERE product_category = ? ORDER BY product_name";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getProductsByCategory: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "s", $category);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    return $products;
}

/**
 * Search products by name, category, or color
 */
function searchProducts($conn, $search_term) {
    $search_term = "%{$search_term}%";
    $query = "SELECT product_id, product_name, product_category, product_size, product_colour, product_price, product_quantity FROM product WHERE product_name LIKE ? OR product_category LIKE ? OR product_colour LIKE ? ORDER BY product_name";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing searchProducts: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "sss", $search_term, $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    return $products;
}

/**
 * Update product quantity (for inventory management)
 */
function updateProductQuantity($conn, $product_id, $new_quantity) {
    $query = "UPDATE product SET product_quantity = ? WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing updateProductQuantity: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $product_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

// ==================== SESSION-BASED CART FUNCTIONS ====================

/**
 * Add item to session cart
 */
function addToCart($session_id, $product_id, $quantity = 1) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    logActivity("Product $product_id added to cart for session $session_id");
    return true;
}

/**
 * Get all cart items for current session
 */
function getCartItems($conn, $session_id) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $query = "SELECT product_id, product_name, product_category, product_size, product_colour, product_price, product_quantity 
              FROM product 
              WHERE product_id IN ($placeholders)";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getCartItems: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    // Create types string for bind_param
    $types = str_repeat('i', count($product_ids));
    mysqli_stmt_bind_param($stmt, $types, ...$product_ids);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['cart_quantity'] = $_SESSION['cart'][$row['product_id']];
        $items[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    return $items;
}

/**
 * Update cart item quantity
 */
function updateCartQuantity($session_id, $product_id, $quantity) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($quantity <= 0) {
        return removeFromCart($session_id, $product_id);
    }
    
    $_SESSION['cart'][$product_id] = $quantity;
    logActivity("Cart quantity updated for product $product_id, session $session_id");
    return true;
}

/**
 * Remove item from cart
 */
function removeFromCart($session_id, $product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        logActivity("Product $product_id removed from cart for session $session_id");
        return true;
    }
    return false;
}

/**
 * Get cart item count
 */
function getCartCount($conn, $session_id) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}

/**
 * Get cart total amount
 */
function getCartTotal($conn, $session_id) {
    $cart_items = getCartItems($conn, $session_id);
    $total = 0;
    
    foreach ($cart_items as $item) {
        $total += $item['product_price'] * $item['cart_quantity'];
    }
    
    return $total;
}

/**
 * Clear entire cart
 */
function clearCart($session_id) {
    $_SESSION['cart'] = [];
    logActivity("Cart cleared for session $session_id");
    return true;
}

/**
 * Check if product is in cart
 */
function isInCart($product_id) {
    return isset($_SESSION['cart'][$product_id]);
}

/**
 * Get quantity of specific product in cart
 */
function getCartProductQuantity($product_id) {
    return isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
}

// ==================== CUSTOMER FUNCTIONS ====================

/**
 * Get customer by ID
 */
function getCustomerById($conn, $customer_id) {
    $query = "SELECT customer_id, customer_name, customer_phone_num, customer_email, customer_address FROM customer WHERE customer_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getCustomerById: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $customer ? $customer : false;
}

/**
 * Get customer by email
 */
function getCustomerByEmail($conn, $email) {
    $query = "SELECT customer_id, customer_name, customer_phone_num, customer_email, customer_address, PASSWORD FROM customer WHERE customer_email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getCustomerByEmail: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customer = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $customer ? $customer : false;
}

/**
 * Create new customer
 */
function createCustomer($conn, $name, $phone, $email, $address, $password) {
    // Check if email already exists
    $check_query = "SELECT customer_id FROM customer WHERE customer_email = ? LIMIT 1";
    $check_stmt = mysqli_prepare($conn, $check_query);
    
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($check_stmt);
            logActivity("Attempted to create customer with existing email: $email", 'WARNING');
            return false; // Email already exists
        }
        mysqli_stmt_close($check_stmt);
    }
    
    // Insert new customer
    $query = "INSERT INTO customer (customer_name, customer_phone_num, customer_email, customer_address, PASSWORD) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing createCustomer: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $phone, $email, $address, $hashed_password);
    
    $result = mysqli_stmt_execute($stmt);
    
    if (!$result) {
        logActivity("Error executing createCustomer: " . mysqli_stmt_error($stmt), 'ERROR');
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $customer_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    logActivity("New customer created: $email (ID: $customer_id)");
    return $customer_id; // Return the new customer ID instead of just true
}

/**
 * Update customer information
 */
function updateCustomer($conn, $customer_id, $name, $phone, $email, $address) {
    $query = "UPDATE customer SET customer_name = ?, customer_phone_num = ?, customer_email = ?, customer_address = ? WHERE customer_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing updateCustomer: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "sissi", $name, $phone, $email, $address, $customer_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Verify customer login
 */
function verifyCustomerLogin($conn, $email, $password) {
    $customer = getCustomerByEmail($conn, $email);
    if ($customer && password_verify($password, $customer['PASSWORD'])) {
        return $customer;
    }
    return false;
}

/**
 * Get all customers (for admin)
 */
function getAllCustomers($conn) {
    $query = "SELECT customer_id, customer_name, customer_phone_num, customer_email, customer_address FROM customer ORDER BY customer_name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getAllCustomers: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $customers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $customers[] = $row;
    }
    
    return $customers;
}

// ==================== ORDER FUNCTIONS ====================

/**
 * Create new order from cart
 */
function createOrderFromCart($conn, $customer_id) {
    $cart_items = getCartItems($conn, $_SESSION['session_id']);
    $total_price = getCartTotal($conn, $_SESSION['session_id']);
    
    if (empty($cart_items)) {
        return false;
    }
    
    // Start transaction
    mysqli_autocommit($conn, false);
    
    try {
        foreach ($cart_items as $item) {
            $query = "INSERT INTO orders (customer_id, product_id, order_date, total_price) VALUES (?, ?, CURDATE(), ?)";
            $stmt = mysqli_prepare($conn, $query);
            
            if (!$stmt) {
                throw new Exception("Error preparing createOrder: " . mysqli_error($conn));
            }
            
            $item_total = $item['product_price'] * $item['cart_quantity'];
            mysqli_stmt_bind_param($stmt, "iid", $customer_id, $item['product_id'], $item_total);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error executing createOrder: " . mysqli_stmt_error($stmt));
            }
            
            mysqli_stmt_close($stmt);
            
            // Update product quantity
            $new_quantity = $item['product_quantity'] - $item['cart_quantity'];
            if (!updateProductQuantity($conn, $item['product_id'], $new_quantity)) {
                throw new Exception("Error updating product quantity");
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        mysqli_autocommit($conn, true);
        
        // Clear cart after successful order
        clearCart($_SESSION['session_id']);
        
        logActivity("Order created for customer $customer_id with total $total_price");
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);
        logActivity("Error creating order: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * Get orders by customer ID
 */
function getOrdersByCustomer($conn, $customer_id) {
    $query = "SELECT o.order_id, o.product_id, o.order_date, o.total_price, p.product_name, p.product_colour, p.product_size 
              FROM orders o 
              JOIN product p ON o.product_id = p.product_id 
              WHERE o.customer_id = ? 
              ORDER BY o.order_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getOrdersByCustomer: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    return $orders;
}

/**
 * Get order by ID
 */
function getOrderById($conn, $order_id) {
    $query = "SELECT o.order_id, o.customer_id, o.product_id, o.order_date, o.total_price, 
                     c.customer_name, c.customer_email, c.customer_address,
                     p.product_name, p.product_colour, p.product_size, p.product_price
              FROM orders o 
              JOIN customer c ON o.customer_id = c.customer_id
              JOIN product p ON o.product_id = p.product_id 
              WHERE o.order_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getOrderById: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $order ? $order : false;
}

/**
 * Get all orders (for admin)
 */
function getAllOrders($conn) {
    $query = "SELECT o.order_id, o.customer_id, o.product_id, o.order_date, o.total_price, 
                     c.customer_name, c.customer_email,
                     p.product_name, p.product_colour, p.product_size
              FROM orders o 
              JOIN customer c ON o.customer_id = c.customer_id
              JOIN product p ON o.product_id = p.product_id 
              ORDER BY o.order_date DESC";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getAllOrders: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }
    
    return $orders;
}

// ==================== PAYMENT FUNCTIONS ====================

/**
 * Create payment record
 */
function createPayment($conn, $customer_id, $payment_method, $payment_amount) {
    $query = "INSERT INTO payment (customer_id, payment_date, payment_method, payment_amount) VALUES (?, CURDATE(), ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing createPayment: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "isd", $customer_id, $payment_method, $payment_amount);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Get payments by customer ID
 */
function getPaymentsByCustomer($conn, $customer_id) {
    $query = "SELECT payment_id, payment_date, payment_method, payment_amount FROM payment WHERE customer_id = ? ORDER BY payment_date DESC";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getPaymentsByCustomer: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, "i", $customer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $payments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
    mysqli_stmt_close($stmt);
    
    return $payments;
}

// ==================== ADMIN FUNCTIONS ====================

/**
 * Get admin by ID
 */
function getAdminById($conn, $admin_id) {
    $query = "SELECT admin_id, admin_name, admin_phone, admin_email FROM admin WHERE admin_id = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getAdminById: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $admin ? $admin : false;
}

/**
 * Get admin by email
 */
function getAdminByEmail($conn, $email) {
    $query = "SELECT admin_id, admin_name, admin_phone, admin_email, admin_password FROM admin WHERE admin_email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing getAdminByEmail: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $admin ? $admin : false;
}

/**
 * Get all admins
 */
function getAllAdmins($conn) {
    $query = "SELECT admin_id, admin_name, admin_phone, admin_email FROM admin ORDER BY admin_name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getAllAdmins: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $admins = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $admins[] = $row;
    }
    
    return $admins;
}

/**
 * Create new admin account
 */
function createAdmin($conn, $name, $phone, $email, $password, $role = 'admin') {
    $query = "INSERT INTO admin (admin_name, admin_phone, admin_email, admin_password, admin_role) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing createAdmin: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $phone, $email, $hashed_password, $role);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        logActivity("New admin created: $email");
    } else {
        logActivity("Failed to create admin: $email", 'ERROR');
    }
    
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Update admin information
 */
function updateAdmin($conn, $admin_id, $name, $phone, $email, $role = null) {
    if ($role) {
        $query = "UPDATE admin SET admin_name = ?, admin_phone = ?, admin_email = ?, admin_role = ? WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $phone, $email, $role, $admin_id);
    } else {
        $query = "UPDATE admin SET admin_name = ?, admin_phone = ?, admin_email = ? WHERE admin_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $phone, $email, $admin_id);
    }
    
    if (!$stmt) {
        logActivity("Error preparing updateAdmin: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $result;
}

/**
 * Update admin password
 */
function updateAdminPassword($conn, $admin_id, $new_password) {
    $query = "UPDATE admin SET admin_password = ? WHERE admin_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing updateAdminPassword: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $admin_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if ($result) {
        logActivity("Password updated for admin ID: $admin_id");
    }
    
    return $result;
}

/**
 * Delete admin account
 */
function deleteAdmin($conn, $admin_id) {
    // Don't allow deletion of the last admin
    $admin_count = getAdminCount($conn);
    if ($admin_count <= 1) {
        logActivity("Attempted to delete last admin account", 'WARNING');
        return false;
    }
    
    $query = "DELETE FROM admin WHERE admin_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        logActivity("Error preparing deleteAdmin: " . mysqli_error($conn), 'ERROR');
        return false;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    if ($result) {
        logActivity("Admin deleted: ID $admin_id");
    }
    
    return $result;
}

/**
 * Get admin count
 */
function getAdminCount($conn) {
    $query = "SELECT COUNT(*) as count FROM admin";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getAdminCount: " . mysqli_error($conn), 'ERROR');
        return 0;
    }
    
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

/**
 * Verify admin login
 */
function verifyAdminLogin($conn, $email, $password) {
    $admin = getAdminByEmail($conn, $email);
    if ($admin && password_verify($password, $admin['admin_password'])) {
        logActivity("Admin login successful: $email");
        return $admin;
    }
    logActivity("Admin login failed: $email", 'WARNING');
    return false;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is super admin
 */
function isSuperAdmin($conn) {
    if (!isAdmin()) {
        return false;
    }
    
    $admin = getAdminById($conn, $_SESSION['admin_id']);
    return $admin && $admin['admin_role'] === 'super_admin';
}

/**
 * Get current logged in admin
 */
function getCurrentAdmin($conn) {
    if (isAdmin()) {
        return getAdminById($conn, $_SESSION['admin_id']);
    }
    return false;
}

// ==================== UTILITY FUNCTIONS ====================

/**
 * Calculate tax amount
 */
function calculateTax($amount, $tax_rate = 0.08) {
    return $amount * $tax_rate;
}

/**
 * Format price for display
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Generate session ID if not exists
 */
function initializeSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['session_id'])) {
        $_SESSION['session_id'] = session_id();
    }
    return $_SESSION['session_id'];
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate random string (for tokens, etc.)
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

/**
 * Check if product is in stock
 */
function isProductInStock($conn, $product_id, $required_quantity = 1) {
    $product = getProductById($conn, $product_id);
    return $product && $product['product_quantity'] >= $required_quantity;
}

/**
 * Get product categories
 */
function getProductCategories($conn) {
    $query = "SELECT DISTINCT product_category FROM product ORDER BY product_category";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getProductCategories: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row['product_category'];
    }
    
    return $categories;
}

/**
 * Get product colors
 */
function getProductColors($conn) {
    $query = "SELECT DISTINCT product_colour FROM product ORDER BY product_colour";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getProductColors: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $colors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $colors[] = $row['product_colour'];
    }
    
    return $colors;
}

/**
 * Get product sizes
 */
function getProductSizes($conn) {
    $query = "SELECT DISTINCT product_size FROM product ORDER BY product_size";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        logActivity("Error in getProductSizes: " . mysqli_error($conn), 'ERROR');
        return [];
    }
    
    $sizes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sizes[] = $row['product_size'];
    }
    
    return $sizes;
}

/**
 * Log activity (for debugging/tracking)
 */
function logActivity($message, $type = 'INFO') {
    $log_file = 'logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] [$type] $message" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }
    
    file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
}

/**
 * Send JSON response (for AJAX)
 */
function sendJsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect to page
 */
function redirectTo($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

/**
 * Get current logged in customer
 */
function getCurrentCustomer($conn) {
    if (isLoggedIn()) {
        return getCustomerById($conn, $_SESSION['customer_id']);
    }
    return false;
}
?>
