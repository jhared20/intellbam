<?php
/**
 * Delete User (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$pdo = getDB();
$user_id = $_GET['id'] ?? 0;

if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Prevent deleting own account
if ($user_id == $_SESSION['user_id']) {
    header('Location: index.php?error=cannot_delete_self');
    exit;
}

try {
    // Get username for logging
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        logActivity("User deleted: {$user['username']} (ID: {$user_id})");
        
        header('Location: index.php?success=1');
    } else {
        header('Location: index.php?error=user_not_found');
    }
} catch (PDOException $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
}

exit;

