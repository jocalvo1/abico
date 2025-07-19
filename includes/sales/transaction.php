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
    public $is_debt = false;
    public $remaining_balance = 0.00;
    public $payment_status = 'unpaid';
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

    // Log sales activity
    private function logSalesActivity() {
        $query = "INSERT INTO sales_activity_log (transaction_id, customer_name, description) 
                 VALUES (:transaction_id, :customer_name, :description)";
        
        $stmt = $this->conn->prepare($query);
        
        // Count the number of items
        $item_count = count($this->items);
        
        // Build the description
        $description = "Customer {$this->customer_name} bought {$item_count} items for ₱" . number_format($this->total_amount, 2);
        
        // Bind parameters
        $stmt->bindParam(":transaction_id", $this->id);
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":description", $description);
        
        // Execute the query
        return $stmt->execute();
    }

    // Update customer's debt balance
    private function updateCustomerDebt($customerId, $amount) {
        if (!$customerId) return false;
        
        $query = "UPDATE customers SET debt = GREATEST(0, COALESCE(debt, 0) + :amount) WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":amount", $amount);
        $stmt->bindParam(":id", $customerId);
        return $stmt->execute();
    }

    // Create a new transaction
    public function create() {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Calculate remaining balance if this is a debt transaction
            $this->remaining_balance = $this->is_debt ? 
                max(0, $this->total_amount - $this->amount_received) : 0;
                
            // Set transaction status
            if ($this->amount_received >= $this->total_amount) {
                $this->payment_status = 'paid';
            } elseif ($this->is_debt && $this->remaining_balance > 0) {
                $this->payment_status = 'partially_paid';
            } else {
                $this->payment_status = 'unpaid';
            }

            // Insert into transactions table
            $query = "INSERT INTO " . $this->table_name . " 
                     SET customer_id = :customer_id,
                         customer_name = :customer_name,
                         total_amount = :total_amount,
                         payment_method = :payment_method,
                         payment_method_id = :payment_method_id,
                         is_debt = :is_debt,
                         remaining_balance = :remaining_balance,
                         amount_received = :amount_received,
                         change_amount = :change_amount,
                         payment_status = :payment_status";

            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
            $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
            $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
            $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
            $this->amount_received = htmlspecialchars(strip_tags($this->amount_received));
            $this->change_amount = htmlspecialchars(strip_tags($this->change_amount));

            // Get payment method ID
            $payment_method_id = $this->getPaymentMethodId($this->payment_method);

            // Bind data
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":customer_name", $this->customer_name);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":payment_method", $this->payment_method);
            $stmt->bindParam(":payment_method_id", $payment_method_id);
            $stmt->bindParam(":is_debt", $this->is_debt, PDO::PARAM_BOOL);
            $stmt->bindParam(":remaining_balance", $this->remaining_balance);
            $stmt->bindParam(":amount_received", $this->amount_received);
            $stmt->bindParam(":change_amount", $this->change_amount);
            $stmt->bindParam(":payment_status", $this->payment_status);

            // Execute query
            if($stmt->execute()) {
                // Get inserted ID
                $this->id = $this->conn->lastInsertId();

                // Insert sale items
                foreach($this->items as $item) {
                    $query = "INSERT INTO " . $this->items_table . " 
                             SET transaction_id = :transaction_id,
                                 product_id = :product_id,
                                 product_name = :product_name,
                                 quantity = :quantity,
                                 price = :price,
                                 subtotal = :subtotal";

                    $stmt = $this->conn->prepare($query);

                    // Clean data
                    $item['product_id'] = htmlspecialchars(strip_tags($item['product_id']));
                    $item['product_name'] = htmlspecialchars(strip_tags($item['product_name']));
                    $item['quantity'] = htmlspecialchars(strip_tags($item['quantity']));
                    $item['price'] = htmlspecialchars(strip_tags($item['price']));
                    $item['subtotal'] = htmlspecialchars(strip_tags($item['subtotal']));

                    // Bind data
                    $stmt->bindParam(":transaction_id", $this->id);
                    $stmt->bindParam(":product_id", $item['product_id']);
                    $stmt->bindParam(":product_name", $item['product_name']);
                    $stmt->bindParam(":quantity", $item['quantity']);
                    $stmt->bindParam(":price", $item['price']);
                    $stmt->bindParam(":subtotal", $item['subtotal']);

                    // Execute query
                    if(!$stmt->execute()) {
                        throw new Exception("Error inserting sale item");
                    }
                }

                // Log sales activity
                if(!$this->logSalesActivity()) {
                    throw new Exception("Error logging sales activity");
                }

                // Update customer's debt balance if this is a debt transaction
                if ($this->is_debt && $this->customer_id) {
                    if (!$this->updateCustomerDebt($this->customer_id, $this->remaining_balance)) {
                        throw new Exception("Failed to update customer's debt balance");
                    }
                }

                // Commit transaction
                $this->conn->commit();

                return true;
            }

            return false;            // Start transaction
            $this->conn->beginTransaction();

            // Get payment method ID
            $paymentMethodId = $this->getPaymentMethodId($this->payment_method);
            if (!$paymentMethodId) {
                throw new Exception("Invalid payment method");
            }

            // Insert transaction
            $query = "INSERT INTO " . $this->table_name . " 
                     (customer_id, customer_name, total_amount, payment_method_id, payment_method, amount_received, change_amount, created_at)
                     VALUES (:customer_id, :customer_name, :total_amount, :payment_method_id, :payment_method, :amount_received, :change_amount, NOW())";
            
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
            $stmt->bindParam(":payment_method", $this->payment_method);
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
                    $updateQuery = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id";
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
