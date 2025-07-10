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

// Set ID to be updated
$product->id = $data['id'];

// Check if product exists
if(!$product->readOne()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Product not found.'
    ]);
    exit();
}

// Set product property values
$product->product_name = $data['product_name'];
$product->description = $data['description'];
$product->quantity = $data['quantity'];
$product->price = $data['price'];

// Update the product
if($product->update()){
    echo json_encode([
        'status' => 'success',
        'message' => 'Product was updated successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to update product.'
    ]);
}
?>
