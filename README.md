# 🍕 Fast Food POS System

A complete, professional Point of Sale (POS) system designed for restaurants and fast-food establishments. Built with modern web technologies and inspired by real-world POS interfaces.

## ✨ Features

### 🛒 Core POS Features
- **Real-time Cart Management** - Add, remove, and modify items with live updates
- **Category-based Menu** - Organized food categories with intuitive navigation
- **Order Processing** - Complete order workflow with payment options
- **Customer Management** - Store customer information and order history
- **Receipt Generation** - Print-ready receipts with order details
- **Order Status Tracking** - Track orders from pending to completed

### 🎨 User Interface
- **Modern Design** - Clean, professional interface optimized for touch screens
- **Responsive Layout** - Works on desktop, tablet, and mobile devices
- **Keyboard Shortcuts** - Function keys for quick category access
- **Toast Notifications** - Real-time feedback for user actions
- **Modal Dialogs** - Clean popup interfaces for special functions

### 🔧 Administrative Features
- **Admin Dashboard** - Overview of sales, orders, and system statistics
- **User Management** - Role-based access control (Admin/Cashier)
- **Menu Management** - Add, edit, and organize menu items and categories
- **Sales Reports** - Daily, weekly, and monthly sales analytics
- **Order History** - Complete order tracking and management

### 💾 Technical Features
- **Database Integration** - MySQL database with normalized structure
- **Session Management** - Secure user authentication and session handling
- **AJAX Support** - Real-time updates without page refreshes
- **Local Storage** - Cart persistence across browser sessions
- **Print Support** - Receipt printing functionality

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Installation

1. **Clone or Download**
   ```bash
   git clone <repository-url>
   cd pizza_POS
   ```

2. **Database Setup**
   - Create a MySQL database named `pos_system`
   - Import the database schema:
   ```bash
   mysql -u root -p pos_system < database.sql
   ```

3. **Configuration**
   - Edit `config/database.php` with your database credentials:
   ```php
   private $host = 'localhost';
   private $db_name = 'pos_system';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

4. **Web Server Setup**
   - Point your web server to the project directory
   - Ensure PHP has write permissions for session handling

5. **Access the System**
   - Navigate to `http://localhost/pizza_POS/`
   - Login with default credentials:
     - **Username:** `admin`
     - **Password:** `password`

## 📁 Project Structure

```
pizza_POS/
├── config/
│   └── database.php          # Database configuration
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet
│   └── js/
│       ├── cart.js           # Cart management
│       └── app.js            # Main application logic
├── api/
│   ├── get_items.php         # Fetch items by category
│   ├── process_order.php     # Process orders
│   └── search_items.php      # Search functionality
├── admin/
│   └── index.php             # Admin dashboard
├── database.sql              # Database schema
├── index.php                 # Main POS interface
├── login.php                 # Login page
├── logout.php                # Logout functionality
└── README.md                 # This file
```

## 🎯 Usage Guide

### For Cashiers
1. **Login** with your credentials
2. **Select Categories** using the category grid or function keys (F1-F12)
3. **Add Items** by clicking on menu items
4. **Manage Cart** using the left sidebar controls
5. **Enter Customer Info** (optional)
6. **Process Payment** using the Order button
7. **Print Receipt** when prompted

### For Administrators
1. **Access Admin Panel** via the Back Office button
2. **View Statistics** on the dashboard
3. **Manage Menu Items** and categories
4. **Generate Reports** for sales analysis
5. **Manage Users** and system settings

### Keyboard Shortcuts
- **F1-F12**: Quick category selection
- **Arrow Keys**: Navigate cart items
- **+/-**: Increase/decrease quantity
- **Delete**: Remove selected item
- **Ctrl+Enter**: Process order
- **Escape**: Close modals

## 🗄️ Database Schema

### Core Tables
- **users** - User accounts and roles
- **categories** - Menu categories
- **items** - Menu items with prices
- **customers** - Customer information
- **orders** - Order headers
- **order_items** - Individual order items
- **settings** - System configuration

### Sample Data
The system comes pre-loaded with:
- 14 food categories (Pizza, Burgers, Drinks, etc.)
- 30+ menu items with realistic prices
- Admin user account
- Default system settings

## 🔧 Customization

### Adding New Categories
1. Access the admin panel
2. Navigate to "Manage Categories"
3. Add new category with appropriate icon

### Modifying Menu Items
1. Go to "Manage Items" in admin panel
2. Edit existing items or add new ones
3. Set prices, descriptions, and availability

### Styling Customization
- Edit `assets/css/style.css` for visual changes
- Modify color schemes in CSS variables
- Adjust layout for different screen sizes

## 🛡️ Security Features

- **Password Hashing** - Secure password storage using bcrypt
- **SQL Injection Prevention** - Prepared statements throughout
- **Session Management** - Secure session handling
- **Input Sanitization** - All user inputs are sanitized
- **Role-based Access** - Admin/Cashier permissions

## 📊 Reporting Features

- **Daily Sales Reports** - Revenue and order counts
- **Order Analytics** - Popular items and trends
- **Customer Reports** - Customer order history
- **Inventory Tracking** - Stock management (basic)

## 🔄 Future Enhancements

- **Inventory Management** - Advanced stock tracking
- **Multi-location Support** - Multiple restaurant locations
- **Online Ordering** - Customer-facing ordering system
- **Mobile App** - Native mobile applications
- **Payment Gateway Integration** - Credit card processing
- **Kitchen Display System** - Real-time order display
- **Loyalty Program** - Customer rewards system

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database name exists

**Login Issues**
- Default credentials: admin/password
- Clear browser cache and cookies
- Check PHP session configuration

**Cart Not Working**
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify file permissions

**Print Issues**
- Allow popups for receipt printing
- Check printer settings
- Use modern browsers for best compatibility

## 📞 Support

For technical support or feature requests:
- Check the troubleshooting section above
- Review browser console for JavaScript errors
- Verify PHP error logs for server issues

## 📄 License

This project is open source and available under the MIT License.

## 🙏 Acknowledgments

- Inspired by real-world POS systems
- Built with modern web standards
- Designed for restaurant efficiency
- Optimized for touch-screen interfaces

---

**🍕 Fast Food POS System** - Making restaurant management easier, one order at a time! 