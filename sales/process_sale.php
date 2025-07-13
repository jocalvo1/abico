<?php
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
$data = json_decode($json, true);

// Validate input
if (empty($data) || !isset($data['items']) || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
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
        'message' => 'Error: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
