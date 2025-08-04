-- Restaurant POS System Database Schema
-- Created for Fast Food POS System

-- Drop database if exists and create new one
DROP DATABASE IF EXISTS pizza_pos;
CREATE DATABASE pizza_pos;
USE pizza_pos;

-- Users table for authentication and role management
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table for organizing menu items
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(100) DEFAULT 'default-icon.png',
    image VARCHAR(255) DEFAULT 'default-category.jpg',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items table for menu products
CREATE TABLE items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT 'default-item.jpg',
    description TEXT,
    is_available BOOLEAN DEFAULT TRUE,
    stock_quantity INT DEFAULT 0,
    has_size_variants BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Item size variants table for pizza sizes
CREATE TABLE item_size_variants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT NOT NULL,
    size_name VARCHAR(50) NOT NULL,
    size_price DECIMAL(10,2) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_item_size (item_id, size_name)
);

-- Customers table for customer information
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    postcode VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table for main order information
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    customer_id INT,
    order_type ENUM('dine_in', 'takeaway', 'delivery') DEFAULT 'dine_in',
    table_number INT DEFAULT 0,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('cash', 'card', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    order_status ENUM('pending', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Order items table for individual items in orders
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    size_name VARCHAR(50) DEFAULT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id)
);

-- Settings table for system configuration
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Special offers table for promotions and discounts
CREATE TABLE special_offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    minimum_order_amount DECIMAL(10,2) DEFAULT 0.00,
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (name, username, password, role) VALUES 
('Admin User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default categories
INSERT INTO categories (name, display_order) VALUES 
('PIZZA', 1),
('BURGERS', 2),
('FRIED ITEMS', 3),
('WINGS', 4),
('SOUP', 5),
('CHINESE', 6),
('COLD DRINKS', 7),
('HOT DRINKS', 8),
('SHAWARMA', 9),
('FRIES', 10),
('SHAKES', 11),
('SANDWICH', 12);

-- Insert sample pizza items with size variants
INSERT INTO items (name, category_id, price, description, has_size_variants) VALUES 
('Margherita Pizza', 1, 0.00, 'Classic tomato sauce with mozzarella cheese', TRUE),
('Pepperoni Pizza', 1, 0.00, 'Spicy pepperoni with melted cheese', TRUE),
('BBQ Chicken Pizza', 1, 0.00, 'BBQ sauce with grilled chicken and onions', TRUE),
('Supreme Pizza', 1, 0.00, 'Loaded with pepperoni, sausage, mushrooms, and vegetables', TRUE);

-- Insert size variants for pizza items
INSERT INTO item_size_variants (item_id, size_name, size_price, display_order) VALUES 
-- Margherita Pizza sizes
(1, 'Small', 800.00, 1),
(1, 'Medium', 1200.00, 2),
(1, 'Large', 1600.00, 3),
(1, 'Extra Large', 2000.00, 4),

-- Pepperoni Pizza sizes
(2, 'Small', 900.00, 1),
(2, 'Medium', 1300.00, 2),
(2, 'Large', 1700.00, 3),
(2, 'Extra Large', 2100.00, 4),

-- BBQ Chicken Pizza sizes
(3, 'Small', 1000.00, 1),
(3, 'Medium', 1400.00, 2),
(3, 'Large', 1800.00, 3),
(3, 'Extra Large', 2200.00, 4),

-- Supreme Pizza sizes
(4, 'Small', 1100.00, 1),
(4, 'Medium', 1500.00, 2),
(4, 'Large', 1900.00, 3),
(4, 'Extra Large', 2300.00, 4);

-- Insert other menu items (non-pizza)
INSERT INTO items (name, category_id, price, description) VALUES 
('Chicken Burger', 2, 450.00, 'Grilled chicken with fresh vegetables'),
('Beef Burger', 2, 500.00, 'Juicy beef patty with cheese and toppings'),
('French Fries', 10, 200.00, 'Crispy golden fries'),
('Chicken Wings', 4, 600.00, 'Spicy buffalo wings'),
('Coca Cola', 7, 150.00, 'Refreshing soft drink'),
('Coffee', 8, 200.00, 'Hot brewed coffee');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('company_name', 'Fast Food POS', 'Company name for receipts and invoices'),
('company_address', '123 Main Street, City, Country', 'Company address'),
('company_phone', '+92 300 1234567', 'Company phone number'),
('company_email', 'info@fastfoodpos.com', 'Company email address'),
('company_website', 'www.fastfoodpos.com', 'Company website'),
('company_gst', 'GST123456789', 'Company GST number'),
('company_license', 'LIC123456789', 'Business license number'),
('tax_rate', '15.00', 'Tax rate percentage'),
('currency', 'PKR', 'Currency symbol'),
('receipt_footer', 'Thank you for your order!', 'Footer message for receipts'),
('auto_order_number', 'true', 'Auto-generate order numbers');

-- Create indexes for better performance
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_items_category_id ON items(category_id);
CREATE INDEX idx_items_is_available ON items(is_available);
CREATE INDEX idx_item_size_variants_item_id ON item_size_variants(item_id); 