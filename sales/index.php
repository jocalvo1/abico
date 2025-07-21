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
?>

<?php include __DIR__ . "/../templates/header.php"; ?>
<?php 
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
            <table id="salesTable" class="table table-hover" style="width:100%">
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
        </div>
      </div>
    </div>

  </div>
</div>
<?php include __DIR__ . "/../templates/footer.php"; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">

<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom scripts -->
<script>
$(document).ready(function() {
    // Initialize tooltips
    const initTooltips = () => {
        $('[data-bs-toggle="tooltip"]').tooltip();
    };
    
    // Format date for display
    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    };
    
    // Format currency
    const formatCurrency = (amount) => {
        const value = parseFloat(amount);
        return '₱' + (isNaN(value) ? '0.00' : value.toFixed(2));
    };
    
    // Initialize DataTable with enhanced configuration
    const initDataTable = () => {
        // Destroy existing DataTable instance if it exists
        if ($.fn.DataTable.isDataTable('#salesTable')) {
            $('#salesTable').DataTable().destroy();
        }

        const table = $('#salesTable').DataTable({
            // Basic configuration
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            stateSave: true, // Save state (pagination, search, etc.)
            stateDuration: 60 * 60 * 24, // 24 hours
            order: [[1, 'desc']], // Sort by created_at (index 1) in descending order
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
            
            // Language configuration
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                search: '',
                searchPlaceholder: 'Search transactions...',
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
            
            // AJAX configuration with enhanced error handling
            ajax: {
                url: "../includes/sales/transaction_history.php",
                type: "POST",
                data: (d) => {
                    // Add any additional data you want to send with each request
                    return d;
                },
                dataSrc: function(json) {
                    if (json.error) {
                        console.error('Server error:', json.error);
                        return [];
                    }
                    return json.data || [];
                },
                error: (xhr, error, thrown) => {
                    console.error('DataTables AJAX Error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                    
                    // Show error message to user
                    let errorMsg = 'Failed to load data. ';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.status === 0) {
                        errorMsg = 'Network error. Please check your connection.';
                    } else {
                        errorMsg = 'Failed to load transaction data. Please try again.';
                    }
                    
                    // Show error in console
                    console.error('DataTables error:', errorMsg);
                    
                    // Show error to user using SweetAlert if available
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                    
                    // Clear and redraw the table
                    const table = $('#salesTable').DataTable();
                    table.clear().draw();
                }
            },
            
            // Column definitions
            columns: [
                { 
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    width: '60px',
                    render: function(data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
                    }
                },
                { 
                    data: 'created_at',
                    orderable: true,
                    width: '150px',
                    type: 'date',
                    render: function(data, type, row) {
                        if (type === 'sort' || type === 'type') {
                            return data; // Return raw data for sorting
                        }
                        return formatDate(data);
                    }
                },
                { 
                    data: 'customer_name',
                    orderable: true,
                    width: '25%'
                },
                { 
                    data: 'item_count',
                    orderable: true,
                    className: 'text-center',
                    width: '80px'
                },
                { 
                    data: 'total_amount',
                    orderable: true,
                    className: 'text-end',
                    width: '120px',
                    render: formatCurrency
                },
                { 
                    data: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    width: '100px',
                    render: function(data, type, row) {
                        return `
                            <a href="view.php?id=${data}" 
                               class="btn btn-sm btn-outline-primary view-transaction"
                               title="View transaction details"
                               data-bs-toggle="tooltip"
                               data-bs-placement="top">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                        `;
                    }
                }
            ],
            
            // Language configuration
            language: {
                search: '_INPUT_',
                searchPlaceholder: 'Search transactions...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'No entries found',
                infoFiltered: '(filtered from _MAX_ total entries)',
                zeroRecords: 'No matching records found',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            
            // DOM layout
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            
            // Callbacks
            initComplete: function() {
                // Style DataTable elements
                $('.dataTables_filter input')
                    .addClass('form-control mb-3')
                    .attr('placeholder', 'Search transactions...');
                    
                $('.dataTables_length select')
                    .addClass('form-select mb-3');
                
                // Style pagination
                $('.dataTables_paginate')
                    .addClass('mt-3');
                
                // Make table header sticky on scroll
                $('.dataTables_scrollHead')
                    .css('position', 'sticky')
                    .css('top', '0')
                    .css('z-index', '5');
                
                // Add hover effect to view buttons
                $('.view-transaction')
                    .on('mouseenter', function() {
                        $(this).addClass('btn-primary').removeClass('btn-outline-primary');
                    })
                    .on('mouseleave', function() {
                        $(this).removeClass('btn-primary').addClass('btn-outline-primary');
                    });
                
                initTooltips();
                console.log('Sales DataTable initialized successfully');
            },
            
            drawCallback: function() {
                initTooltips();
                
                // Re-attach hover effects after table redraw
                $('.view-transaction')
                    .off('mouseenter mouseleave')
                    .on('mouseenter', function() {
                        $(this).addClass('btn-primary').removeClass('btn-outline-primary');
                    })
                    .on('mouseleave', function() {
                        $(this).removeClass('btn-primary').addClass('btn-outline-primary');
                    });
            }
        });
        
        return table;
    };
    
    // Initialize everything when document is ready
    $(() => {
        console.log('Document ready, initializing DataTable...');
        initTooltips();
        try {
            const salesTable = initDataTable();
            console.log('DataTable initialized:', salesTable);
        } catch (error) {
            console.error('Error initializing DataTable:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Initialization Error',
                    text: 'Failed to initialize the data table. Please check console for details.'
                });
            }
        }
    });
});
</script>

<?php include __DIR__ . "/../templates/scripts.php"; ?>
</body>
</html>