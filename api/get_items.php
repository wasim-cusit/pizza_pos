<?php
/**
 * Get Items by Category API
 * Fast Food POS System
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

try {
    // Get category ID from request
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 1;
    
    // Validate category ID
    if ($categoryId <= 0) {
        throw new Exception('Invalid category ID');
    }
    
    // Fetch items for the category
    $query = "SELECT id, name, price, description, image FROM items 
              WHERE category_id = ? AND is_available = 1 
              ORDER BY name";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$categoryId]);
    $items = $stmt->fetchAll();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'items' => $items,
        'category_id' => $categoryId,
        'count' => count($items)
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => 'Database error occurred'
    ]);
}
?> 