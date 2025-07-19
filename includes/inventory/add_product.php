<?php
session_start();
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/activity_log_product.php';

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
    try {
        // Initialize database connection
        $database = new dbconn();
        $db = $database->getConnection();
        
        // Get and validate form data
        $product_name = trim($_POST['product_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $price_per_unit = (float)($_POST['price'] ?? 0);
        $unit_type = trim($_POST['unit_type'] ?? '');
        $unit_value = (float)($_POST['unit_value'] ?? 1);
        $other_unit_type = trim($_POST['other_unit_type'] ?? '');
        $pieces_per_pack = ($unit_type === 'pack' && !empty($_POST['pieces_per_pack'])) ? (int)$_POST['pieces_per_pack'] : null;
        $low_stock_threshold = !empty($_POST['low_stock_threshold']) ? (int)$_POST['low_stock_threshold'] : null;

        // Basic validation
        $errors = [];
        
        if (empty($product_name)) {
            $errors[] = 'Product name is required';
        }
        
        if ($quantity < 0) {
            $errors[] = 'Quantity cannot be negative';
        }
        
        if ($price_per_unit < 0) {
            $errors[] = 'Price cannot be negative';
        }
        
        if ($low_stock_threshold !== null && $low_stock_threshold < 0) {
            $errors[] = 'Low stock threshold cannot be negative';
        }
        
        if (empty($unit_type)) {
            $errors[] = 'Unit type is required';
        } elseif ($unit_type === 'other' && empty($other_unit_type)) {
            $errors[] = 'Please specify a custom unit type';
        }
        
        if ($unit_type === 'pack' && (empty($pieces_per_pack) || $pieces_per_pack <= 0)) {
            $errors[] = 'Please specify a valid number of pieces per pack';
        }
        
        if ($unit_value <= 0) {
            $errors[] = 'Unit value must be greater than 0';
        }
        
        // If there are validation errors, show them
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }
        
        // If unit_type is 'other', use the custom unit type
        $final_unit_type = ($unit_type === 'other') ? $other_unit_type : $unit_type;
        
        // Initialize Product object
        $product = new product($db);
        
        // Set product property values
        $product->product_name = $product_name;
        $product->description = $description;
        $product->stock_quantity = $quantity;
        $product->price_per_unit = $price_per_unit;
        $product->unit_type = $final_unit_type;
        $product->unit_value = $unit_value;
        $product->pieces_per_pack = $pieces_per_pack;
        $product->low_stock_threshold = $low_stock_threshold;
        
        // If other_unit_type was provided, store it
        if ($unit_type === 'other') {
            $product->other_unit_type = $other_unit_type;
        }
        
        // Attempt to create the product
        if ($product->create()) {
            // Log the activity
            $activityLog = new ActivityLog($db);
            $activityLog->user_id = $_SESSION['user_id'];
            $activityLog->action = 'add';
            $activityLog->description = 'Added new product';
            $activityLog->product_id = $db->lastInsertId();
            $activityLog->product_name = $product->product_name;
            $activityLog->old_values = null;
            $activityLog->new_values = [
                'product_name' => $product->product_name,
                'description' => $product->description,
                'price' => $product->price,
                'price_per_unit' => $product->price_per_unit,
                'stock_quantity' => $product->stock_quantity,
                'unit_type' => $product->unit_type,
                'unit_value' => $product->unit_value,
                'other_unit_type' => $product->other_unit_type ?? null,
                'pieces_per_pack' => $product->pieces_per_pack ?? null
            ];
            $activityLog->create();
            
            // Set success message in session for SweetAlert
            $_SESSION['alert'] = [
                'title' => 'Success!',
                'message' => 'Product has been added successfully!',
                'icon' => 'success'
            ];
        } else {
            throw new Exception('Unable to add product. Please try again.');
        }
        
    } catch (Exception $e) {
        // Set error message in session for SweetAlert
        $_SESSION['alert'] = [
            'title' => 'Error!',
            'message' => $e->getMessage(),
            'icon' => 'error'
        ];
    }
}

// Redirect back to the inventory page
header('Location: /abico/inventory/index.php');
exit();
?>
