<?php
class product {
    private $conn;
    private $table_name = "products";

    // Object properties
    public $id;
    public $product_name;
    public $unit_value;
    public $unit_type;
    public $other_unit_type;
    public $pieces_per_pack;
    public $stock_quantity;
    public $price_per_unit;
    public $created_at;
    public $updated_at;
    public $low_stock_threshold;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                SET product_name = :product_name,
                    unit_value = :unit_value,
                    unit_type = :unit_type,
                    other_unit_type = :other_unit_type,
                    pieces_per_pack = :pieces_per_pack,
                    stock_quantity = :stock_quantity,
                    price_per_unit = :price_per_unit,
                    low_stock_threshold = :low_stock_threshold,
                    created_at = :created_at";

        $stmt = $this->conn->prepare($query);

        // Sanitize and validate
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->unit_value = (float)$this->unit_value;
        $this->unit_type = htmlspecialchars(strip_tags($this->unit_type));
        $this->other_unit_type = !empty($this->other_unit_type) ? htmlspecialchars(strip_tags($this->other_unit_type)) : null;
        $this->stock_quantity = (int)$this->stock_quantity;
        $this->price_per_unit = (float)$this->price_per_unit;
        $this->low_stock_threshold = !empty($this->low_stock_threshold) ? (int)$this->low_stock_threshold : null;
        $this->created_at = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(":product_name", $this->product_name);
        $stmt->bindParam(":unit_value", $this->unit_value);
        $stmt->bindParam(":unit_type", $this->unit_type);
        $stmt->bindParam(":other_unit_type", $this->other_unit_type);
        $stmt->bindParam(":pieces_per_pack", $this->pieces_per_pack, PDO::PARAM_INT);
        $stmt->bindParam(":stock_quantity", $this->stock_quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price_per_unit", $this->price_per_unit);
        $stmt->bindParam(":low_stock_threshold", $this->low_stock_threshold, PDO::PARAM_INT);
        $stmt->bindParam(":created_at", $this->created_at);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Read all products
    public function readAll() {
        $query = "SELECT 
                    id,
                    product_name,
                    unit_value,
                    COALESCE(other_unit_type, unit_type) as unit_type,
                    other_unit_type,
                    pieces_per_pack,
                    stock_quantity,
                    low_stock_threshold,
                    price_per_unit as price,
                    created_at,
                    updated_at
                  FROM " . $this->table_name . " 
                  ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read one product
    public function readOne() {
        $query = "SELECT 
                    id,
                    product_name,
                    unit_value,
                    unit_type,
                    other_unit_type,
                    pieces_per_pack,
                    stock_quantity,
                    low_stock_threshold,
                    price_per_unit,
                    created_at,
                    updated_at
                  FROM " . $this->table_name . " 
                  WHERE id = ? 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->product_name = $row['product_name'];
            $this->unit_value = $row['unit_value'];
            $this->unit_type = $row['unit_type'];
            $this->other_unit_type = $row['other_unit_type'];
            $this->pieces_per_pack = $row['pieces_per_pack'];
            $this->stock_quantity = $row['stock_quantity'];
            $this->price_per_unit = $row['price_per_unit'];
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
                     unit_value = :unit_value,
                     unit_type = :unit_type,
                     other_unit_type = :other_unit_type,
                     pieces_per_pack = :pieces_per_pack,
                     stock_quantity = :stock_quantity,
                     low_stock_threshold = :low_stock_threshold,
                     price_per_unit = :price_per_unit,
                     updated_at = :updated_at
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and validate
        $this->product_name = htmlspecialchars(strip_tags($this->product_name));
        $this->unit_value = (float)$this->unit_value;
        $this->unit_type = htmlspecialchars(strip_tags($this->unit_type));
        $this->other_unit_type = !empty($this->other_unit_type) ? htmlspecialchars(strip_tags($this->other_unit_type)) : null;
        $this->stock_quantity = (int)$this->stock_quantity;
        $this->low_stock_threshold = !empty($this->low_stock_threshold) ? (int)$this->low_stock_threshold : null;
        $this->price_per_unit = (float)$this->price_per_unit;
        $this->updated_at = date('Y-m-d H:i:s');

        // Bind values
        $stmt->bindParam(':product_name', $this->product_name);
        $stmt->bindParam(':unit_value', $this->unit_value);
        $stmt->bindParam(':unit_type', $this->unit_type);
        $stmt->bindParam(':other_unit_type', $this->other_unit_type);
        $stmt->bindParam(':pieces_per_pack', $this->pieces_per_pack, PDO::PARAM_INT);
        $stmt->bindParam(':stock_quantity', $this->stock_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':low_stock_threshold', $this->low_stock_threshold, PDO::PARAM_INT);
        $stmt->bindParam(':price_per_unit', $this->price_per_unit);
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
