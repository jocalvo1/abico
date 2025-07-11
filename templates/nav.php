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
        <div class="container-fluid">
            <div class="current-time d-flex align-items-center">
            <div class="text-center">
                <div id="current-time" class="h4 mb-0 fw-bold"></div>
                <div id="current-date" class="small text-muted"></div>
            </div>
            </div>

            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
            
            <li class="nav-item topbar-icon dropdown hidden-caret">
                <a
                class="nav-link dropdown-toggle"
                href="#"
                id="messageDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                >
                <i class="fa fa-envelope"></i>
                </a>
                <ul
                class="dropdown-menu messages-notif-box animated fadeIn"
                aria-labelledby="messageDropdown"
                >
                <li>
                    <div
                    class="dropdown-title d-flex justify-content-between align-items-center"
                    >
                    Messages
                    <a href="#" class="small">Mark all as read</a>
                    </div>
                </li>
                <li>
                    <div class="message-notif-scroll scrollbar-outer">
                    <div class="notif-center">
                        <a href="#">
                        <div class="notif-img">
                            <img
                            src="assets/img/jm_denis.jpg"
                            alt="Img Profile"
                            />
                        </div>
                        <div class="notif-content">
                            <span class="subject">Jimmy Denis</span>
                            <span class="block"> How are you ? </span>
                            <span class="time">5 minutes ago</span>
                        </div>
                        </a>
                        <a href="#">
                        <div class="notif-img">
                            <img
                            src="assets/img/chadengle.jpg"
                            alt="Img Profile"
                            />
                        </div>
                        <div class="notif-content">
                            <span class="subject">Chad</span>
                            <span class="block"> Ok, Thanks ! </span>
                            <span class="time">12 minutes ago</span>
                        </div>
                        </a>
                        <a href="#">
                        <div class="notif-img">
                            <img
                            src="assets/img/mlane.jpg"
                            alt="Img Profile"
                            />
                        </div>
                        <div class="notif-content">
                            <span class="subject">Jhon Doe</span>
                            <span class="block">
                            Ready for the meeting today...
                            </span>
                            <span class="time">12 minutes ago</span>
                        </div>
                        </a>
                        <a href="#">
                        <div class="notif-img">
                            <img
                            src="assets/img/talha.jpg"
                            alt="Img Profile"
                            />
                        </div>
                        <div class="notif-content">
                            <span class="subject">Talha</span>
                            <span class="block"> Hi, Apa Kabar ? </span>
                            <span class="time">17 minutes ago</span>
                        </div>
                        </a>
                    </div>
                    </div>
                </li>
                <li>
                    <a class="see-all" href="javascript:void(0);"
                    >See all messages<i class="fa fa-angle-right"></i>
                    </a>
                </li>
                </ul>
            </li>
            </ul>
        </div>
        </nav>
        <!-- End Navbar -->
    </div>