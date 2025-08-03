<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$db = new Database();
$pdo = $db->getConnection();

// Handle delete test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_delete'])) {
    $id = $_POST['id'];
    
    // First check if the offer exists
    $checkQuery = "SELECT * FROM special_offers WHERE id = ?";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([$id]);
    $offer = $checkStmt->fetch();
    
    if ($offer) {
        // Try to delete
        $deleteQuery = "DELETE FROM special_offers WHERE id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $result = $deleteStmt->execute([$id]);
        
        if ($result) {
            $message = "✅ Successfully deleted offer ID: $id";
            $messageClass = "success";
        } else {
            $message = "❌ Failed to delete offer ID: $id";
            $messageClass = "error";
        }
    } else {
        $message = "❌ Offer ID: $id not found";
        $messageClass = "error";
    }
}

// Fetch all offers for testing
$query = "SELECT * FROM special_offers ORDER BY id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Delete Special Offers</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f8fafc; }
        .container { max-width: 800px; margin: 0 auto; }
        .message { padding: 15px; border-radius: 8px; margin: 20px 0; }
        .success { background: #d1fae5; color: #065f46; border: 2px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; border: 2px solid #ef4444; }
        .offer-card { background: white; padding: 20px; margin: 15px 0; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
        .btn-delete { background: #ef4444; color: white; }
        .btn-delete:hover { background: #dc2626; }
        .btn-back { background: #3b82f6; color: white; text-decoration: none; display: inline-block; margin: 20px 0; }
        .btn-back:hover { background: #2563eb; }
        .debug-info { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 15px 0; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Delete Special Offers</h1>
        
        <a href="manage_special_offers.php" class="btn btn-back">← Back to Special Offers</a>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $messageClass; ?>">
                <strong><?php echo $message; ?></strong>
            </div>
        <?php endif; ?>
        
        <div class="debug-info">
            <h3>🔍 Debug Information:</h3>
            <p><strong>Total Offers:</strong> <?php echo count($offers); ?></p>
            <p><strong>Session User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
            <p><strong>Session User Role:</strong> <?php echo $_SESSION['user_role']; ?></p>
            <p><strong>Request Method:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?></p>
            <p><strong>POST Data:</strong> <?php echo !empty($_POST) ? json_encode($_POST) : 'None'; ?></p>
        </div>
        
        <?php if (empty($offers)): ?>
            <div class="message error">
                <strong>No special offers found in database.</strong>
            </div>
        <?php else: ?>
            <h2>📋 Available Offers to Delete:</h2>
            <?php foreach ($offers as $offer): ?>
                <div class="offer-card">
                    <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
                    <p><strong>ID:</strong> <?php echo $offer['id']; ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($offer['description']); ?></p>
                    <p><strong>Original Price:</strong> PKR <?php echo number_format($offer['original_price'], 2); ?></p>
                    <p><strong>Discounted Price:</strong> PKR <?php echo number_format($offer['discounted_price'], 2); ?></p>
                    <p><strong>Discount:</strong> <?php echo number_format($offer['discount_percentage'], 1); ?>%</p>
                    <p><strong>Active:</strong> <?php echo $offer['is_active'] ? 'Yes' : 'No'; ?></p>
                    
                    <form method="POST" style="margin-top: 15px;">
                        <input type="hidden" name="test_delete" value="1">
                        <input type="hidden" name="id" value="<?php echo $offer['id']; ?>">
                        <button type="submit" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this offer?')">
                            🗑️ Delete This Offer
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <h3>🔧 Manual Delete Test:</h3>
            <form method="POST" style="background: #f3f4f6; padding: 20px; border-radius: 8px;">
                <input type="hidden" name="test_delete" value="1">
                <label for="manual_id"><strong>Enter Offer ID to Delete:</strong></label><br>
                <input type="number" id="manual_id" name="id" min="1" style="padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; width: 200px;">
                <button type="submit" class="btn btn-delete">🗑️ Delete by ID</button>
            </form>
        </div>
    </div>
</body>
</html> 