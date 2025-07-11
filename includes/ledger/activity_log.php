<?php
class ActivityLog {
    private $conn;
    private $table_name = "activity_logs";

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
                    update_type = :update_type";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->details = htmlspecialchars(strip_tags($this->details));
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":customer_id", $this->customer_id);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":details", $this->details);
        $stmt->bindParam(":old_value", $this->old_value);
        $stmt->bindParam(":new_value", $this->new_value);
        $stmt->bindParam(":update_type", $this->update_type);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all activity logs with optional filters
    public function readAll($start = 0, $limit = 10, $customer_id = null) {
        $query = "SELECT l.*, c.customer_name 
                 FROM " . $this->table_name . " l 
                 LEFT JOIN customers c ON l.customer_id = c.id 
                 WHERE 1=1";
        
        if($customer_id) {
            $query .= " AND l.customer_id = :customer_id";
        }
        
        $query .= " ORDER BY l.created_at DESC 
                  LIMIT :start, :limit";
        
        $stmt = $this->conn->prepare($query);
        
        if($customer_id) {
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        }
        
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }

    // Get total count of logs
    public function countAll($customer_id = null) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE 1=1";
        
        if($customer_id) {
            $query .= " AND customer_id = :customer_id";
        }
        
        $stmt = $this->conn->prepare($query);
        
        if($customer_id) {
            $stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
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
