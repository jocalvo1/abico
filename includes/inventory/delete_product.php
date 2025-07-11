<?php
// Include required files
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/../database.php';

// Set headers
header('Content-Type: application/json');

// Initialize database connection
$database = new dbconn();
$db = $database->getConnection();

// Initialize Product object
$product = new product($db);

// Get posted data
$data = $_POST;

// Set product ID to be deleted
$product->id = $data['id'];

// Check if product exists
if(!$product->readOne()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found.'
    ]);
    exit();
}

// Delete the product
if($product->delete()){
    echo json_encode([
        'status' => 'success',
        'message' => 'Product was deleted successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to delete product.'
    ]);
}
?>
