<?php

//Strict type checking
declare(strict_types=1);

// Load bootstrap.php file
require __DIR__.  '/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$parts = explode("/", $path);

$resource = $parts[2];

$id = $parts[3] ?? null;

echo $resource . " " . $id; 


if ($resource != "tasks") {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . "/src/TaskController.php";

$controller = new TaskController();
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);

// print_r($parts);
// echo "test";