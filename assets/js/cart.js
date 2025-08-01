/**
 * Cart Management System
 * Fast Food POS System
 */

// Global cart state
let cart = [];
let selectedItemIndex = -1;

// Cart functions
function addToCart(itemId, itemName, itemPrice, quantity = 1) {
    // Check if item already exists in cart
    const existingItemIndex = cart.findIndex(item => item.id === itemId);
    
    if (existingItemIndex !== -1) {
        // Update existing item quantity
        cart[existingItemIndex].quantity += quantity;
        cart[existingItemIndex].totalPrice = cart[existingItemIndex].quantity * cart[existingItemIndex].price;
    } else {
        // Add new item to cart
        cart.push({
            id: itemId,
            name: itemName,
            price: parseFloat(itemPrice),
            quantity: quantity,
            totalPrice: parseFloat(itemPrice) * quantity
        });
    }
    
    // Update cart display
    updateCartDisplay();
    
    // Show success message
    showToast('Item added to cart', 'success');
    
    // Select the newly added item
    selectCartItem(cart.length - 1);
}

function removeFromCart(index) {
    if (index >= 0 && index < cart.length) {
        const removedItem = cart[index];
        cart.splice(index, 1);
        
        // Update selected item index
        if (selectedItemIndex >= cart.length) {
            selectedItemIndex = cart.length - 1;
        }
        
        updateCartDisplay();
        showToast(`${removedItem.name} removed from cart`, 'success');
    }
}

function updateItemQuantity(index, newQuantity) {
    if (index >= 0 && index < cart.length && newQuantity > 0) {
        cart[index].quantity = newQuantity;
        cart[index].totalPrice = cart[index].price * newQuantity;
        updateCartDisplay();
    } else if (newQuantity <= 0) {
        removeFromCart(index);
    }
}

function selectCartItem(index) {
    // Remove previous selection
    const cartItems = document.querySelectorAll('.cart-item');
    cartItems.forEach(item => item.classList.remove('selected'));
    
    // Add selection to new item
    if (index >= 0 && index < cart.length) {
        selectedItemIndex = index;
        if (cartItems[index]) {
            cartItems[index].classList.add('selected');
        }
        
        // Update quantity display
        document.getElementById('selected-quantity').textContent = cart[index].quantity;
    }
}

function increaseQuantity() {
    if (selectedItemIndex >= 0 && selectedItemIndex < cart.length) {
        const newQuantity = cart[selectedItemIndex].quantity + 1;
        updateItemQuantity(selectedItemIndex, newQuantity);
        document.getElementById('selected-quantity').textContent = newQuantity;
    }
}

function decreaseQuantity() {
    if (selectedItemIndex >= 0 && selectedItemIndex < cart.length) {
        const newQuantity = cart[selectedItemIndex].quantity - 1;
        updateItemQuantity(selectedItemIndex, newQuantity);
        document.getElementById('selected-quantity').textContent = newQuantity;
    }
}

function deleteSelectedItem() {
    if (selectedItemIndex >= 0 && selectedItemIndex < cart.length) {
        removeFromCart(selectedItemIndex);
    }
}

function updateCartDisplay() {
    const cartContainer = document.getElementById('cart-items');
    const cartItemCount = document.getElementById('cart-item-count');
    const totalAmount = document.getElementById('total-amount');
    const totalItems = document.getElementById('total-items');
    
    // Clear cart display
    cartContainer.innerHTML = '';
    
    // Add cart items
    cart.forEach((item, index) => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.onclick = () => selectCartItem(index);
        
        cartItem.innerHTML = `
            <div class="item-details">
                <div class="item-name">${item.name}</div>
                <div class="item-price">PKR ${item.price.toFixed(2)}</div>
            </div>
            <div class="item-quantity">
                <button class="quantity-btn" onclick="event.stopPropagation(); updateItemQuantity(${index}, ${item.quantity - 1})">-</button>
                <span class="quantity-display">${item.quantity}</span>
                <button class="quantity-btn" onclick="event.stopPropagation(); updateItemQuantity(${index}, ${item.quantity + 1})">+</button>
            </div>
            <button class="delete-btn" onclick="event.stopPropagation(); removeFromCart(${index})">×</button>
        `;
        
        cartContainer.appendChild(cartItem);
    });
    
    // Update counters
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + item.totalPrice, 0);
    
    cartItemCount.textContent = totalQuantity;
    totalAmount.textContent = `Payment PKR ${totalPrice.toFixed(2)}`;
    totalItems.textContent = `Item(s): ${cart.length}`;
    
    // Select first item if none selected and cart has items
    if (selectedItemIndex === -1 && cart.length > 0) {
        selectCartItem(0);
    }
}

function clearCart() {
    cart = [];
    selectedItemIndex = -1;
    updateCartDisplay();
    showToast('Cart cleared', 'success');
}

function getCartTotal() {
    return cart.reduce((sum, item) => sum + item.totalPrice, 0);
}

function getCartItemCount() {
    return cart.reduce((sum, item) => sum + item.quantity, 0);
}

function getCartItems() {
    return cart;
}

// Toast notification system
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    toastContainer.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Keyboard shortcuts for cart
document.addEventListener('keydown', function(e) {
    // Arrow keys for cart navigation
    if (e.key === 'ArrowUp' && selectedItemIndex > 0) {
        e.preventDefault();
        selectCartItem(selectedItemIndex - 1);
    } else if (e.key === 'ArrowDown' && selectedItemIndex < cart.length - 1) {
        e.preventDefault();
        selectCartItem(selectedItemIndex + 1);
    }
    
    // Plus/Minus for quantity
    if (e.key === '+' || e.key === '=') {
        e.preventDefault();
        increaseQuantity();
    } else if (e.key === '-') {
        e.preventDefault();
        decreaseQuantity();
    }
    
    // Delete key for removing items
    if (e.key === 'Delete' || e.key === 'Backspace') {
        e.preventDefault();
        deleteSelectedItem();
    }
    
    // Enter key for processing order
    if (e.key === 'Enter' && e.ctrlKey) {
        e.preventDefault();
        processOrder();
    }
});

// Export cart data for order processing
function getCartData() {
    return {
        items: cart,
        total: getCartTotal(),
        itemCount: getCartItemCount(),
        customerName: document.getElementById('customer-name').value,
        customerPostcode: document.getElementById('customer-postcode').value
    };
}

// Save cart to localStorage for persistence
function saveCartToStorage() {
    localStorage.setItem('pos_cart', JSON.stringify(cart));
}

// Load cart from localStorage
function loadCartFromStorage() {
    const savedCart = localStorage.getItem('pos_cart');
    if (savedCart) {
        try {
            cart = JSON.parse(savedCart);
            updateCartDisplay();
        } catch (e) {
            console.error('Error loading cart from storage:', e);
            cart = [];
        }
    }
}

// Auto-save cart every 5 seconds
setInterval(saveCartToStorage, 5000);

// Load cart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCartFromStorage();
});

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