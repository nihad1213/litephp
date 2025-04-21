<?php

/**
 * -----------------------------------------------------------------------------
 * Class: Router
 * -----------------------------------------------------------------------------
 * Handles HTTP request routing and controller action dispatching.
 * 
 * Features:
 *  - Supports dynamic route registration using PHP Attributes
 *  - Supports parameterized routes (e.g. /user/{id})
 *  - Supports authentication via JWT
 * 
 * Created by: Nihad Namatli
 * -----------------------------------------------------------------------------
 */
class Router
{
    /**
     * @var array Registered routes
     */
    private array $routes = [];

    /**
     * @var array Controller instances
     */
    private array $controllers = [];

    /**
     * Register a controller and extract its route definitions via attributes.
     * 
     * @param string $controllerClass Fully qualified controller class name
     * @param mixed $gateway Optional dependency to inject into the controller
     * @return void
     */
    public function registerController(string $controllerClass, $gateway = null): void
    {
        $controller = $gateway ? new $controllerClass($gateway) : new $controllerClass();
        $this->controllers[$controllerClass] = $controller;

        $reflectionClass = new \ReflectionClass($controllerClass);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Route::class);
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $this->routes[] = [
                    'path' => $route->path,
                    'method' => $route->method,
                    'requiresAuth' => $route->requiresAuth,
                    'controller' => $controllerClass,
                    'action' => $method->getName()
                ];
            }
        }
    }

    /**
     * Match and dispatch a request to the appropriate controller and action.
     * 
     * @param string $requestPath HTTP request URI path
     * @param string $requestMethod HTTP method (GET, POST, etc.)
     * @return void
     */
    public function dispatch(string $requestPath, string $requestMethod): void
    {
        $requestPath = trim($requestPath, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod && $route['method'] !== '*') {
                continue;
            }

            $pattern = $this->convertRouteToRegex($route['path']);

            if (preg_match($pattern['regex'], $requestPath, $matches)) {
                $params = [];
                foreach ($pattern['params'] as $index => $name) {
                    $params[$name] = $matches[$index + 1];
                }

                if ($route['requiresAuth']) {
                    $this->authenticate();
                }

                $controller = $this->controllers[$route['controller']];
                $action = $route['action'];

                call_user_func_array([$controller, $action], $params);
                exit;
            }
        }

        http_response_code(404);
        echo json_encode(["error" => "Route not found"]);
    }

    /**
     * Convert a parameterized route path into a regex pattern for matching.
     * 
     * @param string $route The route path (e.g., /user/{id})
     * @return array Associative array with 'regex' and 'params'
     */
    private function convertRouteToRegex(string $route): array
    {
        $params = [];
        $pattern = preg_replace_callback('/{([^}]+)}/', function ($matches) use (&$params) {
            $params[] = $matches[1];
            return '([^/]+)';
        }, $route);

        return [
            'regex' => '#^' . $pattern . '$#',
            'params' => $params
        ];
    }

    /**
     * Validate the Authorization header and decode the JWT token.
     * 
     * @throws \Exception If authentication fails or token is invalid
     * @return void
     */
    private function authenticate(): void
    {
        $headers = getallheaders();
        $authorization = $headers['Authorization'] ?? null;

        if ($authorization) {
            if (strpos($authorization, ' ') === false) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid authorization format, expected 'Bearer token'"]);
                exit;
            }

            list($type, $token) = explode(' ', $authorization, 2);

            if ($type === 'Bearer' && !empty($token)) {
                try {
                    $jwtSecret = $_ENV["JWT_SECRET"] ?? null;
                    if (!$jwtSecret) {
                        http_response_code(500);
                        echo json_encode(["error" => "JWT_SECRET is not set in the environment."]);
                        exit;
                    }

                    $jwtCodec = new JWTCodec($jwtSecret);
                    $decoded = $jwtCodec->decode($token);
                    $GLOBALS['user'] = $decoded['user'];
                    return;
                } catch (\Exception $e) {
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
            http_response_code(401);
            echo json_encode(["error" => "Authorization header missing"]);
            exit;
        }
    }
}
