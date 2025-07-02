<?php
class Snippet {
    private $conn;
    private $table_name = "snippets";

    public $id;
    public $title;
    public $description;
    public $code;
    public $language_id;
    public $category_id;
    public $user_id;
    public $is_public;
    public $is_favorite;
    public $view_count;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, description=:description, code=:code, 
                      language_id=:language_id, category_id=:category_id, 
                      user_id=:user_id, is_public=:is_public";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":language_id", $this->language_id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":is_public", $this->is_public);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read($user_id = null, $search = '', $category = '', $language = '', $sort = 'created_at', $order = 'DESC', $limit = 20, $offset = 0) {
        $query = "SELECT s.*, l.name as language_name, l.color as language_color, l.prism_class,
                         c.name as category_name, c.color as category_color, c.icon as category_icon,
                         u.username, u.first_name, u.last_name,
                         GROUP_CONCAT(t.name) as tags,
                         GROUP_CONCAT(t.color) as tag_colors,
                         (SELECT COUNT(*) FROM favorites f WHERE f.snippet_id = s.id AND f.user_id = :current_user_id) as is_favorited
                  FROM " . $this->table_name . " s
                  LEFT JOIN languages l ON s.language_id = l.id
                  LEFT JOIN categories c ON s.category_id = c.id
                  LEFT JOIN users u ON s.user_id = u.id
                  LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                  LEFT JOIN tags t ON st.tag_id = t.id
                  WHERE 1=1";

        if($user_id) {
            $query .= " AND (s.user_id = :user_id OR s.is_public = 1)";
        } else {
            $query .= " AND s.is_public = 1";
        }

        if($search) {
            $query .= " AND (MATCH(s.title, s.description, s.code) AGAINST(:search IN NATURAL LANGUAGE MODE) 
                           OR s.title LIKE :search_like 
                           OR s.description LIKE :search_like)";
        }

        if($category) {
            $query .= " AND s.category_id = :category";
        }

        if($language) {
            $query .= " AND s.language_id = :language";
        }

        $query .= " GROUP BY s.id ORDER BY s." . $sort . " " . $order . " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":current_user_id", $user_id, PDO::PARAM_INT);
        
        if($user_id) {
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        }

        if($search) {
            $stmt->bindParam(":search", $search);
            $search_like = '%' . $search . '%';
            $stmt->bindParam(":search_like", $search_like);
        }

        if($category) {
            $stmt->bindParam(":category", $category, PDO::PARAM_INT);
        }

        if($language) {
            $stmt->bindParam(":language", $language, PDO::PARAM_INT);
        }

        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT s.*, l.name as language_name, l.color as language_color, l.prism_class,
                         c.name as category_name, c.color as category_color, c.icon as category_icon,
                         u.username, u.first_name, u.last_name,
                         GROUP_CONCAT(t.name) as tags,
                         GROUP_CONCAT(t.color) as tag_colors,
                         GROUP_CONCAT(t.id) as tag_ids
                  FROM " . $this->table_name . " s
                  LEFT JOIN languages l ON s.language_id = l.id
                  LEFT JOIN categories c ON s.category_id = c.id
                  LEFT JOIN users u ON s.user_id = u.id
                  LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                  LEFT JOIN tags t ON st.tag_id = t.id
                  WHERE s.id = :id
                  GROUP BY s.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, code=:code, 
                      language_id=:language_id, category_id=:category_id, 
                      is_public=:is_public, updated_at=NOW()
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":language_id", $this->language_id);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":is_public", $this->is_public);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":snippet_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    public function incrementViewCount($id, $user_id = null, $ip_address = null, $user_agent = null) {
        // Update view count
        $query = "UPDATE " . $this->table_name . " SET view_count = view_count + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        // Log the view
        $query = "INSERT INTO snippet_views (snippet_id, user_id, ip_address, user_agent) 
                  VALUES (:snippet_id, :user_id, :ip_address, :user_agent)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":snippet_id", $id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->execute();
    }

    public function addToFavorites($snippet_id, $user_id) {
        $query = "INSERT IGNORE INTO favorites (snippet_id, user_id, created_at) VALUES (:snippet_id, :user_id, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":snippet_id", $snippet_id, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $result = $stmt->execute();
        if (!$result) {
            $error = $stmt->errorInfo();
            throw new Exception("Favori eklenemedi: " . $error[2]);
        }
        return $result;
    }
    

    public function removeFromFavorites($snippet_id, $user_id) {
        $query = "DELETE FROM favorites WHERE snippet_id = :snippet_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":snippet_id", $snippet_id);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    public function getFavorites($user_id, $limit = 20, $offset = 0) {
        $query = "SELECT s.*, l.name as language_name, l.color as language_color, l.prism_class,
                         c.name as category_name, c.color as category_color, c.icon as category_icon,
                         u.username, u.first_name, u.last_name,
                         GROUP_CONCAT(t.name) as tags,
                         GROUP_CONCAT(t.color) as tag_colors,
                         f.created_at as favorited_at
                  FROM favorites f
                  JOIN " . $this->table_name . " s ON f.snippet_id = s.id
                  LEFT JOIN languages l ON s.language_id = l.id
                  LEFT JOIN categories c ON s.category_id = c.id
                  LEFT JOIN users u ON s.user_id = u.id
                  LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                  LEFT JOIN tags t ON st.tag_id = t.id
                  WHERE f.user_id = :user_id
                  GROUP BY s.id
                  ORDER BY f.created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    public function getSnippetCount($user_id = null, $search = '', $category = '', $language = '') {
        $query = "SELECT COUNT(DISTINCT s.id) as total
                  FROM snippets s
                  LEFT JOIN snippet_tags st ON s.id = st.snippet_id
                  LEFT JOIN tags t ON st.tag_id = t.id
                  WHERE 1=1";
    
        if ($user_id) {
            $query .= " AND (s.user_id = :user_id OR s.is_public = 1)";
        } else {
            $query .= " AND s.is_public = 1";
        }
    
        if ($search) {
            $query .= " AND (s.title LIKE :search OR s.description LIKE :search)";
        }
    
        if ($category) {
            $query .= " AND s.category_id = :category";
        }
    
        if ($language) {
            $query .= " AND s.language_id = :language";
        }
    
        $stmt = $this->conn->prepare($query);
    
        if ($user_id) $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        if ($search) {
            $search_like = '%' . $search . '%';
            $stmt->bindParam(":search", $search_like);
        }
        if ($category) $stmt->bindParam(":category", $category, PDO::PARAM_INT);
        if ($language) $stmt->bindParam(":language", $language, PDO::PARAM_INT);
    
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
    
}
?>