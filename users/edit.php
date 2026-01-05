<?php
/**
 * Edit User (Admin Only)
 */

require_once '../config.php';
requireAdmin();

$pdo = getDB();
$error = '';
$user = null;

// Get user ID
$user_id = $_GET['id'] ?? 0;

if (!$user_id) {
    header('Location: index.php');
    exit;
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT user_id, username, role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error loading user: " . $e->getMessage();
}

// Handle POST request BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'cashier';
    
    if (empty($username)) {
        $error = 'Username is required';
    } else {
        try {
            // Check if username exists (excluding current user)
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
            $stmt->execute([$username, $user_id]);
            
            if ($stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                if (!empty($password)) {
                    // Update with password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE user_id = ?");
                    $stmt->execute([$username, $hashed_password, $role, $user_id]);
                } else {
                    // Update without password
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE user_id = ?");
                    $stmt->execute([$username, $role, $user_id]);
                }
                
                logActivity("User updated: {$username} (ID: {$user_id})");
                
                header('Location: index.php?success=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error updating user: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit User';
require_once '../includes/header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo escape($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit User</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo escape($user['username']); ?>" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="cashier" <?php echo $user['role'] === 'cashier' ? 'selected' : ''; ?>>Cashier</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update User
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

