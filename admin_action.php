<?php
session_start();
require_once 'config/database.php';

// Sadece admin giriş yapmışsa işle
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: index.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

function redirect_back($msg = null) {
    if ($msg) {
        $_SESSION['flash_message'] = $msg;
    }
    header('Location: admin.php');
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'add_user':
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!$username || !$email || !$password) {
                redirect_back('Kullanıcı adı, e-posta ve parola zorunludur.');
            }

            // Kullanıcı adı veya email benzersiz mi kontrolü
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $username, ':email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                redirect_back('Bu kullanıcı adı veya e-posta zaten kayıtlı.');
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (username, email, password, first_name, last_name, is_active, created_at) VALUES (:username, :email, :password, :first_name, :last_name, :is_active, NOW())");
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password_hash,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':is_active' => $is_active,
            ]);

            redirect_back('Kullanıcı başarıyla eklendi.');
            break;

        case 'edit_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'] ?? '';
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if ($user_id === 1) {
                redirect_back('Admin kullanıcısı düzenlenemez.');
            }

            if (!$username || !$email || !$user_id) {
                redirect_back('Eksik veya geçersiz bilgi.');
            }

            // Benzersiz kontrolü, kendisi hariç
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id != :id");
            $stmt->execute([':username' => $username, ':email' => $email, ':id' => $user_id]);
            if ($stmt->fetchColumn() > 0) {
                redirect_back('Bu kullanıcı adı veya e-posta başka kullanıcı tarafından kullanılıyor.');
            }

            if ($password) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET username=:username, email=:email, password=:password, first_name=:first_name, last_name=:last_name, is_active=:is_active WHERE id=:id");
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $password_hash,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':is_active' => $is_active,
                    ':id' => $user_id,
                ]);
            } else {
                $stmt = $db->prepare("UPDATE users SET username=:username, email=:email, first_name=:first_name, last_name=:last_name, is_active=:is_active WHERE id=:id");
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':is_active' => $is_active,
                    ':id' => $user_id,
                ]);
            }

            redirect_back('Kullanıcı bilgileri güncellendi.');
            break;

        case 'delete_user':
            $user_id = (int)($_POST['user_id'] ?? 0);
            if ($user_id === 1) {
                redirect_back('Admin kullanıcısı silinemez.');
            }

            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);

            redirect_back('Kullanıcı silindi.');
            break;

        case 'add_snippet':
            $title = trim($_POST['title']);
            $user_id = (int)($_POST['user_id'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);
            $language_id = (int)($_POST['language_id'] ?? 0);
            $code = trim($_POST['code']);
            $description = trim($_POST['description'] ?? '');

            if (!$title || !$user_id || !$category_id || !$language_id || !$code) {
                redirect_back('Lütfen tüm zorunlu alanları doldurun.');
            }

            $stmt = $db->prepare("INSERT INTO snippets (title, user_id, category_id, language_id, code, description, created_at) VALUES (:title, :user_id, :category_id, :language_id, :code, :description, NOW())");
            $stmt->execute([
                ':title' => $title,
                ':user_id' => $user_id,
                ':category_id' => $category_id,
                ':language_id' => $language_id,
                ':code' => $code,
                ':description' => $description,
            ]);

            redirect_back('Snippet başarıyla eklendi.');
            break;

        case 'edit_snippet':
            $snippet_id = (int)($_POST['snippet_id'] ?? 0);
            $title = trim($_POST['title']);
            $user_id = (int)($_POST['user_id'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);
            $language_id = (int)($_POST['language_id'] ?? 0);
            $code = trim($_POST['code']);
            $description = trim($_POST['description'] ?? '');

            if (!$snippet_id || !$title || !$user_id || !$category_id || !$language_id || !$code) {
                redirect_back('Eksik veya geçersiz bilgi.');
            }

            $stmt = $db->prepare("UPDATE snippets SET title=:title, user_id=:user_id, category_id=:category_id, language_id=:language_id, code=:code, description=:description WHERE id=:id");
            $stmt->execute([
                ':title' => $title,
                ':user_id' => $user_id,
                ':category_id' => $category_id,
                ':language_id' => $language_id,
                ':code' => $code,
                ':description' => $description,
                ':id' => $snippet_id,
            ]);

            redirect_back('Snippet güncellendi.');
            break;

        case 'delete_snippet':
            $snippet_id = (int)($_POST['snippet_id'] ?? 0);
            if ($snippet_id) {
                $stmt = $db->prepare("DELETE FROM snippets WHERE id = :id");
                $stmt->execute([':id' => $snippet_id]);
                redirect_back('Snippet silindi.');
            }
            redirect_back('Geçersiz snippet ID.');
            break;

        case 'add_category':
            $name = trim($_POST['name']);
            $color = trim($_POST['color']);

            if (!$name || !$color) {
                redirect_back('Kategori adı ve renk zorunludur.');
            }

            // Aynı isimde kategori var mı kontrolü
            $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
            $stmt->execute([':name' => $name]);
            if ($stmt->fetchColumn() > 0) {
                redirect_back('Bu isimde zaten kategori var.');
            }

            $stmt = $db->prepare("INSERT INTO categories (name, color) VALUES (:name, :color)");
            $stmt->execute([':name' => $name, ':color' => $color]);

            redirect_back('Kategori eklendi.');
            break;

        case 'edit_category':
            $category_id = (int)($_POST['category_id'] ?? 0);
            $name = trim($_POST['name']);
            $color = trim($_POST['color']);

            if (!$category_id || !$name || !$color) {
                redirect_back('Eksik bilgi.');
            }

            // Aynı isim başka kategori var mı kontrolü
            $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND id != :id");
            $stmt->execute([':name' => $name, ':id' => $category_id]);
            if ($stmt->fetchColumn() > 0) {
                redirect_back('Bu isimde başka kategori var.');
            }

            $stmt = $db->prepare("UPDATE categories SET name = :name, color = :color WHERE id = :id");
            $stmt->execute([':name' => $name, ':color' => $color, ':id' => $category_id]);

            redirect_back('Kategori güncellendi.');
            break;

        case 'delete_category':
            $category_id = (int)($_POST['category_id'] ?? 0);
            if ($category_id) {
                $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
                $stmt->execute([':id' => $category_id]);
                redirect_back('Kategori silindi.');
            }
            redirect_back('Geçersiz kategori ID.');
            break;

        case 'add_language':
            $name = trim($_POST['name']);
            $color = trim($_POST['color']);

            if (!$name || !$color) {
                redirect_back('Dil adı ve renk zorunludur.');
            }

            // Aynı isimde dil var mı kontrolü
            $stmt = $db->prepare("SELECT COUNT(*) FROM languages WHERE name = :name");
            $stmt->execute([':name' => $name]);
            if ($stmt->fetchColumn() > 0) {
                redirect_back('Bu isimde zaten dil var.');
            }

            $stmt = $db->prepare("INSERT INTO languages (name, color) VALUES (:name, :color)");
            $stmt->execute([':name' => $name, ':color' => $color]);

            redirect_back('Dil eklendi.');
            break;

        case 'edit_language':
            $language_id = (int)($_POST['language_id'] ?? 0);
            $name = trim($_POST['name']);
            $color = trim($_POST['color']);

            if (!$language_id || !$name || !$color) {
                redirect_back('Eksik bilgi.');
            }

            // Aynı isim başka dil var mı kontrolü
            $stmt = $db->prepare("SELECT COUNT(*) FROM languages WHERE name = :name AND id != :id");
            $stmt->execute([':name' => $name, ':id' => $language_id]);
            if ($stmt->fetchColumn() > 0) {
                redirect_back('Bu isimde başka dil var.');
            }

            $stmt = $db->prepare("UPDATE languages SET name = :name, color = :color WHERE id = :id");
            $stmt->execute([':name' => $name, ':color' => $color, ':id' => $language_id]);

            redirect_back('Dil güncellendi.');
            break;

        case 'delete_language':
            $language_id = (int)($_POST['language_id'] ?? 0);
            if ($language_id) {
                $stmt = $db->prepare("DELETE FROM languages WHERE id = :id");
                $stmt->execute([':id' => $language_id]);
                redirect_back('Dil silindi.');
            }
            redirect_back('Geçersiz dil ID.');
            break;

        default:
            redirect_back('Geçersiz işlem.');
    }
} catch (PDOException $e) {
    // Hata varsa mesaj ver
    redirect_back('Veritabanı hatası: ' . $e->getMessage());
}
