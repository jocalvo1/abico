<?php
require_once __DIR__ . '/../includes/database.php';

// Initialize database connection
$database = new dbconn();
$conn = $database->getConnection();

try {
    $stmt = $conn->query("SHOW COLUMNS FROM transactions LIKE 'payment_status'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Add the payment_status column
        $conn->exec("ALTER TABLE transactions 
                    ADD COLUMN payment_status ENUM('paid', 'unpaid') DEFAULT 'unpaid' 
                    AFTER is_debt");
        echo "Successfully added payment_status column to transactions table.\n";
    } else {
        echo "payment_status column already exists in transactions table.\n";
    }
    
    // Verify the column was added
    $stmt = $conn->query("SHOW COLUMNS FROM transactions LIKE 'payment_status'");
    if ($stmt->rowCount() > 0) {
        echo "Verification: payment_status column is present.\n";
    } else {
        echo "Error: Failed to verify payment_status column.\n";
    }
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}

echo "Script completed.\n";
