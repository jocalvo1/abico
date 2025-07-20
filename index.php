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

// Get low stock and out of stock items
$stmt = $db->query("SELECT p.*, 
    CASE 
        WHEN stock_quantity <= 0 THEN 0 
        WHEN low_stock_threshold IS NOT NULL AND stock_quantity <= low_stock_threshold THEN 1 
        WHEN low_stock_threshold IS NULL AND stock_quantity < 10 THEN 1 
        ELSE 2 
    END as stock_status
FROM products p
WHERE stock_quantity <= 0 OR (low_stock_threshold IS NOT NULL AND stock_quantity <= low_stock_threshold) OR (low_stock_threshold IS NULL AND stock_quantity < 10)
ORDER BY stock_status ASC, stock_quantity ASC
LIMIT 5");
$low_stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count low stock and out of stock items
$stmt = $db->query("SELECT 
    SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
    SUM(CASE 
        WHEN stock_quantity > 0 AND 
             ((low_stock_threshold IS NOT NULL AND stock_quantity <= low_stock_threshold) OR 
              (low_stock_threshold IS NULL AND stock_quantity < 10)) 
        THEN 1 ELSE 0 
    END) as low_stock_count
FROM products");
$stock_counts = $stmt->fetch(PDO::FETCH_ASSOC);

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
                <a href="sales/create.php" class="btn btn-primary btn-round">New Transaction</a>
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
                                        <h4 class="card-title"><?php echo ($stock_counts['out_of_stock_count'] + $stock_counts['low_stock_count']); ?></h4>
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
        
        <!-- Charts Section -->
        <div class="row mb-4">
            <!-- Monthly Sales Chart -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title me-3">Monthly Sales</h4>
                                <select class="form-select form-select-sm me-2" id="salesMonthSelector" onchange="console.log('Month changed to:', this.value); loadMonthlySalesChart();" style="width: 150px;">
                                    <!-- Options will be populated by JavaScript -->
                                </select>
                            </div>
                            <div class="card-tools">
                                <span class="badge badge-info me-2" id="sales-month">Loading...</span>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel('monthly_sales')" title="Export Monthly Sales">
                                        <i class="fas fa-file-excel"></i> Export
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary" onclick="exportToExcel('all_sales')" title="Export All Transactions">
                                        <i class="fas fa-list"></i> All Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height: 400px;">
                            <canvas id="monthlySalesChart"></canvas>
                        </div>
                    </div>
                </div>
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="card-title mb-0">Stock Alerts</h4>
                            <a href="inventory/index.php" class="btn btn-sm btn-info">
                                <i class="fas fa-boxes me-1"></i> Add Stocks
                            </a>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="text-danger">
                                <i class="fas fa-times-circle me-1"></i> 
                                <span class="fw-bold"><?php echo $stock_counts['out_of_stock_count']; ?></span> Out of Stock
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> 
                                <span class="fw-bold"><?php echo $stock_counts['low_stock_count']; ?></span> Low Stock
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($low_stock_items) > 0): ?>
                                        <?php foreach ($low_stock_items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $item['stock_quantity'] <= 0 ? 'danger' : 'warning'; ?>">
                                                        <?php echo $item['stock_quantity']; ?> pcs
                                                    </span>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center">All items are well-stocked</td>
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

<!-- Dashboard Charts Script -->
<script>
$(document).ready(function() {
    
    function populateMonthSelectors() {
        console.log('Populating month selectors...');
        const currentDate = new Date();
        const months = [];
        
        // Generate last 12 months
        for (let i = 0; i < 12; i++) {
            const date = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
            const monthValue = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
            const monthText = date.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
            months.push({ value: monthValue, text: monthText });
        }
        
        // Populate sales selector
        const salesSelector = $('#salesMonthSelector');
        
        months.forEach(month => {
            salesSelector.append(`<option value="${month.value}">${month.text}</option>`);
        });
    }
    
    function loadMonthlySalesChart() {
        const selectedMonth = $('#salesMonthSelector').val() || new Date().toISOString().slice(0, 7);
        console.log('Loading chart for month:', selectedMonth);
        
        // Destroy existing chart if it exists
        if (window.monthlySalesChartInstance) {
            window.monthlySalesChartInstance.destroy();
            window.monthlySalesChartInstance = null;
        }
        
        // Show loading state
        $('#sales-month').text('Loading...');
        
        $.ajax({
            url: 'api/chart_data.php?type=monthly_sales&month=' + selectedMonth,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Chart data received:', response);
                $('#sales-month').text(response.month);
                
                const ctx = document.getElementById('monthlySalesChart').getContext('2d');
                window.monthlySalesChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: response.labels,
                        datasets: [{
                            label: 'Daily Sales (₱)',
                            data: response.data,
                            borderColor: '#177dff',
                            backgroundColor: 'rgba(23, 125, 255, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Day of Month'
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading monthly sales chart:', error);
                console.error('Response:', xhr.responseText);
                $('#sales-month').text('Error loading data');
            }
        });
    }
    
    // Make function globally accessible for the onchange event
    window.loadMonthlySalesChart = loadMonthlySalesChart;
    
    // Initialize month selectors and charts
    populateMonthSelectors();
    loadMonthlySalesChart();
    
    // Export to Excel function
    window.exportToExcel = function(type) {
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
        button.disabled = true;
        
        // Get selected month from sales selector
        const selectedMonth = $('#salesMonthSelector').val() || new Date().toISOString().slice(0, 7);
        
        // Create a temporary link to download the file
        const link = document.createElement('a');
        link.href = 'api/export_excel.php?type=' + type + '&month=' + selectedMonth;
        link.download = type + '_' + selectedMonth + '.xls';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button state after a short delay
        setTimeout(function() {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
        
        // Show success message
        $.notify({
            icon: 'fas fa-file-excel',
            title: 'Export Successful!',
            message: 'Your Excel file has been downloaded.'
        }, {
            type: 'success',
            placement: {
                from: 'top',
                align: 'right'
            },
            time: 3000
        });
    };
});
</script>
</body>
</html>
