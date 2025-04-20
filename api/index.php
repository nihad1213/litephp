<?php

// Load bootstrap.php file
require __DIR__ . '/bootstrap.php';


$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;


// Add database
$database = new Database($_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']);
$database->getConnect();


if ($resource != "tasks") {
    http_response_code(404);
    exit;
}

$controller = new TaskController();
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);








