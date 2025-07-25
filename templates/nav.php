<?php
$base = "/ABICO";
?>
<div class="main-panel">
    <div class="main-header">
        <div class="main-header-logo">
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
        <!-- Navbar Header -->
        <nav
        class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom"
        >
            <div class="container-fluid d-flex justify-content-end">
                <div class="current-time d-flex align-items-center">
                    <div class="text-center">
                        <div id="current-time" class="h4 mb-0 fw-bold"></div>
                        <div id="current-date" class="small text-muted"></div>
                    </div>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->
    </div>