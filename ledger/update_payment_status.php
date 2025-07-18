<?php
session_start();
require_once __DIR__ . '/../includes/database.php';

// Initialize database connection
$database = new dbconn();
$conn = $database->getConnection();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$transactionId = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';

// Ensure status is a string
$status = is_string($status) ? trim($status) : '';

if (!$transactionId || $status !== 'paid') {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid input parameters',
        'transaction_id' => $transactionId,
        'status' => $status
    ]);
    exit;
}

try {
    // Update the payment status directly
    $updateSql = "UPDATE transactions SET payment_status = 'paid' WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $result = $stmt->execute([$transactionId]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Transaction marked as paid successfully',
            'transaction_id' => $transactionId
        ]);
    } else {
        throw new Exception('Failed to update transaction status');
    }
    
} catch (Exception $e) {
    // Return error response
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update payment status',
        'error' => $e->getMessage()
    ]);
}
