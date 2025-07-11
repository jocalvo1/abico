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

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $requiredFields = ['id', 'product_name', 'quantity', 'price'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }

    // Sanitize and validate input
    $id = (int)$_POST['id'];
    $productName = trim($_POST['product_name']);
    $description = trim($_POST['description'] ?? '');
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    if ($quantity < 0) {
        throw new Exception('Quantity must be a positive number');
    }

    if ($price < 0) {
        throw new Exception('Price must be a positive number');
    }

    // Initialize database connection
    $database = new dbconn();
    $db = $database->getConnection();

    // Initialize Product object
    $product = new product($db);
    $product->id = $id;

    // Check if product exists
    if (!$product->readOne()) {
        throw new Exception('Product not found');
    }

    // Update product properties
    $product->product_name = $productName;
    $product->description = $description;
    $product->quantity = $quantity;
    $product->price = $price;

    // Attempt to update the product
    if ($product->update()) {
        $response['success'] = true;
        $response['message'] = 'Product updated successfully';
    } else {
        throw new Exception('Failed to update product');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>

// Set headers
header('Content-Type: application/json');

// Initialize database connection
$database = new dbconn();
$db = $database->getConnection();

// Initialize Product object
$product = new product($db);

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
