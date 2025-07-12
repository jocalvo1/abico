<?php
session_start();
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/session.php';

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
    <!-- Ledger Card -->
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
          <h4 class="card-title fw-bold m-0">Ledger</h4>
          <button class="btn btn-primary btn-round">
            <i class="fas fa-plus me-2"></i>Add new customer
          </button>
        </div>
      </div>
      <div class="card-body">
        <p class="text-muted">Ledger content will go here.</p>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . "/../templates/footer.php"; ?>
</div>
  <!--   Core JS Files   -->
  <?php include __DIR__ . "/../templates/scripts.php"; ?>
</body>
</html>