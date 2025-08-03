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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
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

-- Special Offers table for promotional offers
CREATE TABLE special_offers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    original_price DECIMAL(10,2) NOT NULL,
    discounted_price DECIMAL(10,2) NOT NULL,
    discount_percentage DECIMAL(5,2),
    items_included TEXT,
    image VARCHAR(255) DEFAULT 'default-offer.jpg',
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE,
    end_date DATE,
    priority INT DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (name, username, password, role) VALUES 
('Admin User', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

-- Insert default categories based on the POS image
INSERT INTO categories (name, icon, image, display_order) VALUES 
('PIZZA', 'pizza-icon.png', 'pizza-category.jpg', 1),
('BURGERS', 'burger-icon.png', 'burger-category.jpg', 2),
('FRIED ITEMS', 'fried-icon.png', 'fried-category.jpg', 3),
('WINGS', 'wings-icon.png', 'wings-category.jpg', 4),
('SOUP', 'soup-icon.png', 'soup-category.jpg', 5),
('CHINESE', 'chinese-icon.png', 'chinese-category.jpg', 6),
('COLD DRINKS', 'cold-drink-icon.png', 'cold-drink-category.jpg', 7),
('HOT DRINKS', 'hot-drink-icon.png', 'hot-drink-category.jpg', 8),
('SHAWARMA', 'shawarma-icon.png', 'shawarma-category.jpg', 9),
('FRIES', 'fries-icon.png', 'fries-category.jpg', 10),
('SHAKES', 'shake-icon.png', 'shake-category.jpg', 11),
('DELIVERY', 'delivery-icon.png', 'delivery-category.jpg', 12),
('SERVICES', 'service-icon.png', 'service-category.jpg', 13),
('SANDWICH', 'sandwich-icon.png', 'sandwich-category.jpg', 14);

-- Insert sample items for each category
INSERT INTO items (name, category_id, price, description) VALUES 
-- Pizza items
('US SPECIAL PIZZA', 1, 550.00, 'Delicious US style pizza with premium toppings'),
('MARGHERITA PIZZA', 1, 450.00, 'Classic margherita with tomato and mozzarella'),
('PEPPERONI PIZZA', 1, 500.00, 'Spicy pepperoni pizza with melted cheese'),

-- Burger items
('ZINGER BURGER', 2, 500.00, 'Spicy chicken zinger burger'),
('CHICKEN BURGER', 2, 400.00, 'Grilled chicken burger with fresh vegetables'),
('BEEF BURGER', 2, 450.00, 'Juicy beef burger with cheese'),

-- Fried items
('CHICKEN WINGS', 3, 300.00, 'Crispy fried chicken wings'),
('FISH FINGERS', 3, 250.00, 'Breaded fish fingers with tartar sauce'),
('ONION RINGS', 3, 200.00, 'Crispy onion rings'),

-- Wings
('BBQ WINGS', 4, 350.00, 'BBQ flavored chicken wings'),
('HOT WINGS', 4, 350.00, 'Spicy hot chicken wings'),

-- Soup
('CHICKEN SOUP', 5, 180.00, 'Homemade chicken soup'),
('TOMATO SOUP', 5, 150.00, 'Fresh tomato soup'),

-- Chinese
('FRIED RICE', 6, 280.00, 'Chinese style fried rice'),
('CHOW MEIN', 6, 300.00, 'Stir-fried noodles with vegetables'),

-- Cold drinks
('SMALL', 7, 170.00, 'Small cold drink'),
('1 LTR', 7, 250.00, '1 liter cold drink'),
('COLA', 7, 180.00, 'Refreshing cola drink'),

-- Hot drinks
('COFFEE', 8, 120.00, 'Hot coffee'),
('TEA', 8, 80.00, 'Hot tea'),

-- Shawarma
('CHICKEN SHAWARMA', 9, 220.00, 'Grilled chicken shawarma'),
('BEEF SHAWARMA', 9, 250.00, 'Grilled beef shawarma'),

-- Fries
('FRENCH FRIES', 10, 150.00, 'Crispy french fries'),
('CHEESE FRIES', 10, 180.00, 'French fries with cheese'),

-- Shakes
('CHOCOLATE SHAKE', 11, 200.00, 'Chocolate milkshake'),
('VANILLA SHAKE', 11, 180.00, 'Vanilla milkshake'),

-- Services
('DELIVERY FEE', 12, 50.00, 'Delivery service charge'),
('PACKAGING', 13, 20.00, 'Packaging charge'),

-- Sandwich
('CHICKEN SANDWICH', 14, 180.00, 'Grilled chicken sandwich'),
('VEG SANDWICH', 14, 150.00, 'Vegetable sandwich');

-- Insert default settings
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('company_name', 'Fast Food POS', 'Company name for receipts and reports'),
('tax_rate', '5.00', 'Tax rate percentage'),
('currency', 'PKR', 'Currency symbol'),
('receipt_footer', 'Thank you for your order!', 'Footer text for receipts'),
('auto_order_number', 'true', 'Auto generate order numbers'),
('order_prefix', 'ORD', 'Prefix for order numbers');

-- Insert sample special offers
INSERT INTO special_offers (title, description, original_price, discounted_price, discount_percentage, items_included, is_active, start_date, end_date, priority, created_by) VALUES 
('🍕 Pizza Combo Special', 'Any Pizza + Drink + Fries', 750.00, 650.00, 13.33, 'Pizza, Cold Drink, French Fries', TRUE, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 2, 1),
('🍔 Burger Special', 'Any Burger + Fries + Drink', 680.00, 600.00, 11.76, 'Burger, French Fries, Cold Drink', TRUE, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, 1),
('🍗 Wings Combo', 'BBQ Wings + Fries + Drink', 500.00, 450.00, 10.00, 'BBQ Wings, French Fries, Cold Drink', TRUE, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1, 1),
('🥤 Family Pack', '2 Pizzas + 2 Drinks + Fries', 1200.00, 1000.00, 16.67, '2 Pizzas, 2 Cold Drinks, French Fries', TRUE, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 3, 1);

-- Create indexes for better performance
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_items_category_id ON items(category_id);
CREATE INDEX idx_items_is_available ON items(is_available); 