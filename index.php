<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/session.php';

$session = new session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: login.php');
    exit();
}

// Database connection
$database = new dbconn();
$db = $database->getConnection();

// Get total products
$total_products = 0;
$total_stock = 0;
$stmt = $db->query("SELECT COUNT(*) as count, COALESCE(SUM(stock_quantity), 0) as total_stock FROM products");
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_products = $row['count'];
    $total_stock = $row['total_stock'];
}

// Get today's sales
$today_sales = 0;
$today = date('Y-m-d');
$stmt = $db->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM transactions WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $today_sales = number_format($row['total'], 2);
}

// Get total customers
$total_customers = 0;
$stmt = $db->query("SELECT COUNT(*) as count FROM customers");
if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total_customers = $row['count'];
}

// Get recent transactions
$recent_transactions = [];
$stmt = $db->query("SELECT t.*, c.customer_name 
                   FROM transactions t 
                   LEFT JOIN customers c ON t.customer_id = c.id 
                   ORDER BY t.created_at DESC LIMIT 5");
$recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get low stock items
$low_stock_items = [];
$stmt = $db->query("SELECT * FROM products WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 5");
$low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . "/templates/header.php";
include __DIR__ . "/templates/sidebar.php";
include __DIR__ . "/templates/nav.php";
?>
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Dashboard</h3>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                <a href="inventory/index.php" class="btn btn-label-info btn-round me-2">View Inventory</a>
                <a href="sales/index.php" class="btn btn-primary btn-round">New Transaction</a>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row">
            <!-- Total Products -->
            <div class="col-sm-6 col-md-3">
                <a href="inventory/index.php" class="text-decoration-none">
                    <div class="card card-stats card-round card-hover">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary">
                                        <i class="fas fa-boxes"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Total Products</p>
                                        <h4 class="card-title"><?php echo number_format($total_products); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Low Stock Items Count -->
            <div class="col-sm-6 col-md-3">
                <a href="inventory/index.php?filter=low_stock" class="text-decoration-none">
                    <div class="card card-stats card-round card-hover">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center <?php echo count($low_stock_items) > 0 ? 'icon-danger' : 'icon-success'; ?>">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Low Stock Items</p>
                                        <h4 class="card-title"><?php echo count($low_stock_items); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Today's Sales -->
            <div class="col-sm-6 col-md-3">
                <a href="sales/index.php?filter=today" class="text-decoration-none">
                    <div class="card card-stats card-round card-hover">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-info">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Today's Sales</p>
                                        <h4 class="card-title">₱<?php echo $today_sales; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- Total Customers -->
            <div class="col-sm-6 col-md-3">
                <a href="ledger/index.php" class="text-decoration-none">
                    <div class="card card-stats card-round card-hover">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-secondary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Total Customers</p>
                                        <h4 class="card-title"><?php echo number_format($total_customers); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Transactions -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-head-row">
                            <h4 class="card-title">Recent Transactions</h4>
                            <div class="card-tools">
                                <a href="sales/index.php" class="btn btn-info btn-border btn-round btn-sm">
                                    <span class="btn-label">
                                        <i class="fa fa-list"></i>
                                    </span>
                                    View All
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recent_transactions) > 0): ?>
                                        <?php foreach ($recent_transactions as $transaction): ?>
                                            <tr>
                                                <td>#<?php echo $transaction['id']; ?></td>
                                                <td><?php echo htmlspecialchars($transaction['customer_name'] ?: 'Walk-in'); ?></td>
                                                <td>₱<?php echo number_format($transaction['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $transaction['payment_status'] === 'paid' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($transaction['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($transaction['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No recent transactions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Items -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Low Stock Items</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($low_stock_items) > 0): ?>
                                        <?php foreach ($low_stock_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $item['stock_quantity'] < 5 ? 'danger' : 'warning'; ?>">
                                                        <?php echo $item['stock_quantity']; ?> pcs
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="inventory/edit.php?id=<?php echo $item['id']; ?>" class="btn btn-xs btn-info">
                                                        <i class="fa fa-edit"></i> Restock
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">All items are well-stocked</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
  <?php include __DIR__ . "/templates/footer.php"; ?>
</div>
</div>
<!--   Core JS Files   -->
<?php include __DIR__ . "/templates/scripts.php"; ?>
</body>
</html>
