<?php
/**
 * Print Receipt Page
 * Fast Food POS System - Enhanced with QR Codes
 */

require_once 'config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get order data from POST or GET
$orderData = null;
$qrCodeURL = '';
$qrData = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderData = json_decode($_POST['order_data'], true);
    $qrCodeURL = $_POST['qr_code_url'] ?? '';
    $qrData = $_POST['qr_data'] ?? '';
} elseif (isset($_GET['order_id'])) {
    // Fetch order from database
    $orderId = $_GET['order_id'];
    
    $query = "SELECT o.*, u.name as user_name, c.name as customer_name, c.phone as customer_phone
              FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id 
              LEFT JOIN customers c ON o.customer_id = c.id 
              WHERE o.id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order items
        $query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $orderData = [
            'order_number' => $order['order_number'],
            'date_time' => $order['created_at'],
            'cashier' => $order['user_name'] ?? 'Admin',
            'customer' => $order['customer_name'] ?? 'Walk-in Customer',
            'table_number' => $order['table_number'] ?? '',
            'items' => $orderItems,
            'subtotal' => $order['subtotal'],
            'tax_amount' => $order['tax_amount'],
            'total_amount' => $order['total_amount'],
            'payment_method' => $order['payment_method']
        ];
        
        // Generate QR code data
        $qrData = json_encode([
            'order_number' => $order['order_number'],
            'total_amount' => $order['total_amount'],
            'items_count' => count($orderItems),
            'timestamp' => $order['created_at'],
            'pos_system' => 'Fast Food POS'
        ]);
        
        $qrCodeURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
    }
}

// If no order data, show error
if (!$orderData) {
    echo '<div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">
            <h2>Error: No order data found</h2>
            <p>Please provide valid order information.</p>
          </div>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo htmlspecialchars($orderData['order_number']); ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .receipt { box-shadow: none !important; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            font-size: 14px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            color: #20bf55;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            color: #64748b;
            font-size: 12px;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 12px;
            color: #64748b;
        }
        
        .items {
            margin: 20px 0;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .item-name {
            flex: 1;
            font-weight: 500;
        }
        
        .item-quantity {
            margin: 0 10px;
            color: #64748b;
        }
        
        .item-price {
            font-weight: 600;
            color: #1e293b;
        }
        
        .totals {
            border-top: 2px solid #e2e8f0;
            margin-top: 15px;
            padding-top: 15px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 14px;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 16px;
            color: #20bf55;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .qr-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }
        
        .qr-code img {
            max-width: 150px;
            height: auto;
            border-radius: 6px;
            margin: 10px 0;
        }
        
        .qr-text {
            font-size: 11px;
            color: #64748b;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            color: #64748b;
            font-size: 11px;
        }
        
        .payment-info {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 6px;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
        }
        
        .payment-method {
            font-weight: bold;
            color: #0ea5e9;
            font-size: 14px;
        }
        
        .customer-info {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 10px;
            margin: 15px 0;
        }
        
        .customer-name {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        
        .table-info {
            background: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 6px;
            padding: 10px;
            margin: 15px 0;
            text-align: center;
        }
        
        .table-number {
            font-weight: bold;
            color: #065f46;
        }
        
        .print-buttons {
            text-align: center;
            margin: 20px 0;
        }
        
        .btn {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #20bf55;
            color: white;
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .special-instructions {
            background: #fef2f2;
            border: 1px solid #ef4444;
            border-radius: 6px;
            padding: 8px;
            margin: 5px 0;
            font-size: 11px;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            <h1>🍕 Fast Food POS</h1>
            <p>Delicious Food, Great Service</p>
            <p>www.fastfoodpos.com</p>
        </div>
        
        <!-- Order Information -->
        <div class="order-info">
            <span>Order: <?php echo htmlspecialchars($orderData['order_number']); ?></span>
            <span>Date: <?php echo date('Y-m-d H:i', strtotime($orderData['date_time'])); ?></span>
        </div>
        
        <div class="order-info">
            <span>Cashier: <?php echo htmlspecialchars($orderData['cashier']); ?></span>
            <span>Type: <?php echo ucfirst(str_replace('_', ' ', $orderData['payment_method'])); ?></span>
        </div>
        
        <!-- Customer Information -->
        <?php if (!empty($orderData['customer']) && $orderData['customer'] !== 'Walk-in Customer'): ?>
        <div class="customer-info">
            <div class="customer-name">Customer: <?php echo htmlspecialchars($orderData['customer']); ?></div>
        </div>
        <?php endif; ?>
        
        <!-- Table Information -->
        <?php if (!empty($orderData['table_number'])): ?>
        <div class="table-info">
            <div class="table-number">Table: <?php echo htmlspecialchars($orderData['table_number']); ?></div>
        </div>
        <?php endif; ?>
        
        <!-- Items -->
        <div class="items">
            <?php foreach ($orderData['items'] as $item): ?>
            <div class="item">
                <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                <div class="item-quantity">x<?php echo $item['quantity']; ?></div>
                <div class="item-price">PKR <?php echo number_format($item['total_price'], 2); ?></div>
            </div>
            <?php if (!empty($item['special_instructions'])): ?>
            <div class="special-instructions">
                📝 <?php echo htmlspecialchars($item['special_instructions']); ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>PKR <?php echo number_format($orderData['subtotal'], 2); ?></span>
            </div>
            <div class="total-row">
                <span>Tax (15%):</span>
                <span>PKR <?php echo number_format($orderData['tax_amount'], 2); ?></span>
            </div>
            <div class="total-row final">
                <span>Total:</span>
                <span>PKR <?php echo number_format($orderData['total_amount'], 2); ?></span>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="payment-info">
            <div class="payment-method">
                💳 Payment Method: <?php echo ucfirst(str_replace('_', ' ', $orderData['payment_method'])); ?>
            </div>
        </div>
        
        <!-- QR Code Section -->
        <?php if (!empty($qrCodeURL)): ?>
        <div class="qr-section">
            <h4 style="margin: 0 0 10px 0; color: #1e293b;">📱 Scan for Order Details</h4>
            <div class="qr-code">
                <img src="<?php echo htmlspecialchars($qrCodeURL); ?>" alt="QR Code" />
            </div>
            <div class="qr-text">
                Scan to view order details online<br>
                Order: <?php echo htmlspecialchars($orderData['order_number']); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your order!</p>
            <p>Visit us again soon</p>
            <p>🍕 Fast Food POS System</p>
            <p>www.fastfoodpos.com</p>
        </div>
    </div>
    
    <!-- Print Buttons -->
    <div class="print-buttons no-print">
        <button class="btn btn-primary" onclick="window.print()">
            🖨️ Print Receipt
        </button>
        <a href="index.php" class="btn btn-secondary">
            🏠 Back to POS
        </a>
        <button class="btn btn-secondary" onclick="window.close()">
            ❌ Close
        </button>
    </div>
    
    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // };
        
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html> 