<?php
// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Session.php';
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

// Initialize Customer object
$customer = new Customer($db);

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Handle new customer creation and debt updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['customer_name'])) {
        // Handle new customer creation
        $customer->customer_name = $_POST['customer_name'];
        $customer->contact = $_POST['contact'] ?? '';
        
        if ($customer->create()) {
            // Log the activity
            require_once __DIR__ . '/includes/ActivityLog.php';
            $activityLog = new ActivityLog($db);
            $activityLog->log($db, $_SESSION['user_id'], $db->lastInsertId(), 'create_customer', 'Added new customer: ' . $customer->customer_name);
            
            $_SESSION['success_message'] = "Customer added successfully!";
            // Redirect to prevent form resubmission
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error_message'] = "Failed to add customer. Please try again.";
        }
    } elseif (isset($_POST['update_debt'])) {
        // Handle debt update
        $customer->id = $_POST['customer_id'];
        $customer->debt = $_POST['debt'];
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        try {
            // Start transaction
            if ($db->beginTransaction() === false) {
                throw new Exception('Could not start transaction');
            }
            
            // Update the debt
            if (!$customer->updateDebt()) {
                throw new Exception('Failed to update customer debt');
            }
            
            // Log the activity
            require_once __DIR__ . '/includes/ActivityLog.php';
            $activityLog = new ActivityLog($db);
            $logResult = $activityLog->log(
                $db, 
                $_SESSION['user_id'] ?? 0, 
                $customer->id, 
                'update_debt', 
                'Updated customer debt',
                isset($_POST['old_debt']) ? (float)$_POST['old_debt'] : null,
                (float)$customer->debt,
                $_POST['update_type'] ?? null
            );
            
            if (!$logResult) {
                throw new Exception('Failed to log activity');
            }
            
            // Commit transaction
            if (!$db->commit()) {
                throw new Exception('Failed to commit transaction');
            }
            
            $response = [
                'success' => true,
                'message' => 'Debt updated successfully!',
                'debt' => number_format($customer->debt, 2)
            ];
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            } else {
                $_SESSION['success_message'] = $response['message'];
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            error_log('Error in update_debt: ' . $e->getMessage());
            $errorMessage = 'Failed to update debt. Please try again.';
            
            if ($isAjax) {
                header('Content-Type: application/json', true, 500);
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage,
                    'debug' => $e->getMessage()
                ]);
                exit();
            } else {
                $_SESSION['error_message'] = $errorMessage;
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit();
            }
        }
        
        if (!$isAjax) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Handle get customer for editing
if (isset($_GET['action']) && $_GET['action'] === 'get_customer' && isset($_GET['id'])) {
    $customer->id = $_GET['id'];
    if ($customer->readOne()) {
        header('Content-Type: application/json');
        echo json_encode([
            'id' => $customer->id,
            'customer_name' => $customer->customer_name,
            'debt' => $customer->debt
        ]);
        exit();
    }
    http_response_code(404);
    echo json_encode(['error' => 'Customer not found']);
    exit();
}

// Get all customers
$stmt = $customer->readAll($search);

// Include template header and sidebar
include __DIR__ . "/../templates/links.php";
include __DIR__ . "/../templates/sidebar.php";
?>

<div class="main-panel">
    <?php include __DIR__ . "/../templates/header.php"; ?>
    
    <div class="content">
        <div class="page-inner">
            <div class="page-header">
                <h4 class="page-title">Customer Ledger</h4>
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
                        <a href="#">Ledger</a>
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
                                            <a href="index.php" class="btn btn-outline-primary active">
                                                <i class="fas fa-users"></i> Customer List
                                            </a>
                                            <a href="activity_log.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-history"></i> Activity Logs
                                            </a>
                                        </div>
                                    </h4>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                        <i class="fa fa-plus"></i> Add Customer
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Success and error messages will be shown via SweetAlert -->
                            <?php 
                            if (isset($_SESSION['success_message'])): 
                                $success_message = $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                            ?>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: '<?php echo addslashes($success_message); ?>',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                });
                                </script>
                            <?php endif; ?>
                            
                            <?php 
                            if (isset($_SESSION['error_message'])): 
                                $error_message = $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                            ?>
                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: '<?php echo addslashes($error_message); ?>',
                                        showConfirmButton: true
                                    });
                                });
                                </script>
                            <?php endif; ?>
                            
                            <!-- Customers Table -->
                            <div class="table-responsive">
                                <table id="customerTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer Name</th>
                                            <th>Contact</th>
                                            <th class="text-right">Debt</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['contact']); ?></td>
                                                <td class="text-right">₱<?php echo number_format($row['debt'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn btn-warning btn-sm edit-debt-btn" 
                                                                data-id="<?php echo $row['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($row['customer_name']); ?>"
                                                                data-debt="<?php echo $row['debt']; ?>"
                                                                data-toggle="tooltip" title="Update Debt">
                                                            <i class="fa fa-edit"></i> Update Debt
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
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

    <!-- Footer -->
    <?php include __DIR__ . "/../templates/footer.php"; ?>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addCustomerForm" method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="customer_name">Customer Name *</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact">Contact Number</label>
                            <input type="text" class="form-control" id="contact" name="contact">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Debt Modal -->
    <div class="modal fade" id="updateDebtModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Customer Debt</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateDebtForm" method="POST" action="">
                    <input type="hidden" name="update_debt" value="1">
                    <input type="hidden" name="customer_id" id="debt_customer_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" class="form-control" id="debt_customer_name" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Current Debt (₱)</label>
                            <input type="text" class="form-control font-weight-bold" id="current_debt" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Update Type</label>
                            <div class="btn-group btn-group-toggle w-100 mb-3" data-toggle="buttons">
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="update_type" value="partial" checked> Partial Payment
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="update_type" value="full"> Pay in Full
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="update_type" value="adjust"> Adjust Balance
                                </label>
                            </div>
                        </div>
                        
                        <div id="partial_payment_section">
                            <div class="form-group">
                                <label for="payment_amount">Payment Amount (₱)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">-</span>
                                    </div>
                                    <input type="number" class="form-control" id="payment_amount" step="0.01" min="0.01" placeholder="Enter payment amount">
                                </div>
                                <small class="form-text text-muted">Amount to be deducted from current debt</small>
                            </div>
                            <div class="form-group">
                                <label for="new_debt">New Debt (₱)</label>
                                <input type="number" class="form-control font-weight-bold" id="new_debt" name="debt" readonly>
                            </div>
                        </div>
                        
                        <div id="adjust_balance_section" style="display: none;">
                            <div class="form-group">
                                <label for="new_balance">Set New Balance (₱)</label>
                                <input type="number" class="form-control" id="new_balance" step="0.01" min="0">
                                <small class="form-text text-muted">Enter the new total debt amount</small>
                            </div>
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Debt</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!--   Core JS Files   -->
<script src="/ABICO/assets/js/core/jquery-3.7.1.min.js"></script>
<script src="/ABICO/assets/js/core/popper.min.js"></script>
<!-- SweetAlert2 -->
<script src="/ABICO/assets/js/plugin/sweetalert/sweetalert2.all.min.js"></script>
<script src="/ABICO/assets/js/core/bootstrap.min.js"></script>
<script src="/ABICO/assets/js/plugin/datatables/datatables.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#customerTable').DataTable({
            "pageLength": 10,
            "order": [[0, "desc"]],
            "language": {
                "search": "",
                "searchPlaceholder": "Search customers...",
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
                { "orderable": false, "targets": -1 }
            ]
        });

                // Format number with 2 decimal places
        function formatCurrency(amount) {
            return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        // Handle edit debt button click
        $('.edit-debt-btn').on('click', function() {
            var customerId = $(this).data('id');
            var customerName = $(this).data('name');
            var currentDebt = parseFloat($(this).data('debt'));
            
            // Reset form and show loading state
            var form = $('#updateDebtForm')[0];
            form.reset();
            
            // Set values in the modal
            $('#debt_customer_id').val(customerId);
            $('#debt_customer_name').val(customerName);
            $('#current_debt').val('₱' + formatCurrency(currentDebt));
            $('#new_debt').val(currentDebt.toFixed(2));
            
            // Store original debt amount
            $('#new_debt').data('original-debt', currentDebt);
            
            // Set max payment amount and reset payment field
            $('#payment_amount').val('').attr('max', currentDebt);
            
            // Reset and show the correct section
            $('input[name="update_type"]').first().prop('checked', true).trigger('change');
            
            // Show the modal
            $('#updateDebtModal').modal('show');
        });
        
        // Handle update type change
        $('input[name="update_type"]').change(function() {
            var type = $(this).val();
            var currentDebt = parseFloat($('#new_debt').data('original-debt') || $('#current_debt').text().replace(/[^0-9.-]+/g,""));
            
            // Reset all inputs
            $('#payment_amount').val('');
            $('#new_balance').val('');
            
            if (type === 'full') {
                $('#partial_payment_section, #adjust_balance_section').hide();
                $('#new_debt').val('0.00');
            } else if (type === 'partial') {
                $('#partial_payment_section').show();
                $('#adjust_balance_section').hide();
                $('#payment_amount').val('').focus();
            } else if (type === 'adjust') {
                $('#partial_payment_section').hide();
                $('#adjust_balance_section').show();
                $('#new_balance').val(currentDebt.toFixed(2)).focus().select();
            }
        });
        
        // Auto-calculate new debt when payment amount changes
        $('#payment_amount').on('input', function() {
            var payment = parseFloat($(this).val()) || 0;
            var currentDebt = parseFloat($('#new_debt').data('original-debt'));
            
            if (payment > currentDebt) {
                $(this).val(currentDebt.toFixed(2));
                payment = currentDebt;
            }
            
            var newDebt = currentDebt - payment;
            $('#new_debt').val(newDebt.toFixed(2));
        });
        
        // Handle new balance input
        $('#new_balance').on('input', function() {
            var newBalance = parseFloat($(this).val()) || 0;
            if (newBalance < 0) newBalance = 0;
            $('#new_debt').val(newBalance.toFixed(2));
        });
        
        // Add CSS for button hover effects
        $('<style>').text(`
            .btn-group-toggle .btn {
                transition: all 0.3s ease;
            }
            .btn-group-toggle .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .btn-group-toggle .btn:active {
                transform: translateY(0);
            }
            .btn-group-toggle .btn.active {
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
        `).appendTo('head');
        
        // Handle form submission via AJAX
        $('#updateDebtForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var oldDebt = parseFloat($('#current_debt').val().replace(/[^0-9.-]+/g,""));
            var formData = form.serialize() + '&old_debt=' + oldDebt;
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.html();
            
            // Disable submit button and show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
            
            $.ajax({
                url: '',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response && response.success) {
                        $('#updateDebtModal').modal('hide');
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Debt updated successfully!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page to show updated data
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: (response && response.message) || 'Failed to update debt. Please try again.'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'An error occurred while updating debt.';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage
                    });
                },
                complete: function() {
                    // Re-enable submit button and restore original text
                    submitBtn.prop('disabled', false).html('Update Debt');
                }
            });
        });
    });
</script>
