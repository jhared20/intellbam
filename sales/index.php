<?php
/**
 * Sales History
 * List all completed sales
 */

require_once '../../config.php';
requireLogin();

$page_title = 'Sales History';
require_once '../../includes/header.php';

$pdo = getDB();
$sales = [];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Date filter
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.username, c.full_name as customer_name, p.payment_method
        FROM sales s
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN customers c ON s.customer_id = c.customer_id
        LEFT JOIN payments p ON s.sale_id = p.sale_id
        WHERE DATE(s.sale_date) BETWEEN ? AND ?
        ORDER BY s.sale_date DESC
    ");
    $stmt->execute([$date_from, $date_to]);
    $sales = $stmt->fetchAll();
    
    // Calculate totals
    $total_sales = 0;
    foreach ($sales as $sale) {
        $total_sales += $sale['total_amount'];
    }
} catch (PDOException $e) {
    $error = "Error loading sales: " . $e->getMessage();
}
?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> Operation completed successfully!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?php echo escape($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Sales</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo escape($date_from); ?>">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo escape($date_to); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Sales History</h5>
        <div>
            <strong>Total: <?php echo formatCurrency($total_sales); ?></strong>
        </div>
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
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No sales found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td>#<?php echo $sale['sale_id']; ?></td>
                        <td><?php echo formatDate($sale['sale_date']); ?></td>
                        <td><?php echo escape($sale['customer_name'] ?? 'Walk-in'); ?></td>
                        <td><?php echo escape($sale['username']); ?></td>
                        <td><span class="badge bg-info text-capitalize"><?php echo escape($sale['payment_method']); ?></span></td>
                        <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                        <td>
                            <a href="receipt.php?id=<?php echo $sale['sale_id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                <i class="bi bi-receipt"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

