<?php
/**
 * Setup Script
 * Run this once to set up default users with proper password hashes
 * 
 * Usage: php setup.php
 * Or access via browser: http://localhost/inventory/setup.php
 */

require_once 'config.php';

// Only allow setup if users table is empty or if explicitly requested
$pdo = getDB();
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$user_count = $stmt->fetch()['count'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || (php_sapi_name() === 'cli' && $user_count == 0)) {
    try {
        // Hash passwords
        $admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $cashier_hash = password_hash('cashier123', PASSWORD_DEFAULT);
        
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
            $stmt->execute([$admin_hash]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')");
            $stmt->execute([$admin_hash]);
        }
        
        // Check if cashier exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute(['cashier']);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'cashier'");
            $stmt->execute([$cashier_hash]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('cashier', ?, 'cashier')");
            $stmt->execute([$cashier_hash]);
        }
        
        $message = "Setup completed successfully! Default users created/updated.";
        
        if (php_sapi_name() === 'cli') {
            echo $message . PHP_EOL;
            exit(0);
        }
        
    } catch (PDOException $e) {
        $error = "Setup error: " . $e->getMessage();
        
        if (php_sapi_name() === 'cli') {
            echo $error . PHP_EOL;
            exit(1);
        }
    }
}

// If running from CLI and users exist, skip
if (php_sapi_name() === 'cli') {
    if ($user_count > 0) {
        echo "Users already exist. Use --force to update passwords." . PHP_EOL;
        exit(0);
    }
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">System Setup</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($message); ?>
                            <hr>
                            <strong>Default Login Credentials:</strong><br>
                            Admin: admin / admin123<br>
                            Cashier: cashier / cashier123<br>
                            <br>
                            <a href="auth/login.php" class="btn btn-primary">Go to Login</a>
                        </div>
                        <?php elseif ($error): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                        <?php else: ?>
                        <p>This script will create/update default user accounts with proper password hashes.</p>
                        <form method="POST">
                            <button type="submit" class="btn btn-primary">Run Setup</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

