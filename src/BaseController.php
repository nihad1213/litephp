<?php

abstract class BaseController
{
    protected function respondUnprocessableEntity(array $errors): void 
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    protected function respondMethodNotAllowed(string $allowedMethods): void 
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    protected function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["error" => "Resource with ID $id not found!"]);
    }

    protected function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(["success" => "Resource created!", "id" => $id]);
    }

    protected function respondInternalError(string $message): void
    {
        http_response_code(500);
        echo json_encode(["error" => $message]);
    }

    protected function getJsonInput(): array
    {
        return (array) json_decode(file_get_contents("php://input"), true);
    }
}
