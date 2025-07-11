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
                <td>&#8369; 1,200.00</td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="#" class="btn btn-warning btn-sm"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>
                  </div>
                </td>
              </tr>
              <tr>
                <td>2</td>
                <td>TRX-789012</td>
                <td>Jan 11, 2023</td>
                <td>Jane Doe</td>
                <td>&#8369; 1,500.00</td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="#" class="btn btn-warning btn-sm"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>
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

<!-- DataTables Script -->
<script>
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
