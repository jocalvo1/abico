<?php
  $base = "/ABICO";
?>
<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <!-- Logo Header -->
    <div class="logo-header" data-background-color="dark">
      <a href="<?= $base; ?>/index.php" class="logo">
        <img
          src="/ABICO/assets/img/kaiadmin/logo_light.svg"
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
          <a href="/ABICO/l/index.php">
            <i class="fa-solid fa-power-off"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>

