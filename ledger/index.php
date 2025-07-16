<?php
// Include required files
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';
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

// Initialize Customer object
$customer = new Customer($db);

// Handle new customer creation and debt updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['customer_name'])) {
        // Handle new customer creation
        $customer->customer_name = $_POST['customer_name'];
        $customer->contact = $_POST['contact'] ?? '';
        
        if ($customer->create()) {
            // Log the activity
            require_once __DIR__ . '/../includes/ledger/activity_log.php';
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
            require_once __DIR__ . '/../includes/ledger/activity_log.php';
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
$stmt = $customer->readAll();

include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>

<div class="container">
    <div class="page-inner">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
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

                <button class="btn btn-primary btn-round" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                    <i class="fas fa-plus me-2"></i>Add Customer
                </button>
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
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset the statement pointer to the beginning
                            $stmt->execute();
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                                $statusClass = $row['debt'] > 0 ? 'text-danger' : 'text-success';
                                $statusIcon = 'fa-circle';
                                $statusText = $row['debt'] > 0 ? 'Has Debt' : 'Paid';
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td style="position: relative;">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span style="vertical-align: middle; flex-grow: 1;"><?php echo htmlspecialchars($row['customer_name']); ?></span>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <i class="fas <?php echo $statusIcon; ?> <?php echo $statusClass; ?>" 
                                                   style="font-size: 0.7em; vertical-align: middle;"></i>
                                                <button class="btn btn-info ms-2 btn-sm view-customer" 
                                                    data-id="<?php echo $row['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['customer_name']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($row['contact']); ?>"
                                                    data-debt="<?php echo $row['debt']; ?>"
                                                    data-created="<?php echo date('M d, Y', strtotime($row['created_at'])); ?>"
                                                    data-status="<?php echo $statusText; ?>"
                                                    data-status-class="<?php echo $statusClass; ?>"
                                                    data-status-icon="<?php echo $statusIcon; ?>">
                                                    view record
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Customer Details Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h4 id="customerName" class="mb-3"></h4>
                    <p><strong>Contact:</strong> <span id="customerContact"></span></p>
                    <p><strong>Current Debt:</strong> <span id="customerDebt" class="font-weight-bold"></span></p>
                    <p><strong>Date Added:</strong> <span id="customerCreated"></span></p>
                    <p><strong>Status:</strong> <span id="customerStatus"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-debt-btn" data-bs-toggle="modal" data-bs-target="#updateDebtModal">
                    <i class="fa fa-edit"></i> Update Debt
                </button>
                <a href="#" class="btn btn-info view-history-btn">
                    <i class="fa fa-history"></i> View History
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Customer</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Debt</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Initialize modals
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
    });
    
    // Handle view customer details
    $(document).on('click', '.view-customer', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var contact = $(this).data('contact');
        var debt = parseFloat($(this).data('debt')).toFixed(2);
        var created = $(this).data('created');
        var status = $(this).data('status');
        var statusClass = $(this).data('status-class');
        var statusIcon = $(this).data('status-icon');
        
        // Update modal content
        $('#customerName').text(name);
        $('#customerContact').text(contact || 'N/A');
        $('#customerDebt').text('₱' + debt);
        $('#customerCreated').text(created);
        $('#customerStatus').html('<i class="fas ' + statusIcon + ' ' + statusClass + ' me-2"></i>' + status);
        
        // Update edit button
        $('.edit-debt-btn')
            .data('id', id)
            .data('name', name)
            .data('debt', debt);
        
        // Show the modal
        $('#viewCustomerModal').modal('show');
    });

    // Add DataTables CSS if not already included
    if ($('link[href*="dataTables.bootstrap5"]').length === 0) {
        $('head').append('<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">');
    }

    $(document).ready(function() {
        // Initialize DataTable with custom sorting
        var table = $('#customerTable').DataTable({
            responsive: true,
            order: [[2, 'desc']], // Sort by date added by default
            columnDefs: [
                {
                    targets: 0, // # column
                    orderable: false,
                    className: 'text-center',
                    width: '50px',
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { 
                    targets: 1, // Customer Name column
                    className: 'text-nowrap'
                },
                {
                    targets: 2, // Date column
                    className: 'text-nowrap',
                    width: '180px'
                },
                { 
                    targets: -1, // Actions column
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '100px'
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries found",
                infoFiltered: '(filtered from _MAX_ total entries)',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
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
        
        // Make table header sticky on scroll
        $('.dataTables_scrollHead').css('position', 'sticky').css('top', '0').css('z-index', '5');
        
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

</body>
</html>
