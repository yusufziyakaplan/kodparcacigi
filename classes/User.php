<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $avatar;
    public $theme_preference;
    public $is_active;
    public $created_at;
    public $last_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        try {
            $query = "INSERT INTO users 
            (username, email, password, first_name, last_name, created_at, is_active) 
            VALUES 
            (:username, :email, :password, :first_name, :last_name, NOW(), 1)";
    
            $stmt = $this->conn->prepare($query);
    
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);
    
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':first_name', $this->first_name);
            $stmt->bindParam(':last_name', $this->last_name);
    
            if ($stmt->execute()) {
                return true;
            }
            return false;
    
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    

    public function login($email, $password)
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    return $user;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    
    
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " SET last_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
    }

    public function getUserStats($user_id) {
        $query = "SELECT 
                    (SELECT COUNT(*) FROM snippets WHERE user_id = :user_id) as total_snippets,
                    (SELECT COUNT(*) FROM snippets WHERE user_id = :user_id AND is_public = 1) as public_snippets,
                    (SELECT COUNT(*) FROM favorites WHERE user_id = :user_id) as total_favorites,
                    (SELECT SUM(view_count) FROM snippets WHERE user_id = :user_id) as total_views";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($user_id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = :first_name, last_name = :last_name, 
                      theme_preference = :theme_preference 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->bindParam(":first_name", $data['first_name']);
        $stmt->bindParam(":last_name", $data['last_name']);
        $stmt->bindParam(":theme_preference", $data['theme_preference']);
        
        return $stmt->execute();
    }
}
?>