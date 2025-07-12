<?php
session_start();
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/database.php';

$session = new session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: ../login.php');
    exit();
}

// In a real application, you would fetch this data from the database
// For now, using sample data
$transaction_id = isset($_GET['id']) ? $_GET['id'] : '';

// Sample transaction data - replace with database query
$transaction = [
    'id' => $transaction_id,
    'date' => '2023-07-11 14:30:00',
    'customer_name' => 'John Doe',
    'items' => [
        ['name' => 'Product 1', 'quantity' => 2, 'price' => 500.00, 'subtotal' => 1000.00],
        ['name' => 'Product 2', 'quantity' => 1, 'price' => 750.00, 'subtotal' => 750.00],
    ],
    'total' => 1750.00,
    'payment_type' => 'Cash',
    'amount_paid' => 2000.00,
    'change' => 250.00
];

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
                        <p class="text-muted">Date: <?php echo date('F j, Y h:i A', strtotime($transaction['date'])); ?></p>
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
                            <?php foreach ($transaction['items'] as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="text-end">₱ <?php echo number_format($item['price'], 2); ?></td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-end">₱ <?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="text-muted">Thank you for your business!</div>
                    </div>
                    <div class="col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td class="text-end">₱ <?php echo number_format($transaction['total'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td class="text-end"><?php echo htmlspecialchars($transaction['payment_type']); ?></td>
                                </tr>
                                <tr>
                                    <th>Amount Paid:</th>
                                    <td class="text-end">₱ <?php echo number_format($transaction['amount_paid'], 2); ?></td>
                                </tr>
                                <tr class="table-active">
                                    <th>Change:</th>
                                    <td class="text-end fw-bold">₱ <?php echo number_format($transaction['change'], 2); ?></td>
                                </tr>
                                <tr class="table-primary">
                                    <th>Total Amount:</th>
                                    <td class="text-end fw-bold">₱ <?php echo number_format($transaction['total'], 2); ?></td>
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
