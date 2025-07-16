<?php
class ActivityLog {
    private $conn;
    private $table_name = "ledger_activity_log";

    public $id;
    public $user_id;
    public $customer_id;
    public $action;
    public $details;
    public $old_value;
    public $new_value;
    public $update_type;
    public $ip_address;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create activity log
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id = :user_id,
                    customer_id = :customer_id,
                    action = :action,
                    details = :details,
                    old_value = :old_value,
                    new_value = :new_value,
                    update_type = :update_type,
                    created_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->customer_id = $this->customer_id !== null ? htmlspecialchars(strip_tags($this->customer_id)) : null;
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->details = $this->details !== null ? htmlspecialchars(strip_tags($this->details)) : null;
        
        // Bind values with proper null handling
        $stmt->bindValue(":user_id", $this->user_id, PDO::PARAM_INT);
        $stmt->bindValue(":customer_id", $this->customer_id, $this->customer_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(":action", $this->action, PDO::PARAM_STR);
        $stmt->bindValue(":details", $this->details, $this->details !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(":old_value", $this->old_value, $this->old_value !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(":new_value", $this->new_value, $this->new_value !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(":update_type", $this->update_type, $this->update_type !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        
        try {
            if($stmt->execute()) {
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error creating activity log: " . $e->getMessage());
            return false;
        }
    }

    // Read all activity logs with optional filters
    public function readAll($start = 0, $limit = 10, $customer_id = null) {
        $query = "SELECT l.*, c.customer_name 
                 FROM " . $this->table_name . " l 
                 LEFT JOIN customers c ON l.customer_id = c.id 
                 WHERE 1=1";
        
        $params = [];
        
        if($customer_id) {
            $query .= " AND l.customer_id = :customer_id";
            $params[':customer_id'] = $customer_id;
        }
        
        $query .= " ORDER BY l.created_at DESC 
                  LIMIT :start, :limit";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error reading activity logs: " . $e->getMessage());
            return false;
        }
    }

    // Get total count of logs
    public function countAll($customer_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        $params = [];
        
        if($customer_id) {
            $query .= " AND customer_id = :customer_id";
            $params[':customer_id'] = $customer_id;
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        
        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::PARAM_NULL);
            return $row ? (int)$row['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error counting activity logs: " . $e->getMessage());
            return 0;
        }
    }

    // Log a new activity
    public static function log($db, $user_id, $customer_id, $action, $details = '', $old_value = null, $new_value = null, $update_type = null) {
        $activity = new self($db);
        $activity->user_id = $user_id;
        $activity->customer_id = $customer_id;
        $activity->action = $action;
        $activity->details = $details;
        $activity->old_value = $old_value;
        $activity->new_value = $new_value;
        $activity->update_type = $update_type;
        
        return $activity->create();
    }
}
