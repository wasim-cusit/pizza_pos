<?php
/**
 * Test Connection File
 * This file helps diagnose database and system issues
 */

echo "<h1>🍕 Fast Food POS - System Test</h1>";

// Test 1: PHP Version
echo "<h2>1. PHP Version Check</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? '✅ Available' : '❌ Not Available') . "<br>";

// Test 2: Database Connection
echo "<h2>2. Database Connection Test</h2>";
try {
    require_once 'config/database.php';
    echo "✅ Database connection successful<br>";
    
    // Test query
    $query = "SELECT COUNT(*) as count FROM categories";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Categories table accessible: " . $result['count'] . " categories found<br>";
    
    // Test items query
    $query = "SELECT COUNT(*) as count FROM items";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✅ Items table accessible: " . $result['count'] . " items found<br>";
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

// Test 3: File Permissions
echo "<h2>3. File Permissions Test</h2>";
$testFile = 'test_write.txt';
if (file_put_contents($testFile, 'test')) {
    echo "✅ Write permission: OK<br>";
    unlink($testFile);
} else {
    echo "❌ Write permission: Failed<br>";
}

// Test 4: Session Test
echo "<h2>4. Session Test</h2>";
session_start();
$_SESSION['test'] = 'working';
if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
    echo "✅ Sessions: Working<br>";
} else {
    echo "❌ Sessions: Failed<br>";
}

// Test 5: API Endpoints
echo "<h2>5. API Endpoints Test</h2>";
$apiFiles = ['api/get_items.php', 'api/process_order.php', 'api/search_items.php'];
foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file: Exists<br>";
    } else {
        echo "❌ $file: Missing<br>";
    }
}

// Test 6: JavaScript Files
echo "<h2>6. JavaScript Files Test</h2>";
$jsFiles = ['assets/js/cart.js', 'assets/js/app.js'];
foreach ($jsFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file: Exists<br>";
    } else {
        echo "❌ $file: Missing<br>";
    }
}

// Test 7: CSS Files
echo "<h2>7. CSS Files Test</h2>";
$cssFiles = ['assets/css/style.css'];
foreach ($cssFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file: Exists<br>";
    } else {
        echo "❌ $file: Missing<br>";
    }
}

echo "<h2>8. Quick Fixes</h2>";
echo "<p>If you see any ❌ errors above, here are the solutions:</p>";
echo "<ul>";
echo "<li><strong>Database Connection Error:</strong> Check config/database.php credentials</li>";
echo "<li><strong>Missing Files:</strong> Ensure all files are uploaded correctly</li>";
echo "<li><strong>Permission Errors:</strong> Set folder permissions to 755</li>";
echo "<li><strong>Session Errors:</strong> Check PHP session configuration</li>";
echo "</ul>";

echo "<p><a href='login.php'>Go to Login Page</a> | <a href='index.php'>Go to POS System</a></p>";
?> 