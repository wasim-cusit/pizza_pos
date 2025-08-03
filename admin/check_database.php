<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$pdo = $db->getConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .check-result { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 10px 0; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #991b1b; }
        .info { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <h1>Database Check for Special Offers</h1>
    
    <?php
    try {
        // Check if special_offers table exists
        $query = "SHOW TABLES LIKE 'special_offers'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo '<div class="check-result success">';
            echo '<h3>✅ Special Offers Table Exists</h3>';
            echo '<p>The special_offers table is present in the database.</p>';
            echo '</div>';
            
            // Check table structure
            $query = "DESCRIBE special_offers";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<div class="check-result info">';
            echo '<h3>📋 Table Structure:</h3>';
            echo '<table border="1" style="border-collapse: collapse; width: 100%;">';
            echo '<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
            foreach ($columns as $column) {
                echo '<tr>';
                echo '<td>' . $column['Field'] . '</td>';
                echo '<td>' . $column['Type'] . '</td>';
                echo '<td>' . $column['Null'] . '</td>';
                echo '<td>' . $column['Key'] . '</td>';
                echo '<td>' . $column['Default'] . '</td>';
                echo '<td>' . $column['Extra'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '</div>';
            
            // Check for data
            $query = "SELECT COUNT(*) as count FROM special_offers";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            
            echo '<div class="check-result info">';
            echo '<h3>📊 Data Count:</h3>';
            echo '<p>Total special offers: ' . $count . '</p>';
            echo '</div>';
            
            if ($count > 0) {
                // Show sample data
                $query = "SELECT * FROM special_offers LIMIT 3";
                $stmt = $pdo->prepare($query);
                $stmt->execute();
                $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<div class="check-result info">';
                echo '<h3>📋 Sample Data:</h3>';
                foreach ($offers as $offer) {
                    echo '<div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                    echo '<strong>ID:</strong> ' . $offer['id'] . '<br>';
                    echo '<strong>Title:</strong> ' . $offer['title'] . '<br>';
                    echo '<strong>Original Price:</strong> PKR ' . $offer['original_price'] . '<br>';
                    echo '<strong>Discounted Price:</strong> PKR ' . $offer['discounted_price'] . '<br>';
                    echo '<strong>Discount:</strong> ' . $offer['discount_percentage'] . '%<br>';
                    echo '<strong>Priority:</strong> ' . $offer['priority'] . '<br>';
                    echo '<strong>Active:</strong> ' . ($offer['is_active'] ? 'Yes' : 'No') . '<br>';
                    echo '</div>';
                }
                echo '</div>';
            }
            
        } else {
            echo '<div class="check-result error">';
            echo '<h3>❌ Special Offers Table Missing</h3>';
            echo '<p>The special_offers table does not exist in the database.</p>';
            echo '<p>Please run the database.sql file to create the table.</p>';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="check-result error">';
        echo '<h3>❌ Database Error</h3>';
        echo '<p>Error: ' . $e->getMessage() . '</p>';
        echo '</div>';
    }
    ?>
    
    <div class="check-result">
        <h3>🔗 Test Links:</h3>
        <p><a href="manage_special_offers.php">Test Special Offers Management</a></p>
        <p><a href="index.php">Back to Admin Dashboard</a></p>
        <p><a href="../index.php">Back to POS</a></p>
    </div>
</body>
</html> 