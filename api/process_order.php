<?php
/**
 * Process Order API
 * Fast Food POS System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Check if user is logged in
    if (!isLoggedIn()) {
        throw new Exception('User not logged in');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $requiredFields = ['order_number', 'items', 'total_amount', 'payment_method'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Validate items array
    if (!is_array($input['items']) || empty($input['items'])) {
        throw new Exception('No items in order');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Create customer record if customer info provided
        $customerId = null;
        if (!empty($input['customer_name'])) {
            $query = "INSERT INTO customers (name, postcode) VALUES (?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                sanitize($input['customer_name']),
                sanitize($input['customer_postcode'] ?? '')
            ]);
            $customerId = $db->lastInsertId();
        }
        
        // Create order record
        $query = "INSERT INTO orders (
            order_number, user_id, customer_id, order_type, 
            subtotal, total_amount, payment_method, payment_status, 
            order_status, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'paid', 'pending', ?, NOW())";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            sanitize($input['order_number']),
            $_SESSION['user_id'],
            $customerId,
            $input['order_type'] ?? 'dine_in',
            $input['total_amount'],
            $input['total_amount'],
            $input['payment_method'],
            sanitize($input['notes'] ?? '')
        ]);
        
        $orderId = $db->lastInsertId();
        
        // Insert order items
        $query = "INSERT INTO order_items (
            order_id, item_id, item_name, quantity, 
            unit_price, total_price, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($query);
        
        foreach ($input['items'] as $item) {
            $stmt->execute([
                $orderId,
                $item['id'],
                sanitize($item['name']),
                $item['quantity'],
                $item['price'],
                $item['totalPrice']
            ]);
        }
        
        // Commit transaction
        $db->commit();
        
        // Get the complete order for response
        $query = "SELECT o.*, u.name as user_name, c.name as customer_name 
                  FROM orders o 
                  LEFT JOIN users u ON o.user_id = u.id 
                  LEFT JOIN customers c ON o.customer_id = c.id 
                  WHERE o.id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        
        // Get order items
        $query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
        
        $order['items'] = $orderItems;
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Order processed successfully',
            'order' => $order,
            'order_id' => $orderId
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => 'Order processing failed'
    ]);
}
?> 