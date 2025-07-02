<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_GET['username']) || trim($_GET['username']) === '') {
    echo json_encode(['available' => false]);
    exit;
}

$username = trim($_GET['username']);
$database = new Database();
$db = $database->getConnection();
$stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['available' => false]);
} else {
    echo json_encode(['available' => true]);
} 