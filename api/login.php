<?php

require_once __DIR__ . '/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST allowed"]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$username = $input["username"] ?? null;
$password = $input["password"] ?? null;

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Username and password required"]);
    exit;
}

// Dummy credentials – in real app, check database
if ($username !== "admin" || $password !== "secret") {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

$jwt = new JWTCodec($_ENV["JWT_SECRET"]);

$payload = [
    "user" => $username,
    "exp" => time() + 3600 // expires in 1 hour
];

$token = $jwt->encode($payload);

echo json_encode([
    "access_token" => $token
]);
