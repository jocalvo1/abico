<?php
session_start();
require_once 'includes/Session.php';
require_once 'config/database.php';

$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ABICO Store</title>
    <?php include __DIR__ . "/templates/links.php"; ?>
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php include __DIR__ . "/templates/sidebar.php"; ?>
        
        <div class="main-panel">
            <?php include __DIR__ . "/templates/header.php"; ?>
            
            <div class="content">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                        <div>
                            <h3 class="fw-bold mb-3">Dashboard</h3>
                        </div>
                        <div class="ms-md-auto py-2 py-md-0">
                            <a href="inventory/" class="btn btn-label-info btn-round me-2">View Inventory</a>
                            <a href="sales/" class="btn btn-primary btn-round">New Transaction</a>
                        </div>
                    </div>
                    <!-- Dashboard Stats -->
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                                <i class="fas fa-boxes"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Stocks (Current)</p>
                                                <h4 class="card-title" id="totalProducts">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="inventory/" class="text-primary">View Inventory</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                                <i class="fas fa-shopping-cart"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Sales (This Week)</p>
                                                <h4 class="card-title" id="weeklySales">₱0.00</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="sales/" class="text-success">View Sales</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                                <i class="fas fa-clipboard-list"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Orders (This Week)</p>
                                                <h4 class="card-title" id="weeklyOrders">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="sales/" class="text-info">View Orders</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-icon">
                                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                        </div>
                                        <div class="col col-stats ms-3 ms-sm-0">
                                            <div class="numbers">
                                                <p class="card-category">Low Stock Items</p>
                                                <h4 class="card-title" id="lowStockItems">0</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <a href="inventory/?filter=low_stock" class="text-warning">View Items</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-round">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Monthly Net Profit</div>
                                        <div class="card-tools">
                                            <select class="form-control form-control-sm" id="profitYear">
                                                <?php
                                                $currentYear = date('Y');
                                                for ($year = $currentYear - 2; $year <= $currentYear + 1; $year++) {
                                                    $selected = ($year == $currentYear) ? 'selected' : '';
                                                    echo "<option value='$year' $selected>$year</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <canvas id="profitChart" height="300"></canvas>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-sm btn-primary" onclick="exportChart('profitChart', 'profit-export')">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="printChart('profitChart')">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <div id="profit-export" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                      <div class="card-tools">
                        <a
                          href="#"
                          class="btn btn-label-success btn-round btn-sm me-2"
                        >
                          <span class="btn-label">
                            <i class="fa fa-pencil"></i>
                          </span>
                          Export
                        </a>
                        <a href="#" class="btn btn-label-info btn-round btn-sm">
                          <span class="btn-label">
                            <i class="fa fa-print"></i>
                          </span>
                          Print
                        </a>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                      <canvas id="statisticsChart"></canvas>
                    </div>
                    <div id="myChartLegend"></div>
                  </div>
                </div>

                <!-- Transaction History -->
                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                      <div class="card-title">Transaction History</div>
                      <div class="card-tools">
                        
                        <a href="/ABICO/sales/history.php" class="btn btn-label-primary btn-round btn-sm">View all</a>
                      </div>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <!-- Projects table -->
                      <table class="table align-items-center mb-0">
                        <thead class="thead-light">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col" class="text-end">Date & Time</th>
                            <th scope="col" class="text-end">Amount</th>
                            <th scope="col" class="text-end">Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>1</td>
                            <td>Customer #10231</td>
                            <td class="text-end">06-01-25, 2:45pm</td>
                            <td class="text-end">P250.00</td>
                            <td class="text-end">
                              <a class="badge badge-success">Paid</a>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
                        <div class="col-md-4">
                            <div class="card card-primary card-round">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Daily Sales (This Month)</div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <canvas id="dailySalesChart" height="300"></canvas>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-sm btn-primary" onclick="exportChart('dailySalesChart', 'daily-sales-export')">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="printChart('dailySalesChart')">
                                        <i class="fas fa-print"></i> Print
                                    </button>
                                    <div id="daily-sales-export" style="display: none;"></div>
                                </div>
                            </div>
                      <div class="card-tools">
                        <div class="dropdown">
                          <button
                            class="btn btn-sm btn-label-light dropdown-toggle"
                            type="button"
                            id="dropdownMenuButton"
                            data-bs-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                          >
                            Export
                          </button>
                          <div
                            class="dropdown-menu"
                            aria-labelledby="dropdownMenuButton"
                          >
                            <a class="dropdown-item" href="#">Action</a>
                            <a class="dropdown-item" href="#">Another action</a>
                            <a class="dropdown-item" href="#"
                              >Something else here</a
                            >
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-category">June 1 - June 7</div>
                  </div>
                  <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                      <h1>P4,578.58</h1>
                    </div>
                    <div class="pull-in">
                      <canvas id="dailySalesChart"></canvas>
                    </div>
                  </div>
                </div>

                <div class="card card-round">
                  <div class="card-header">
                    <div class="card-head-row card-tools-still-right">
                      <div class="card-title">Popular Items</div>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <!-- Projects table -->
                      <table class="table align-items-center mb-0">
                        <thead class="thead-light">
                          <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Purchases</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>1</td>
                            <td>Dildo</td>
                            <td>48</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
                    </div>
                    
                    <!-- Recent Transactions -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-head-row">
                                        <div class="card-title">Recent Transactions</div>
                                        <div class="card-tools">
                                            <a href="sales/" class="btn btn-info btn-sm">View All</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="recentTransactions">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Transaction ID</th>
                                                    <th>Customer</th>
                                                    <th>Date</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="recentTransactionsBody">
                                                <tr>
                                                    <td colspan="7" class="text-center">Loading data...</td>
                                                </tr>
                                            </tbody>
                                        </table>
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
    
    <!-- Include core JS files -->
    <?php include __DIR__ . "/templates/scripts.php"; ?>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Initialize dashboard data
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            initializeProfitChart();
            initializeDailySalesChart();
            loadRecentTransactions();
            
            // Update charts when year changes
            document.getElementById('profitYear').addEventListener('change', function() {
                updateProfitChart(this.value);
            });
        });
        
        // Load dashboard statistics
        function loadDashboardStats() {
            // This would be an AJAX call to your backend
            // For now, we'll use dummy data
            document.getElementById('totalProducts').textContent = '1,250';
            document.getElementById('weeklySales').textContent = '₱24,567.89';
            document.getElementById('weeklyOrders').textContent = '42';
            document.getElementById('lowStockItems').textContent = '8';
        }
        
        // Initialize profit chart
        function initializeProfitChart() {
            const ctx = document.getElementById('profitChart').getContext('2d');
            window.profitChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Net Profit',
                        data: [12500, 18900, 18000, 20890, 25980, 28900, 31200, 29870, 27890, 30200, 32890, 38900],
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.raw.toLocaleString('en-PH');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString('en-PH');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Initialize daily sales chart
        function initializeDailySalesChart() {
            const ctx = document.getElementById('dailySalesChart').getContext('2d');
            window.dailySalesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Array.from({length: 30}, (_, i) => i + 1),
                    datasets: [{
                        label: 'Daily Sales',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 5000) + 1000),
                        backgroundColor: 'rgba(111, 66, 193, 0.8)',
                        borderColor: 'rgba(111, 66, 193, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.raw.toLocaleString('en-PH');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString('en-PH');
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Update profit chart when year changes
        function updateProfitChart(year) {
            // This would be an AJAX call to get data for the selected year
            // For now, we'll just update with random data
            const newData = Array.from({length: 12}, () => Math.floor(Math.random() * 30000) + 10000);
            window.profitChart.data.datasets[0].data = newData;
            window.profitChart.update();
        }
        
        // Load recent transactions
        function loadRecentTransactions() {
            // This would be an AJAX call to your backend
            // For now, we'll use dummy data
            const transactions = [
                { id: 'TXN-001', customer: 'John Doe', date: '2025-07-08', amount: 1250.75, status: 'Paid' },
                { id: 'TXN-002', customer: 'Jane Smith', date: '2025-07-07', amount: 875.50, status: 'Paid' },
                { id: 'TXN-003', customer: 'Robert Johnson', date: '2025-07-07', amount: 1560.25, status: 'Pending' },
                { id: 'TXN-004', customer: 'Emily Davis', date: '2025-07-06', amount: 450.00, status: 'Paid' },
                { id: 'TXN-005', customer: 'Michael Brown', date: '2025-07-06', amount: 2100.00, status: 'Pending' }
            ];
            
            const tbody = document.getElementById('recentTransactionsBody');
            tbody.innerHTML = '';
            
            transactions.forEach((transaction, index) => {
                const row = document.createElement('tr');
                const statusClass = transaction.status === 'Paid' ? 'success' : 'warning';
                
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${transaction.id}</td>
                    <td>${transaction.customer}</td>
                    <td>${new Date(transaction.date).toLocaleDateString()}</td>
                    <td>₱${transaction.amount.toFixed(2)}</td>
                    <td><span class="badge bg-${statusClass}">${transaction.status}</span></td>
                    <td>
                        <a href="sales/view.php?id=${transaction.id}" class="btn btn-sm btn-info">View</a>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        // Export chart as image
        function exportChart(chartId, exportId) {
            const chart = window[chartId];
            const link = document.createElement('a');
            link.download = `${chartId}.png`;
            link.href = chart.toBase64Image('image/png');
            document.getElementById(exportId).appendChild(link);
            link.click();
            document.getElementById(exportId).removeChild(link);
        }
        
        // Print chart
        function printChart(chartId) {
            const chart = window[chartId];
            const win = window.open('', '', 'width=800,height=600');
            win.document.write(`
                <html>
                    <head>
                        <title>Print Chart</title>
                        <style>
                            body { text-align: center; padding: 20px; }
                            img { max-width: 100%; height: auto; }
                        </style>
                    </head>
                    <body>
                        <h2>${document.title}</h2>
                        <img src="${chart.toBase64Image('image/png')}" />
                        <script>
                            window.onload = function() {
                                window.print();
                                setTimeout(function() { window.close(); }, 500);
                            };
                        <\/script>
                    </body>
                </html>
            `);
            win.document.close();
        }
    </script>
</body>
</html>
