<?php

/*
* -----------------------------------------------------------------------------
* Abstract Class: BaseController
* -----------------------------------------------------------------------------
* This abstract base controller provides reusable HTTP response utilities for 
* API controllers. It promotes clean code by centralizing common response logic.
*
* Key Responsibilities:
*  - Standardized JSON responses for HTTP errors and success
*  - Common helper for parsing JSON input from request body
*  - Designed to be extended by specific resource controllers (e.g., TaskController)
*
* Intended Usage:
*  Extend this class in your controllers to inherit built-in response handling:
*      class TaskController extends BaseController { ... }
*
* Created by: Nihad Namatli
* -----------------------------------------------------------------------------
*/

abstract class BaseController
{
    /*
    * -------------------------------------------------------------------------
    * Function: respondUnprocessableEntity
    * -------------------------------------------------------------------------
    * Sends a 422 Unprocessable Entity response with validation error details.
    *
    * Parameters:
    *  - array $errors : Array of validation error messages
    *
    * Returns:
    *  - void
    * -------------------------------------------------------------------------
    */
    protected function respondUnprocessableEntity(array $errors): void 
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    /*
    * -------------------------------------------------------------------------
    * Function: respondMethodNotAllowed
    * -------------------------------------------------------------------------
    * Sends a 405 Method Not Allowed response with allowed methods in header.
    *
    * Parameters:
    *  - string $allowedMethods : Comma-separated list of allowed methods
    *
    * Returns:
    *  - void
    * -------------------------------------------------------------------------
    */
    protected function respondMethodNotAllowed(string $allowedMethods): void 
    {
        http_response_code(405);
        header("Allow: $allowedMethods");
    }

    /*
    * -------------------------------------------------------------------------
    * Function: respondNotFound
    * -------------------------------------------------------------------------
    * Sends a 404 Not Found response with a message including the resource ID.
    *
    * Parameters:
    *  - string $id : Resource ID that was not found
    *
    * Returns:
    *  - void
    * -------------------------------------------------------------------------
    */
    protected function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["error" => "Resource with ID $id not found!"]);
    }

    /*
    * -------------------------------------------------------------------------
    * Function: respondCreated
    * -------------------------------------------------------------------------
    * Sends a 201 Created response with a success message and new resource ID.
    *
    * Parameters:
    *  - string $id : ID of the newly created resource
    *
    * Returns:
    *  - void
    * -------------------------------------------------------------------------
    */
    protected function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(["success" => "Resource created!", "id" => $id]);
    }

    /*
    * -------------------------------------------------------------------------
    * Function: respondInternalError
    * -------------------------------------------------------------------------
    * Sends a 500 Internal Server Error response with an error message.
    *
    * Parameters:
    *  - string $message : Error message to return
    *
    * Returns:
    *  - void
    * -------------------------------------------------------------------------
    */
    protected function respondInternalError(string $message): void
    {
        http_response_code(500);
        echo json_encode(["error" => $message]);
    }

    /*
    * -------------------------------------------------------------------------
    * Function: getJsonInput
    * -------------------------------------------------------------------------
    * Parses and returns incoming JSON request body as an associative array.
    *
    * Returns:
    *  - array : Decoded request data
    * -------------------------------------------------------------------------
    */
    protected function getJsonInput(): array
    {
        return (array) json_decode(file_get_contents("php://input"), true);
    }
}
