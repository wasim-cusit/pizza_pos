<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
requireLogin();

// Get current order number
$orderNumber = generateOrderNumber();

// Get categories
$query = "SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order, name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll();

// Get items for the first category (default) - ensure we have items
$defaultCategoryId = $categories[0]['id'] ?? 1;
$query = "SELECT * FROM items WHERE category_id = ? AND is_available = 1 ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute([$defaultCategoryId]);
$items = $stmt->fetchAll();

// If no items found, get items from any category
if (empty($items)) {
    $query = "SELECT * FROM items WHERE is_available = 1 ORDER BY category_id, name LIMIT 20";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $items = $stmt->fetchAll();
}

// Debug: Log the number of items loaded
error_log("Loaded " . count($items) . " items for category " . $defaultCategoryId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fast Food POS System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        // Set user role for JavaScript access
        window.userRole = '<?php echo $_SESSION['user_role']; ?>';
        window.userId = <?php echo $_SESSION['user_id']; ?>;
        window.userName = '<?php echo $_SESSION['user_name']; ?>';
    </script>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="order-info">
                <span>Order No: <?php echo $orderNumber; ?></span>
                <span>User: <?php echo $_SESSION['user_name']; ?></span>
                <span>Date: <?php echo date('d/m/Y H:i'); ?></span>
                <span>Till: 1</span>
            </div>
        </div>
        <div class="header-right">
            <!-- Logout Button For Temp -->
            <button class="nav-btn">
                <i class="fas fa-sign-out-alt"></i> <a href="./logout.php">Logout</a>
            </button>   

            <!-- Debug button for testing -->
            <button class="nav-btn" onclick="testCategoryLoading()" style="background: #f59e0b;">
                <i class="fas fa-bug"></i> Test Categories
            </button>

            <!-- Debug button for checking category cards -->
            <button class="nav-btn" onclick="checkCategoryCards()" style="background: #3b82f6;">
                <i class="fas fa-search"></i> Check Categories
            </button>

            <!-- Debug button for testing individual categories -->
            <button class="nav-btn" onclick="testIndividualCategories()" style="background: #10b981;">
                <i class="fas fa-play"></i> Test Individual
            </button>

            <button class="nav-btn" onclick="goHome()">
                <i class="fas fa-home"></i> Home
            </button>
            <button class="nav-btn" onclick="goBack()">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <button class="nav-btn" onclick="goNext()">
                <i class="fas fa-arrow-right"></i> Next
            </button>
            <button class="nav-btn" onclick="levelUp()">
                <i class="fas fa-arrow-up"></i> Level Up
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Left Sidebar - Cart -->
        <div class="cart-sidebar">
            <div class="cart-header">
                <h3>🛒 Order Cart</h3>
                <p>Items: <span id="cart-item-count">0</span></p>
            </div>
            
            <div class="cart-items" id="cart-items">
                <!-- Cart items will be loaded here -->
            </div>
            
            <div class="cart-controls">
                <div class="control-labels">
                    <span>PRICE</span>
                    <span>DISCOUNT</span>
                    <span>QUANTITY</span>
                    <span>DELETE</span>
                </div>
                <div class="control-buttons">
                    <button class="control-btn" onclick="decreaseQuantity()">-</button>
                    <button class="control-btn" onclick="applyDiscount()">%</button>
                    <div class="quantity-display" id="selected-quantity">1</div>
                    <button class="control-btn delete" onclick="deleteSelectedItem()">×</button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Categories Section -->
            <div class="categories-section">
                <div class="categories-grid" id="categories-grid">
                    <?php foreach ($categories as $category): ?>
                    <div class="category-card" data-category-id="<?php echo $category['id']; ?>">
                        <div class="category-icon">
                            <?php
                            $iconMap = [
                                'PIZZA' => '🍕',
                                'BURGERS' => '🍔',
                                'FRIED ITEMS' => '🍟',
                                'WINGS' => '🍗',
                                'SOUP' => '🥣',
                                'CHINESE' => '🥢',
                                'COLD DRINKS' => '🥤',
                                'HOT DRINKS' => '☕',
                                'SHAWARMA' => '🌯',
                                'FRIES' => '🍟',
                                'SHAKES' => '🥤',
                                'DELIVERY' => '🛵',
                                'SERVICES' => '⚙️',
                                'SANDWICH' => '🥪'
                            ];
                            echo $iconMap[$category['name']] ?? '🍽️';
                            ?>
                        </div>
                        <div class="category-name"><?php echo $category['name']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Items Section -->
            <div class="items-section">
                <div class="items-grid" id="items-grid">
                    <?php if (empty($items)): ?>
                        <div style="text-align: center; padding: 40px; color: #666;">
                            <div class="loading"></div>
                            <p>Loading items...</p>
                            <p style="font-size: 12px; color: #999;">Debug: No items found in PHP</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                        <div class="item-card" onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['name']); ?>', <?php echo $item['price']; ?>)">
                            <div class="item-image">
                                <?php
                                $itemIcons = [
                                    'PIZZA' => '🍕',
                                    'BURGER' => '🍔',
                                    'WINGS' => '🍗',
                                    'SOUP' => '🥣',
                                    'DRINKS' => '🥤',
                                    'SHAWARMA' => '🌯',
                                    'FRIES' => '🍟',
                                    'SHAKE' => '🥤',
                                    'SANDWICH' => '🥪'
                                ];
                                
                                $icon = '🍽️';
                                foreach ($itemIcons as $keyword => $itemIcon) {
                                    if (stripos($item['name'], $keyword) !== false) {
                                        $icon = $itemIcon;
                                        break;
                                    }
                                }
                                echo $icon;
                                ?>
                            </div>
                            <div class="item-name"><?php echo $item['name']; ?></div>
                            <div class="item-price">PKR <?php echo number_format($item['price'], 2); ?></div>
                            <?php if ($item['description']): ?>
                            <div class="item-description"><?php echo $item['description']; ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        <script>
                            console.log('PHP loaded <?php echo count($items); ?> items');
                        </script>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="bottom-section">
                <div class="customer-section">
                    <input type="text" class="customer-input" id="customer-name" placeholder="Customer">
                    <input type="text" class="customer-input" id="customer-postcode" placeholder="Postcode">
                    <button class="select-btn" onclick="selectCustomer()">
                        <i class="fas fa-user"></i> Select
                    </button>
                </div>
                
                <div class="payment-section">
                    <div class="payment-amount" id="total-amount">
                        Payment PKR 0.00
                    </div>
                    <div class="items-count" id="total-items">
                        Item(s): 0
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="action-btn" onclick="showDressings()">
                        <i class="fas fa-bottle-water"></i> Dressings
                    </button>
                    <button class="action-btn" onclick="showNotes()">
                        <i class="fas fa-sticky-note"></i> Instructions and Notes
                    </button>
                    <button class="action-btn" onclick="showSubMenu()">
                        <i class="fas fa-list"></i> Sub Menu
                    </button>
                    <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'cashier'): ?>
                        <button class="action-btn" onclick="openBackOffice()">
                            <i class="fas fa-building"></i> Back Office
                        </button>
                    <?php else: ?>
                        <button class="action-btn" onclick="openBackOffice()" style="background: #6c757d; opacity: 0.7;" title="Admin/Cashier access required">
                            <i class="fas fa-building"></i> Back Office
                        </button>
                    <?php endif; ?>
                    <button class="action-btn" onclick="showSpecialOffers()">
                        <i class="fas fa-gift"></i> Special Offers
                    </button>
                    <button class="action-btn" onclick="showPizza()">
                        <i class="fas fa-pizza-slice"></i> Pizza
                    </button>
                    <button class="action-btn" onclick="showZebra()">
                        <i class="fas fa-print"></i> Zebra
                    </button>
                    <button class="action-btn" onclick="showEasyPad()">
                        <i class="fas fa-calculator"></i> EasyPad
                    </button>
                    <button class="action-btn" onclick="updatePaymentDisplay()" style="background: #17a2b8;">
                        <i class="fas fa-sync"></i> Update Payment
                    </button>

                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <button class="sidebar-btn" onclick="processOrder()">
                <i class="fas fa-receipt"></i> Order
            </button>
            <button class="sidebar-btn" onclick="takeAway()">
                <i class="fas fa-shopping-bag"></i> Take away
            </button>
            <button class="sidebar-btn" onclick="selectTable()">
                <i class="fas fa-table"></i> Table (0)
            </button>
            <button class="sidebar-btn" onclick="holdOrder()">
                <i class="fas fa-pause"></i> Hold
            </button>
            <button class="sidebar-btn" onclick="recallOrder()">
                <i class="fas fa-redo"></i> Recall
            </button>
            <button class="sidebar-btn" onclick="kitchenDone()">
                <i class="fas fa-utensils"></i> Kitchen Done...
            </button>
            <button class="sidebar-btn" onclick="showFunctions()">
                <i class="fas fa-cog"></i> Functions
            </button>
            <button class="sidebar-btn" onclick="showMore()">
                <i class="fas fa-ellipsis-h"></i> More
            </button>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Scripts -->
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
        // Initialize the POS system
        document.addEventListener('DOMContentLoaded', function() {
            // Set first category as active
            const firstCategory = document.querySelector('.category-card');
            if (firstCategory) {
                firstCategory.classList.add('active');
            }
            
            // Load cart from storage and update display
            loadCartFromStorage();
            updateCartDisplay();
            
            // Force update payment display after initialization
            setTimeout(() => {
                updatePaymentDisplay();
            }, 200);
            
            // Set up keyboard shortcuts
            setupKeyboardShortcuts();
        });
    </script>
</body>
</html> 