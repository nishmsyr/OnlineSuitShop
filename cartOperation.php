<?php
include_once 'function.php';

$session_id = initializeSession();
$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch($action) {
        case 'add':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if($product_id > 0) {
                if(isProductInStock($conn, $product_id, $quantity)) {
                    if(addToCart($session_id, $product_id, $quantity)) {
                        $cart_count = getCartCount($conn, $session_id);
                        $response = [
                            'success' => true, 
                            'message' => 'Item added to cart!',
                            'cart_count' => $cart_count
                        ];
                    } else {
                        $response['message'] = 'Failed to add item to cart';
                    }
                } else {
                    $response['message'] = 'Product is out of stock';
                }
            } else {
                $response['message'] = 'Invalid product ID';
            }
            break;
            
        case 'update':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            
            if($product_id > 0) {
                if($quantity > 0 && !isProductInStock($conn, $product_id, $quantity)) {
                    $response['message'] = 'Not enough stock available';
                } else {
                    if(updateCartQuantity($session_id, $product_id, $quantity)) {
                        $cart_count = getCartCount($conn, $session_id);
                        $response = [
                            'success' => true, 
                            'message' => 'Cart updated!',
                            'cart_count' => $cart_count
                        ];
                    } else {
                        $response['message'] = 'Failed to update cart';
                    }
                }
            } else {
                $response['message'] = 'Invalid product ID';
            }
            break;
            
        case 'remove':
            $product_id = (int)($_POST['product_id'] ?? 0);
            
            if($product_id > 0) {
                if(removeFromCart($session_id, $product_id)) {
                    $cart_count = getCartCount($conn, $session_id);
                    $response = [
                        'success' => true, 
                        'message' => 'Item removed from cart!',
                        'cart_count' => $cart_count
                    ];
                } else {
                    $response['message'] = 'Failed to remove item from cart';
                }
            } else {
                $response['message'] = 'Invalid product ID';
            }
            break;
            
        case 'get_count':
            $cart_count = getCartCount($conn, $session_id);
            $response = [
                'success' => true,
                'cart_count' => $cart_count
            ];
            break;
            
        case 'clear':
            if(clearCart($session_id)) {
                $response = [
                    'success' => true,
                    'message' => 'Cart cleared!',
                    'cart_count' => 0
                ];
            } else {
                $response['message'] = 'Failed to clear cart';
            }
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    logActivity("Error in cart operations: " . $e->getMessage(), 'ERROR');
}

sendJsonResponse($response);
?>
