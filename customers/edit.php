<?php
/**
 * Edit Customer (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$pdo = getDB();
$error = '';
$customer = null;

$customer_id = $_GET['id'] ?? 0;

if (!$customer_id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error loading customer: " . $e->getMessage();
}

// Handle POST request BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    
    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET full_name = ?, contact = ? WHERE customer_id = ?");
            $stmt->execute([$full_name, $contact ?: null, $customer_id]);
            
            logActivity("Customer updated: {$full_name} (ID: {$customer_id})");
            
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Error updating customer: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Customer';
require_once '../includes/header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo escape($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Customer</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo escape($customer['full_name']); ?>" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="contact" class="form-label">Contact</label>
                <input type="text" class="form-control" id="contact" name="contact" value="<?php echo escape($customer['contact'] ?? ''); ?>" placeholder="Phone number or email">
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Customer
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

