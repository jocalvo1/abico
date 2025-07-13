<?php
session_start();
header('Content-Type: application/json');

// Include required files
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/activity_log_product.php';

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

    // Get product details before deletion
    $product_to_delete = new Product($db);
    $product_to_delete->id = $product->id;
    $product_to_delete->readOne();

    // Attempt to delete the product
    try {
        // First, log the activity
        $activityLog = new ActivityLog($db);
        $activityLog->user_id = $_SESSION['user_id'];
        $activityLog->action = 'delete';
        $activityLog->description = 'Removed product: ' . $product_to_delete->product_name;
        $activityLog->product_id = $product->id;
        $activityLog->product_name = $product_to_delete->product_name;
        $activityLog->old_values = [
            'product_name' => $product_to_delete->product_name,
            'description' => $product_to_delete->description ?? '',
            'price' => $product_to_delete->price ?? 0,
            'price_per_unit' => $product_to_delete->price_per_unit ?? 0,
            'stock_quantity' => $product_to_delete->stock_quantity ?? 0,
            'unit_type' => $product_to_delete->unit_type ?? '',
            'unit_value' => $product_to_delete->unit_value ?? 0,
            'other_unit_type' => $product_to_delete->other_unit_type ?? null,
            'pieces_per_pack' => $product_to_delete->pieces_per_pack ?? null
        ];
        $activityLog->new_values = null;
        
        // Save the activity log first
        if (!$activityLog->create()) {
            throw new Exception('Failed to create activity log entry');
        }
        
        // Then delete the product
        if ($product->delete()) {
            $response['success'] = true;
            $response['message'] = "Product '{$productName}' has been deleted successfully.";
        } else {
            // If delete fails, but we've already logged the activity, we have a problem
            throw new Exception('Product deletion failed after logging the activity');
        }
    } catch (Exception $e) {
        // Log the detailed error
        error_log('Delete Product Error: ' . $e->getMessage());
        
        // Check if it's a database error
        if ($db->errorInfo()) {
            $errorInfo = $db->errorInfo();
            error_log('Database Error: ' . $errorInfo[2]);
            throw new Exception('Database error occurred. Please try again. ' . $errorInfo[2]);
        }
        
        throw new Exception('Failed to delete product: ' . $e->getMessage());
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
