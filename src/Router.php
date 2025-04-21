<?php


/**
 * Router class for handling HTTP requests and routing them to controller actions
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
     * Register a controller and its routes
     * 
     * @param string $controllerClass Fully qualified controller class name
     * @param mixed $gateway Gateway instance to inject into controller
     * @return void
     */
    public function registerController(string $controllerClass, $gateway = null): void
    {
        // Create controller instance
        $controller = $gateway ? new $controllerClass($gateway) : new $controllerClass();
        $this->controllers[$controllerClass] = $controller;
        
        // Get reflection class
        $reflectionClass = new \ReflectionClass($controllerClass);
        
        // Get all methods
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        // Loop through methods to find Route attributes
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Route::class);
            
            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                
                // Register the route
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
     * Dispatch an HTTP request to the appropriate controller action
     * 
     * @param string $requestPath The request URL path
     * @param string $requestMethod The HTTP method
     * @return void
     */
    public function dispatch(string $requestPath, string $requestMethod): void
    {
        // Clean up request path
        $requestPath = trim($requestPath, '/');
        
        // Check for matching routes
        foreach ($this->routes as $route) {
            // Convert route path to regex pattern
            $pattern = $this->convertRouteToRegex($route['path']);
            
            // Check if method matches
            if ($route['method'] !== $requestMethod && $route['method'] !== '*') {
                continue;
            }
            
            // Check if path matches
            if (preg_match($pattern['regex'], $requestPath, $matches)) {
                // Extract route parameters
                $params = [];
                foreach ($pattern['params'] as $index => $name) {
                    $params[$name] = $matches[$index + 1];
                }
                
                // Check if authentication is required
                if ($route['requiresAuth']) {
                    $this->authenticate();
                }
                
                // Call the controller action
                $controller = $this->controllers[$route['controller']];
                $action = $route['action'];
                
                // Call the method with parameters
                call_user_func_array([$controller, $action], $params);
                exit;
            }
        }
        
        // No route matched
        http_response_code(404);
        echo json_encode(["error" => "Route not found"]);
    }
    
    /**
     * Convert a route path with parameters to a regex pattern
     * 
     * @param string $route The route path
     * @return array The regex pattern and parameter names
     */
    private function convertRouteToRegex(string $route): array
    {
        $params = [];
        $pattern = preg_replace_callback('/{([^}]+)}/', function($matches) use (&$params) {
            $params[] = $matches[1];
            return '([^/]+)';
        }, $route);
        
        return [
            'regex' => '#^' . $pattern . '$#',
            'params' => $params
        ];
    }
    
    /**
     * Authenticate the current request using JWT
     * 
     * @return void
     * @throws \Exception If authentication fails
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

                    // Store user info in global variable or request context
                    $GLOBALS['user'] = $decoded['user'];
                    return;

                } catch (\Exception $e) {
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
}