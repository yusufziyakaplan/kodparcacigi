<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Snippet.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID eksik veya geçersiz!']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$snippet = new Snippet($db);

$data = $snippet->getById($_GET['id']);

if ($data) {
    echo json_encode(['success' => true, 'snippet' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Kod parçacığı bulunamadı!']);
} 