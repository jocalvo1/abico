<?php
class Customer {
    private $conn;
    private $table_name = "customers";

    public $id;
    public $customer_name;
    public $contact;
    public $debt;
    public $notes;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new customer
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET customer_name = :customer_name, 
                     contact = :contact,
                     debt = 0,
                     notes = :notes,
                     created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->contact = htmlspecialchars(strip_tags($this->contact));
        $this->notes = !empty($this->notes) ? htmlspecialchars(strip_tags($this->notes)) : null;

        // Bind values
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":contact", $this->contact);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all customers
    public function readAll($search = '') {
        $query = "SELECT * FROM " . $this->table_name;
        
        if(!empty($search)) {
            $query .= " WHERE customer_name LIKE :search OR contact LIKE :search";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if(!empty($search)) {
            $search = "%" . $search . "%";
            $stmt->bindParam(':search', $search);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Update customer debt
    public function updateDebt($notes = null) {
        // Get current debt before update
        $current_debt = 0;
        $current_notes = '';
        $get_current = "SELECT debt, notes FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt_current = $this->conn->prepare($get_current);
        $stmt_current->bindParam(":id", $this->id, PDO::PARAM_INT);
        if ($stmt_current->execute()) {
            $row = $stmt_current->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $current_debt = (float)$row['debt'];
                $current_notes = $row['notes'] ?? '';
            }
        }

        // Sanitize input
        $new_debt = (float)$this->debt;
        $this->id = (int)$this->id;
        $notes = !empty($notes) ? htmlspecialchars(strip_tags($notes)) : null;
        
        // Update the debt and notes
        $query = "UPDATE " . $this->table_name . " 
                 SET debt = :debt,
                     notes = :notes,
                     updated_at = NOW()
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":debt", $new_debt);
        $stmt->bindParam(":notes", $notes);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            // Log the activity with both debt update and notes
            $this->logDebtUpdate($this->id, $current_debt, $new_debt, $notes);
            return true;
        }
        return false;
    }
    
    // Log debt update activity
    private function logDebtUpdate($customerId, $oldDebt, $newDebt, $notes = null) {
        try {
            require_once __DIR__ . '/activity_log.php';
            
            // Determine the payment status
            $status = '';
            if ($newDebt == 0) {
                $status = 'Fully Paid';
            } elseif ($newDebt < $oldDebt) {
                $status = 'Partial Payment';
            } else {
                $status = 'Debt Updated';
            }
            
            // Create details with status and amount change
            $details = $status . ' - ';
            $amountChanged = abs($newDebt - $oldDebt);
            
            if ($newDebt == 0) {
                $details .= "Paid in full (₱" . number_format($oldDebt, 2) . ")";
            } elseif ($newDebt < $oldDebt) {
                $details .= "Paid ₱" . number_format($amountChanged, 2) . " of ₱" . number_format($oldDebt, 2) . ". ";
                $details .= "Remaining balance: ₱" . number_format($newDebt, 2);
            } else {
                $details .= "Updated debt from ₱" . number_format($oldDebt, 2) . " to ₱" . number_format($newDebt, 2);
            }
            
            // Add notes to details if provided
            if (!empty($notes)) {
                $details .= ". Notes: " . $notes;
            }
            
            // Log the activity
            return ActivityLog::log(
                $this->conn,
                $_SESSION['user_id'] ?? 0,
                $customerId,
                'update_debt',
                $details,
                $oldDebt,
                $newDebt,
                $status  // Using update_type to store the status
            );
            
        } catch (Exception $e) {
            error_log('Error logging debt update: ' . $e->getMessage());
            return false;
        }
    }
    
    // This method is no longer needed as we've combined the logging
    // into the updateDebt method
    
    // Read single customer
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->customer_name = $row['customer_name'];
            $this->contact = $row['contact'];
            $this->debt = $row['debt'];
            $this->notes = $row['notes'] ?? null;
            $this->created_at = $row['created_at'];
            return true;
        }
        
        return false;
    }
}
?>