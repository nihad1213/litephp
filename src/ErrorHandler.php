<?php 

/*
* -----------------------------------------------------------------------------
* Class: ErrorHandler
* -----------------------------------------------------------------------------
* This class is responsible for handling errors and exceptions in a structured 
* and consistent way throughout the application.
*
* It defines two static methods:
*  - handleError(): Converts standard PHP errors into ErrorException instances.
*  - handleException(): Catches uncaught exceptions and returns a JSON response.
*
* Created by: Nihad Namatli
* -----------------------------------------------------------------------------
*/

class ErrorHandler {

    /*
    * -------------------------------------------------------------------------
    * Function: handleError
    * -------------------------------------------------------------------------
    * Converts standard PHP errors into ErrorException objects, allowing them 
    * to be caught by exception handling mechanisms.
    *
    * Parameters:
    *  - int $errno      : The level of the error raised.
    *  - string $errstr  : The error message.
    *  - string $errfile : The filename where the error was raised.
    *  - int $errline    : The line number where the error occurred.
    *
    * Throws:
    *  - ErrorException
    *
    * Return Type:
    *  - void
    * -------------------------------------------------------------------------
    */
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): void {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /*
    * -------------------------------------------------------------------------
    * Function: handleException
    * -------------------------------------------------------------------------
    * Handles uncaught exceptions and returns a JSON-formatted response with
    * relevant details such as message, file, and line number. Also sets the
    * HTTP status code to 500 (Internal Server Error).
    *
    * Parameters:
    *  - Throwable $exception : The exception that was thrown.
    *
    * Return Type:
    *  - void
    * -------------------------------------------------------------------------
    */
    public static function handleException(Throwable $exception): void {

        // Set HTTP response code to 500 Internal Server Error
        http_response_code(500);
        
        // Return JSON response containing error details
        echo json_encode([
            "code" => $exception->getCode(),
            "message" => $exception->getMessage(),
            "file" => $exception->getFile(),
            "line" => $exception->getLine()
        ]);
    }
}
