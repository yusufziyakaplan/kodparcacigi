<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Snippet.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$snippet_id = (int)$data['snippet_id'];
$user_id = $_SESSION['user_id'];

$database = new Database();
$db = $database->getConnection();
$snippet = new Snippet($db);

// Favoride mi kontrol et
$query = "SELECT 1 FROM favorites WHERE user_id = :user_id AND snippet_id = :snippet_id";
$stmt = $db->prepare($query);
$stmt->execute(['user_id' => $user_id, 'snippet_id' => $snippet_id]);

if ($stmt->fetch()) {
    $snippet->removeFromFavorites($snippet_id, $user_id);
    echo json_encode(['status' => 'removed']);
} else {
    $snippet->addToFavorites($snippet_id, $user_id);
    echo json_encode(['status' => 'added']);
}
