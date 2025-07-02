<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Snippet.php';

$data = json_decode(file_get_contents("php://input"), true);
$snippet_id = (int)($data['snippet_id'] ?? 0);

$database = new Database();
$db = $database->getConnection();
$snippet = new Snippet($db);

$snippet->incrementViewCount(
    $snippet_id,
    $_SESSION['user_id'] ?? null,
    $_SERVER['REMOTE_ADDR'],
    $_SERVER['HTTP_USER_AGENT']
);
