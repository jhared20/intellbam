<?php
/**
 * Sales Reports
 * Daily and Monthly sales reports
 */

require_once __DIR__ . '/../config.php';
requireLogin();

$page_title = 'Sales Reports';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDB();
$report_type = $_GET['type'] ?? 'daily';
$date = $_GET['date'] ?? date('Y-m-d');
$month = $_GET['month'] ?? date('Y-m');
$year = $_GET['year'] ?? date('Y');

$report_data = [];
$total_sales = 0;
$total_items = 0;
$total_customers = 0;

try {
    if ($report_type === 'daily') {
        // Daily Report
        $stmt = $pdo->prepare("
            SELECT 
                s.sale_id,
                s.sale_date,
                s.total_amount,
                u.username,
                c.full_name as customer_name,
                p.payment_method,
                COUNT(si.sale_item_id) as item_count
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.user_id
            LEFT JOIN customers c ON s.customer_id = c.customer_id
            LEFT JOIN payments p ON s.sale_id = p.sale_id
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            WHERE DATE(s.sale_date) = ?
            GROUP BY s.sale_id
            ORDER BY s.sale_date DESC
        ");
        $stmt->execute([$date]);
        $report_data = $stmt->fetchAll();
        
        // Get summary
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT s.sale_id) as total_sales,
                COUNT(DISTINCT s.customer_id) as total_customers,
                COALESCE(SUM(s.total_amount), 0) as total_amount,
                COALESCE(SUM(si.quantity), 0) as total_items
            FROM sales s
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            WHERE DATE(s.sale_date) = ?
        ");
        $stmt->execute([$date]);
        $summary = $stmt->fetch();
        $total_sales = $summary['total_amount'];
        $total_items = $summary['total_items'];
        $total_customers = $summary['total_customers'];
        
    } elseif ($report_type === 'monthly') {
        // Monthly Report
        list($year, $month_num) = explode('-', $month);
        
        $stmt = $pdo->prepare("
            SELECT 
                DATE(s.sale_date) as sale_date,
                COUNT(DISTINCT s.sale_id) as sale_count,
                COUNT(DISTINCT s.customer_id) as customer_count,
                COALESCE(SUM(s.total_amount), 0) as daily_total,
                COALESCE(SUM(si.quantity), 0) as item_count
            FROM sales s
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            WHERE YEAR(s.sale_date) = ? AND MONTH(s.sale_date) = ?
            GROUP BY DATE(s.sale_date)
            ORDER BY sale_date DESC
        ");
        $stmt->execute([$year, $month_num]);
        $report_data = $stmt->fetchAll();
        
        // Get summary
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT s.sale_id) as total_sales,
                COUNT(DISTINCT s.customer_id) as total_customers,
                COALESCE(SUM(s.total_amount), 0) as total_amount,
                COALESCE(SUM(si.quantity), 0) as total_items
            FROM sales s
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            WHERE YEAR(s.sale_date) = ? AND MONTH(s.sale_date) = ?
        ");
        $stmt->execute([$year, $month_num]);
        $summary = $stmt->fetch();
        $total_sales = $summary['total_amount'];
        $total_items = $summary['total_items'];
        $total_customers = $summary['total_customers'];
        
    } elseif ($report_type === 'yearly') {
        // Yearly Report
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(s.sale_date) as month_num,
                DATE_FORMAT(s.sale_date, '%M %Y') as month_name,
                COUNT(DISTINCT s.sale_id) as sale_count,
                COUNT(DISTINCT s.customer_id) as customer_count,
                COALESCE(SUM(s.total_amount), 0) as monthly_total,
                COALESCE(SUM(si.quantity), 0) as item_count
            FROM sales s
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            WHERE YEAR(s.sale_date) = ?
            GROUP BY MONTH(s.sale_date)
            ORDER BY month_num DESC
        ");
        $stmt->execute([$year]);
        $report_data = $stmt->fetchAll();
        
        // Get summary
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT s.sale_id) as total_sales,
                COUNT(DISTINCT s.customer_id) as total_customers,
                COALESCE(SUM(s.total_amount), 0) as total_amount,
                COALESCE(SUM(si.quantity), 0) as total_items
            FROM sales s
            LEFT JOIN sale_items si ON s.sale_id = si.sale_id
            WHERE YEAR(s.sale_date) = ?
        ");
        $stmt->execute([$year]);
        $summary = $stmt->fetch();
        $total_sales = $summary['total_amount'];
        $total_items = $summary['total_items'];
        $total_customers = $summary['total_customers'];
    }
} catch (PDOException $e) {
    $error = "Error loading report: " . $e->getMessage();
}
?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Report Options</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="type" class="form-label">Report Type</label>
                <select class="form-select" id="type" name="type" onchange="this.form.submit()">
                    <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="monthly" <?php echo $report_type === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    <option value="yearly" <?php echo $report_type === 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                </select>
            </div>
            
            <?php if ($report_type === 'daily'): ?>
            <div class="col-md-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo escape($date); ?>">
            </div>
            <?php elseif ($report_type === 'monthly'): ?>
            <div class="col-md-3">
                <label for="month" class="form-label">Month</label>
                <input type="month" class="form-control" id="month" name="month" value="<?php echo escape($month); ?>">
            </div>
            <?php else: ?>
            <div class="col-md-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" name="year" value="<?php echo escape($year); ?>" min="2020" max="2099">
            </div>
            <?php endif; ?>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Sales</h6>
                <h3 class="mb-0"><?php echo formatCurrency($total_sales); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Transactions</h6>
                <h3 class="mb-0"><?php echo number_format(count($report_data)); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Items Sold</h6>
                <h3 class="mb-0"><?php echo number_format($total_items); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6 class="card-subtitle mb-2">Total Customers</h6>
                <h3 class="mb-0"><?php echo number_format($total_customers); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-graph-up"></i> 
            <?php 
            if ($report_type === 'daily') echo 'Daily Report - ' . date('F d, Y', strtotime($date));
            elseif ($report_type === 'monthly') echo 'Monthly Report - ' . date('F Y', strtotime($month . '-01'));
            else echo 'Yearly Report - ' . $year;
            ?>
        </h5>
        <button onclick="window.print()" class="btn btn-sm btn-primary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <?php if ($report_type === 'daily'): ?>
                    <tr>
                        <th>Sale ID</th>
                        <th>Time</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Payment</th>
                        <th>Items</th>
                        <th>Amount</th>
                    </tr>
                    <?php elseif ($report_type === 'monthly'): ?>
                    <tr>
                        <th>Date</th>
                        <th>Sales</th>
                        <th>Customers</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <th>Month</th>
                        <th>Sales</th>
                        <th>Customers</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                    </tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (empty($report_data)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No data found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($report_data as $row): ?>
                    <tr>
                        <?php if ($report_type === 'daily'): ?>
                        <td>#<?php echo $row['sale_id']; ?></td>
                        <td><?php echo date('h:i A', strtotime($row['sale_date'])); ?></td>
                        <td><?php echo escape($row['customer_name'] ?? 'Walk-in'); ?></td>
                        <td><?php echo escape($row['username']); ?></td>
                        <td><span class="badge bg-info text-capitalize"><?php echo escape($row['payment_method']); ?></span></td>
                        <td><?php echo $row['item_count']; ?></td>
                        <td><strong><?php echo formatCurrency($row['total_amount']); ?></strong></td>
                        <?php elseif ($report_type === 'monthly'): ?>
                        <td><?php echo date('M d, Y', strtotime($row['sale_date'])); ?></td>
                        <td><?php echo $row['sale_count']; ?></td>
                        <td><?php echo $row['customer_count']; ?></td>
                        <td><?php echo number_format($row['item_count']); ?></td>
                        <td><strong><?php echo formatCurrency($row['daily_total']); ?></strong></td>
                        <?php else: ?>
                        <td><?php echo $row['month_name']; ?></td>
                        <td><?php echo $row['sale_count']; ?></td>
                        <td><?php echo $row['customer_count']; ?></td>
                        <td><?php echo number_format($row['item_count']); ?></td>
                        <td><strong><?php echo formatCurrency($row['monthly_total']); ?></strong></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="<?php echo $report_type === 'daily' ? '6' : '4'; ?>" class="text-end">Grand Total:</th>
                        <th><?php echo formatCurrency($total_sales); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .main-content h2, .btn, nav, .card-header .btn {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>

<?php require_once __DIR__ . '/../config.php';?>

