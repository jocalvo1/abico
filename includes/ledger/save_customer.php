<?php
// Start the session
session_start();

// Check if user is logged in
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Include database and customer class
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/customer_list.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate input
$customer_name = trim($_POST['customer_name'] ?? '');
$contact = trim($_POST['contact'] ?? '');

if (empty($customer_name)) {
    echo json_encode(['success' => false, 'message' => 'Customer name is required']);
    exit;
}

try {
    // Initialize database connection
    $database = new dbconn();
    $db = $database->getConnection();
    
    // Create customer object
    $customer = new Customer($db);
    $customer->customer_name = $customer_name;
    $customer->contact = $contact;
    
    // Create the customer
    if ($customer->create()) {
        // Get the newly created customer
        $customer->id = $db->lastInsertId();
        if ($customer->readOne()) {
            echo json_encode([
                'success' => true,
                'message' => 'Customer added successfully',
                'customer' => [
                    'id' => $customer->id,
                    'customer_name' => $customer->customer_name,
                    'contact' => $customer->contact,
                    'debt' => $customer->debt
                ]
            ]);
        } else {
            throw new Exception('Failed to retrieve created customer');
        }
    } else {
        throw new Exception('Failed to create customer');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
