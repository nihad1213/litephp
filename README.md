# LitePHP Framework

A lightweight PHP framework for building REST APIs with modern features and minimal dependencies.

## Overview

LitePHP is a simple yet powerful PHP framework designed for creating secure and efficient RESTful APIs. It provides essential components for modern web application development including:

- HTTP response standardization
- Database abstraction with PDO support
- JWT authentication
- Error handling
- Environment configuration
- Command-line interface for common tasks

Created by [Nihad Namatli](mailto:nihad.nemetli@gmail.com).

## Requirements

- PHP 8.0 or higher
- Composer
- Required PHP extensions:
  - json
  - curl
  - openssl
  - pdo
  - pdo_mysql

## Installation

### Option 1: Using Composer create-project

The easiest way to get started with LitePHP is by using Composer's create-project command:

```bash
composer create-project nihad1213/litephp litephp-test
```

This will install LitePHP and all its dependencies in a new directory called `litephp-test`.

### Option 2: Manual Installation

1. Clone the repository or download the source code
2. Install dependencies:

```bash
composer install
```

3. Copy the environment file and configure your settings:

```bash
cp .env.example .env
```

4. Generate a secure JWT secret:

```bash
php lite key:generate
```

5. Update other values in your `.env` file with your database credentials

## CLI Commands

LitePHP comes with a command-line interface tool (`lite`) that helps with common development tasks:

```bash
# Generate a secure JWT secret and add it to .env
php lite key:generate

# Start the development server
php lite start

# Create a new controller class
php lite create:controller YourControllerName

# Create a new gateway class for database operations
php lite create:gateway YourGatewayName
```

## Environment Configuration

The framework uses environment variables for configuration. Edit your `.env` file with the appropriate values:

```
# JWT CONFIGURATION
JWT_SECRET = your_secure_secret_key  # Use php lite key:generate to create this

# DATABASE CONFIGURATION
DB_CONNECTION = mysql  # Options: mysql, pgsql, sqlite
DB_HOST = localhost
DB_PORT = 3306
DB_NAME = your_database
DB_USER = db_username
DB_PASSWORD = db_password
DB_SQLITE_PATH = /path/to/sqlite.db  # Required only for SQLite
```

## Architecture: MVC with Gateway Pattern

LitePHP follows a variant of the MVC pattern with a Gateway layer:

- **Controllers**: Handle HTTP requests and responses
- **Gateways**: Manage data access and database operations
- **Models**: Implicitly represented in the database tables

### The Gateway Pattern

Gateways serve as a data access layer between controllers and the database. This pattern offers several benefits:

1. **Separation of concerns**: Controllers handle HTTP, Gateways handle data
2. **Reusability**: The same Gateway can be used by multiple Controllers
3. **Testability**: Easier to mock for unit testing
4. **Security**: Centralizes data validation and sanitization

## Core Components

### BaseController

An abstract class that provides standardized HTTP response methods for API controllers:

- `respondUnprocessableEntity(array $errors)`: Return 422 status with validation errors
- `respondMethodNotAllowed(string $allowedMethods)`: Return 405 status with allowed methods
- `respondNotFound(string $id)`: Return 404 status when a resource isn't found
- `respondCreated(string $id)`: Return 201 status when a resource is created
- `respondInternalError(string $message)`: Return 500 status for server errors
- `getJsonInput()`: Parse and return JSON from request body

Usage example:

```php
class TaskController extends BaseController 
{
    public function __construct(private TaskGateway $gateway) {}
    
    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {
                echo json_encode($this->gateway->getAll());
            } else if ($method == "POST") {
                $data = $this->getJsonInput();
                $errors = $this->getValidationErrors($data);
    
                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }
    
                $id = $this->gateway->create($data);
                $this->respondCreated($id);
            } else {
                $this->respondMethodNotAllowed("POST, GET");
            }
        }
        
        // Other request handling...
    }
}
```

### Database

Manages database connections using PDO with support for multiple database drivers:

- MySQL
- PostgreSQL
- SQLite

Usage example:

```php
$database = new Database(
    $_ENV['DB_CONNECTION'],
    $_ENV['DB_HOST'],
    $_ENV['DB_PORT'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASSWORD'],
    $_ENV['DB_SQLITE_PATH']
);

$pdo = $database->getConnect();
```

### JWTCodec

Handles JWT (JSON Web Token) encoding and decoding for secure authentication:

- `encode(array $payload)`: Create a signed JWT token
- `decode(string $jwt)`: Verify and decode a JWT token

Usage example:

```php
$jwt = new JWTCodec($_ENV["JWT_SECRET"]);

// Create a token
$payload = [
    "user" => $username,
    "exp" => time() + 3600 // Expires in 1 hour
];

$token = $jwt->encode($payload);

// Verify a token
$decoded = $jwt->decode($token);
if ($decoded) {
    $username = $decoded['user'];
    // User is authenticated
}
```

### ErrorHandler

Provides centralized error and exception handling:

- `handleError()`: Converts PHP errors to exceptions
- `handleException()`: Returns JSON formatted error responses

## Creating Controllers and Gateways

### Controllers

Controllers handle HTTP requests and return appropriate responses. They use Gateways to interact with the database.

Create a controller using the CLI:

```bash
php lite create:controller GameController
```

Example controller structure:

```php
<?php
require_once __DIR__ . "/../api/bootstrap.php";
    
class GameController extends BaseController
{
    public function __construct(private GameGateway $gateway) {}
    
    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {
                echo json_encode($this->gateway->getAll());
            } else if ($method == "POST") {
                $data = $this->getJsonInput();
                $errors = $this->getValidationErrors($data);
    
                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }
    
                $id = $this->gateway->create($data);
                $this->respondCreated($id);
            } else {
                $this->respondMethodNotAllowed("POST, GET");
            }
        } else {
            // Handle requests with ID...
        }
    }
    
    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];
        // Validation logic...        
        return $errors;
    }
}
```

### Gateways

Gateways handle database operations for a specific resource. They encapsulate SQL queries and provide a clean API for controllers.

Create a gateway using the CLI:

```bash
php lite create:gateway GameGateway
```

Example gateway structure:

```php
<?php
class GameGateway 
{
    private PDO $conn;
    
    public function __construct(Database $database) 
    {
        $this->conn = $database->getConnect();
    }
    
    public function getAll(): array 
    {
        $sql = "SELECT * FROM `game`";
        $stmt = $this->conn->query($sql);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }
    
    public function get(string $id): array|false 
    {
        $sql = "SELECT * FROM `game` WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Other CRUD operations...
}
```

## Routing

The framework uses a simple routing system in `index.php` that maps URLs to controller actions. Routes are defined by parsing the request URI.

Example routing setup:

```php
// Example for handling game routes
if ($path === "games" || str_starts_with($path, "games/")) {
    $parts = explode("/", $path);
    $id = (count($parts) > 1) ? $parts[1] : null;
    
    // Initialize dependencies
    $gameGateway = new GameGateway($database);
    $controller = new GameController($gameGateway);
    
    // Process the request
    $controller->processRequest($_SERVER["REQUEST_METHOD"], $id);
}
```

## Authentication

The framework includes JWT-based authentication. Users can obtain a token through the login endpoint, which is then used in subsequent API requests.

To authenticate requests:

1. Include the token in the Authorization header:
```
Authorization: Bearer your_jwt_token
```

2. The token is automatically verified in `index.php` before processing the request.

## Quick Start

1. Install the framework:
   ```bash
   composer create-project nihad1213/litephp my-api
   ```

2. Generate a JWT secret:
   ```bash
   php lite key:generate
   ```

3. Configure your database connection in `.env`

4. Create a gateway for your database table:
   ```bash
   php lite create:gateway ProductGateway
   ```

5. Create a controller that uses the gateway:
   ```bash
   php lite create:controller ProductController
   ```

6. Add your route to `index.php`

7. Start the development server:
   ```bash
   php lite start
   ```

## Web Interface

A basic JWT login interface is provided at the `/login` route, allowing users to:
- Generate JWT tokens for testing
- Copy tokens for use with API requests
- Store tokens in localStorage

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.