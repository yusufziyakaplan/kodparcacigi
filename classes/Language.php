<?php
class Language {
    private $conn;
    private $table_name = "languages";

    public $id;
    public $name;
    public $extension;
    public $color;
    public $prism_class;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT l.*, 
                         (SELECT COUNT(*) FROM snippets s WHERE s.language_id = l.id) as snippet_count
                  FROM " . $this->table_name . " l
                  ORDER BY l.name";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPopularLanguages($limit = 10) {
        $query = "SELECT l.*, COUNT(s.id) as snippet_count
                  FROM " . $this->table_name . " l
                  LEFT JOIN snippets s ON l.id = s.language_id
                  GROUP BY l.id
                  ORDER BY snippet_count DESC, l.name ASC
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
?>