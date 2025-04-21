<?php

/*
* -----------------------------------------------------------------------------
* Attribute: Route
* -----------------------------------------------------------------------------
* This attribute can be used to define HTTP routes for controller methods.
* It supports route path definitions, HTTP methods, and authentication flags.
*
* Usage Example:
*  #[Route('/users/{id}', method: 'GET', requiresAuth: true)]
*
* Parameters:
*  - string $path          : The URL path for the route (e.g., '/users/{id}')
*  - string $method        : HTTP method for the route (GET, POST, etc.)
*  - bool $requiresAuth    : Whether authentication is required for access
*
* Created by: Nihad Namatli
* -----------------------------------------------------------------------------
*/
#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    /*
    * -------------------------------------------------------------------------
    * Constructor: __construct
    * -------------------------------------------------------------------------
    * Initializes the Route attribute with path, HTTP method, and auth flag.
    *
    * Parameters:
    *  - string $path         : URL path to associate with this route
    *  - string $method       : HTTP method (default is 'GET')
    *  - bool $requiresAuth   : Whether route requires authentication
    *
    * Return Type:
    *  - void
    * -------------------------------------------------------------------------
    */
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public bool $requiresAuth = true
    ) {}
}
