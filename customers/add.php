<?php
/**
 * Add Customer (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$page_title = 'Add Customer';
require_once '../includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    
    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        $pdo = getDB();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (full_name, contact) VALUES (?, ?)");
            $stmt->execute([$full_name, $contact ?: null]);
            
            logActivity("Customer created: {$full_name}");
            
            header('Location: index.php?success=1');
            exit;
        } catch (PDOException $e) {
            $error = 'Error creating customer: ' . $e->getMessage();
        }
    }
}
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
                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="contact" class="form-label">Contact</label>
                <input type="text" class="form-control" id="contact" name="contact" placeholder="Phone number or email">
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

