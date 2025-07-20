<?php
session_start();
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/sales/transaction_history.php';

$session = new session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: ../login.php');
    exit();
}

// Get transaction ID from URL
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($transaction_id <= 0) {
    header('Location: index.php');
    exit();
}

// Initialize database and TransactionHistory
$database = new dbconn();
$db = $database->getConnection();
$transactionHistory = new TransactionHistory($db);

// Get transaction details
$transaction = $transactionHistory->readOne($transaction_id);
if (!$transaction) {
    header('Location: index.php');
    exit();
}

// Get transaction items
$items = $transactionHistory->getTransactionItems($transaction_id);

include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>

<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-bold mb-0">Transaction Details</h3>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Transactions
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Transaction #<?php echo htmlspecialchars($transaction['id']); ?></h5>
                        <p class="text-muted">Date: <?php echo date('F j, Y h:i A', strtotime($transaction['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h5>Customer</h5>
                        <p class="text-muted"><?php echo htmlspecialchars($transaction['customer_name']); ?></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td class="text-end">₱ <?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end">₱ <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php 
                $isDebt = isset($transaction['is_debt']) && $transaction['is_debt'] == 1;
                $remainingBalance = isset($transaction['remaining_balance']) ? floatval($transaction['remaining_balance']) : 0;
                $hasRemainingBalance = $remainingBalance > 0;
                $displayBalance = $hasRemainingBalance ? -$remainingBalance : 0; // Show as negative for debt
                ?>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="text-muted">Thank you for your business!</div>
                        <?php if ($isDebt && $hasRemainingBalance): ?>
                        <div class="alert alert-warning mt-2 p-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div>
                                    <strong>This is a debt transaction</strong>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Remaining Balance:&nbsp; </span>
                                        <strong>₱<?php echo number_format($remainingBalance, 2); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td class="text-end">₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td class="text-end"><?php echo htmlspecialchars($transaction['payment_method']); ?></td>
                                </tr>
                                <tr>
                                    <th>Amount Paid:</th>
                                    <td class="text-end">₱<?php echo number_format($transaction['amount_received'], 2); ?></td>
                                </tr>
                                <?php if ($transaction['change_amount'] > 0): ?>
                                <tr>
                                    <th>Change:</th>
                                    <td class="text-end">₱<?php echo number_format($transaction['change_amount'], 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($hasRemainingBalance): ?>
                                <tr class="table-warning">
                                    <th>Remaining Balance:</th>
                                    <td class="text-end fw-bold">₱<?php echo number_format($remainingBalance, 2); ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="table-primary">
                                    <th>Total Amount:</th>
                                    <td class="text-end fw-bold">₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-primary" onclick="printReceipt()">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Transactions
                    </a>
                </div>

                <!-- Hidden receipt for printing -->
                <div id="printable-receipt" class="d-none">
                    <div class="receipt-container p-4">
                        <div class="text-center mb-4">
                            <h2 class="mb-1">ABICO STORE</h2>
                            <p class="mb-1">123 Store Street, City</p>
                            <p class="mb-1">Tel: (123) 456-7890</p>
                            <p class="mb-1">TIN: 123-456-789-000</p>
                            <p class="mb-1">S/N: <?php echo str_pad($transaction['id'], 5, '0', STR_PAD_LEFT); ?></p>
                            <p class="mb-1"><?php echo date('m/d/Y h:i A', strtotime($transaction['created_at'])); ?></p>
                            <p class="mb-1">--------------------------</p>
                        </div>
                        
                        <div class="receipt-items mb-3">
                            <table class="table table-sm table-borderless mb-2">
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td class="p-1"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td class="text-end p-1"><?php echo $item['quantity']; ?> x ₱<?php echo number_format($item['price'], 2); ?></td>
                                        <td class="text-end p-1">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="border-top border-dark pt-2">
                                <div class="d-flex justify-content-between">
                                    <span>Subtotal:</span>
                                    <span>₱<?php echo number_format($transaction['total_amount'], 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Payment Method:</span>
                                    <span><?php echo htmlspecialchars($transaction['payment_method']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Amount Paid:</span>
                                    <span>₱<?php echo number_format($transaction['amount_received'], 2); ?></span>
                                </div>
                                <?php if ($transaction['change_amount'] > 0): ?>
                                <div class="d-flex justify-content-between">
                                    <span>Change:</span>
                                    <span>₱<?php echo number_format($transaction['change_amount'], 2); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($hasRemainingBalance): ?>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Balance Due:</span>
                                    <span>₱<?php echo number_format($remainingBalance, 2); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between fw-bold border-top border-dark mt-1 pt-1">
                                    <span>TOTAL:</span>
                                    <span>₱<?php echo number_format($transaction['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="mb-1">Thank you for shopping with us!</p>
                            <p class="mb-1">This receipt serves as your official invoice</p>
                            <p>--------------------------</p>
                            <p class="small">For inquiries, please contact us at:</p>
                            <p class="small mb-0">Email: info@abicostore.com</p>
                            <p class="small">Phone: (123) 456-7890</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Print styles for receipt */
    @media print {
        body * {
            visibility: hidden;
            margin: 0;
            padding: 0;
        }
        
        #printable-receipt, #printable-receipt * {
            visibility: visible;
        }
        
        #printable-receipt {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            color: #000;
            background: #fff;
            padding: 10mm;
        }
        
        .receipt-container {
            max-width: 80mm;
            margin: 0 auto;
        }
        
        .receipt-items table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .receipt-items td {
            padding: 2px 0;
            border: none !important;
        }
        
        .no-print, .no-print * {
            display: none !important;
        }
        
        @page {
            size: auto;
            margin: 0;
        }
    }
    
    /* Hide receipt in normal view */
    #printable-receipt {
        display: none;
    }
    
    /* Print button styles */
    @media screen {
        .receipt-container {
            display: none;
        }
    }
</style>

<script>
function printReceipt() {
    // Get the receipt HTML
    const receiptContent = document.getElementById('printable-receipt').innerHTML;
    
    // Open a new window for printing with larger dimensions
    const printWindow = window.open('', '', 'width=800,height=900,top=50,left=50,resizable=yes,scrollbars=yes');
    
    // Write the receipt content to the new window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Receipt #<?php echo $transaction['id']; ?></title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    line-height: 1.2;
                    color: #000;
                    background: #fff;
                    margin: 0;
                    padding: 5mm;
                }
                .receipt-container {
                    max-width: 80mm;
                    margin: 0 auto;
                }
                .receipt-items table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 5px 0;
                }
                .receipt-items td {
                    padding: 1px 0;
                }
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                .fw-bold { font-weight: bold; }
                .border-top { border-top: 1px dashed #000; }
                .border-dark { border-color: #000 !important; }
                .p-1 { padding: 0.25rem !important; }
                .mb-1 { margin-bottom: 0.25rem !important; }
                .mb-2 { margin-bottom: 0.5rem !important; }
                .mb-3 { margin-bottom: 1rem !important; }
                .mb-4 { margin-bottom: 1.5rem !important; }
                .mt-1 { margin-top: 0.25rem !important; }
                .mt-2 { margin-top: 0.5rem !important; }
                .mt-4 { margin-top: 1.5rem !important; }
                .pt-1 { padding-top: 0.25rem !important; }
                .pt-2 { padding-top: 0.5rem !important; }
                .small { font-size: 85%; }
                .d-flex { display: flex; }
                .justify-content-between { justify-content: space-between; }
                .w-100 { width: 100%; }
            </style>
        </head>
        <body onload="window.print(); window.onafterprint = function() { window.close(); };">
            ${receiptContent}
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
</script>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>
