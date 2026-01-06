<?php
/**
 * Sales History
 * List all completed sales
 */

require_once '../config.php';
requireLogin();

$page_title = 'Sales History';
require_once '../includes/header.php';

$pdo = getDB();
$sales = [];
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Date filter
$date_from = $_GET['date_from'] ?? null;
$date_to = $_GET['date_to'] ?? null;
$customer_filter = isset($_GET['customer_name']) ? trim($_GET['customer_name']) : null;

// If viewing receipts for a specific customer and no date filter provided,
// default to a wide range so results aren't empty.
if ($date_from === null) {
    $date_from = $customer_filter !== null ? '1970-01-01' : date('Y-m-d');
}
if ($date_to === null) {
    $date_to = date('Y-m-d');
}

try {
    $query = "
        SELECT s.*
        FROM sales s
        WHERE DATE(s.sales_date) BETWEEN ? AND ?
    ";

    // Apply customer filter if provided (now filter by customer_name directly)
    $params = [$date_from, $date_to];
    if ($customer_filter !== null) {
        $query .= " AND s.customer_name = ?";
        $params[] = $customer_filter;
    }

    // Cashiers can only see their own sales
    if (!isAdmin()) {
        $query .= " AND s.username = ? ";
        $params[] = $_SESSION['username'];
    }

    $stmt = $pdo->prepare($query . " ORDER BY s.sales_id ASC");
    $stmt->execute($params);
    
    $sales = $stmt->fetchAll();
    
    // Calculate totals
    $total_sales = 0;
    foreach ($sales as $sale) {
        $total_sales += $sale['total_amount'];
    }
} catch (PDOException $e) {
    $error = "Error loading sales: " . $e->getMessage();
}
?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle"></i> Operation completed successfully!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-circle"></i> <?php echo escape($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Sales</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo escape($date_from); ?>">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo escape($date_to); ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Sales History</h5>
        <div>
            <strong>Total: <?php echo formatCurrency($total_sales); ?></strong>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No sales found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td>#<?php echo $sale['sales_id']; ?></td>
                        <td><?php echo formatDate($sale['sales_date']); ?></td>
                        <td><?php echo escape($sale['customer_name'] ?? 'Walk-in'); ?></td>
                        <td><?php echo escape($sale['username']); ?></td>
                        <td><strong><?php echo formatCurrency($sale['total_amount']); ?></strong></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="viewReceipt(<?php echo $sale['sales_id']; ?>)">
                                <i class="bi bi-receipt"></i> Receipt
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Sale Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="receiptContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printReceipt()">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Alert Container -->
<div id="receiptContainer"></div>

<script>
function viewReceipt(salesId) {
    console.log('Loading receipt for sales_id:', salesId);
    
    fetch(`receipt-ajax.php?id=${salesId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                displayReceiptModal(data.html);
            } else {
                showAlert('Error loading receipt: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showAlert('Error loading receipt: ' + error, 'danger');
        });
}

function displayReceiptModal(receiptHtml) {
    const receiptContent = document.getElementById('receiptContent');
    receiptContent.innerHTML = receiptHtml;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

function printReceipt() {
    const printContent = document.getElementById('receiptContent').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { margin: 20px; padding: 0; }
                @media print {
                    body { margin: 0; padding: 10px; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            ${printContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 250);
}

function showAlert(message, type = 'info') {
    const container = document.getElementById('receiptContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show my-4`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    container.innerHTML = '';
    container.appendChild(alertDiv);
}
</script>

<?php require_once '../includes/footer.php'; ?>

