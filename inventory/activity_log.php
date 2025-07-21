<?php
session_start();
// Include required files
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/inventory/activity_log_product.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /abico/login.php');
    exit();
}

// Initialize database connection
$database = new dbconn();
$db = $database->getConnection();

// Initialize ActivityLog object
$activityLog = new ActivityLog($db);

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get logs and total count
$logs = $activityLog->readAll($page, $perPage);
$totalLogs = $activityLog->countAll();
$totalPages = ceil($totalLogs / $perPage);

// Include template header and sidebar
include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>
    
<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <div class="btn-group" role="group">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-boxes"></i> Inventory
                        </a>
                        <a href="activity_log.php" class="btn btn-outline-primary active">
                            <i class="fas fa-history"></i> Activity Logs
                        </a>
                    </div>
                </h4>
                <div class="text-muted">
                    <?= $totalLogs ?> total entries
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="activityTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $index => $row): 
                            $rowNumber = $offset + $index + 1;
                            $actionClass = $activityLog->getActionClass($row['action']);
                            $description = $activityLog->formatDescription($row);
                            $timeAgo = $activityLog->timeElapsedString($row['created_at']);
                        ?>
                        <tr>
                            <td><?= $rowNumber ?></td>
                            <td>
                                    <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-column">
                                            <div class="mb-1"><?= $description ?></div>
                                            <?php 
                                            // Show changes directly in the description if available
                                            if (!empty($row['old_values']) || !empty($row['new_values'])) {
                                                $old = json_decode($row['old_values'] ?? '{}', true);
                                                $new = json_decode($row['new_values'] ?? '{}', true);
                                                
                                                $changes = [];
                                                foreach ($new as $field => $value) {
                                                    $oldVal = $old[$field] ?? '';
                                                    if ($oldVal != $value) {
                                                        $changes[] = ucfirst(str_replace('_', ' ', $field)) . ": $oldVal → $value";
                                                    }
                                                }
                                                
                                                if (!empty($changes)) {
                                                    echo '<div class="small text-muted">' . 
                                                         implode(' • ', $changes) . 
                                                         '</div>';
                                                }
                                            }
                                            ?>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?= $timeAgo ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td data-order="<?= strtotime($row['created_at']) ?>">
                                <?= date('M j, Y h:i A', strtotime($row['created_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- DataTables will handle pagination -->
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<!-- Custom DataTables CSS -->
<link rel="stylesheet" href="/ABICO/assets/css/datatables.css">

<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

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
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
            
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
            
            // Column definitions
            columnDefs: [
                {
                    targets: 0, // # column
                    orderable: false,
                    className: 'text-center',
                    width: '60px'
                },
                {
                    targets: 2, // Date column
                    className: 'text-nowrap',
                    width: '180px'
                }
            ],
            
            // Draw callback for additional styling
            drawCallback: function() {
                // Reinitialize tooltips
                $('[data-bs-toggle="tooltip"]').tooltip();
                
                // Style the pagination controls
                const paginateButtons = $('.dataTables_paginate .paginate_button');
                paginateButtons.removeClass('btn-sm btn-primary');
                paginateButtons.filter('.current').addClass('active');
                
                // Ensure search and length controls are properly aligned
                $('.dataTables_length select').css({
                    'min-width': '80px',
                    'padding-right': '30px'
                });
                
                $('.dataTables_filter input').css({
                    'min-width': '200px',
                    'max-width': '350px'
                });
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
        
        console.log('Activity log table initialized');
        
    } catch (error) {
        console.error('Error initializing activity log table:', error);
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

</body>
</html>