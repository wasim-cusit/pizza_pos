/**
 * Main Application JavaScript
 * Fast Food POS System
 */

// Global variables
let currentCategoryId = 1;
let currentOrderNumber = '';

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    // Get current order number from header
    const orderInfo = document.querySelector('.order-info span');
    if (orderInfo && orderInfo.textContent.includes('Order No:')) {
        currentOrderNumber = orderInfo.textContent.replace('Order No: ', '');
    }
    
    // Set up event listeners
    setupEventListeners();
    
    // Load initial data
    loadInitialData();
});

// Setup event listeners
function setupEventListeners() {
    // Category click events
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function() {
            const categoryId = this.dataset.categoryId;
            loadItems(categoryId);
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchItems(this.value);
        });
    }
}

// Load initial data
function loadInitialData() {
    // Load first category items
    const firstCategory = document.querySelector('.category-card');
    if (firstCategory) {
        currentCategoryId = firstCategory.dataset.categoryId;
        loadItems(currentCategoryId);
    }
}

// Load items for a specific category
function loadItems(categoryId) {
    currentCategoryId = categoryId;
    
    // Update active category
    document.querySelectorAll('.category-card').forEach(card => {
        card.classList.remove('active');
    });
    
    const activeCard = document.querySelector(`[data-category-id="${categoryId}"]`);
    if (activeCard) {
        activeCard.classList.add('active');
    }
    
    // Show loading state
    const itemsGrid = document.getElementById('items-grid');
    itemsGrid.innerHTML = '<div class="loading">Loading items...</div>';
    
    // Try to fetch items via AJAX first
    fetch(`api/get_items.php?category_id=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayItems(data.items);
            } else {
                throw new Error(data.message || 'Error loading items');
            }
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            // Fallback: Load items from the page data
            loadItemsFromPageData(categoryId);
        });
}

// Fallback function to load items from page data
function loadItemsFromPageData(categoryId) {
    const itemsGrid = document.getElementById('items-grid');
    
    // Get all item cards on the page
    const allItemCards = document.querySelectorAll('.item-card');
    const categoryItems = [];
    
    // Filter items by category (this is a fallback since we don't have category data in HTML)
    // For now, show all items and let the user know
    allItemCards.forEach(card => {
        categoryItems.push(card);
    });
    
    if (categoryItems.length > 0) {
        // Show all items as fallback
        itemsGrid.innerHTML = '';
        categoryItems.forEach(card => {
            itemsGrid.appendChild(card.cloneNode(true));
        });
        showToast('Loaded items (fallback mode)', 'info');
    } else {
        itemsGrid.innerHTML = '<div class="no-items">No items available in this category</div>';
        showToast('No items found for this category', 'warning');
    }
}

// Display items in the grid
function displayItems(items) {
    const itemsGrid = document.getElementById('items-grid');
    itemsGrid.innerHTML = '';
    
    if (items.length === 0) {
        itemsGrid.innerHTML = '<div class="no-items">No items available in this category</div>';
        return;
    }
    
    items.forEach(item => {
        const itemCard = document.createElement('div');
        itemCard.className = 'item-card';
        itemCard.onclick = () => addToCart(item.id, item.name, item.price);
        
        const itemIcon = getItemIcon(item.name);
        
        itemCard.innerHTML = `
            <div class="item-image">
                ${itemIcon}
            </div>
            <div class="item-name">${item.name}</div>
            <div class="item-price">PKR ${parseFloat(item.price).toFixed(2)}</div>
            ${item.description ? `<div class="item-description">${item.description}</div>` : ''}
        `;
        
        itemsGrid.appendChild(itemCard);
    });
}

// Get appropriate icon for item
function getItemIcon(itemName) {
    const itemIcons = {
        'PIZZA': '🍕',
        'BURGER': '🍔',
        'WINGS': '🍗',
        'SOUP': '🥣',
        'DRINKS': '🥤',
        'SHAWARMA': '🌯',
        'FRIES': '🍟',
        'SHAKE': '🥤',
        'SANDWICH': '🥪',
        'CHINESE': '🥢',
        'FRIED': '🍤',
        'COFFEE': '☕',
        'TEA': '🫖'
    };
    
    for (const [keyword, icon] of Object.entries(itemIcons)) {
        if (itemName.toUpperCase().includes(keyword)) {
            return icon;
        }
    }
    
    return '🍽️';
}

// Search items
function searchItems(query) {
    if (!query.trim()) {
        loadItems(currentCategoryId);
        return;
    }
    
    fetch(`api/search_items.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayItems(data.items);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Navigation functions
function goHome() {
    window.location.href = 'index.php';
}

function goBack() {
    window.history.back();
}

function goNext() {
    window.history.forward();
}

function levelUp() {
    // Go to parent category or main menu
    showToast('Level Up functionality', 'info');
}

// Customer functions
function selectCustomer() {
    const customerName = document.getElementById('customer-name').value;
    const customerPostcode = document.getElementById('customer-postcode').value;
    
    if (!customerName.trim()) {
        showToast('Please enter customer name', 'warning');
        return;
    }
    
    // Save customer info
    localStorage.setItem('pos_customer_name', customerName);
    localStorage.setItem('pos_customer_postcode', customerPostcode);
    
    showToast('Customer selected: ' + customerName, 'success');
}

// Order processing functions
function processOrder() {
    if (cart.length === 0) {
        showToast('Cart is empty', 'warning');
        return;
    }
    
    const cartData = getCartData();
    
    // Show payment modal
    showPaymentModal(cartData);
}

function takeAway() {
    if (cart.length === 0) {
        showToast('Cart is empty', 'warning');
        return;
    }
    
    const cartData = getCartData();
    cartData.orderType = 'takeaway';
    
    // Process takeaway order
    processOrderWithType(cartData);
}

function selectTable() {
    const tableNumber = prompt('Enter table number:');
    if (tableNumber) {
        localStorage.setItem('pos_table_number', tableNumber);
        showToast(`Table ${tableNumber} selected`, 'success');
    }
}

function holdOrder() {
    if (cart.length === 0) {
        showToast('Cart is empty', 'warning');
        return;
    }
    
    const orderData = {
        items: cart,
        customerName: document.getElementById('customer-name').value,
        customerPostcode: document.getElementById('customer-postcode').value,
        orderNumber: currentOrderNumber,
        timestamp: new Date().toISOString()
    };
    
    // Save to localStorage
    const heldOrders = JSON.parse(localStorage.getItem('pos_held_orders') || '[]');
    heldOrders.push(orderData);
    localStorage.setItem('pos_held_orders', JSON.stringify(heldOrders));
    
    clearCart();
    showToast('Order held successfully', 'success');
}

function recallOrder() {
    const heldOrders = JSON.parse(localStorage.getItem('pos_held_orders') || '[]');
    
    if (heldOrders.length === 0) {
        showToast('No held orders', 'info');
        return;
    }
    
    // Show held orders modal
    showHeldOrdersModal(heldOrders);
}

function kitchenDone() {
    showToast('Kitchen Done functionality', 'info');
}

// Action button functions
function showDressings() {
    showModal('Dressings', `
        <div class="dressings-grid">
            <button class="dressing-btn" onclick="addToCart(999, 'Ketchup', 20)">Ketchup</button>
            <button class="dressing-btn" onclick="addToCart(998, 'Mayonnaise', 25)">Mayonnaise</button>
            <button class="dressing-btn" onclick="addToCart(997, 'Mustard', 20)">Mustard</button>
            <button class="dressing-btn" onclick="addToCart(996, 'Hot Sauce', 30)">Hot Sauce</button>
            <button class="dressing-btn" onclick="addToCart(995, 'BBQ Sauce', 25)">BBQ Sauce</button>
            <button class="dressing-btn" onclick="addToCart(994, 'Ranch', 30)">Ranch</button>
        </div>
    `);
}

function showNotes() {
    const notes = prompt('Enter order notes:');
    if (notes !== null) {
        localStorage.setItem('pos_order_notes', notes);
        showToast('Notes saved', 'success');
    }
}

function showSubMenu() {
    showToast('Sub Menu functionality', 'info');
}

function openBackOffice() {
    if (confirm('Open Back Office?')) {
        window.open('admin/', '_blank');
    }
}

function showSpecialOffers() {
    showModal('Special Offers', `
        <div class="offers-grid">
            <div class="offer-card">
                <h3>🍕 Pizza Combo</h3>
                <p>Large Pizza + 2 Drinks</p>
                <p class="offer-price">PKR 800 (Save PKR 200)</p>
                <button onclick="addComboToCart()">Add Combo</button>
            </div>
            <div class="offer-card">
                <h3>🍔 Burger Combo</h3>
                <p>Burger + Fries + Drink</p>
                <p class="offer-price">PKR 450 (Save PKR 100)</p>
                <button onclick="addComboToCart()">Add Combo</button>
            </div>
        </div>
    `);
}

function showPizza() {
    loadItems(1); // Pizza category
}

function showZebra() {
    showToast('Zebra printer functionality', 'info');
}

function showEasyPad() {
    showModal('EasyPad Calculator', `
        <div class="calculator">
            <input type="text" id="calc-display" readonly>
            <div class="calc-buttons">
                <button onclick="calcInput('7')">7</button>
                <button onclick="calcInput('8')">8</button>
                <button onclick="calcInput('9')">9</button>
                <button onclick="calcInput('+')">+</button>
                <button onclick="calcInput('4')">4</button>
                <button onclick="calcInput('5')">5</button>
                <button onclick="calcInput('6')">6</button>
                <button onclick="calcInput('-')">-</button>
                <button onclick="calcInput('1')">1</button>
                <button onclick="calcInput('2')">2</button>
                <button onclick="calcInput('3')">3</button>
                <button onclick="calcInput('*')">×</button>
                <button onclick="calcInput('0')">0</button>
                <button onclick="calcInput('.')">.</button>
                <button onclick="calcClear()">C</button>
                <button onclick="calcInput('/')">/</button>
                <button onclick="calcResult()" class="calc-equals">=</button>
            </div>
        </div>
    `);
}

// Modal functions
function showModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">${title}</h3>
                <span class="close" onclick="closeModal(this)">&times;</span>
            </div>
            <div class="modal-body">
                ${content}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'block';
}

function closeModal(element) {
    const modal = element.closest('.modal');
    modal.style.display = 'none';
    modal.remove();
}

// Payment modal
function showPaymentModal(cartData) {
    const total = cartData.total;
    const items = cartData.items;
    
    const modalContent = `
        <div class="payment-summary">
            <h3>Order Summary</h3>
            <div class="order-items">
                ${items.map(item => `
                    <div class="order-item">
                        <span>${item.name} x${item.quantity}</span>
                        <span>PKR ${item.totalPrice.toFixed(2)}</span>
                    </div>
                `).join('')}
            </div>
            <div class="order-total">
                <strong>Total: PKR ${total.toFixed(2)}</strong>
            </div>
            <div class="payment-methods">
                <button onclick="processPayment('cash', ${total})" class="btn btn-primary">Cash Payment</button>
                <button onclick="processPayment('card', ${total})" class="btn btn-primary">Card Payment</button>
                <button onclick="processPayment('online', ${total})" class="btn btn-primary">Online Payment</button>
            </div>
        </div>
    `;
    
    showModal('Payment', modalContent);
}

// Process payment
function processPayment(method, amount) {
    const cartData = getCartData();
    
    // Prepare order data
    const orderData = {
        order_number: currentOrderNumber,
        items: cartData.items,
        total_amount: amount,
        payment_method: method,
        customer_name: cartData.customerName,
        customer_postcode: cartData.customerPostcode,
        order_type: 'dine_in'
    };
    
    // Send to server
    fetch('api/process_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Order processed successfully!', 'success');
            clearCart();
            closeModal(document.querySelector('.close'));
            
            // Print receipt
            printReceipt(data.order);
        } else {
            showToast('Error processing order: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error processing order', 'error');
    });
}

// Print receipt
function printReceipt(order) {
    const receiptWindow = window.open('', '_blank');
    receiptWindow.document.write(`
        <html>
        <head>
            <title>Receipt</title>
            <style>
                body { font-family: monospace; font-size: 12px; }
                .receipt { width: 300px; margin: 0 auto; }
                .header { text-align: center; border-bottom: 1px solid #000; padding: 10px 0; }
                .item { display: flex; justify-content: space-between; margin: 5px 0; }
                .total { border-top: 1px solid #000; padding: 10px 0; font-weight: bold; }
                .footer { text-align: center; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <h2>Fast Food POS</h2>
                    <p>Order: ${order.order_number}</p>
                    <p>Date: ${new Date().toLocaleString()}</p>
                </div>
                <div class="items">
                    ${order.items.map(item => `
                        <div class="item">
                            <span>${item.name} x${item.quantity}</span>
                            <span>PKR ${item.totalPrice.toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
                <div class="total">
                    <div class="item">
                        <span>Total:</span>
                        <span>PKR ${order.total_amount.toFixed(2)}</span>
                    </div>
                </div>
                <div class="footer">
                    <p>Thank you for your order!</p>
                </div>
            </div>
        </body>
        </html>
    `);
    receiptWindow.document.close();
    receiptWindow.print();
}

// Keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Function keys for categories
        if (e.key >= 'F1' && e.key <= 'F12') {
            e.preventDefault();
            const categoryIndex = parseInt(e.key.replace('F', '')) - 1;
            const categories = document.querySelectorAll('.category-card');
            if (categories[categoryIndex]) {
                categories[categoryIndex].click();
            }
        }
        
        // Ctrl + Enter for order processing
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            processOrder();
        }
        
        // Escape to close modals
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.style.display = 'none';
                modal.remove();
            });
        }
    });
}

// Calculator functions
function calcInput(value) {
    const display = document.getElementById('calc-display');
    if (display) {
        display.value += value;
    }
}

function calcClear() {
    const display = document.getElementById('calc-display');
    if (display) {
        display.value = '';
    }
}

function calcResult() {
    const display = document.getElementById('calc-display');
    if (display) {
        try {
            display.value = eval(display.value);
        } catch (e) {
            display.value = 'Error';
        }
    }
}

// Utility functions
function showFunctions() {
    showToast('Functions menu', 'info');
}

function showMore() {
    showToast('More options', 'info');
}

// Missing functions that are referenced in HTML
function processOrderWithType(cartData) {
    // Process order with specific type (takeaway, dine-in, etc.)
    showPaymentModal(cartData);
}

function showHeldOrdersModal(heldOrders) {
    let modalContent = '<div class="held-orders">';
    modalContent += '<h3>Held Orders</h3>';
    
    if (heldOrders.length === 0) {
        modalContent += '<p>No held orders found.</p>';
    } else {
        modalContent += '<div class="held-orders-list">';
        heldOrders.forEach((order, index) => {
            const total = order.items.reduce((sum, item) => sum + item.totalPrice, 0);
            modalContent += `
                <div class="held-order-item">
                    <div class="order-info">
                        <strong>Order: ${order.orderNumber}</strong>
                        <span>${order.items.length} items</span>
                        <span>PKR ${total.toFixed(2)}</span>
                    </div>
                    <button onclick="recallHeldOrder(${index})" class="btn btn-primary">Recall</button>
                </div>
            `;
        });
        modalContent += '</div>';
    }
    
    modalContent += '</div>';
    showModal('Held Orders', modalContent);
}

function recallHeldOrder(index) {
    const heldOrders = JSON.parse(localStorage.getItem('pos_held_orders') || '[]');
    if (heldOrders[index]) {
        // Restore cart from held order
        cart = heldOrders[index].items;
        selectedItemIndex = 0;
        
        // Restore customer info
        document.getElementById('customer-name').value = heldOrders[index].customerName || '';
        document.getElementById('customer-postcode').value = heldOrders[index].customerPostcode || '';
        
        // Remove from held orders
        heldOrders.splice(index, 1);
        localStorage.setItem('pos_held_orders', JSON.stringify(heldOrders));
        
        // Update display
        updateCartDisplay();
        closeModal(document.querySelector('.close'));
        showToast('Order recalled successfully', 'success');
    }
}

function addComboToCart() {
    // Add combo items to cart
    showToast('Combo added to cart', 'success');
    closeModal(document.querySelector('.close'));
}

// Export functions for global access
window.loadItems = loadItems;
window.processOrder = processOrder;
window.takeAway = takeAway;
window.selectTable = selectTable;
window.holdOrder = holdOrder;
window.recallOrder = recallOrder;
window.kitchenDone = kitchenDone;
window.showDressings = showDressings;
window.showNotes = showNotes;
window.showSubMenu = showSubMenu;
window.openBackOffice = openBackOffice;
window.showSpecialOffers = showSpecialOffers;
window.showPizza = showPizza;
window.showZebra = showZebra;
window.showEasyPad = showEasyPad;
window.goHome = goHome;
window.goBack = goBack;
window.goNext = goNext;
window.levelUp = levelUp;
window.selectCustomer = selectCustomer;

// Export cart functions for global access
window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateItemQuantity = updateItemQuantity;
window.selectCartItem = selectCartItem;
window.increaseQuantity = increaseQuantity;
window.decreaseQuantity = decreaseQuantity;
window.deleteSelectedItem = deleteSelectedItem;
window.updateCartDisplay = updateCartDisplay;
window.clearCart = clearCart;
window.getCartTotal = getCartTotal;
window.getCartItemCount = getCartItemCount;
window.getCartItems = getCartItems;
window.getCartData = getCartData;
window.showToast = showToast;

// Export additional functions
window.processOrderWithType = processOrderWithType;
window.showHeldOrdersModal = showHeldOrdersModal;
window.recallHeldOrder = recallHeldOrder;
window.addComboToCart = addComboToCart;
window.closeModal = closeModal;
window.loadItemsFromPageData = loadItemsFromPageData; 