<?php
/**
 * Categories Management (Admin Only)
 * List all categories
 */

require_once '../config.php';
requireAdmin();

$page_title = 'Categories';
require_once '../includes/header.php';

$pdo = getDB();
$categories = [];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

try {
    $stmt = $pdo->query("SELECT c.*, COUNT(p.product_id) as product_count FROM categories c LEFT JOIN products p ON c.category_id = p.category_id GROUP BY c.category_id ORDER BY c.category_name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading categories: " . $e->getMessage();
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
        <h5 class="mb-0"><i class="bi bi-tags"></i> Categories</h5>
        <a href="add.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Add Category
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Products</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">No categories found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['category_id']; ?></td>
                        <td><strong><?php echo escape($category['category_name']); ?></strong></td>
                        <td><span class="badge bg-info"><?php echo $category['product_count']; ?></span></td>
                        <td><?php echo formatDate($category['created_at']); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <a href="delete.php?id=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Are you sure? This will fail if products are using this category.')">
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

