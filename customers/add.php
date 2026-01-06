<?php
/**
 * Add Customer (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    
    if (empty($customer_name)) {
        $error = 'Customer name is required';
    } else {
        $pdo = getDB();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (customer_name) VALUES (?)");
            $stmt->execute([$customer_name]);
            
            logActivity("Customer created: {$customer_name}");
            
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Error creating customer: ' . $e->getMessage();
        }
    }
}

$page_title = 'Add Customer';
require_once '../includes/header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo escape($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Add New Customer</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="customer_name" name="customer_name" required autofocus>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Customer
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

