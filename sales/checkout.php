<?php
/**
 * Checkout Page
 * Process payment and complete sale
 */

require_once '../config.php';
requireLogin();

$pdo = getDB();
$error = '';

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: pos.php');
    exit;
}

// Calculate total
$cart_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_total += $item['subtotal'];
}

// Get customers
$customers = [];
$selected_customer_name = $_SESSION['selected_customer_name'] ?? null;
try {
    $stmt = $pdo->query("SELECT customer_id, full_name FROM customers ORDER BY customer_id ASC");
    $customers = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    
    if ($amount_paid < $cart_total) {
        $error = 'Amount paid is less than total amount';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Verify stock availability before processing
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
                $stmt->execute([$item['product_id']]);
                $product = $stmt->fetch();
                
                if (!$product || $product['stock_quantity'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for: {$item['product_name']}");
                }
            }
            
            // Create or get customer if name is provided
            $customer_id = null;
            $_SESSION['sale_customer_name'] = $customer_name;
            
            if (!empty($customer_name)) {
                // Check if customer with this name already exists
                $stmt = $pdo->prepare("SELECT customer_id FROM customers WHERE full_name = ?");
                $stmt->execute([$customer_name]);
                $existing_customer = $stmt->fetch();
                
                if ($existing_customer) {
                    $customer_id = $existing_customer['customer_id'];
                } else {
                    // Create new customer
                    $stmt = $pdo->prepare("INSERT INTO customers (full_name) VALUES (?)");
                    $stmt->execute([$customer_name]);
                    $customer_id = $pdo->lastInsertId();
                }
            }
            
            // Create sale record (store customer name as denormalized field)
            $user_id = $_SESSION['user_id'];
            $customer_display_name = !empty($customer_name) ? $customer_name : null;
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, customer_name, user_id, total_amount) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer_id, $customer_display_name, $user_id, $cart_total]);
            $sale_id = $pdo->lastInsertId();
            
            // Create sale items and update stock
            foreach ($_SESSION['cart'] as $item) {
                // Insert sale item (store product name to preserve historical data)
                $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$sale_id, $item['product_id'], $item['product_name'] ?? null, $item['quantity'], $item['price'], $item['subtotal']]);
                
                // Update product stock
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Create payment record
            $change_amount = $amount_paid - $cart_total;
            $stmt = $pdo->prepare("INSERT INTO payments (sale_id, payment_method, amount_paid, change_amount) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sale_id, $payment_method, $amount_paid, $change_amount]);
            
            // Log activity
            $customer_display = !empty($customer_name) ? $customer_name : 'Walk-in';
            logActivity("Sale completed: Sale #{$sale_id}, Customer: {$customer_display}, Total: " . formatCurrency($cart_total));
            
            $pdo->commit();
            
            // Clear cart and customer name
            $_SESSION['cart'] = [];
            unset($_SESSION['selected_customer_name']);
            
            // Redirect to receipt
            header("Location: receipt.php?id={$sale_id}");
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Checkout';
require_once '../includes/header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-circle"></i> <?php echo escape($error); ?>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-cart-check"></i> Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                            <tr>
                                <td><?php echo escape($item['product_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo formatCurrency($item['price']); ?></td>
                                <td><?php echo formatCurrency($item['subtotal']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total:</th>
                                <th class="text-primary" style="font-size: 1.5rem;"><?php echo formatCurrency($cart_total); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="checkoutForm">
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name (Optional)</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter customer name..." value="<?php echo escape($selected_customer_name ?? ''); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="gcash">GCash</option>
                            <option value="paymaya">PayMaya</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Amount Paid <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amount_paid" name="amount_paid" step="0.01" min="<?php echo $cart_total; ?>" value="<?php echo $cart_total; ?>" required autofocus>
                        </div>
                        <small class="text-muted">Minimum: <?php echo formatCurrency($cart_total); ?></small>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Change:</strong> <span id="change_amount">₱0.00</span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Complete Payment
                        </button>
                        <a href="pos.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to POS
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const total = <?php echo $cart_total; ?>;
    const amountPaidInput = document.getElementById('amount_paid');
    const changeAmountSpan = document.getElementById('change_amount');
    
    function calculateChange() {
        const amountPaid = parseFloat(amountPaidInput.value) || 0;
        const change = amountPaid - total;
        changeAmountSpan.textContent = '₱' + (change >= 0 ? change.toFixed(2) : '0.00');
        changeAmountSpan.className = change >= 0 ? 'text-success' : 'text-danger';
    }
    
    amountPaidInput.addEventListener('input', calculateChange);
    calculateChange();
    
    // Quick amount buttons
    const quickAmounts = [total, total * 1.1, total * 1.2, Math.ceil(total / 100) * 100];
    const quickButtons = document.createElement('div');
    quickButtons.className = 'd-flex gap-2 mb-3';
    quickAmounts.forEach(amount => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-primary btn-sm';
        btn.textContent = '₱' + amount.toFixed(2);
        btn.onclick = () => {
            amountPaidInput.value = amount.toFixed(2);
            calculateChange();
        };
        quickButtons.appendChild(btn);
    });
    amountPaidInput.parentElement.parentElement.appendChild(quickButtons);
});
</script>

<?php require_once '../includes/footer.php'; ?>

