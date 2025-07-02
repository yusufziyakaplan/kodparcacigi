<?php
class Tag {
    private $conn;
    private $table_name = "tags";

    public $id;
    public $name;
    public $color;
    public $usage_count;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($limit = 50) {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY usage_count DESC, name ASC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function getPopularTags($limit = 20) {
        $query = "SELECT t.*, COUNT(st.snippet_id) as current_usage
                  FROM " . $this->table_name . " t
                  LEFT JOIN snippet_tags st ON t.id = st.tag_id
                  GROUP BY t.id
                  ORDER BY current_usage DESC, t.name ASC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function findOrCreate($name, $color = '#6B7280') {
        // First try to find existing tag
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['id'];
        }
        
        // Create new tag if not found
        $query = "INSERT INTO " . $this->table_name . " (name, color) VALUES (:name, :color)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":color", $color);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }

    public function addToSnippet($snippet_id, $tag_ids) {
        // First remove existing tags
        $query = "DELETE FROM snippet_tags WHERE snippet_id = :snippet_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":snippet_id", $snippet_id);
        $stmt->execute();

        // Add new tags
        if(!empty($tag_ids)) {
            $query = "INSERT INTO snippet_tags (snippet_id, tag_id) VALUES ";
            $values = [];
            foreach($tag_ids as $tag_id) {
                $values[] = "(:snippet_id, :tag_id_" . $tag_id . ")";
            }
            $query .= implode(", ", $values);

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":snippet_id", $snippet_id);
            
            foreach($tag_ids as $tag_id) {
                $stmt->bindParam(":tag_id_" . $tag_id, $tag_id);
            }
            
            return $stmt->execute();
        }
        
        return true;
    }

    public function updateUsageCount($tag_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET usage_count = (SELECT COUNT(*) FROM snippet_tags WHERE tag_id = :tag_id)
                  WHERE id = :tag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tag_id", $tag_id);
        return $stmt->execute();
    }
}
?>