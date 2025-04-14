<?php
class Router {
    private $routes = [];
    private $notFoundCallback;
    private $currentGroupMiddleware = null;

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
     * Group routes with middleware
     * 
     * @param array $options Group options including middleware
     * @param callable $callback The function to define the group routes
     */
    public function group($options, $callback)
    {
        // Store previous middleware if nested groups
        $previousMiddleware = $this->currentGroupMiddleware;

        // Set current group middleware
        $this->currentGroupMiddleware = $options['before'] ?? null;

        // Execute the group definition
        call_user_func($callback);

        // Restore previous middleware
        $this->currentGroupMiddleware = $previousMiddleware;
    }

    /**
     * Handle a route with middleware
     * 
     * @param string $method HTTP method
     * @param string $path URL path
     * @param callable $callback The route callback
     * @param array $matches Route parameters
     * @return mixed
     */
    private function handleRoute($method, $path, $callback, $matches = [])
    {
        if ($this->currentGroupMiddleware) {
            call_user_func($this->currentGroupMiddleware);
        }

        return call_user_func_array($callback, $matches);
    }

    /**
     * Resolve the current route
     */
    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Parse the URI and remove query string
        $path = parse_url($uri, PHP_URL_PATH);

        // Remove base path if it exists
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/') {
            $path = str_replace($basePath, '', $path);
        }

        // Ensure leading slash and remove trailing slash
        $path = '/' . trim($path, '/');

        error_log("Attempting to match - Method: $method, Path: $path");

        if (isset($this->routes[$method])) {
            // Try exact match first
            if (isset($this->routes[$method][$path])) {
                return $this->handleRoute($method, $path, $this->routes[$method][$path]);
            }

            // Try dynamic routes
            foreach ($this->routes[$method] as $route => $callback) {
                $pattern = $this->convertRouteToRegex($route);
                error_log("Checking route pattern: $pattern against path: $path");

                if (preg_match($pattern, $path, $matches)) {
                    // Filter out numeric keys
                    $params = array_filter($matches, function ($key) {
                        return !is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);

                    return $this->handleRoute($method, $path, $callback, array_values($params));
                }
            }
        }

        // Route not found
        header("HTTP/1.0 404 Not Found");
        return json_encode([
            'status' => 404,
            'message' => 'Route not found: ' . $path,
            'method' => $method,
            'available_routes' => array_keys($this->routes[$method] ?? [])
        ]);
    }

    /**
     * Convert a route pattern to a regex pattern
     * 
     * @param string $route The route pattern
     * @return string The regex pattern
     */
    private function convertRouteToRegex($route) {
        // Replace parameters before escaping
        $parameterized = preg_replace('/:([^\/]+)/', '(?P<$1>[^/]+)', $route);

        // Escape special characters except for ()
        $escaped = str_replace('/', '\/', $parameterized);

        // Add start and end markers
        return '/^' . $escaped . '$/';
    }

    /**
     * Get registered routes for debugging
     * 
     * @return array
     */
    public function getRegisteredRoutes()
    {
        return array_map(function ($methods) {
            return array_keys($methods);
        }, $this->routes);
    }
}
?>