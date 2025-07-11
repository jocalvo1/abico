<?php
$base = "/ABICO";
?>
<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <div class="sidebar" data-background-color="dark">
      <div class="sidebar-logo">
        <!-- Logo Header -->
        <div class="logo-header" data-background-color="dark">
          <a href="<?= $base; ?>/index.php" class="logo">
            <img
              src="<?= $base; ?>/assets/img/kaiadmin/logo_light.svg"
              alt="navbar brand"
              class="navbar-brand"
              height="20"
            />
          </a>
          <div class="nav-toggle">
            <button class="btn btn-toggle toggle-sidebar">
              <i class="gg-menu-right"></i>
            </button>
            <button class="btn btn-toggle sidenav-toggler">
              <i class="gg-menu-left"></i>
            </button>
          </div>
          <button class="topbar-toggler more">
            <i class="gg-more-vertical-alt"></i>
          </button>
        </div>
        <!-- End Logo Header -->
      </div>
      <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
          <ul class="nav nav-secondary">
            <li class="nav-item">
              <a href="<?= $base; ?>/index.php">
                <i class="fas fa-home"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-section">
              <span class="sidebar-mini-icon">
                <i class="fa fa-ellipsis-h"></i>
              </span>
              <h4 class="text-section">Modules</h4>
            </li>

            <li class="nav-item">
              <a href="<?= $base; ?>/sales/index.php">
                <i class="fas fa-table"></i>
                <p>Sales</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="<?= $base; ?>/inventory/index.php">
                <i class="fas fa-layer-group"></i>
                <p>Inventory</p>
              </a>
            </li>
            
            <li class="nav-item">
              <a href="<?= $base; ?>/ledger/index.php">
                <i class="fas fa-pen-square"></i>
                <p>Ledger</p>
              </a>
            </li>
            <li class="nav-item">
<<<<<<< HEAD
              <a href="javascript:void(0)" class="text-danger" id="logoutBtn">
=======
              <a href="<?= $base; ?>/includes/session.php?destroy=1">
>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
                <i class="fa-solid fa-power-off"></i>
                <p>Logout</p>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
      <!-- End Sidebar -->
<<<<<<< HEAD

<!-- Include jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Then include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#logoutBtn').on('click', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Logout',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?= $base; ?>/includes/logout.php';
            }
        });
    });
});
</script>
=======
>>>>>>> b08138570596c522d62056973ef020fc9b47a02f
