<?php
class TransactionHistory {
    private $conn;
    private $transactions_table = "transactions";
    private $items_table = "sale_items";
    private $products_table = "products";
    private $customers_table = "customers";
    private $payment_methods_table = "payment_methods";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all transactions for a customer
    public function getCustomerTransactions($customer_id) {
        $query = "SELECT 
                    t.id, 
                    t.created_at,
                    t.total_amount,
                    t.payment_method_id,
                    t.is_debt,
                    t.payment_status,
                    t.remaining_balance,
                    pm.name as payment_method,
                    CASE 
                        WHEN t.payment_status = 'paid' AND t.is_debt = 1 THEN 'Paid (Was Debt)'
                        WHEN t.payment_status = 'paid' THEN 'Paid'
                        WHEN t.is_debt = 1 THEN 'Unpaid (Debt)'
                        ELSE 'Unpaid'
                    END as display_status
                FROM " . $this->transactions_table . " t
                LEFT JOIN " . $this->payment_methods_table . " pm ON t.payment_method_id = pm.id
                WHERE t.customer_id = :customer_id
                ORDER BY t.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":customer_id", $customer_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get items for a specific transaction
    public function getTransactionItems($transaction_id) {
        $query = "SELECT 
                    si.quantity,
                    si.price,
                    si.subtotal,
                    p.product_name,
                    p.unit_type as unit
                FROM " . $this->items_table . " si
                JOIN " . $this->products_table . " p ON si.product_id = p.id
                WHERE si.transaction_id = :transaction_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":transaction_id", $transaction_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get customer details
    public function getCustomerDetails($customer_id) {
        $query = "SELECT id, customer_name, contact FROM " . $this->customers_table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $customer_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
