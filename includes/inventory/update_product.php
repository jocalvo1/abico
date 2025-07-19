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

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate required fields
    $requiredFields = ['id', 'product_name', 'unit_type', 'unit_value', 'stock_quantity', 'price_per_unit'];
    
    // Add pieces_per_pack to required fields if unit_type is 'pack'
    if (isset($_POST['unit_type']) && $_POST['unit_type'] === 'pack') {
        $requiredFields[] = 'pieces_per_pack';
    }
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || (is_string($_POST[$field]) && trim($_POST[$field]) === '')) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }

    // Sanitize and validate input
    $id = (int)$_POST['id'];
    $productName = trim($_POST['product_name']);
    $unitType = trim($_POST['unit_type']);
    $unitValue = (float)$_POST['unit_value'];
    $otherUnitType = isset($_POST['other_unit_type']) ? trim($_POST['other_unit_type']) : null;
    $piecesPerPack = ($unitType === 'pack' && isset($_POST['pieces_per_pack'])) ? (int)$_POST['pieces_per_pack'] : null;
    $lowStockThreshold = !empty($_POST['low_stock_threshold']) ? (int)$_POST['low_stock_threshold'] : null;
    $stockQuantity = (int)$_POST['stock_quantity'];
    $pricePerUnit = (float)$_POST['price_per_unit'];

    // Additional validation for custom unit type
    if ($unitType === 'other' && empty($otherUnitType)) {
        throw new Exception('Custom unit type is required when "Other" is selected');
    }
    
    if ($unitType === 'pack' && (empty($piecesPerPack) || $piecesPerPack <= 0)) {
        throw new Exception('Please specify a valid number of pieces per pack');
    }

    if ($unitValue <= 0) {
        throw new Exception('Unit value must be greater than 0');
    }

    if ($lowStockThreshold !== null && $lowStockThreshold < 0) {
        throw new Exception('Low stock threshold cannot be negative');
    }

    if ($stockQuantity < 0) {
        throw new Exception('Stock quantity must be a positive number');
    }

    if ($pricePerUnit < 0) {
        throw new Exception('Price per unit must be a positive number');
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
    $product->unit_type = $unitType === 'other' ? $otherUnitType : $unitType;
    $product->unit_value = $unitValue;
    $product->other_unit_type = $unitType === 'other' ? $otherUnitType : null;
    $product->pieces_per_pack = $piecesPerPack;
    $product->stock_quantity = $stockQuantity;
    $product->price_per_unit = $pricePerUnit;
    $product->low_stock_threshold = $lowStockThreshold;

    // Get the current product data before update
    $current_product = new Product($db);
    $current_product->id = $product->id;
    $current_product->readOne();

    // Attempt to update the product
    if ($product->update()) {
        // Log the activity
        $activityLog = new ActivityLog($db);
        $activityLog->user_id = $_SESSION['user_id'];
        $activityLog->action = 'update';
        $activityLog->description = 'Updated product details';
        $activityLog->product_id = $product->id;
        $activityLog->product_name = $product->product_name;
        
        // Prepare old and new values for comparison
        $old_values = [];
        $new_values = [];
        
        $fields_to_compare = [
            'product_name', 'description', 'price', 'price_per_unit', 'stock_quantity',
            'unit_type', 'unit_value', 'other_unit_type', 'pieces_per_pack'
        ];
        
        foreach ($fields_to_compare as $field) {
            if (isset($current_product->$field) && isset($product->$field) && 
                $current_product->$field != $product->$field) {
                $old_values[$field] = $current_product->$field;
                $new_values[$field] = $product->$field;
            }
        }
        
        if (!empty($old_values) && !empty($new_values)) {
            $activityLog->old_values = $old_values;
            $activityLog->new_values = $new_values;
            $activityLog->create();
        }
        
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
