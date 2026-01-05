<?php
/**
 * Main Dashboard
 * Shows overview statistics
 */

require_once 'config.php';
requireLogin();

$page_title = 'Dashboard';
require_once 'includes/header.php';

$pdo = getDB();

// Get statistics
try {
    // Initialize variables with defaults
    $total_products = 0;
    $total_categories = 0;
    $total_customers = 0;
    $low_stock = 0;
    
    if (isAdmin()) {
        // Admin gets full statistics
        // Total products
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
        $total_products = $stmt->fetch()['total'];
        
        // Total categories
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
        $total_categories = $stmt->fetch()['total'];
        
        // Total customers
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM customers");
        $total_customers = $stmt->fetch()['total'];
        
        // Low stock products (less than 10)
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity < 10");
        $low_stock = $stmt->fetch()['total'];
    }
    
    // Today's sales - Admins see all, Cashiers see only their own
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURDATE()");
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURDATE() AND user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    $today_sales = $stmt->fetch()['total'];
    
    // This month's sales - Admins see all, Cashiers see only their own
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())");
    } else {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE()) AND user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    }
    $month_sales = $stmt->fetch()['total'];
    
    // Recent sales - Admins see all, Cashiers see only their own
    if (isAdmin()) {
        $stmt = $pdo->prepare("
            SELECT s.*, u.username, c.full_name as customer_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.user_id
            LEFT JOIN customers c ON s.customer_id = c.customer_id
            ORDER BY s.sale_date DESC
            LIMIT 10
        ");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("
            SELECT s.*, u.username, c.full_name as customer_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.user_id
            LEFT JOIN customers c ON s.customer_id = c.customer_id
            WHERE s.user_id = ?
            ORDER BY s.sale_date DESC
            LIMIT 10
        ");
        $stmt->execute([$_SESSION['user_id']]);
    }
    $recent_sales = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}
?>

<div class="row mb-4">
    <?php if (isAdmin()): ?>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Products</h6>
                        <h2 class="mb-0"><?php echo number_format($total_products); ?></h2>
                    </div>
                    <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2"><?php echo isAdmin() ? "Today's Sales" : "My Sales Today"; ?></h6>
                        <h2 class="mb-0"><?php echo formatCurrency($today_sales); ?></h2>
                    </div>
                    <i class="bi bi-cash-coin" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2"><?php echo isAdmin() ? "This Month" : "My Monthly Sales"; ?></h6>
                        <h2 class="mb-0"><?php echo formatCurrency($month_sales); ?></h2>
                    </div>
                    <i class="bi bi-graph-up" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isAdmin()): ?>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Low Stock Items</h6>
                        <h2 class="mb-0"><?php echo number_format($low_stock); ?></h2>
                    </div>
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Sales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Cashier</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_sales)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No sales yet</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recent_sales as $sale): ?>
                            <tr>
                                <td>#<?php echo $sale['sale_id']; ?></td>
                                <td><?php echo formatDate($sale['sale_date']); ?></td>
                                <td><?php echo escape($sale['customer_name'] ?? 'Walk-in'); ?></td>
                                <td><?php echo escape($sale['username']); ?></td>
                                <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Quick Stats</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Categories</span>
                        <strong><?php echo number_format($total_categories); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Customers</span>
                        <strong><?php echo number_format($total_customers); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Today's Sales</span>
                        <strong><?php echo formatCurrency($today_sales); ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>This Month</span>
                        <strong><?php echo formatCurrency($month_sales); ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

