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

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get logs and total count
$logs = $activityLog->readAll($page, $perPage);
$totalLogs = $activityLog->countAll();
$totalPages = ceil($totalLogs / $perPage);

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
                    Showing <?= $offset + 1 ?> - <?= min($offset + count($logs), $totalLogs) ?> of <?= $totalLogs ?> entries
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="activityTable" class="table table-hover table-striped" style="width:100%">
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
                            $rowNumber = $offset + $index + 1;
                            $description = $activityLog->formatDescription($row);
                            $timeAgo = $activityLog->timeElapsedString($row['created_at']);
                        ?>
                        <tr>
                            <td><?= $rowNumber ?></td>
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add DataTables and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with custom settings
    $('#activityTable').DataTable({
        "pageLength": 5,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "order": [[3, 'desc']],
        "columnDefs": [
            { "orderable": false, "targets": [0] }
        ],
        "language": {
            "search": "Search:",
            "searchPlaceholder": "Search by customer name, description...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "No entries found",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
               "<'row'<'col-sm-12'tr>>" +
               "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
});
</script>

<?php
include __DIR__ . '/../templates/footer.php';
?>