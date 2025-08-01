<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitize($_POST['name']);
                $category_id = (int)$_POST['category_id'];
                $price = (float)$_POST['price'];
                $description = sanitize($_POST['description']);
                
                $query = "INSERT INTO items (name, category_id, price, description) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$name, $category_id, $price, $description]);
                
                header('Location: manage_items.php?success=Item added successfully');
                exit();
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = sanitize($_POST['name']);
                $category_id = (int)$_POST['category_id'];
                $price = (float)$_POST['price'];
                $description = sanitize($_POST['description']);
                
                $query = "UPDATE items SET name = ?, category_id = ?, price = ?, description = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$name, $category_id, $price, $description, $id]);
                
                header('Location: manage_items.php?success=Item updated successfully');
                exit();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                $query = "DELETE FROM items WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$id]);
                
                header('Location: manage_items.php?success=Item deleted successfully');
                exit();
                break;
        }
    }
}

// Get categories for dropdown
$query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll();

// Get items with category names
$query = "SELECT i.*, c.name as category_name 
          FROM items i 
          JOIN categories c ON i.category_id = c.id 
          ORDER BY c.name, i.name";
$stmt = $db->prepare($query);
$stmt->execute();
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - Fast Food POS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override main CSS for admin pages to enable scrolling */
        body {
            overflow: auto !important;
            height: auto !important;
            min-height: 100vh;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .items-table th,
        .items-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .items-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .items-table tr:hover {
            background: #f8f9fa;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-admin {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 14px;
        }
        
        .btn-primary { background: #20bf55; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        
        .btn-admin:hover {
            opacity: 0.9;
        }
        
        .add-item-btn {
            background: #20bf55;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .add-item-btn:hover {
            background: #1a9f47;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #20bf55;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h1>🍕 Manage Menu Items</h1>
                <p>Add, edit, and manage menu items</p>
            </div>
            <div>
                <button class="btn-admin btn-primary" onclick="showAddModal()">
                    <i class="fas fa-plus"></i> Add New Item
                </button>
                <a href="index.php" class="btn-admin btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['success']); ?></div>
        <?php endif; ?>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                    <td>PKR <?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                    <td>
                        <span class="btn-admin <?php echo $item['is_available'] ? 'btn-primary' : 'btn-secondary'; ?>" style="padding: 4px 8px; font-size: 12px;">
                            <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-admin btn-warning" onclick="showEditModal(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', <?php echo $item['category_id']; ?>, <?php echo $item['price']; ?>, '<?php echo addslashes($item['description'] ?? ''); ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-admin btn-danger" onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add Item Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Item</h3>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (PKR)</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-admin btn-primary">Add Item</button>
                    <button type="button" class="btn-admin btn-secondary" onclick="closeModal('addModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Item Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Item</h3>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="edit_category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (PKR)</label>
                    <input type="number" name="price" id="edit_price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-admin btn-primary">Update Item</button>
                    <button type="button" class="btn-admin btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }
        
        function showEditModal(id, name, categoryId, price, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function deleteItem(id, name) {
            if (confirm('Are you sure you want to delete "' + name + '"?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html> 