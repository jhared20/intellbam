<?php
/**
 * Edit Category
 */

require_once __DIR__ . '../config.php';
requireLogin();

$page_title = 'Edit Category';

$pdo = getDB();
$error = '';
$category = null;

$category_id = $_GET['id'] ?? 0;

if (!$category_id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT category_id, category_name FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    if (!$category) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error loading category: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    
    if (empty($category_name)) {
        $error = 'Category name is required';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT category_id FROM categories WHERE category_name = ? AND category_id != ?");
            $stmt->execute([$category_name, $category_id]);
            
            if ($stmt->fetch()) {
                $error = 'Category name already exists';
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
                $stmt->execute([$category_name, $category_id]);
                
                logActivity("Category updated: {$category_name} (ID: {$category_id})");
                
                header('Location: index.php?success=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error updating category: ' . $e->getMessage();
        }
    }
    require_once __DIR__ . '../../includes/header.php';
}
?>


<?php if ($error): ?>
<div class="alert alert-danger"><?php echo escape($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Category</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="category_name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="category_name" name="category_name" value="<?php echo escape($category['category_name']); ?>" required autofocus>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Category
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '../includes/footer.php'; ?>

