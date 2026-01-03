<?php
/**
 * Clear Cart
 */

require_once '../../config.php';
requireLogin();

$_SESSION['cart'] = [];
header('Location: pos.php');
exit;

