<?php

/**
 * Route attribute for defining HTTP routes on controller methods
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Route
{
    /**
     * Constructor
     * 
     * @param string $path The URL path for this route (can include parameters like {id})
     * @param string $method The HTTP method (GET, POST, etc.)
     * @param bool $requiresAuth Whether this route requires authentication
     */
    public function __construct(
        public string $path,
        public string $method = 'GET',
        public bool $requiresAuth = true
    ) {}
}