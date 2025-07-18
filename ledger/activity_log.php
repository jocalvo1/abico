<?php
// Include required files
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/ledger/activity_log.php';
require_once __DIR__ . '/../includes/ledger/customer_list.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /ABICO/login.php');
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
$stmt = $activityLog->readAll(0, 1000); // Get all logs for DataTable to handle pagination
$totalLogs = $activityLog->countAll();

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
                            <i class="fas fa-users"></i> Customer List
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
                    <table id="activityTable" class="table table-hover table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rowNumber = 0;
                            try {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                    $rowNumber++;
                                    $badge_class = 'info';
                                    if (strpos($row['action'], 'create') !== false) $badge_class = 'success';
                                    elseif (strpos($row['action'], 'update') !== false) $badge_class = 'warning';
                                    elseif (strpos($row['action'], 'delete') !== false) $badge_class = 'danger';
                                    
                                    // Format the time ago
                                    $timeAgo = time_elapsed_string($row['created_at']);
                                    
                                    // Format the description
                                    $description = htmlspecialchars($row['details']);
                                    if (!empty($row['customer_name'])) {
                                        $customerLink = '<a href="index.php?search=' . urlencode($row['customer_name']) . '">' . 
                                                      htmlspecialchars($row['customer_name']) . '</a>';
                                    } else {
                                        $customerLink = '<span class="text-muted">System</span>';
                                    }
                                    
                                    // Add value changes if available
                                    $valueInfo = '';
                                    if ($row['old_value'] !== null || $row['new_value'] !== null) {
                                        $oldVal = $row['old_value'] !== null ? '₱' . number_format($row['old_value'], 2) : 'N/A';
                                        $newVal = $row['new_value'] !== null ? '₱' . number_format($row['new_value'], 2) : 'N/A';
                                        if ($oldVal !== 'N/A' || $newVal !== 'N/A') {
                                            $valueInfo = '<div class="small text-muted">' . 
                                                       'Changed from ' . $oldVal . ' to ' . $newVal . 
                                                       '</div>';
                                        }
                                    }
                            ?>
                            <tr>
                                <td><?= $rowNumber ?></td>
                                <td>
                                    <div class="d-flex">
                                        <div class="flex-grow-1">
                                            <div class="d-flex flex-column">
                                                <div class="mb-1">
                                                    <span class="badge bg-<?= $badge_class ?> me-2">
                                                        <?= ucwords(str_replace('_', ' ', $row['action'])) ?>
                                                    </span>
                                                    <?= $customerLink ?>: <?= $description ?>
                                                    <?php if (!empty($row['update_type'])): ?>
                                                        <span class="badge bg-secondary"><?= ucfirst($row['update_type']) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?= $valueInfo ?>
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
                            <?php 
                                endwhile;
                            } catch (Exception $e) {
                                error_log('Error loading activity logs: ' . $e->getMessage());
                                $_SESSION['error_message'] = 'Failed to load activity logs. Please try again.';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<?php 
// Function to format time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

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
                className: 'text-center',
                width: '50px'
            },
            {
                targets: 2, // Date column
                className: 'text-nowrap',
                width: '180px'
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
