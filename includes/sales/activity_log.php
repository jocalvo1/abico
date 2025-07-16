<?php
class SalesActivityLog {
    private $conn;
    private $table_name = "sales_activity_log";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all activity logs with pagination
    public function readAll($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT id, transaction_id, customer_name, description, created_at 
                 FROM " . $this->table_name . " 
                 ORDER BY created_at DESC 
                 LIMIT :offset, :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get total count of logs
    public function countAll() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['count'] : 0;
    }

    // Format description for display
    public function formatDescription($row) {
        return $row['description'];
    }

    // Get time elapsed string
    public function timeElapsedString($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;
        
        if ($difference < 60) {
            return $difference . ' seconds ago';
        } elseif ($difference < 3600) {
            return floor($difference / 60) . ' minutes ago';
        } elseif ($difference < 86400) {
            return floor($difference / 3600) . ' hours ago';
        } elseif ($difference < 604800) {
            return floor($difference / 86400) . ' days ago';
        } elseif ($difference < 2678400) {
            return floor($difference / 604800) . ' weeks ago';
        } else {
            return date('M d, Y', $timestamp);
        }
    }
}
