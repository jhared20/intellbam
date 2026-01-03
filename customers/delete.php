<?php
/**
 * Delete Customer
 */

require_once __DIR__ . '/../config.php';
requireLogin();

$pdo = getDB();
$customer_id = $_GET['id'] ?? 0;

if (!$customer_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get customer name for logging
    $stmt = $pdo->prepare("SELECT full_name FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    
    if ($customer) {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
        $stmt->execute([$customer_id]);
        
        logActivity("Customer deleted: {$customer['full_name']} (ID: {$customer_id})");
        
        header('Location: index.php?success=1');
    } else {
        header('Location: index.php?error=customer_not_found');
    }
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}

exit;

