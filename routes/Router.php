<?php
class Router {
    private $routes = [];
    private $notFoundHandler;
    
    public function __construct() {
        error_log("[DEBUG] Router initialized");
    }
    
    /**
     * Register a GET route
     * 
     * @param string $path The URL path pattern to match
     * @param callable $callback The function to execute when the route is matched
     * @return Router
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
        error_log("[DEBUG] Registered GET route: " . $path);
        return $this;
    }

    /**
     * Register a POST route
     * 
     * @param string $path The URL path pattern to match
     * @param callable $callback The function to execute when the route is matched
     * @return Router
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
        error_log("[DEBUG] Registered POST route: " . $path);
        return $this;
    }

    /**
     * Register a PUT route
     * 
     * @param string $path The URL path pattern to match
     * @param callable $callback The function to execute when the route is matched
     * @return Router
     */
    public function put($path, $callback) {
        $this->routes['PUT'][$path] = $callback;
        error_log("[DEBUG] Registered PUT route: " . $path);
        return $this;
    }

    /**
     * Register a DELETE route
     * 
     * @param string $path The URL path pattern to match
     * @param callable $callback The function to execute when the route is matched
     * @return Router
     */
    public function delete($path, $callback) {
        $this->routes['DELETE'][$path] = $callback;
        error_log("[DEBUG] Registered DELETE route: " . $path);
        return $this;
    }

    /**
     * Register a 404 not found callback
     * 
     * @param callable $callback The function to execute when no routes match
     * @return Router
     */
    public function notFound($callback) {
        $this->notFoundHandler = $callback;
        return $this;
    }

    /**
     * Resolve the current route
     */
    public function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Get the path from the REQUEST_URI
        $uri = $_SERVER['REQUEST_URI'];
        error_log("[DEBUG] Original URI: " . $uri);
        
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        error_log("[DEBUG] URI without query: " . $uri);
        
        // No need to remove base folder since we're just using the root path
        // Comment out this section to remove /php-backend path handling
        /*
        $baseFolder = '/php-backend';
        if (strpos($uri, $baseFolder) === 0) {
            $uri = substr($uri, strlen($baseFolder));
        }
        */
        
        error_log("[DEBUG] Final processed URI: " . $uri);
        error_log("[DEBUG] REQUEST_METHOD: " . $method);
        
        // Check for direct route match
        if (isset($this->routes[$method][$uri])) {
            error_log("[DEBUG] Found exact route match: " . $uri);
            echo call_user_func($this->routes[$method][$uri]);
            return;
        }
        
        // Check for routes with parameters
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = $this->convertRouteToRegex($route);
            error_log("[DEBUG] Checking pattern: " . $pattern . " against URI: " . $uri);
            
            if (preg_match($pattern, $uri, $matches)) {
                error_log("[DEBUG] Found parameterized route match: " . $route);
                
                // Remove the full match
                array_shift($matches);
                
                echo call_user_func_array($callback, $matches);
                return;
            }
        }
        
        // No route found
        error_log("[DEBUG] No route found for: " . $uri);
        if ($this->notFoundHandler) {
            echo call_user_func($this->notFoundHandler);
        } else {
            echo "404 Not Found";
        }
    }

    /**
     * Convert a route pattern to a regex pattern
     * 
     * @param string $route The route pattern
     * @return string The regex pattern
     */
    private function convertRouteToRegex($route) {
        // Convert routes like '/api/users/:id' to regex patterns
        // that capture the parameters
        $pattern = preg_replace('/\/:([^\/]+)/', '/([^/]+)', $route);
        return "#^" . $pattern . "$#";
    }
}
?>