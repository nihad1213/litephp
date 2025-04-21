<?php
// Load the bootstrap.php file
require __DIR__ . '/bootstrap.php';

// Initialize router
$router = new Router();

// Register controllers with their gateways
// Example Usage
//$router->registerController(TestController::class, new TestGateway($database));


// Get the request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Special case for login route (no authentication needed)
if ($path === "/login") {
    require __DIR__ . '/login.php';
    exit;
}

// Dispatch the request
$router->dispatch($path, $method);