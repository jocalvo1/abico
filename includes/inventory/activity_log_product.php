<?php
class ActivityLog {
    private $conn;
    private $table_name = "inventory_activity_logs";

    // Object properties
    public $id;
    public $product_id;
    public $action;
    public $details;
    public $old_quantity;
    public $new_quantity;
    public $old_price;
    public $new_price;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new activity log
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET product_id = :product_id,
                    action = :action,
                    details = :details,
                    old_quantity = :old_quantity,
                    new_quantity = :new_quantity,
                    old_price = :old_price,
                    new_price = :new_price";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->product_id = htmlspecialchars(strip_tags($this->product_id));
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->details = htmlspecialchars(strip_tags($this->details));
        
        // Bind values
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":details", $this->details);
        $stmt->bindParam(":old_quantity", $this->old_quantity);
        $stmt->bindParam(":new_quantity", $this->new_quantity);
        $stmt->bindParam(":old_price", $this->old_price);
        $stmt->bindParam(":new_price", $this->new_price);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all activity logs with pagination
    public function readAll($page = 1, $records_per_page = 10) {
        $start = ($page - 1) * $records_per_page;
        
        $query = "SELECT l.*, p.product_name
                 FROM " . $this->table_name . " l
                 LEFT JOIN products p ON l.product_id = p.id
                 ORDER BY l.created_at DESC
                 LIMIT :start, :records_per_page";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Count total number of activity logs
    public function countAll() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    // Static method to log activities
    public static function log($db, $product_id, $action, $details, $old_quantity = null, $new_quantity = null, $old_price = null, $new_price = null) {
        $activityLog = new self($db);
        $activityLog->product_id = $product_id;
        $activityLog->action = $action;
        $activityLog->details = $details;
        $activityLog->old_quantity = $old_quantity;
        $activityLog->new_quantity = $new_quantity;
        $activityLog->old_price = $old_price;
        $activityLog->new_price = $new_price;
        
        return $activityLog->create();
    }
}
?>
