<?php

// Load bootstrap.php file
require __DIR__ . '/bootstrap.php';


$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

if ($resource != "tasks") {
    http_response_code(404);
    exit;
}

$id = $parts[3] ?? null;


$taskGateway = new TaskGateway($database);
$controller = new TaskController($taskGateway); 
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);








