<?php
session_start();
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/database.php';

$session = new session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: ../login.php');
    exit();
}

include __DIR__ . "/../templates/header.php";
include __DIR__ . "/../templates/sidebar.php";
include __DIR__ . "/../templates/nav.php";
?>
<div class="container">
  <div class="page-inner">
    <!-- Sales Content Here -->
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title fw-bold mb-0">Transaction History</h3>
          <a href="create.php" class="btn btn-primary btn-round"><i class="fas fa-plus me-2"></i>New Transaction</a>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="salesTable" class="table table-hover table-striped" style="width:100%">
            <thead>
              <tr>
                <th>#</th>
                <th>ID</th>
                <th>Date</th>
                <th>Name</th>
                <th>items</th>
                <th>Total</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>1</td>
                <td>TRX-123456</td>
                <td>Jan 12, 2023</td>
                <td>John Doe</td>
                <td>4</td>
                <td>&#8369; 1,200.00</td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="view.php?id=TRX-123456" class="btn btn-warning btn-sm" title="View Details"><i class="fas fa-eye"></i></a>
                    <button class="btn btn-danger btn-sm btn-void" data-id="TRX-123456" data-name="John Doe&apos;s transaction"><i class="fas fa-times"></i></button>
                  </div>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>TRX-789012</td>
                <td>Jan 11, 2023</td>
                <td>Jane Doe</td>
                <td>2</td>
                <td>&#8369; 1,500.00</td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="view.php?id=TRX-123456" class="btn btn-warning btn-sm" title="View Details"><i class="fas fa-eye"></i></a>
                    <button class="btn btn-danger btn-sm btn-void" data-id="TRX-123456" data-name="John Doe&apos;s transaction"><i class="fas fa-times"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
</div>
</div>
<!--   Core JS Files   -->
<?php include __DIR__ . "/../templates/scripts.php"; ?>

<!-- Void Confirmation Modal -->
<div class="modal fade" id="voidModal" tabindex="-1" role="dialog" aria-labelledby="voidModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="voidModalLabel">Confirm Void Transaction</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to void this transaction?</p>
        <p><strong>Transaction ID:</strong> <span id="voidTransactionId"></span></p>
        <p><strong>Customer:</strong> <span id="voidCustomerName"></span></p>
        <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmVoid">Yes, Void Transaction</button>
      </div>
    </div>
  </div>
</div>

<!-- DataTables Script -->
<script>
// Handle void button click
$(document).on('click', '.btn-void', function() {
    const transactionId = $(this).data('id');
    const customerName = $(this).data('name');
    
    $('#voidTransactionId').text(transactionId);
    $('#voidCustomerName').text(customerName);
    $('#voidModal').modal('show');
});

// Handle confirm void
$('#confirmVoid').click(function() {
    const transactionId = $('#voidTransactionId').text();
    
    // Show loading state
    const $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Voiding...');
    
    // In a real application, you would make an AJAX call here to void the transaction
    // For now, we'll just simulate a successful response
    setTimeout(function() {
        // Simulate API call
        console.log('Voiding transaction:', transactionId);
        
        // Show success message
        $('#voidModal').modal('hide');
        
        // Show success alert
        const alertHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Transaction ${transactionId} has been voided successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        
        // Insert alert at the top of the card body
        $('.card-body').prepend(alertHtml);
        
        // Remove the row from the table
        $(`button[data-id="${transactionId}"]`).closest('tr').fadeOut(400, function() {
            $(this).remove();
            // If you're using DataTables, you might need to redraw the table
            if ($.fn.DataTable.isDataTable('#salesTable')) {
                $('#salesTable').DataTable().draw(false);
            }
        });
        
        // Reset button state
        $btn.prop('disabled', false).text('Yes, Void Transaction');
    }, 1000);
});

$(document).ready(function() {
    $('#salesTable').DataTable({
        responsive: true,
        order: [[1, 'desc']], // Sort by date column (index 1) in descending order
        columnDefs: [
            { orderable: false, targets: [4] } // Disable sorting on Action column
        ],
        language: {
            search: "Search transactions:",
            searchPlaceholder: "Search by ID, customer...",
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
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
});
</script>
</body>
</html>


