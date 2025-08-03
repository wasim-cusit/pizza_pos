<?php
/**
 * Database Configuration and Connection
 * Fast Food POS System
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'pizza_pos';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function closeConnection() {
        $this->conn = null;
    }
}

// Global database instance
$database = new Database();
$db = $database->getConnection();

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateOrderNumber() {
    global $db;
    
    $prefix = 'ORD';
    $date = date('Ymd');
    $query = "SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    
    $count = $result['count'] + 1;
    return $prefix . $date . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function formatCurrency($amount) {
    return number_format($amount, 2);
}

function getSetting($key) {
    global $db;
    
    $query = "SELECT setting_value FROM settings WHERE setting_key = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    
    return $result ? $result['setting_value'] : null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php?error=access_denied');
        exit();
    }
}
?>