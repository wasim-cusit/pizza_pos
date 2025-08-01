<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrolling Test - Fast Food POS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override main CSS for admin pages to enable scrolling */
        body {
            overflow: auto !important;
            height: auto !important;
            min-height: 100vh;
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .test-section h2 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .test-content {
            height: 300px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            padding: 15px;
            background: #f8f9fa;
        }
        
        .btn-test {
            background: #20bf55;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        
        .btn-test:hover {
            background: #1a9f47;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-section">
            <h2>✅ Scrolling Test Page</h2>
            <p>This page tests if scrolling is working properly on all admin pages.</p>
            
            <div class="test-content">
                <h3>Test Content (Scrollable Area)</h3>
                <p>This is a test to verify that scrolling works correctly on all admin pages.</p>
                
                <?php for($i = 1; $i <= 50; $i++): ?>
                <p><strong>Line <?php echo $i; ?>:</strong> This is test content to make the page scrollable. 
                If you can see this content and scroll through it, then the scrolling fix is working correctly.</p>
                <?php endfor; ?>
                
                <p><strong>End of test content.</strong> If you can scroll to see this line, scrolling is working!</p>
            </div>
        </div>
        
        <div class="test-section">
            <h2>🔗 Admin Pages Links</h2>
            <p>Test these pages to verify scrolling works:</p>
            
            <a href="admin/index.php" class="btn-test">Dashboard</a>
            <a href="admin/manage_items.php" class="btn-test">Manage Items</a>
            <a href="admin/manage_categories.php" class="btn-test">Manage Categories</a>
            <a href="admin/manage_users.php" class="btn-test">Manage Users</a>
            <a href="admin/reports.php" class="btn-test">Reports</a>
            <a href="admin/settings.php" class="btn-test">Settings</a>
            
            <p style="margin-top: 15px; color: #666;">
                <strong>Instructions:</strong> Click on any admin page link above and try scrolling. 
                If you can scroll up and down on those pages, the fix is working correctly.
            </p>
        </div>
        
        <div class="test-section">
            <h2>📋 Status Check</h2>
            <ul>
                <li>✅ Body overflow: auto (Fixed)</li>
                <li>✅ Body height: auto (Fixed)</li>
                <li>✅ Min-height: 100vh (Fixed)</li>
                <li>✅ All admin pages updated</li>
                <li>✅ CSS overrides applied</li>
            </ul>
        </div>
    </div>
</body>
</html> 