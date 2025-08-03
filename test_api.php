<?php
/**
 * Test API and Database Connection
 * Fast Food POS System
 */

require_once 'config/database.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    $testQuery = "SELECT 1 as test";
    $stmt = $db->prepare($testQuery);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
    }
    
    // Test categories
    echo "<h3>Categories Test</h3>";
    $catQuery = "SELECT * FROM categories ORDER BY display_order";
    $stmt = $db->prepare($catQuery);
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    echo "<p>Found " . count($categories) . " categories:</p>";
    echo "<ul>";
    foreach ($categories as $cat) {
        echo "<li>{$cat['name']} (ID: {$cat['id']})</li>";
    }
    echo "</ul>";
    
    // Test items
    echo "<h3>Items Test</h3>";
    $itemQuery = "SELECT * FROM items WHERE is_available = 1 ORDER BY category_id, name";
    $stmt = $db->prepare($itemQuery);
    $stmt->execute();
    $items = $stmt->fetchAll();
    
    echo "<p>Found " . count($items) . " items:</p>";
    echo "<ul>";
    foreach ($items as $item) {
        echo "<li>{$item['name']} - PKR {$item['price']} (Category: {$item['category_id']})</li>";
    }
    echo "</ul>";
    
    // Test API endpoint
    echo "<h3>API Test</h3>";
    echo "<p>Testing API endpoint: <a href='api/get_items.php?category_id=1' target='_blank'>api/get_items.php?category_id=1</a></p>";
    
    // Test the API directly
    $apiUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/api/get_items.php?category_id=1";
    $apiResponse = file_get_contents($apiUrl);
    
    if ($apiResponse) {
        $apiData = json_decode($apiResponse, true);
        if ($apiData && isset($apiData['success'])) {
            echo "<p style='color: green;'>✅ API is working! Found " . count($apiData['items']) . " items in category 1</p>";
        } else {
            echo "<p style='color: red;'>❌ API returned invalid response</p>";
            echo "<pre>" . htmlspecialchars($apiResponse) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ API request failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>File Permissions Test</h3>";
$filesToCheck = [
    'config/database.php',
    'api/get_items.php',
    'assets/js/app.js',
    'assets/js/cart.js',
    'assets/css/style.css'
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

echo "<h3>Quick Fix</h3>";
echo "<p>If items are not loading, try:</p>";
echo "<ol>";
echo "<li>Make sure the database is imported correctly</li>";
echo "<li>Check that the API endpoint is accessible</li>";
echo "<li>Verify file permissions</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to POS System</a></p>";
?> 