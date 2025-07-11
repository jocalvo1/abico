<?php
session_start();
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/product.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if user is logged in
    $session = new Session();
    $session->init();
    
    if (!$session->get('login')) {
        throw new Exception('Unauthorized access');
    }

    // Check if ID is provided
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('Product ID is required');
    }

    $productId = (int)$_POST['id'];

    // Initialize database connection
    $database = new dbconn();
    $db = $database->getConnection();

    // Initialize Product object
    $product = new product($db);
    $product->id = $productId;

    // Check if product exists
    if (!$product->readOne()) {
        throw new Exception('Product not found');
    }

    // Store product name for the success message
    $productName = $product->product_name;

    // Attempt to delete the product
    if ($product->delete()) {
        $response['success'] = true;
        $response['message'] = "Product '{$productName}' has been deleted successfully.";
    } else {
        throw new Exception('Failed to delete product');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
