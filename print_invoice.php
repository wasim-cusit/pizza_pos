<?php
/**
 * Print Invoice
 * Fast Food POS System
 */

require_once 'config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get order ID from URL
$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    die('Order ID is required');
}

try {
    // Get order details
    $orderQuery = "SELECT o.*, u.name as cashier_name, c.name as customer_name, c.contact as customer_contact, c.address as customer_address, c.postcode as customer_postcode
                   FROM orders o
                   LEFT JOIN users u ON o.user_id = u.id
                   LEFT JOIN customers c ON o.customer_id = c.id
                   WHERE o.id = ?";
    
    $orderStmt = $db->prepare($orderQuery);
    $orderStmt->execute([$orderId]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die('Order not found');
    }
    
    // Get order items
    $itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
    $itemsStmt = $db->prepare($itemsQuery);
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    die('Error loading order: ' . $e->getMessage());
}

// Generate QR code data
$qrData = json_encode([
    'order_number' => $order['order_number'],
    'total_amount' => $order['total_amount'],
    'date' => $order['created_at']
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo $order['order_number']; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .invoice-container { box-shadow: none !important; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
            font-size: 12px;
        }
        
        .invoice-container {
            max-width: 80mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .header {
            text-align: center;
            padding: 15px;
            border-bottom: 2px dashed #ccc;
        }
        
        .restaurant-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .restaurant-info {
            font-size: 10px;
            color: #666;
            line-height: 1.3;
        }
        
        .order-info {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .order-label {
            font-weight: bold;
        }
        
        .customer-info {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .items-section {
            padding: 15px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 3px 0;
        }
        
        .item-name {
            flex: 2;
        }
        
        .item-qty {
            text-align: center;
            flex: 1;
        }
        
        .item-price {
            text-align: right;
            flex: 1;
        }
        
        .item-total {
            text-align: right;
            flex: 1;
            font-weight: bold;
        }
        
        .totals-section {
            padding: 15px;
            border-top: 2px dashed #ccc;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-label {
            font-weight: bold;
        }
        
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        
        .footer {
            padding: 15px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        
        .qr-section {
            text-align: center;
            padding: 10px;
        }
        
        .qr-code {
            margin: 0 auto;
        }
        
        .thank-you {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        .order-type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-dine-in {
            background: #28a745;
            color: white;
        }
        
        .badge-takeaway {
            background: #ffc107;
            color: #212529;
        }
        
        .badge-delivery {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ Print Invoice
    </button>
    
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="restaurant-name">PIZZA POS</div>
            <div class="restaurant-info">
                123 Main Street<br>
                City, State 12345<br>
                Phone: (555) 123-4567<br>
                Email: info@pizzapos.com
            </div>
        </div>
        
        <!-- Order Information -->
        <div class="order-info">
            <div class="order-row">
                <span class="order-label">Order #:</span>
                <span><?php echo $order['order_number']; ?></span>
            </div>
            <div class="order-row">
                <span class="order-label">Date:</span>
                <span><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="order-row">
                <span class="order-label">Cashier:</span>
                <span><?php echo htmlspecialchars($order['cashier_name']); ?></span>
            </div>
            <div class="order-row">
                <span class="order-label">Type:</span>
                <span>
                    <span class="order-type-badge badge-<?php echo $order['order_type']; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?>
                    </span>
                    <?php if ($order['table_number']): ?>
                        (Table <?php echo $order['table_number']; ?>)
                    <?php endif; ?>
                </span>
            </div>
            <div class="order-row">
                <span class="order-label">Payment:</span>
                <span><?php echo ucfirst($order['payment_method']); ?></span>
            </div>
        </div>
        
        <!-- Customer Information -->
        <?php if ($order['customer_name'] || $order['customer_contact']): ?>
        <div class="customer-info">
            <div class="order-row">
                <span class="order-label">Customer:</span>
                <span><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></span>
            </div>
            <?php if ($order['customer_contact']): ?>
            <div class="order-row">
                <span class="order-label">Contact:</span>
                <span><?php echo htmlspecialchars($order['customer_contact']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['customer_address']): ?>
            <div class="order-row">
                <span class="order-label">Address:</span>
                <span><?php echo htmlspecialchars($order['customer_address']); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['customer_postcode']): ?>
            <div class="order-row">
                <span class="order-label">Postcode:</span>
                <span><?php echo htmlspecialchars($order['customer_postcode']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Items -->
        <div class="items-section">
            <div class="order-row" style="border-bottom: 1px solid #000; padding-bottom: 5px; margin-bottom: 10px;">
                <span class="item-name"><strong>Item</strong></span>
                <span class="item-qty"><strong>Qty</strong></span>
                <span class="item-price"><strong>Price</strong></span>
                <span class="item-total"><strong>Total</strong></span>
            </div>
            
            <?php foreach ($items as $item): ?>
            <div class="item-row">
                <span class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></span>
                <span class="item-qty"><?php echo $item['quantity']; ?></span>
                <span class="item-price">PKR <?php echo number_format($item['unit_price'], 2); ?></span>
                <span class="item-total">PKR <?php echo number_format($item['total_price'], 2); ?></span>
            </div>
            <?php if ($item['notes']): ?>
            <div class="item-row" style="font-size: 10px; color: #666; padding-left: 10px;">
                <span class="item-name">- <?php echo htmlspecialchars($item['notes']); ?></span>
                <span class="item-qty"></span>
                <span class="item-price"></span>
                <span class="item-total"></span>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span class="total-label">Subtotal:</span>
                <span>PKR <?php echo number_format($order['subtotal'], 2); ?></span>
            </div>
            <div class="total-row">
                <span class="total-label">Tax (15%):</span>
                <span>PKR <?php echo number_format($order['tax_amount'], 2); ?></span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label">Total:</span>
                <span>PKR <?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>
        
        <!-- QR Code -->
        <div class="qr-section">
            <div id="qr-code" class="qr-code"></div>
            <div class="thank-you">Thank you for your order!</div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div style="font-size: 10px; color: #666;">
                For any queries, please contact us<br>
                Visit us again!
            </div>
        </div>
    </div>
    
    <script>
        // Generate QR Code
        QRCode.toCanvas(document.getElementById('qr-code'), '<?php echo $qrData; ?>', {
            width: 100,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        }, function (error) {
            if (error) console.error(error);
        });
        
        // Auto print after 1 second
        setTimeout(() => {
            window.print();
        }, 1000);
    </script>
</body>
</html> 