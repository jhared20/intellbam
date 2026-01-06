<?php
/**
 * Sales Receipt
 * Display receipt for completed sale
 */

require_once '../config.php';
requireLogin();

$pdo = getDB();
$sale_id = $_GET['id'] ?? 0;

if (!$sale_id) {
    header('Location: index.php');
    exit;
}

// Get sale data
try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.username, p.payment_method, p.amount_paid, p.change_amount
        FROM sales s
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN payments p ON s.sale_id = p.sale_id
        WHERE s.sale_id = ?
    ");
    $stmt->execute([$sale_id]);
    $sale = $stmt->fetch();
    
    if (!$sale) {
        header('Location: index.php');
        exit;
    }
    
    // Get customer name from session
    $customer_name = $_SESSION['sale_customer_name'] ?? '';
    unset($_SESSION['sale_customer_name']);
    
    // Get sale items
    $stmt = $pdo->prepare("
        SELECT si.*, COALESCE(si.product_name, p.product_name) AS product_name
        FROM sale_items si
        LEFT JOIN products p ON si.product_id = p.product_id
        WHERE si.sale_id = ?
        ORDER BY si.sale_item_id
    ");
    $stmt->execute([$sale_id]);
    $items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Error loading receipt: " . $e->getMessage());
}

$page_title = 'Receipt #' . $sale_id;
require_once '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <!-- Receipt Header -->
                <div class="text-center mb-4">
                    <h3><?php echo APP_NAME; ?></h3>
                    <p class="text-muted mb-0">Sale Receipt</p>
                    <hr>
                </div>
                
                <!-- Sale Info -->
                <div class="mb-3">
                    <div class="row mb-2">
                        <div class="col-6"><strong>Receipt #:</strong></div>
                        <div class="col-6"><?php echo $sale_id; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Date:</strong></div>
                        <div class="col-6"><?php echo formatDate($sale['sale_date']); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Cashier:</strong></div>
                        <div class="col-6"><?php echo escape($sale['username']); ?></div>
                    </div>
                    <?php if ($customer_name): ?>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Customer:</strong></div>
                        <div class="col-6"><?php echo escape($customer_name); ?></div>
                    </div>
                    <?php else: ?>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Customer:</strong></div>
                        <div class="col-6">
                            <input type="text" class="form-control form-control-sm" id="customer_name" placeholder="Walk-in Customer" value="Walk-in Customer">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <hr>
                
                <!-- Items -->
                <div class="mb-3">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo escape($item['product_name']); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['price']); ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['subtotal']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total:</th>
                                <th class="text-end"><?php echo formatCurrency($sale['total_amount']); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <hr>
                
                <!-- Payment Info -->
                <div class="mb-3">
                    <div class="row mb-2">
                        <div class="col-6"><strong>Payment Method:</strong></div>
                        <div class="col-6 text-capitalize"><?php echo escape($sale['payment_method']); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Amount Paid:</strong></div>
                        <div class="col-6"><?php echo formatCurrency($sale['amount_paid']); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Change:</strong></div>
                        <div class="col-6"><?php echo formatCurrency($sale['change_amount']); ?></div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center text-muted mb-3">
                    <small>Thank you for your purchase!</small>
                </div>
                
                <div class="d-grid gap-2">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Print Receipt
                    </button>
                    <a href="pos.php" class="btn btn-success">
                        <i class="bi bi-cart-plus"></i> New Sale
                    </a>
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="bi bi-house"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .main-content h2, .btn, nav {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>

