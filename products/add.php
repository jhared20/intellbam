<?php
/**
 * Add Product (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$page_title = 'Add Product';
require_once '../includes/header.php';

$pdo = getDB();
$error = '';
$categories = [];

// Get categories
try {
    $stmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading categories: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?? 0;
    $product_name = trim($_POST['product_name'] ?? '');
    $barcode = trim($_POST['barcode'] ?? '');
    $price = $_POST['price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    
    if (empty($product_name) || $category_id == 0 || $price <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            // Check if barcode exists (if provided)
            if (!empty($barcode)) {
                $stmt = $pdo->prepare("SELECT product_id FROM products WHERE barcode = ?");
                $stmt->execute([$barcode]);
                if ($stmt->fetch()) {
                    $error = 'Barcode already exists';
                }
            }
            
            if (empty($error)) {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, product_name, barcode, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $product_name, $barcode ?: null, $price, $stock_quantity]);
                
                logActivity("Product created: {$product_name}");
                
                header('Location: index.php?success=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error creating product: ' . $e->getMessage();
        }
    }
}
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo escape($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-box"></i> Add New Product</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>"><?php echo escape($category['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="product_name" name="product_name" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="barcode" class="form-label">Barcode</label>
                <input type="text" class="form-control" id="barcode" name="barcode" placeholder="Optional">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="0">
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Product
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

