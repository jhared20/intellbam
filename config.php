<?php
/**
 * Database Configuration File
 * Reusable config for database connection
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_pos');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Inventory & POS System');
define('BASE_URL', 'http://localhost/inventory/');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Database Connection Function
 * Returns PDO connection object
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is cashier
 */
function isCashier() {
    return isLoggedIn() && $_SESSION['role'] === 'cashier';
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Require cashier or admin - redirect if neither
 */
function requireCashier() {
    requireLogin();
    if (!isCashier() && !isAdmin()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Require admin or owner - for viewing personal sales history
 * $user_id_to_check: the user_id to verify ownership
 */
function requireAdminOrOwner($user_id_to_check) {
    requireLogin();
    if (!isAdmin() && $_SESSION['user_id'] != $user_id_to_check) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

/**
 * Log activity
 */
function logActivity($action) {
    $pdo = getDB();
    $user_id = $_SESSION['user_id'] ?? null;
    
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
    $stmt->execute([$user_id, $action]);
}

/**
 * Sanitize output
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date) {
    return date('M d, Y h:i A', strtotime($date));
}

