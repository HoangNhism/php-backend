<?php
// Load the router
require_once './routes/Router.php';

// Create a router instance
$router = new Router();
$GLOBALS['router'] = $router;

// Set default headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Determine if the request is for an API route
$isApiRoute = strpos($_SERVER['REQUEST_URI'], '/api') === 0;

// Set content type based on route type
if ($isApiRoute) {
    header('Content-Type: application/json');
}

// Load route files
require_once './routes/api.php';
require_once './routes/web.php';

// Handle 404 Not Found
$router->notFound(function() use ($isApiRoute) {
    header("HTTP/1.0 404 Not Found");
    
    if ($isApiRoute) {
        return json_encode([
            'error' => 'Route not found',
            'status' => 404
        ]);
    } else {
        return '<h1>404 Not Found</h1><p>The page you requested could not be found.</p>';
    }
});

// Resolve the current route
$router->resolve();
?>