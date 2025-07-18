<?php
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/ledger/transaction_history.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /ABICO/login.php');
    exit();
}

// Check if customer ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid customer ID');
}

$customer_id = $_GET['id'];
$database = new dbconn();
$db = $database->getConnection();

// Initialize TransactionHistory
$transactionHistory = new TransactionHistory($db);

// Get customer details
$customer = $transactionHistory->getCustomerDetails($customer_id);
if (!$customer) {
    die('Customer not found');
}

// Get transactions
$transactions = $transactionHistory->getCustomerTransactions($customer_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - <?php echo htmlspecialchars($customer['customer_name']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .transaction-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .transaction-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .debt {
            border-left-color: #dc3545;
        }
        .paid {
            border-left-color: #198754;
        }
        .product-item {
            border-bottom: 1px solid #eee;
            padding: 0.5rem 0;
        }
        .product-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Transaction History</h1>
                <p class="text-muted mb-0"><?php echo htmlspecialchars($customer['customer_name']); ?></p>
                <?php if (!empty($customer['contact'])): ?>
                    <p class="text-muted"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($customer['contact']); ?></p>
                <?php endif; ?>
            </div>
            <div class="btn-group" role="group">
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Ledger
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="alert alert-info">
                No transaction history found for this customer.
            </div>
        <?php else: ?>
            <?php foreach ($transactions as $transaction): 
                $items = $transactionHistory->getTransactionItems($transaction['id']);
                $isDebt = $transaction['is_debt'] ?? 0;
            ?>
                <div class="card mb-3 transaction-card <?php echo $transaction['payment_status'] === 'paid' ? 'paid' : 'debt'; ?>">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                <?php echo date('M d, Y h:i A', strtotime($transaction['created_at'])); ?>
                            </h5>
                            <span class="badge ms-2 transaction-status <?php echo $transaction['payment_status'] === 'paid' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php 
                                if ($transaction['payment_status'] === 'paid' && $transaction['is_debt'] == 1) {
                                    echo 'Paid (Was Debt)';
                                } elseif ($transaction['payment_status'] === 'paid') {
                                    echo 'Paid';
                                } else {
                                    echo 'Unpaid (Debt)';
                                }
                                ?>
                            </span>
                            <?php if ($transaction['payment_status'] !== 'paid'): ?>
                            <button class="btn btn-sm btn-outline-success ms-3 mark-paid-btn" 
                                    data-transaction-id="<?php echo $transaction['id']; ?>"
                                    title="Mark this transaction as paid">
                                <i class="fas fa-check-circle me-1"></i> Mark as Paid
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold h5 mb-0">₱<?php echo number_format($transaction['total_amount'], 2); ?></div>
                            <?php if ($isDebt && isset($transaction['remaining_balance'])): ?>
                                <small class="text-danger">Balance: ₱<?php echo number_format($transaction['remaining_balance'], 2); ?></small>
                            <?php else: ?>
                                <small class="text-muted">Transaction #<?php echo $transaction['id']; ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($items)): ?>
                            <h6 class="card-subtitle mb-2 text-muted">Items:</h6>
                            <div class="mb-3">
                                <?php foreach ($items as $item): ?>
                                    <div class="product-item">
                                        <div class="d-flex justify-content-between">
                                            <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                                            <span class="text-end">
                                                <?php echo (int)$item['quantity']; ?> <?php echo htmlspecialchars($item['unit']); ?>
                                                × ₱<?php echo number_format($item['price'], 2); ?>
                                                = <strong>₱<?php echo number_format($item['subtotal'], 2); ?></strong>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 for alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    $(document).ready(function() {
        // Handle mark as paid button click
        $(document).on('click', '.mark-paid-btn', function() {
            const button = $(this);
            const transactionId = button.data('transaction-id');
            const card = button.closest('.transaction-card');
            
            console.log('Mark as paid clicked for transaction:', transactionId);
            
            Swal.fire({
                title: 'Mark as Paid?',
                text: 'Are you sure you want to mark this transaction as paid?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark as paid',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
                    
                    console.log('Sending AJAX request to update payment status...');
                    
                    // Send AJAX request to update payment status
                    $.ajax({
                        url: 'update_payment_status.php',
                        type: 'POST',
                        data: {
                            transaction_id: transactionId,
                            status: 'paid'
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log('AJAX Success:', response);
                            if (response.success) {
                                // Update UI
                                card.removeClass('debt').addClass('paid');
                                const statusBadge = card.find('.transaction-status');
                                
                                if (statusBadge.length) {
                                    statusBadge
                                        .removeClass('bg-danger')
                                        .addClass('bg-success')
                                        .text('Paid (Was Debt)');
                                }
                                
                                // Remove the button
                                button.remove();
                                
                                // Show success message
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Transaction marked as paid successfully.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                // Show error message from server
                                const errorMsg = response.message || 'Failed to update payment status';
                                console.error('Server error:', errorMsg, response);
                                Swal.fire('Error', errorMsg, 'error');
                                button.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Mark as Paid');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            console.error('Response:', xhr.responseText);
                            Swal.fire('Error', 'An error occurred while updating the payment status. Please try again.', 'error');
                            button.prop('disabled', false).html('<i class="fas fa-check-circle me-1"></i> Mark as Paid');
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
