<?php
/**
 * Edit Product
 */

require_once '../config.php';
requireLogin();

$pdo = getDB();
$error = '';
$product = null;
$categories = [];

$product_id = $_GET['id'] ?? 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Get categories
try {
    $stmt = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading categories: " . $e->getMessage();
}

// Get product data
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error loading product: " . $e->getMessage();
    header('Location: index.php');
    exit;
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
            // Check if barcode exists (if provided, excluding current product)
            if (!empty($barcode)) {
                $stmt = $pdo->prepare("SELECT product_id FROM products WHERE barcode = ? AND product_id != ?");
                $stmt->execute([$barcode, $product_id]);
                if ($stmt->fetch()) {
                    $error = 'Barcode already exists';
                }
            }
            
            if (empty($error)) {
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, product_name = ?, barcode = ?, price = ?, stock_quantity = ? WHERE product_id = ?");
                $stmt->execute([$category_id, $product_name, $barcode ?: null, $price, $stock_quantity, $product_id]);
                
                logActivity("Product updated: {$product_name} (ID: {$product_id})");
                
                header('Location: index.php?success=1');
                exit;
                
            }
        } catch (PDOException $e) {
            $error = 'Error updating product: ' . $e->getMessage();
        }
    
       
    }
    
}

$page_title = 'Edit Product';
 require_once '../includes/header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo escape($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Product</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>" <?php echo $product['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                        <?php echo escape($category['category_name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo escape($product['product_name']); ?>" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="barcode" class="form-label">Barcode</label>
                <input type="text" class="form-control" id="barcode" name="barcode" value="<?php echo escape($product['barcode'] ?? ''); ?>" placeholder="Optional">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $product['stock_quantity']; ?>">
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Product
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

