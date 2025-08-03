<?php
/**
 * View Order Details - Admin Panel
 * Fast Food POS System
 */

require_once '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Get order ID
$orderId = $_GET['id'] ?? 0;

if (!$orderId) {
    header('Location: view_orders.php');
    exit;
}

// Get order details
$query = "SELECT o.*, u.name as user_name, u.username, c.name as customer_name, c.phone as customer_phone, c.email as customer_email
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN customers c ON o.customer_id = c.id 
          WHERE o.id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: view_orders.php');
    exit;
}

// Get order items
$query = "SELECT * FROM order_items WHERE order_id = ? ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate QR code data
$qrData = json_encode([
    'order_number' => $order['order_number'],
    'total_amount' => $order['total_amount'],
    'items_count' => count($orderItems),
    'timestamp' => $order['created_at'],
    'pos_system' => 'Fast Food POS'
]);

$qrCodeURL = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .order-info {
            padding: 30px;
            background: #f8fafc;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 4px solid #20bf55;
        }
        
        .info-card h3 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #64748b;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .items-section {
            padding: 30px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .items-table th {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            padding: 15px;
            text-align: left;
            font-weight: 700;
            color: #374151;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .items-table tr:hover {
            background: #f8fafc;
        }
        
        .totals-section {
            padding: 30px;
            background: #f8fafc;
        }
        
        .totals-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .total-row.final {
            font-weight: bold;
            font-size: 1.2em;
            color: #20bf55;
            border-top: 2px solid #e2e8f0;
            border-bottom: none;
        }
        
        .qr-section {
            padding: 30px;
            text-align: center;
        }
        
        .qr-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: inline-block;
            max-width: 400px;
        }
        
        .qr-code img {
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .actions {
            padding: 30px;
            text-align: center;
            background: #f8fafc;
        }
        
        .btn {
            padding: 12px 24px;
            margin: 5px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #20bf55, #01baef);
            color: white;
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        .btn-print {
            background: #10b981;
            color: white;
        }
        
        .btn-edit {
            background: #f59e0b;
            color: white;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .special-instructions {
            background: #fef2f2;
            border: 1px solid #ef4444;
            border-radius: 6px;
            padding: 10px;
            margin: 5px 0;
            font-size: 12px;
            color: #991b1b;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .items-table {
                font-size: 12px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-receipt"></i> Order Details</h1>
            <p>Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
        </div>
        
        <!-- Order Information -->
        <div class="order-info">
            <div class="info-grid">
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                    <div class="info-item">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Date & Time:</span>
                        <span class="info-value"><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Order Type:</span>
                        <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['order_type'])); ?></span>
                    </div>
                    <?php if ($order['table_number']): ?>
                    <div class="info-item">
                        <span class="info-label">Table Number:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['table_number']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-<?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                    <div class="info-item">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['customer_name'] ?? 'Walk-in Customer'); ?></span>
                    </div>
                    <?php if ($order['customer_phone']): ?>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['customer_email']): ?>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-user-tie"></i> Cashier Information</h3>
                    <div class="info-item">
                        <span class="info-label">Cashier:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['user_name'] ?? 'Admin'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                    </div>
                    <?php if ($order['notes']): ?>
                    <div class="info-item">
                        <span class="info-label">Notes:</span>
                        <span class="info-value"><?php echo htmlspecialchars($order['notes']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="items-section">
            <h2 style="margin-bottom: 20px; color: #1e293b;"><i class="fas fa-list"></i> Order Items</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                            <?php if (!empty($item['special_instructions'])): ?>
                            <div class="special-instructions">
                                📝 <?php echo htmlspecialchars($item['special_instructions']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>PKR <?php echo number_format($item['unit_price'], 2); ?></td>
                        <td><strong>PKR <?php echo number_format($item['total_price'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-card">
                <h3 style="margin-bottom: 20px; color: #1e293b;"><i class="fas fa-calculator"></i> Order Summary</h3>
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>PKR <?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Tax (15%):</span>
                    <span>PKR <?php echo number_format($order['tax_amount'], 2); ?></span>
                </div>
                <div class="total-row final">
                    <span>Total Amount:</span>
                    <span>PKR <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
        
        <!-- QR Code -->
        <div class="qr-section">
            <div class="qr-card">
                <h3 style="margin-bottom: 15px; color: #1e293b;"><i class="fas fa-qrcode"></i> QR Code</h3>
                <div class="qr-code">
                    <img src="<?php echo htmlspecialchars($qrCodeURL); ?>" alt="QR Code" />
                </div>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #64748b;">
                    Scan to view order details online<br>
                    Order: <?php echo htmlspecialchars($order['order_number']); ?>
                </p>
            </div>
        </div>
        
        <!-- Order Status Update -->
        <div class="actions" style="margin-bottom: 20px;">
            <h3 style="margin-bottom: 15px; color: #1e293b;"><i class="fas fa-tasks"></i> Update Order Status</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'pending')" 
                        class="btn <?php echo $order['order_status'] === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>" 
                        style="min-width: 120px;">
                    <i class="fas fa-clock"></i> Pending
                </button>
                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'preparing')" 
                        class="btn <?php echo $order['order_status'] === 'preparing' ? 'btn-primary' : 'btn-secondary'; ?>" 
                        style="min-width: 120px;">
                    <i class="fas fa-utensils"></i> Preparing
                </button>
                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'ready')" 
                        class="btn <?php echo $order['order_status'] === 'ready' ? 'btn-primary' : 'btn-secondary'; ?>" 
                        style="min-width: 120px;">
                    <i class="fas fa-check-circle"></i> Ready
                </button>
                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'completed')" 
                        class="btn <?php echo $order['order_status'] === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>" 
                        style="min-width: 120px;">
                    <i class="fas fa-check-double"></i> Completed
                </button>
                <button onclick="updateOrderStatus(<?php echo $order['id']; ?>, 'cancelled')" 
                        class="btn <?php echo $order['order_status'] === 'cancelled' ? 'btn-danger' : 'btn-secondary'; ?>" 
                        style="min-width: 120px;">
                    <i class="fas fa-times"></i> Cancelled
                </button>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="actions">
            <a href="../print_receipt.php?order_id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-print">
                <i class="fas fa-print"></i> Print Receipt
            </a>
            <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-edit">
                <i class="fas fa-edit"></i> Edit Order
            </a>
            <button onclick="deleteOrder(<?php echo $order['id']; ?>)" class="btn btn-delete">
                <i class="fas fa-trash"></i> Delete Order
            </button>
            <a href="view_orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>
    
    <script>
        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
                fetch('delete_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order deleted successfully');
                        window.location.href = 'view_orders.php';
                    } else {
                        alert('Error deleting order: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting order');
                });
            }
        }
        
        function updateOrderStatus(orderId, status) {
            const statusNames = {
                'pending': 'Pending',
                'preparing': 'Preparing',
                'ready': 'Ready',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            
            if (confirm(`Are you sure you want to mark this order as ${statusNames[status]}?`)) {
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        order_id: orderId,
                        status: status 
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Order status updated to ${statusNames[status]} successfully!`);
                        // Reload the page to show updated status
                        location.reload();
                    } else {
                        alert('Error updating order status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating order status');
                });
            }
        }
    </script>
</body>
</html> 