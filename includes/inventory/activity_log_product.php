<?php
class ActivityLog {
    // Database connection and table name
    private $conn;
    private $table_name = "inventory_activity_log";

    // Object properties
    public $id;
    public $user_id;
    public $action;
    public $description;
    public $product_id;
    public $product_name;
    public $old_values;
    public $new_values;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new activity log
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET user_id = :user_id,
                    action = :action,
                    description = :description,
                    product_id = :product_id,
                    product_name = :product_name,
                    old_values = :old_values,
                    new_values = :new_values,
                    created_at = NOW()";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->action = htmlspecialchars(strip_tags($this->action));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->product_id = $this->product_id ? htmlspecialchars(strip_tags($this->product_id)) : null;
        $this->product_name = $this->product_name ? htmlspecialchars(strip_tags($this->product_name)) : null;
        
        // Convert arrays to JSON
        $this->old_values = $this->old_values ? json_encode($this->old_values, JSON_PRETTY_PRINT) : null;
        $this->new_values = $this->new_values ? json_encode($this->new_values, JSON_PRETTY_PRINT) : null;
        
        // Bind values
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":action", $this->action);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":old_values", $this->old_values);
        $stmt->bindParam(":new_values", $this->new_values);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read activity logs with pagination
    public function readAll($page = 1, $records_per_page = 10) {
        $start = ($page - 1) * $records_per_page;
        
        $query = "SELECT l.*, u.username 
                 FROM " . $this->table_name . " l
                 LEFT JOIN users u ON l.user_id = u.id
                 ORDER BY l.created_at DESC
                 LIMIT :start, :records_per_page";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start", $start, PDO::PARAM_INT);
        $stmt->bindParam(":records_per_page", $records_per_page, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    public static function log($db, $user_id, $action, $description, $product_id = null, $product_name = null, $old_values = null, $new_values = null) {
        $activityLog = new self($db);
        $activityLog->user_id = $user_id;
        $activityLog->action = $action;
        $activityLog->description = $description;
        $activityLog->product_id = $product_id;
        $activityLog->product_name = $product_name;
        $activityLog->old_values = $old_values;
        $activityLog->new_values = $new_values;
        
        return $activityLog->create();
    }

    // Get action class for styling
    public function getActionClass($action) {
        switch (strtolower($action)) {
            case 'create':
            case 'add':
            case 'stock_in':
                return [
                    'bg' => 'success',
                    'text' => 'success',
                    'icon' => 'plus-circle'
                ];
            case 'update':
            case 'edit':
            case 'modify':
                return [
                    'bg' => 'warning',
                    'text' => 'warning',
                    'icon' => 'edit'
                ];
            case 'delete':
            case 'remove':
                return [
                    'bg' => 'danger',
                    'text' => 'danger',
                    'icon' => 'trash-alt'
                ];
            default:
                return [
                    'bg' => 'info',
                    'text' => 'info',
                    'icon' => 'info-circle'
                ];
        }
    }

    // Format description for display
    public function formatDescription($log) {
        $action = strtolower($log['action']);
        $product = !empty($log['product_name']) ? '"' . htmlspecialchars($log['product_name']) . '"' : 'a product';
        
        switch ($action) {
            case 'create':
            case 'add':
                return "Added new product: $product";
            case 'update':
            case 'edit':
                return "Updated product: $product";
            case 'delete':
            case 'remove':
                return "Removed product: $product";
            case 'stock_in':
                return "Stock added to: $product";
            case 'stock_out':
                return "Stock removed from: $product";
            default:
                return ucfirst($action) . " action performed on: $product";
        }
    }

    // Format time elapsed string
    public function timeElapsedString($datetime, $full = false) {
        // Set the default timezone if not set
        date_default_timezone_set('Asia/Manila');
        
        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $ago = new DateTime($datetime, new DateTimeZone('Asia/Manila'));
        $diff = $now->diff($ago);

        // If the difference is less than 1 minute, show 'just now'
        if ($diff->s < 60 && $diff->i === 0 && $diff->h === 0 && $diff->d === 0 && $diff->m === 0 && $diff->y === 0) {
            return 'just now';
        }

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
}
