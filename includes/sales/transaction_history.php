<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/session.php';

// Verify user is logged in
$session = new session();
if (!$session->get('login')) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$database = new dbconn();
$db = $database->getConnection();
$transactionHistory = new TransactionHistory($db);

// Check if this is a DataTables AJAX request
if (isset($_POST['draw'])) {
    try {
        $transactionHistory->handleDataTableRequest();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

class TransactionHistory {
    private $conn;
    private $table_name = "transactions";
    private $items_table = "sale_items";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get transaction details with pagination
    public function readAll($page = 1, $perPage = 10, $search = '', $orderCol = 0, $orderDir = 'desc') {
        $offset = ($page - 1) * $perPage;
        
        // Build search condition
        $searchCond = "";
        $params = [];
        
        if (!empty($search)) {
            $searchCond = "WHERE (t.id LIKE :search OR 
                              t.customer_name LIKE :search OR 
                              t.created_at LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        // Build order by
        $columns = ['id', 'created_at', 'customer_name', 'item_count', 'total_amount'];
        $orderCol = $columns[$orderCol];
        $orderDir = in_array(strtoupper($orderDir), ['ASC', 'DESC']) ? $orderDir : 'DESC';

        // Get total count
        $countQuery = "SELECT COUNT(*) as count FROM $this->table_name t $searchCond";
        $countStmt = $this->conn->prepare($countQuery);
        
        if (!empty($search)) {
            $countStmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Get transaction details
        $query = "SELECT t.*, 
                        (SELECT COUNT(*) FROM $this->items_table WHERE transaction_id = t.id) as item_count
                 FROM $this->table_name t 
                 $searchCond
                 ORDER BY $orderCol $orderDir
                 LIMIT :offset, :limit";

        $stmt = $this->conn->prepare($query);
        if (!empty($search)) {
            $stmt->bindValue(':search', "%{$search}%", PDO::PARAM_STR);
        }
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format response for DataTables
        $response = [
            'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ];

        return $response;
    }

    public function handleDataTableRequest() {
        // Get DataTables parameters
        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
        $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
        $orderCol = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
        $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';

        // Calculate page number
        $page = ($start / $length) + 1;

        try {
            // Get data
            $response = $this->readAll($page, $length, $search, $orderCol, $orderDir);
            
            // Add draw parameter
            $response['draw'] = $draw;
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    // Get total count of transactions
    public function countAll() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['count'] : 0;
    }

    // Get transaction details for a specific transaction
    public function readOne($id) {
        $query = "SELECT t.*, 
                        (SELECT COUNT(*) FROM $this->items_table WHERE transaction_id = t.id) as item_count,
                        (SELECT GROUP_CONCAT(product_name SEPARATOR ', ') 
                         FROM $this->items_table 
                         WHERE transaction_id = t.id 
                         GROUP BY transaction_id) as items_list
                 FROM $this->table_name t
                 WHERE t.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get items for a specific transaction
    public function getTransactionItems($id) {
        $query = "SELECT si.*, p.product_name 
                 FROM $this->items_table si
                 JOIN products p ON si.product_id = p.id
                 WHERE si.transaction_id = :id
                 ORDER BY si.created_at";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
