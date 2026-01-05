<?php
/**
 * Delete Category (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$pdo = getDB();
$category_id = $_GET['id'] ?? 0;

if (!$category_id) {
    header('Location: index.php');
    exit;
}

try {
    // Check if category has products
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        header('Location: index.php?error=' . urlencode('Cannot delete category with existing products'));
        exit;
    }
    
    // Get category name for logging
    $stmt = $pdo->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if ($category) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        
        logActivity("Category deleted: {$category['category_name']} (ID: {$category_id})");
        
        header('Location: index.php?success=1');
    } else {
        header('Location: index.php?error=category_not_found');
    }
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}

exit;

