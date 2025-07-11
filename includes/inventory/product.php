<?php
class product {
    private $conn;
    private $table_name = "products";

    // Object properties
    public $id;
    public $product_name;
    public $description;
    public $quantity;
    public $price;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET product_name = :product_name,
                    description = :description,
                    quantity = :quantity,
                    price = :price,
                    created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        // Sanitize and validate
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->quantity = (int)$this->quantity;
        $this->price = (float)$this->price;
        $this->created_at = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":quantity", $this->quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all products
    public function readAll($search = '') {
        $query = "SELECT * FROM " . $this->table_name;
        
        if (!empty($search)) {
            $query .= " WHERE product_name LIKE :search OR description LIKE :search";
        }
        
        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        
        if (!empty($search)) {
            $search = "%$search%";
            $stmt->bindParam(':search', $search);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Read one product
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->product_name = $row['product_name'];
            $this->description = $row['description'];
            $this->quantity = $row['quantity'];
            $this->price = $row['price'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }

    // Update product
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET product_name = :product_name,
                     description = :description,
                     quantity = :quantity,
                     price = :price,
                     updated_at = :updated_at
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and validate
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->quantity = (int)$this->quantity;
        $this->price = (float)$this->price;
        $this->updated_at = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(':product_name', $this->product_name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':quantity', $this->quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
