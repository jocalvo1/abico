<?php
// Include required files
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../../config/database.php';

// Set headers
header('Content-Type: application/json');

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize Product object
$product = new Product($db);

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
