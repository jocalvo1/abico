<?php
session_start();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/sales/activity_log.php';
?>
<!-- Add DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap5.min.css">

<?php

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: ../login.php');
    exit();
}

// Initialize database connection
$database = new dbconn();
$db = $database->getConnection();

// Initialize SalesActivityLog object
$activityLog = new SalesActivityLog($db);

// Get all logs for DataTable
$logs = $activityLog->readAll();

// Include template header and sidebar
include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/sidebar.php';
include __DIR__ . '/../templates/nav.php';
?>

<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <div class="btn-group" role="group">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-shopping-cart"></i> Sales
                        </a>
                        <a href="activity_log.php" class="btn btn-outline-primary active">
                            <i class="fas fa-book"></i> Activity Logs
                        </a>
                    </div>
                </h4>
                <div class="text-muted">
                    <?= count($logs) ?> total entries
                </div>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="activityTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Description</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $index => $row): 
                            $description = $activityLog->formatDescription($row);
                            $timeAgo = $activityLog->timeElapsedString($row['created_at']);
                        ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($description) ?></td>
                            <td class="text-muted">
                                <div class="d-flex flex-column">
                                    <span><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></span>
                                    <small class="text-muted"><?= $timeAgo ?></small>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<!-- Custom DataTables CSS -->
<link rel="stylesheet" href="/ABICO/assets/css/datatables.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    try {
        // Initialize DataTable with enhanced configuration
        const activityTable = $('#activityTable').DataTable({
            responsive: true,
            autoWidth: false,
            order: [[2, 'desc']], // Sort by date descending
            stateSave: true, // Save state (pagination, search, etc.)
            stateDuration: 60 * 60 * 24, // 24 hours
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                lengthMenu: "Show _MENU_ entries",
                search: "",
                searchPlaceholder: "Search...",
                paginate: {
                    first: '«',
                    previous: '‹',
                    next: '›',
                    last: '»'
                },
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                infoFiltered: "(filtered from _MAX_ total entries)",
                emptyTable: "No data available in table",
                zeroRecords: "No matching records found"
            },
            serverSide: false,
            deferRender: true,
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
            
            // Column definitions
            columnDefs: [
                {
                    targets: 0, // # column
                    orderable: false,
                    className: 'text-center',
                    width: '60px'
                },
                { 
                    targets: 1, // Customer column
                    width: '20%'
                },
                {
                    targets: 3, // Date column
                    className: 'text-nowrap',
                    width: '180px',
                    type: 'date',
                    render: function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return new Date(data).getTime();
                        }
                        return data;
                    }
                }
            ],
            
            // Language configuration
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: '',
                searchPlaceholder: 'Search logs...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'No entries found',
                infoFiltered: '(filtered from _MAX_ total entries)',
                paginate: {
                    first: '<i class="fas fa-angle-double-left"></i>',
                    last: '<i class="fas fa-angle-double-right"></i>',
                    next: '<i class="fas fa-chevron-right"></i>',
                    previous: '<i class="fas fa-chevron-left"></i>'
                }
            },
            
            // DOM layout configuration
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            
            // Draw callback for additional styling
            drawCallback: function() {
                // Re-initialize tooltips if needed
                if (typeof $('[data-bs-toggle="tooltip"]').tooltip === 'function') {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
                
                console.log('Sales activity log table redrawn');
            },
            
            // Error handling
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error, thrown);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load activity log. Please refresh the page.'
                    });
                }
            }
        });
        
        console.log('Sales activity log table initialized');
        
    } catch (error) {
        console.error('Error initializing sales activity log table:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Initialization Error',
                text: 'Failed to initialize the activity log. Please check console for details.'
            });
        }
    }
});
</script>
