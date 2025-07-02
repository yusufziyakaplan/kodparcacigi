<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description;
    public $color;
    public $icon;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($user_id = null) {
        $query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM snippets s WHERE s.category_id = c.id" . 
                         ($user_id ? " AND (s.user_id = :user_id OR s.is_public = 1)" : " AND s.is_public = 1") . 
                         ") as snippet_count
                  FROM " . $this->table_name . " c
                  WHERE c.user_id IS NULL" . ($user_id ? " OR c.user_id = :user_id" : "") . "
                  ORDER BY c.name";

        $stmt = $this->conn->prepare($query);
        
        if($user_id) {
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description, color=:color, icon=:icon, user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icon", $this->icon);
        $stmt->bindParam(":user_id", $this->user_id);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, description=:description, color=:color, icon=:icon
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":icon", $this->icon);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }

    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }
}
?>