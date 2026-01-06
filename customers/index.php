<?php
/**
 * History (Customers) View (Admin Only)
 * List all customer history entries
 */

require_once '../config.php';
requireAdmin();

$page_title = 'History';
require_once '../includes/header.php';

$pdo = getDB();
$customers = [];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Search functionality
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE full_name LIKE ? OR contact LIKE ?";
    $params = ["%{$search}%", "%{$search}%"];
}

try {
    $sql = "SELECT c.*, COUNT(s.sale_id) as total_sales, COALESCE(SUM(s.total_amount), 0) as total_spent
            FROM customers c
            LEFT JOIN sales s ON c.customer_id = s.customer_id
            {$where}
            GROUP BY c.customer_id
            ORDER BY c.customer_id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading customers: " . $e->getMessage();
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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> History</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by name or contact..." value="<?php echo escape($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
                <?php if ($search): ?>
                <a href="sales/index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x"></i> Clear
                </a>
                <?php endif; ?>
            </div>  
        </form>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Receipts</th>
                        <th>Total Sales</th>
                        <th>Total Spent</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No customers found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['customer_id']; ?></td>
                        <td><strong><?php echo escape($customer['full_name']); ?></strong></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>sales/index.php?customer_id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-receipt"></i> View Receipts
                            </a>
                        </td>
                        <td><span class="badge bg-info"><?php echo $customer['total_sales']; ?></span></td>
                        <td><?php echo formatCurrency($customer['total_spent']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                        
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

