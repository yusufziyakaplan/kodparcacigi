<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Snippet.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek!']);
    exit;
}

// Gerekli alanlar
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$code = $_POST['code'] ?? '';
$language_id = $_POST['language_id'] ?? '';
$category_id = $_POST['category_id'] ?? '';
$user_id = $_POST['user_id'] ?? 1; // Giriş yoksa 1 (admin) olarak varsayalım
$is_public = isset($_POST['is_public']) ? 1 : 0;

if (!$title || !$code || !$language_id || !$category_id) {
    echo json_encode(['success' => false, 'message' => 'Tüm alanlar zorunludur!']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$snippet = new Snippet($db);

$snippet->title = $title;
$snippet->description = $description;
$snippet->code = $code;
$snippet->language_id = $language_id;
$snippet->category_id = $category_id;
$snippet->user_id = $user_id;
$snippet->is_public = $is_public;

if ($snippet->create()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Kayıt başarısız!']);
} 