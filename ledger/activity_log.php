<?php
// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
require_once __DIR__ . '/includes/ActivityLog.php';
require_once __DIR__ . '/includes/Customer.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /ABICO/login.php');
    exit();
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize ActivityLog object
$activityLog = new ActivityLog($db);

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$start = ($page > 1) ? ($page - 1) * $per_page : 0;

// Get total records
$total_records = $activityLog->countAll();
$total_pages = ceil($total_records / $per_page);

// Get activity logs
$stmt = $activityLog->readAll($start, $per_page);

// Include template header and sidebar
include __DIR__ . "/../templates/links.php";
include __DIR__ . "/../templates/sidebar.php";
?>

<div class="main-panel">
    <?php include __DIR__ . "/../templates/header.php"; ?>
    
    <div class="content">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title">Activity Logs</h4>
                <ul class="breadcrumbs">
                    <li class="nav-home">
                        <a href="/ABICO">
                            <i class="flaticon-home"></i>
                        </a>
                    </li>
                    <li class="separator">
                        <i class="flaticon-right-arrow"></i>
                    </li>
                    <li class="nav-item">
                        <a href="/ABICO/ledger/">Ledger</a>
                    </li>
                    <li class="separator">
                        <i class="flaticon-right-arrow"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#">Activity Logs</a>
                    </li>
                </ul>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
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
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="activityTable" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date & Time</th>
                                            <th>Action</th>
                                            <th>Customer</th>
                                            <th>Details</th>
                                            <th>Old Value</th>
                                            <th>New Value</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                                            <td>
                                                <?php 
                                                    $badge_class = 'info';
                                                    if (strpos($row['action'], 'create') !== false) $badge_class = 'success';
                                                    elseif (strpos($row['action'], 'update') !== false) $badge_class = 'warning';
                                                    elseif (strpos($row['action'], 'delete') !== false) $badge_class = 'danger';
                                                ?>
                                                <span class="badge badge-<?= $badge_class ?>">
                                                    <?= ucwords(str_replace('_', ' ', $row['action'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['customer_id']): ?>
                                                    <a href="/ABICO/ledger/?search=<?= urlencode($row['customer_name']) ?>">
                                                        <?= htmlspecialchars($row['customer_name']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    System
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['details']) ?></td>
                                            <td class="text-danger">
                                                <?= $row['old_value'] !== null ? '₱' . number_format($row['old_value'], 2) : '-' ?>
                                            </td>
                                            <td class="text-success">
                                                <?= $row['new_value'] !== null ? '₱' . number_format($row['new_value'], 2) : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($row['update_type']): ?>
                                                    <?= ucfirst($row['update_type']) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    Showing <?= ($start + 1) ?> to <?= min(($start + $per_page), $total_records) ?> of <?= $total_records ?> entries
                                </div>
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= ($page - 1) ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= ($page + 1) ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>

<!--   Core JS Files   -->
<script src="/ABICO/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/ABICO/assets/js/core/popper.min.js"></script>
<script src="/ABICO/assets/js/core/bootstrap.min.js"></script>
<script src="/ABICO/assets/js/plugin/datatables/datatables.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#activityTable').DataTable({
            "pageLength": 50,
            "order": [[0, "desc"]],
            "language": {
                "search": "",
                "searchPlaceholder": "Search logs...",
                "lengthMenu": "Show _MENU_ entries per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left'></i>",
                    "next": "<i class='fas fa-chevron-right'></i>"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [1, 2, 3, 4, 5, 6] }
            ]
        });
    });
</script>
