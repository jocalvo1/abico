<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/product.php';

// Initialize session
$session = new Session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    header('Location: /abico/login.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize database connection
    $database = new dbconn();
    $db = $database->getConnection();
    
    // Initialize Product object
    $product = new product($db);
    
    // Set product property values
    $product->product_name = $_POST['product_name'] ?? '';
    $product->description = $_POST['description'] ?? '';
    $product->quantity = $_POST['quantity'] ?? 0;
    $product->price = $_POST['price'] ?? 0;
    
    // Attempt to create the product
    if($product->create()) {
        // Set success message in session for SweetAlert
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Success!',
            'message' => 'Product has been added successfully!',
            'icon' => 'success'
        ];
    } else {
        // Set error message in session for SweetAlert
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Error!',
            'message' => 'Unable to add product. Please try again.',
            'icon' => 'error'
        ];
    }
}

// Redirect back to the inventory page
header('Location: /abico/inventory/index.php');
exit();
?>
