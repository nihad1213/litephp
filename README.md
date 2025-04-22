# LitePHP Framework
[![Installs](https://img.shields.io/packagist/dt/nihad1213/litephp.svg)](https://packagist.org/packages/nihad1213/litephp)

LitePHP is a lightweight PHP framework designed for building simple and efficient APIs. It provides a straightforward CLI tool for common development tasks and a minimalist structure for rapid API development.

## Installation

Get started with LitePHP using Composer:

```bash
composer create-project nihad1213/litephp project_name
```
```bash
cd project_name
```
```bash
cp .env.example .env
```
```bash
php lite generate:key
```
```bash
php lite start
```

This will install LitePHP, navigate to your new project directory, copy .env.example file to .env, genereate JWT_SECRET key and start the built-in PHP development server.

## CLI Commands

LitePHP comes with a built-in command-line interface to help you manage your development workflow. Here are the available commands:

### Start Development Server

```bash
php lite start
```

Launches the PHP built-in development server at http://localhost:8000, serving files from the 'api' directory.

### Generate JWT Secret Key

```bash
php lite key:generate
```

Creates or updates the JWT_SECRET in your .env file with a secure random key (32 bytes). This is essential for JWT-based authentication in your API.

### Create a Controller

```bash
php lite create:controller UserController
```

Scaffolds a new controller in the 'controllers' directory with RESTful methods (getAll, getOne, create, update, delete) and basic validation.

### Create a Gateway

```bash
php lite create:gateway UserGateway
```

Scaffolds a new gateway in the 'gateways' directory. Gateways handle database operations for specific entities, providing a clean separation between controllers and database logic.

### Run Database Migrations

```bash
php lite db:migrate
```

Executes all SQL files in the 'database' directory in alphabetical order, allowing you to set up and modify your database schema.

## Project Structure

```
project_name/
├── api/               # API endpoint directory (server root)
│   └── bootstrap.php  # Application initialization file
├── controllers/       # Controller classes for handling API requests
├── gateways/          # Data gateway classes for database operations
├── src/               # Source codes of api
├── database/          # SQL files for database migrations
├── .env               # Environment configuration
└── lite              # CLI tool for development tasks
```

## Key Components

### Controllers

Controllers handle HTTP requests and return appropriate responses. They are responsible for:
- Processing incoming requests
- Validating input data
- Interacting with gateways to perform data operations
- Returning appropriate HTTP responses

Each controller extends the `BaseController` class, which provides common response methods and utilities for handling API requests.

#### Controller Example

```php
<?php

require_once __DIR__ . "/../api/bootstrap.php";

class TaskController extends BaseController
{
    /**
     * Constructor
     */
    public function __construct(private TaskGateway $gateway) {}

    /**
     * Get all tasks
     */
    #[Route(path: 'tasks', method: 'GET')]
    public function getAll(): void
    {
        echo json_encode($this->gateway->getAll());
    }

    /**
     * Get a specific task by ID
     */
    #[Route(path: 'tasks/{id}', method: 'GET')]
    public function getOne(string $id): void
    {
        $resource = $this->gateway->get($id);

        if (!$resource) {
            $this->respondNotFound($id);
            return;
        }

        echo json_encode($resource);
    }

    // Additional methods for create, update, delete...
}
```

#### Attribute-Based Routing

LitePHP uses PHP 8's attributes for routing. The `#[Route]` attribute defines:
- The endpoint path
- The HTTP method
- Path parameters (using {parameter} syntax)

### Gateways

Gateways provide an abstraction layer for database operations. Each gateway:
- Handles CRUD operations for a specific database table
- Uses PDO for secure database interactions
- Returns structured data to controllers

#### Gateway Example

```php
<?php

class TaskGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnect();
    }

    /**
     * Get all tasks
     * @return array
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM `tasks`";

        $stmt = $this->conn->query($sql);
        $data = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    // Additional methods for get, create, update, delete...
}
```

### Database

The database directory stores SQL migration files that can be executed with the `db:migrate` command. Example migration file:

```sql
-- database/01_create_tasks_table.sql
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `status` VARCHAR(50) DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Setting Up a Complete API Endpoint

### 1. Create the Database Table

Create an SQL file in the `database` directory:

```sql
-- database/01_create_users_table.sql
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Run the migration:

```bash
php lite db:migrate
```

### 2. Create a Gateway

```bash
php lite create:gateway UserGateway
```

Then, update the generated gateway file:

```php
<?php

class UserGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnect();
    }

    // Replace {tablename} with 'users' in all methods
    public function getAll(): array
    {
        $sql = "SELECT id, name, email, created_at FROM `users`";
        // Rest of the code...
    }
    
    // Customize other methods...
}
```

### 3. Create a Controller

```bash
php lite create:controller UserController
```

Then, update the generated controller:

```php
<?php

require_once __DIR__ . "/../api/bootstrap.php";

class UserController extends BaseController
{
    public function __construct(private UserGateway $gateway) {}

    // Replace {route} with 'users' in all Route attributes
    #[Route(path: 'users', method: 'GET')]
    public function getAll(): void
    {
        echo json_encode($this->gateway->getAll());
    }
    
    // Customize validation for your entity
    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data['name'])) {
            $errors[] = "Name is required.";
        }

        if ($is_new && empty($data['email'])) {
            $errors[] = "Email is required.";
        }

        // Add more validation rules...

        return $errors;
    }
}
```

### 4. Access Your API

With your server running (`php lite start`), you can now access your API:

```
GET    http://localhost:8000/users          # Get all users
GET    http://localhost:8000/users/1        # Get user with ID 1
POST   http://localhost:8000/users          # Create a new user
PATCH  http://localhost:8000/users/1        # Update user with ID 1
DELETE http://localhost:8000/users/1        # Delete user with ID 1
```

## Response Methods

The `BaseController` class provides several methods for returning standardized API responses:

- `respondNotFound($id)`: Returns a 404 error when a resource isn't found
- `respondUnprocessableEntity($errors)`: Returns a 422 error for validation failures
- `respondCreated($id)`: Returns a 201 status with the newly created resource's ID
- `respondInternalError($message)`: Returns a 500 error for server-side failures

## Security Features

- **Parameter Binding**: All database queries use prepared statements with parameter binding to prevent SQL injection.
- **JWT Authentication**: Generate secure JWT keys with the `key:generate` command for token-based authentication.
- **Input Validation**: Each controller includes validation methods to ensure data integrity.

## Future Development

- ORM Integration: An object-relational mapping system is planned for future releases to simplify database operations.
- Additional CLI commands for more development tasks.
- Extended middleware support.

## Contributing

Contributions are welcome! Feel free to submit issues or pull requests to help improve LitePHP.

## License

[MIT License]

## Created By

Nihad Namatli
