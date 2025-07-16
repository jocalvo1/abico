<?php
// Disable error reporting to prevent HTML output
error_reporting(0);
ini_set('display_errors', 0);

// Start the session
session_start();

// Check if user is logged in
if(!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Include database and required classes
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/sales/transaction.php';

// Set content type to JSON
header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');

// Validate JSON input
if (empty($json)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Decode JSON data
$data = json_decode($json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data: ' . json_last_error_msg()
    ]);
    exit;
}

// Validate required fields
if (empty($data['items']) || !isset($data['customer_name']) || !isset($data['payment_method'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

try {
    // Initialize database connection
    $database = new dbconn();
    $db = $database->getConnection();
    
    // Create new transaction
    $transaction = new Transaction($db);
    
    // Set transaction properties
    $transaction->customer_id = $data['customer_id'] ?? null;
    $transaction->customer_name = $data['customer_name'];
    $transaction->total_amount = $data['total_amount'];
    $transaction->payment_method = $data['payment_method'];
    $transaction->amount_received = $data['amount_received'];
    $transaction->change_amount = $data['change_amount'];
    $transaction->items = $data['items'];
    
    // Process the transaction
    if ($transaction->create()) {
        // Get the created transaction details
        $transactionDetails = $transaction->readOne();
        
        // Return success response with transaction details
        echo json_encode([
            'success' => true,
            'transaction_id' => $transaction->id,
            'transaction' => $transactionDetails,
            'message' => 'Transaction completed successfully'
        ]);
    } else {
        throw new Exception('Failed to process transaction');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing payment: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing payment: ' . $e->getMessage()
    ]);
}
?>
