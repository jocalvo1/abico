<?php
session_start();
require_once 'includes/database.php';
require_once 'includes/session.php';

$session = new session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
header('Location: login.php');
exit();
}

include __DIR__ . "/templates/header.php";
include __DIR__ . "/templates/sidebar.php";
include __DIR__ . "/templates/nav.php";
?>
  <div class="container">
    <div class="page-inner">
      <div
        class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
      >
        <div>
          <h3 class="fw-bold mb-3">Dashboard</h3>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
          <a href="inventory/index.php" class="btn btn-label-info btn-round me-2">View Inventory</a>
          <a href="sales/index.php" class="btn btn-primary btn-round">New Transaction</a>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 col-md-3">
          <a href="inventory/index.php">
            <div class="card card-stats card-round">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-icon">
                    <div
                      class="icon-big text-center icon-primary bubble-shadow-small"
                    >
                      <i class="fas fa-stream"></i>
                    </div>
                  </div>
                  <div class="col col-stats ms-3 ms-sm-0">
                    <div class="numbers">
                      <p class="card-category">Stocks (Current)</p>
                      <h4 class="card-title">1,294</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-sm-6 col-md-3">
          <a href="sales/history.php">
            <div class="card card-stats card-round">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-icon">
                    <div
                      class="icon-big text-center icon-info bubble-shadow-small"
                    >
                      <i class="fas fa-luggage-cart"></i>
                    </div>
                  </div>
                  <div class="col col-stats ms-3 ms-sm-0">
                    <div class="numbers">
                      <p class="card-category">Sales (Weekly)</p>
                      <h4 class="card-title">P1,345</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-sm-6 col-md-3">
          <a href="sales/history.php">
            <div class="card card-stats card-round">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-icon">
                    <div
                      class="icon-big text-center icon-secondary bubble-shadow-small"
                    >
                      <i class="far fa-check-circle"></i>
                    </div>
                  </div>
                  <div class="col col-stats ms-3 ms-sm-0">
                    <div class="numbers">
                      <p class="card-category">Orders (Weekly)</p>
                      <h4 class="card-title">576</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
        <div class="col-sm-6 col-md-3">
          <a href="inventory/low_stock.php">
            <div class="card card-stats card-round">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-icon">
                    <div
                      class="icon-big text-center icon-warning bubble-shadow-small"
                    >
                      <i class="fas fa-exclamation-triangle"></i>
                    </div>
                  </div>
                  <div class="col col-stats ms-3 ms-sm-0">
                    <div class="numbers">
                      <p class="card-category">Low Stock Items</p>
                      <h4 class="card-title">12</h4>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php include __DIR__ . "/templates/footer.php"; ?>
</div>
  <!--   Core JS Files   -->
  <?php include __DIR__ . "/templates/scripts.php"; ?>
</body>
</html>
