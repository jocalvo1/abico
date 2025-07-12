<?php
class Transaction {
    private $conn;
    private $table_name = "transactions";
    private $items_table = "sale_items";

    public $id;
    public $customer_id;
    public $customer_name;
    public $total_amount;
    public $payment_method;
    public $amount_received;
    public $change_amount;
    public $created_at;
    public $items = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get payment method ID by name
    private function getPaymentMethodId($methodName) {
        $query = "SELECT id FROM payment_methods WHERE name = :name LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $methodName);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    }

    // Create a new transaction
    public function create() {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Get payment method ID
            $paymentMethodId = $this->getPaymentMethodId($this->payment_method);
            if (!$paymentMethodId) {
                throw new Exception("Invalid payment method");
            }

            // Insert transaction
            $query = "INSERT INTO " . $this->table_name . " 
                     (customer_id, customer_name, total_amount, payment_method_id, amount_received, change_amount, created_at)
                     VALUES (:customer_id, :customer_name, :total_amount, :payment_method_id, :amount_received, :change_amount, NOW())";
            
            $stmt = $this->conn->prepare($query);

            // Sanitize and bind values
            $this->customer_id = $this->customer_id ?: null;
            $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
            $this->total_amount = (float)$this->total_amount;
            $this->amount_received = (float)$this->amount_received;
            $this->change_amount = (float)$this->change_amount;

            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":payment_method_id", $paymentMethodId, PDO::PARAM_INT);
            $stmt->bindParam(":amount_received", $this->amount_received);
            $stmt->bindParam(":change_amount", $this->change_amount);

            if (!$stmt->execute()) {
                throw new Exception("Failed to create transaction");
            }

            $this->id = $this->conn->lastInsertId();

            // Insert transaction items
            if (!empty($this->items)) {
                $itemQuery = "INSERT INTO " . $this->items_table . " 
                            (transaction_id, product_id, product_name, quantity, price, subtotal)
                            VALUES ";
                
                $values = [];
                $params = [];
                $i = 0;
                
                foreach ($this->items as $item) {
                    $values[] = "(:transaction_id, :product_id_$i, :product_name_$i, :quantity_$i, :price_$i, :subtotal_$i)";
                    
                    $params[":product_id_$i"] = $item['id'];
                    $params[":product_name_$i"] = $item['name'];
                    $params[":quantity_$i"] = $item['quantity'];
                    $params[":price_$i"] = $item['price'];
                    $params[":subtotal_$i"] = $item['total'];
                    
                    $i++;
                }
                
                $itemQuery .= implode(", ", $values);
                $itemStmt = $this->conn->prepare($itemQuery);
                
                // Bind transaction ID
                $itemStmt->bindValue(":transaction_id", $this->id);
                
                // Bind item parameters
                foreach ($params as $key => $value) {
                    $itemStmt->bindValue($key, $value);
                }
                
                if (!$itemStmt->execute()) {
                    throw new Exception("Failed to add transaction items");
                }
                
                // Update product quantities
                foreach ($this->items as $item) {
                    $updateQuery = "UPDATE products SET quantity = quantity - :quantity WHERE id = :product_id";
                    $updateStmt = $this->conn->prepare($updateQuery);
                    $updateStmt->bindParam(":quantity", $item['quantity'], PDO::PARAM_INT);
                    $updateStmt->bindParam(":product_id", $item['id'], PDO::PARAM_INT);
                    
                    if (!$updateStmt->execute()) {
                        throw new Exception("Failed to update product quantity");
                    }
                }
            }

            // Commit transaction
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }

    // Get transaction by ID
    public function readOne() {
        $query = "SELECT t.*, 
                 GROUP_CONCAT(CONCAT(si.product_name, ' (', si.quantity, ' x ₱', FORMAT(si.price, 2), ')') SEPARATOR ', ') as items_list,
                 COUNT(si.id) as items_count
                 FROM " . $this->table_name . " t
                 LEFT JOIN " . $this->items_table . " si ON t.id = si.transaction_id
                 WHERE t.id = ?
                 GROUP BY t.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
