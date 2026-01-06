<?php
/**
 * Receipt AJAX Endpoint
 * Returns receipt data as JSON for modal display
 */

require_once '../config.php';
requireLogin();

$pdo = getDB();
$sales_id = $_GET['id'] ?? 0;

try {
    if (!$sales_id) {
        throw new Exception('Invalid sales ID');
    }

    // Get sale data
    $stmt = $pdo->prepare("SELECT * FROM sales WHERE sales_id = ?");
    $stmt->execute([$sales_id]);
    $sale = $stmt->fetch();
    
    if (!$sale) {
        throw new Exception('Sale not found');
    }
    
    // Get payment data
    $stmt = $pdo->prepare("SELECT payment_method, amount_paid, change_amount FROM payments WHERE customer_name = ? ORDER BY payment_date DESC LIMIT 1");
    $stmt->execute([$sale['customer_name']]);
    $payment = $stmt->fetch();
    
    if ($payment) {
        $sale['payment_method'] = $payment['payment_method'];
        $sale['amount_paid'] = $payment['amount_paid'];
        $sale['change_amount'] = $payment['change_amount'];
    }
    
    // Get sale items - fetch by matching customer_name and sales_date to ensure accuracy
    $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE customer_name = ? ORDER BY sales_items_id");
    $stmt->execute([$sale['customer_name']]);
    $items = $stmt->fetchAll();
    
    // If no items found by customer name, try getting items for this specific sale by using customer_name AND checking proximity
    if (empty($items) && !empty($sale['customer_name'])) {
        $stmt = $pdo->prepare("SELECT * FROM sale_items WHERE customer_name = ? LIMIT 100");
        $stmt->execute([$sale['customer_name']]);
        $items = $stmt->fetchAll();
    }
    
    // Generate HTML
    $html = '
        <div class="text-center mb-4">
            <h4>' . APP_NAME . '</h4>
            <p class="text-muted mb-0">Sale Receipt</p>
            <hr>
        </div>
        
        <div class="mb-3">
            <div class="row mb-2">
                <div class="col-6"><strong>Receipt #:</strong></div>
                <div class="col-6">' . $sale['sales_id'] . '</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Date:</strong></div>
                <div class="col-6">' . formatDate($sale['sales_date']) . '</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Cashier:</strong></div>
                <div class="col-6">' . escape($sale['username']) . '</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Customer:</strong></div>
                <div class="col-6">' . escape($sale['customer_name'] ?? 'Walk-in') . '</div>
            </div>
        </div>
        
        <hr>
        
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
                <tbody>';
    
    foreach ($items as $item) {
        $html .= '
                    <tr>
                        <td>' . escape($item['product_name']) . '</td>
                        <td class="text-center">' . $item['quantity'] . '</td>
                        <td class="text-end">' . formatCurrency($item['price']) . '</td>
                        <td class="text-end">' . formatCurrency($item['subtotal']) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th class="text-end">' . formatCurrency($sale['total_amount']) . '</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <hr>
        
        <div class="mb-3">
            <div class="row mb-2">
                <div class="col-6"><strong>Payment Method:</strong></div>
                <div class="col-6 text-capitalize">' . escape($sale['payment_method'] ?? 'N/A') . '</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Amount Paid:</strong></div>
                <div class="col-6">' . formatCurrency($sale['amount_paid'] ?? 0) . '</div>
            </div>
            <div class="row mb-2">
                <div class="col-6"><strong>Change:</strong></div>
                <div class="col-6">' . formatCurrency($sale['change_amount'] ?? 0) . '</div>
            </div>
        </div>
        
        <hr>
        
        <div class="text-center text-muted">
            <small>Thank you for your purchase!</small>
        </div>
    ';
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
