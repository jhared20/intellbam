<?php
/**
 * Delete Product (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$pdo = getDB();
$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get product name for logging
    $stmt = $pdo->prepare("SELECT product_name FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        logActivity("Product deleted: {$product['product_name']} (ID: {$product_id})");
        
        header('Location: index.php?success=1');
    } else {
        header('Location: index.php?error=product_not_found');
    }
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}

exit;

