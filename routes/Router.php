<?php
class Router {
    private $routes = [];
    private $notFoundCallback;

    /**
     * Register a GET route
     * 
     * @param string $path The URL path pattern to match
     * @param callable $callback The function to execute when the route is matched
     * @return Router
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
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
        return $this;
    }

    /**
     * Register a 404 not found callback
     * 
     * @param callable $callback The function to execute when no routes match
     * @return Router
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
        return $this;
    }

    /**
     * Resolve the current route
     */
    public function resolve() {
        // Get the HTTP method and URI path
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Parse the URI
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove base path from URL (if needed)
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $path = str_replace($basePath, '', $path);
        
        // Clean up the path
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }
        
        // Check for HTTP method override (for PUT, DELETE from forms)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        // Check if the route exists
        if (isset($this->routes[$method])) {
            // Check for exact match
            if (isset($this->routes[$method][$path])) {
                // Execute the callback
                $callback = $this->routes[$method][$path];
                echo call_user_func($callback);
                return;
            }
            
            // Check for dynamic routes with parameters
            foreach ($this->routes[$method] as $route => $callback) {
                // If the route doesn't have parameters, skip it
                if (strpos($route, ':') === false) {
                    continue;
                }
                
                // Convert route pattern to regex
                $pattern = $this->convertRouteToRegex($route);
                
                // Check if the path matches the pattern
                if (preg_match($pattern, $path, $matches)) {
                    // Remove the first match (the full match)
                    array_shift($matches);
                    
                    // Execute the callback with parameters
                    echo call_user_func_array($callback, $matches);
                    return;
                }
            }
        }
        
        // Route not found, call the not found callback
        if ($this->notFoundCallback) {
            echo call_user_func($this->notFoundCallback);
        } else {
            // Default 404 response
            header("HTTP/1.0 404 Not Found");
            echo '404 Not Found';
        }
    }

    /**
     * Convert a route pattern to a regex pattern
     * 
     * @param string $route The route pattern
     * @return string The regex pattern
     */
    private function convertRouteToRegex($route) {
        // Replace :param with capture groups
        $pattern = preg_replace('/:[a-zA-Z0-9]+/', '([^/]+)', $route);
        
        // Escape forward slashes and add delimiters
        $pattern = '#^' . $pattern . '$#';
        
        return $pattern;
    }
}
?>