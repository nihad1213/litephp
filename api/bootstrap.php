<?php

/*
|--------------------------------------------------------------------------
| Bootstrap File for API Setup
|--------------------------------------------------------------------------
| This file acts as the entry point for core configurations required to 
| run the API. It ensures error handling, strict typing, content formatting, 
| and autoloading are all properly configured before your application logic runs.
|
| It helps create a centralized place to manage startup behavior, keeping 
| other files clean and focused only on their responsibilities.
|
| Created by: Nihad Namatli
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Enable Strict Types
|--------------------------------------------------------------------------
| Forces PHP to respect declared types. Prevents unexpected type coercion.
| Example: If a function expects an int and receives a string, an error is thrown.
*/
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Show All Errors
|--------------------------------------------------------------------------
| Useful for development. Shows notices, warnings, and errors.
| Should be turned off in production for security.
*/
ini_set("display_errors", "On");

/*
|--------------------------------------------------------------------------
| Set Default Response Format
|--------------------------------------------------------------------------
| This ensures every response from your API is sent as JSON,
| which is standard for APIs.
*/
header("Content-type: application/json; charset=UTF-8");

/*
|--------------------------------------------------------------------------
| Composer Autoloading
|--------------------------------------------------------------------------
| Automatically loads your classes and dependencies based on PSR-4 mapping
| defined in composer.json. No need for manual require/include.
*/
require dirname(__DIR__) . "/vendor/autoload.php";

/*
|--------------------------------------------------------------------------
| Custom Error Handling
|--------------------------------------------------------------------------
| Converts all PHP errors into exceptions using your custom ErrorHandler class.
| This ensures consistent error reporting throughout the app.
*/
set_error_handler("ErrorHandler::handleError");

/*
|--------------------------------------------------------------------------
| Custom Exception Handling
|--------------------------------------------------------------------------
| Catches uncaught exceptions and formats them as JSON responses,
| making them readable and useful for debugging.
*/
set_exception_handler("ErrorHandler::handleException");

/*
|--------------------------------------------------------------------------
| Load Environment Variables (Optional)
|--------------------------------------------------------------------------
| It securely loads variables into $_ENV.
*/
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();


/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
| Establishes a connection to the database using configuration values from
| environment variables. This allows flexible and secure database access.
| Supports multiple database types, including MySQL and SQLite, based on 
| the settings provided in the .env file.
*/
$database = new Database(
    $_ENV['DB_CONNECTION'],
    $_ENV['DB_HOST'],
    $_ENV['DB_PORT'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD'],
    $_ENV['DB_SQLITE_PATH'],
);
$database->getConnect();