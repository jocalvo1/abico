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
                                        <span>Remaining Balance:</span>
                                        <strong>-₱<?php echo number_format($remainingBalance, 2); ?></strong>
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
                                    <td class="text-end fw-bold">-₱<?php echo number_format($remainingBalance, 2); ?></td>
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
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Receipt
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Transactions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .card, .card * {
            visibility: visible;
        }
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
        }
        .no-print {
            display: none !important;
        }
        .table th, .table td {
            border-color: #dee2e6 !important;
        }
    }
</style>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>
