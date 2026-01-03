<?php
/**
 * Logout Page
 * Handles user logout
 */

require_once '../config.php';

if (isLoggedIn()) {
    $username = $_SESSION['username'];
    
    // Log activity
    logActivity("User logged out: {$username}");
    
    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login
header('Location: ' . BASE_URL . 'auth/login.php');
exit;

