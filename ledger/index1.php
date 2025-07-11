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
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
      <div>
        <h3 class="fw-bold mb-3">Ledger</h3>
      </div>
      <div class="ms-md-auto py-2 py-md-0">
        <button class="btn btn-primary btn-round">
          <i class="fas fa-plus me-2"></i>New Entry
        </button>
      </div>
    </div>
    
    <!-- Ledger Content Here -->
    <div class="card">
      <div class="card-body">
        <p class="text-muted">Ledger content will go here.</p>
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