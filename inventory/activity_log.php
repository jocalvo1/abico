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

// Get all activity logs
$logs = $activityLog->readAll(1, 1000); // Get all logs for DataTables to handle pagination

// Include template header and sidebar
include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>
    
<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title mb-0">
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-users"></i> Inventory
                            </a>
                            <a href="activity_log.php" class="btn btn-outline-primary active">
                                <i class="fas fa-history"></i> Activity Logs
                            </a>
                        </div>
                    </h4>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="activityTable" class="table table-hover table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date & Time</th>
                                <th>Action</th>
                                <th>Product</th>
                                <th>Details</th>
                                <th>Qty Change</th>
                                <th>Price Change</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $count = 1; ?>
                        <?php while ($row = $logs->fetch(PDO::FETCH_ASSOC)): 
                            $action_badge = '';
                            $action_icon = '';
                            $action_class = '';
                            
                            switch($row['action']) {
                                case 'create_product':
                                    $action_badge = 'Created';
                                    $action_icon = 'plus-circle';
                                    $action_class = 'success';
                                    break;
                                case 'update_product':
                                    $action_badge = 'Updated';
                                    $action_icon = 'edit';
                                    $action_class = 'primary';
                                    break;
                                case 'delete_product':
                                    $action_badge = 'Deleted';
                                    $action_icon = 'trash-alt';
                                    $action_class = 'danger';
                                    break;
                                case 'stock_in':
                                    $action_badge = 'Stock In';
                                    $action_icon = 'arrow-down';
                                    $action_class = 'success';
                                    break;
                                case 'stock_out':
                                    $action_badge = 'Stock Out';
                                    $action_icon = 'arrow-up';
                                    $action_class = 'warning';
                                    break;
                                default:
                                    $action_badge = ucwords(str_replace('_', ' ', $row['action']));
                                    $action_icon = 'info-circle';
                                    $action_class = 'secondary';
                            }
                            
                            $qty_change = '';
                            if ($row['old_quantity'] !== null && $row['new_quantity'] !== null) {
                                $diff = $row['new_quantity'] - $row['old_quantity'];
                                if ($diff > 0) {
                                    $qty_change = '<span class="text-success">+' . $diff . '</span>';
                                } elseif ($diff < 0) {
                                    $qty_change = '<span class="text-danger">' . $diff . '</span>';
                                } else {
                                    $qty_change = '<span class="text-muted">-</span>';
                                }
                            }
                            
                            $price_change = '';
                            if ($row['old_price'] !== null && $row['new_price'] !== null) {
                                $price_diff = $row['new_price'] - $row['old_price'];
                                if ($price_diff > 0) {
                                    $price_change = '<span class="text-success">+₱' . number_format($price_diff, 2) . '</span>';
                                } elseif ($price_diff < 0) {
                                    $price_change = '<span class="text-danger">-₱' . number_format(abs($price_diff), 2) . '</span>';
                                } else {
                                    $price_change = '<span class="text-muted">-</span>';
                                }
                            }
                        ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td data-order="<?= strtotime($row['created_at']) ?>">
                                <?= date('M j, Y h:i A', strtotime($row['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $action_class ?>">
                                    <i class="fas fa-<?= $action_icon ?> me-1"></i>
                                    <?= $action_badge ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['product_name']): ?>
                                    <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="text-primary">
                                        <?= htmlspecialchars(mb_strimwidth($row['product_name'], 0, 30, "...")) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Product #<?= $row['product_id'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= nl2br(htmlspecialchars(mb_strimwidth($row['details'], 0, 30, "..."))) ?></td>
                            <td class="text-center"><?= $qty_change ?: '<span class="text-muted">-</span>' ?></td>
                            <td class="text-center"><?= $price_change ?: '<span class="text-muted">-</span>' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#activityTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "order": [[1, "desc"]],
        "responsive": true,
        "language": {
            "search": "",
            "searchPlaceholder": "Search logs..."
        },
        "columnDefs": [
            { "orderable": false, "targets": [0] },
            { "className": "text-center", "targets": [5, 6] }
        ]
    });
    
    // Style search input
    $('.dataTables_filter input').addClass('form-control form-control-sm');
});
</script>

</body>
</html>