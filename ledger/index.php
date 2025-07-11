<?php
session_start();
require_once 'includes/session.php';
require_once 'includes/database.php';

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


        <?php include __DIR__ . "/templates/footer.php"; ?>
      </div>
    </div>
    <!--   Core JS Files   -->
    <?php include __DIR__ . "/templates/scripts.php"; ?>
  </body>
</html>
