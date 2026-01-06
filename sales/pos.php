<?php
/**
 * Point of Sale (POS) Interface
 * Main POS screen with cart system
 */

require_once '../config.php';
requireLogin();



$pdo = getDB();

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_id = $_POST['product_id'] ?? 0;
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($product_id > 0 && $quantity > 0) {
        try {
            $stmt = $pdo->prepare("SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                // Check stock availability
                if ($product['stock_quantity'] < $quantity) {
                    $error = "Insufficient stock. Available: {$product['stock_quantity']}";
                } else {
                    // Check if product already in cart
                    $found = false;
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['product_id'] == $product_id) {
                            $new_qty = $item['quantity'] + $quantity;
                            if ($new_qty <= $product['stock_quantity']) {
                                $item['quantity'] = $new_qty;
                                $item['subtotal'] = $item['quantity'] * $item['price'];
                                $found = true;
                            } else {
                                $error = "Cannot add more. Available stock: {$product['stock_quantity']}";
                            }
                            break;
                        }
                    }
                    
                    if (!$found && empty($error)) {
                        $_SESSION['cart'][] = [
                            'product_id' => $product['product_id'],
                            'product_name' => $product['product_name'],
                            'price' => $product['price'],
                            'quantity' => $quantity,
                            'subtotal' => $product['price'] * $quantity
                        ];
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle remove from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $index = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    }
    header('Location: pos.php');
    exit;
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $index = intval($_POST['index'] ?? -1);
    $quantity = intval($_POST['quantity'] ?? 0);
    
    if ($index >= 0 && isset($_SESSION['cart'][$index]) && $quantity > 0) {
        $product_id = $_SESSION['cart'][$index]['product_id'];
        
        // Check stock
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product && $quantity <= $product['stock_quantity']) {
            $_SESSION['cart'][$index]['quantity'] = $quantity;
            $_SESSION['cart'][$index]['subtotal'] = $_SESSION['cart'][$index]['price'] * $quantity;
        } else {
            $error = "Insufficient stock";
        }
    }
    header('Location: pos.php');
    exit;
}


// Calculate cart total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['subtotal'];
}

// Get products for selection
$products = [];
$search = $_GET['search'] ?? '';
$where = '';
$params = [];

if (!empty($search)) {
    $where = "WHERE product_name LIKE ? OR barcode LIKE ?";
    $params = ["%{$search}%", "%{$search}%"];
}

try {
    $sql = "SELECT p.*, c.category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            {$where}
            ORDER BY p.product_name
            LIMIT 50";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error loading products: " . $e->getMessage();
}

// Get customers for selection
$customers = [];
try {
    $stmt = $pdo->query("SELECT customer_id, full_name FROM customers ORDER BY customer_id ASC LIMIT 100");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore error
}

// Get selected customer from session
$selected_customer_id = $_SESSION['selected_customer_id'] ?? null;
$selected_customer_name = '';

if ($selected_customer_id && !empty($customers)) {
    foreach ($customers as $c) {
        if ($c['customer_id'] == $selected_customer_id) {
            $selected_customer_name = $c['full_name'];
            break;
        }
    }
}

// Handle customer selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'select_customer') {
    $customer_id = $_POST['customer_id'] ?? null;
    if ($customer_id) {
        $_SESSION['selected_customer_id'] = $customer_id;
    } else {
        unset($_SESSION['selected_customer_id']);
    }
    header('Location: pos.php');
    exit;
}
$page_title = 'Point of Sale';
require_once '../includes/header.php';
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?php echo escape($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Product Selection Panel -->
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-search"></i> Search Products</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or barcode..." value="<?php echo escape($search); ?>" autofocus>
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <?php if ($search): ?>
                        <a href="pos.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> Clear
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Products</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if (empty($products)): ?>
                    <div class="col-12 text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-3">No products found</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card h-100 border">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo escape($product['product_name']); ?></h6>
                                <p class="card-text small text-muted mb-2">
                                    <?php echo escape($product['category_name']); ?>
                                </p>
                                <p class="card-text">
                                    <strong class="text-primary"><?php echo formatCurrency($product['price']); ?></strong>
                                    <br>
                                    <small class="text-muted">Stock: 
                                        <span class="badge bg-<?php echo $product['stock_quantity'] < 10 ? 'danger' : 'success'; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </small>
                                </p>
                                <form method="POST" action="" class="d-flex gap-2">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="form-control form-control-sm" style="width: 80px;">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1" <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                        <i class="bi bi-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Panel -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart"></i> Cart</h5>
                <?php if (!empty($_SESSION['cart'])): ?>
                <a href="clear_cart.php" class="btn btn-sm btn-outline-danger" onclick="return confirm('Clear cart?')">
                    <i class="bi bi-trash"></i> Clear
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <p class="mt-3">Cart is empty</p>
                </div>
                <?php else: ?>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                            <tr>
                                <td>
                                    <small><?php echo escape($item['product_name']); ?></small>
                                </td>
                                <td>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm" style="width: 60px;" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td><small><?php echo formatCurrency($item['subtotal']); ?></small></td>
                                <td>
                                    <a href="?remove=<?php echo $index; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove item?')">
                                        <i class="bi bi-x"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-3">
                    <strong>Total:</strong>
                    <strong class="text-primary" style="font-size: 1.5rem;"><?php echo formatCurrency($cart_total); ?></strong>
                </div>
                
                <a href="checkout.php" class="btn btn-success w-100 btn-lg">
                    <i class="bi bi-cash-coin"></i> Checkout
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

