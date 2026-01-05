<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .brand h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid #f0f0f0;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .btn {
            border-radius: 6px;
            padding: 8px 16px;
        }
        
        .table {
            background-color: white;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="sidebar">
        <div class="brand">
            <h4><i class="bi bi-shop"></i> <?php echo APP_NAME; ?></h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'sales') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>sales/pos.php">
                <i class="bi bi-cart-plus"></i> POS / Sales
            </a>
            <?php if (isAdmin()): ?>
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'products') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>products/index.php">
                <i class="bi bi-box-seam"></i> Products
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'categories') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>categories/index.php">
                <i class="bi bi-tags"></i> Categories
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'customers') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customers/index.php">
                <i class="bi bi-people"></i> Customers
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'reports') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>reports/index.php">
                <i class="bi bi-graph-up"></i> Reports
            </a>
            <a class="nav-link <?php echo (strpos($_SERVER['PHP_SELF'], 'users') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>users/index.php">
                <i class="bi bi-person-gear"></i> Users
            </a>
            <?php endif; ?>
            <div class="mt-auto p-3">
                <div class="text-center mb-3">
                    <small class="text-white-50">Logged in as: <strong><?php echo escape($_SESSION['username']); ?></strong></small><br>
                    <small class="text-white-50">Role: <span class="badge bg-light text-dark"><?php echo escape($_SESSION['role']); ?></span></small>
                </div>
                <a class="nav-link" href="<?php echo BASE_URL; ?>auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </nav>
    </div>
    <?php endif; ?>
    
    <div class="main-content">
        <?php if (isLoggedIn()): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo isset($page_title) ? escape($page_title) : 'Dashboard'; ?></h2>
            <button class="btn btn-primary d-md-none" type="button" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
        </div>
        <?php endif; ?>

