<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 10px 0; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
        .info { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <h1>Session Debug Information</h1>
    
    <div class="debug-info">
        <h3>Session Variables:</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
    
    <div class="debug-info <?php echo isset($_SESSION['user_id']) ? 'success' : 'error'; ?>">
        <h3>User ID Check:</h3>
        <p>User ID: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'; ?></p>
    </div>
    
    <div class="debug-info <?php echo isset($_SESSION['user_role']) ? 'success' : 'error'; ?>">
        <h3>User Role Check:</h3>
        <p>User Role: <?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET'; ?></p>
    </div>
    
    <div class="debug-info <?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'success' : 'error'; ?>">
        <h3>Admin Check:</h3>
        <p>Is Admin: <?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') ? 'YES' : 'NO'; ?></p>
    </div>
    
    <div class="debug-info info">
        <h3>Available Session Keys:</h3>
        <ul>
            <?php foreach (array_keys($_SESSION) as $key): ?>
                <li><?php echo $key; ?>: <?php echo $_SESSION[$key]; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="debug-info">
        <h3>Test Links:</h3>
        <p><a href="manage_special_offers.php">Test Special Offers Page</a></p>
        <p><a href="index.php">Back to Admin Dashboard</a></p>
        <p><a href="../index.php">Back to POS</a></p>
    </div>
</body>
</html> 