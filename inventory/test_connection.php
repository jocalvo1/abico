<?php
// Include required files
require_once __DIR__ . '/../config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Check connection
if ($db) {
    echo "Database connection successful!<br><br>";
    
    // Test query to list products
    $query = "SELECT * FROM products LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    echo "Found " . $stmt->rowCount() . " products:<br>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " - " . $row['product_name'] . " (Qty: " . $row['quantity'] . ", Price: $" . $row['price'] . ")<br>";
    }
} else {
    echo "Failed to connect to database.";
}
?>
