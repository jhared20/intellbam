<?php
/**
 * Products Management (Admin Only)
 * List all products with inventory
 */

require_once '../config.php';
requireAdmin();

$page_title = 'Products';
require_once '../includes/header.php';

$pdo = getDB();
$products = [];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Search functionality
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE p.product_name LIKE ? OR p.barcode LIKE ?";
    $params = ["%{$search}%", "%{$search}%"];
}

try {
    $sql = "SELECT p.* 
            FROM products p 
            {$where}
            ORDER BY p.product_id ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading products: " . $e->getMessage();
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
        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Products</h5>
        <a href="add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add Product
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by name or barcode..." value="<?php echo escape($search); ?>">
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
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Barcode</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">No products found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><strong><?php echo escape($product['product_name']); ?></strong></td>
                        <td><?php echo escape($product['category_name']); ?></td>
                        <td><?php echo escape($product['barcode'] ?? '-'); ?></td>
                        <td><?php echo formatCurrency($product['price']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $product['stock_quantity'] < 10 ? 'danger' : ($product['stock_quantity'] < 50 ? 'warning' : 'success'); ?>">
                                <?php echo number_format($product['stock_quantity']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete()">
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

