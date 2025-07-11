<?php
class Customer {
    private $conn;
    private $table_name = "customers";

    public $id;
    public $customer_name;
    public $contact;
    public $debt;
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
                     created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->customer_name = htmlspecialchars(strip_tags($this->customer_name));
        $this->contact = htmlspecialchars(strip_tags($this->contact));

        // Bind values
        $stmt->bindParam(":customer_name", $this->customer_name);
        $stmt->bindParam(":contact", $this->contact);

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
    public function updateDebt() {
        $query = "UPDATE " . $this->table_name . " 
                 SET debt = :debt,
                     updated_at = NOW()
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->debt = (float)$this->debt;
        $this->id = (int)$this->id;

        // Bind values
        $stmt->bindParam(":debt", $this->debt);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Get single customer by ID
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
            return true;
        }
        return false;
    }
}
?>
