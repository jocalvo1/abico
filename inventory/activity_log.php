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
                    Showing <?= $offset + 1 ?> - <?= min($offset + count($logs), $totalLogs) ?> of <?= $totalLogs ?> entries
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="activityTable" class="table table-hover table-striped" style="width:100%">
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

<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with pagination
    $('#activityTable').DataTable({
        responsive: true,
        order: [[2, 'desc']], // Sort by date descending
        columnDefs: [
            {
                targets: 0, // # column
                orderable: false,
                className: 'text-center'
            },
            {
                targets: 2, // Date column
                className: 'text-nowrap'
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search logs...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        pageLength: 10,  
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]]
    });
    
    // Add custom search input
    $('.dataTables_filter input').addClass('form-control mb-3');
    
    // Add custom length menu
    $('.dataTables_length select').addClass('form-select mb-3');
    
    // Style pagination
    $('.dataTables_paginate').addClass('mt-3');
});
</script>

</body>
</html>