<?php

// Load the bootstrap.php file
require __DIR__ . '/bootstrap.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, "/"); // Trim leading and trailing slashes

// Debugging: Output the current request path
error_log("Current path: $path");

// Handle the login route
if ($path === "login") {
    require __DIR__ . '/login.php'; // This is the login logic
    exit;
}

// Handle tasks route (e.g., /tasks or /tasks/{id})
if ($path === "tasks" || str_starts_with($path, "tasks/")) {
    // Extract task ID from the URL, if available (e.g., /tasks/1)
    $parts = explode("/", $path);
    $id = (count($parts) > 1) ? $parts[1] : null;

    // Authorization header check
    $headers = getallheaders();
    $authorization = $headers['Authorization'] ?? null;

    if ($authorization) {
        // Add a check to make sure the Authorization header has the right format
        if (strpos($authorization, ' ') === false) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid authorization format, expected 'Bearer token'"]);
            exit;
        }

        list($type, $token) = explode(' ', $authorization, 2);

        if ($type === 'Bearer' && !empty($token)) {
            try {
                // Check if JWT_SECRET is set in the environment
                $jwtSecret = $_ENV["JWT_SECRET"] ?? null;
                if (!$jwtSecret) {
                    http_response_code(500);
                    echo json_encode(["error" => "JWT_SECRET is not set in the environment."]);
                    exit;
                }

                // Initialize JWT Codec with the secret key
                $jwtCodec = new JWTCodec($jwtSecret);
                $decoded = $jwtCodec->decode($token); // Decode and validate the token

                // Example: Access the user info from the token
                $user = $decoded['user']; // Adjust according to your JWT payload

                // Proceed with task processing (you could also check user roles here)
                $taskGateway = new TaskGateway($database);
                $controller = new TaskController($taskGateway);
                $controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
                exit;

            } catch (Exception $e) {
                // Invalid token error
                http_response_code(401);
                echo json_encode(["error" => "Invalid token: " . $e->getMessage()]);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid authorization token format"]);
            exit;
        }
    } else {
        // Token missing
        http_response_code(401);
        echo json_encode(["error" => "Authorization header missing"]);
        exit;
    }
}

// Fallback for unknown routes
http_response_code(404);
echo json_encode(["error" => "Route not found"]);
