<?php
/**
 * Customers Management
 * List all customers
 */

require_once __DIR__ . '/../config.php';
requireLogin();

$page_title = 'Customers';
require_once __DIR__ . '/../includes/header.php';

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
            ORDER BY c.full_name";
    
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Customers</h5>
        <a href="add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add Customer
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by name or contact..." value="<?php echo escape($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="bi bi-search"></i> Search
                </button>
                <?php if ($search): ?>
                <a href="index.php" class="btn btn-outline-secondary">
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
                        <th>Contact</th>
                        <th>Total Sales</th>
                        <th>Total Spent</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No customers found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['customer_id']; ?></td>
                        <td><strong><?php echo escape($customer['full_name']); ?></strong></td>
                        <td><?php echo escape($customer['contact'] ?? '-'); ?></td>
                        <td><span class="badge bg-info"><?php echo $customer['total_sales']; ?></span></td>
                        <td><?php echo formatCurrency($customer['total_spent']); ?></td>
                        <td><?php echo formatDate($customer['created_at']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
                                <i class="bi bi-trash"></i> Delete
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

<?php require_once '../includes/footer.php'; ?>

