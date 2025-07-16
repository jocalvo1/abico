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

// Initialize database connection
$database = new dbconn();
$db = $database->getConnection();

// Initialize TransactionHistory object
$transactionHistory = new TransactionHistory($db);

include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>
<div class="container">
  <div class="page-inner">
    <!-- Sales Content Here -->
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="card-title mb-0">
            <div class="btn-group" role="group">
              <a href="index.php" class="btn btn-outline-primary active">
                <i class="fas fa-shopping-cart"></i> Sales
              </a>
              <a href="activity_log.php" class="btn btn-outline-secondary">
                <i class="fas fa-book"></i> Activity Logs
              </a>
            </div>
          </h4>
          <a href="create.php" class="btn btn-primary btn-round">
            <i class="fas fa-plus me-2"></i>New Transaction
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
            <table id="salesTable" class="table table-hover table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <script>
                $(document).ready(function() {
                    // Initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    
                    // Initialize DataTable
                    var table = $('#salesTable').DataTable({
                        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url": "../includes/sales/transaction_history.php",
                            "type": "POST",
                            "data": function (d) {
                                // Add any additional parameters if needed
                                return d;
                            },
                            "error": function(xhr, error, thrown) {
                                console.error('DataTables AJAX Error:', error, thrown);
                                console.error('Response:', xhr.responseText);
                            }
                        },
                        "columns": [
                            { 
                                "data": "id",
                                "orderable": true
                            },
                            { 
                                "data": "created_at",
                                "orderable": true,
                                "render": function(data) {
                                    const date = new Date(data);
                                    return date.toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: '2-digit',
                                        year: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit',
                                        hour12: true
                                    });
                                }
                            },
                            { 
                                "data": "customer_name",
                                "orderable": true
                            },
                            { 
                                "data": "item_count",
                                "orderable": true
                            },
                            { 
                                "data": "total_amount",
                                "orderable": true,
                                "render": function(data) {
                                    const amount = parseFloat(data);
                                    return '₱' + (isNaN(amount) ? '0.00' : amount.toFixed(2));
                                }
                            },
                            { 
                                "data": "id",
                                "orderable": false,
                                "className": "text-center",
                                "render": function(data) {
                                    return '<a href="view.php?id=' + data + '" ' +
                                           'class="btn btn-sm btn-outline-primary px-3 py-1" ' +
                                           'title="View transaction details" ' +
                                           'data-bs-toggle="tooltip" ' +
                                           'data-bs-placement="top">' +
                                           '<i class="fas fa-eye me-1"></i>View</a>';
                                }
                            }
                        ],
                        "pageLength": 5,
                        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                        "language": {
                            "search": "Search:",
                            "lengthMenu": "Show _MENU_ entries",
                            "info": "Showing page _PAGE_ of _PAGES_",
                            "infoEmpty": "No entries found",
                            "infoFiltered": "(filtered from _MAX_ total entries)"
                        },
                        "order": [[0, "desc"]], // Order by ID descending
                        "initComplete": function() {
                            console.log('DataTables initialized successfully');
                            // Re-initialize tooltips after table is loaded
                            $('[data-bs-toggle="tooltip"]').tooltip();
                        }
                    });
                });
            </script>
        </div>
      </div>
    </div>

  </div>
</div>
<?php include __DIR__ . "/../templates/footer.php"; ?>
</div>
</div>
<!--   Core JS Files   -->
<?php include __DIR__ . "/../templates/scripts.php"; ?>
</body>
</html>