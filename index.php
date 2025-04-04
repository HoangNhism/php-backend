<?php

// Add this at the top for debugging
error_log("[DEBUG] Request URI: " . $_SERVER['REQUEST_URI']);

require_once __DIR__ . '/vendor/autoload.php';
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
$isApiRoute = strpos($_SERVER['REQUEST_URI'], '/api') !== false;
error_log("[DEBUG] Is API route: " . ($isApiRoute ? "yes" : "no"));

// Set content type based on route type
if ($isApiRoute) {
    header('Content-Type: application/json');
}

// Load route files
require_once './routes/api.php';
require_once './routes/web.php';
require_once './routes/attendanceRoute.php';

// Add this test route
$router->get('/test-api', function() {
    return json_encode(['status' => 'success', 'message' => 'API Router is working!']);
});

// Add the new API test route
$router->get('/api/test', function() {
    return json_encode(['status' => 'success', 'message' => 'API route works!']);
});

// Handle 404 Not Found
$router->notFound(function() use ($isApiRoute) {
    header("HTTP/1.0 404 Not Found");
    
    if ($isApiRoute) {
        return json_encode([
            'error' => 'Route not found',
            'status' => 404,
            'requested_uri' => $_SERVER['REQUEST_URI']
        ]);
    } else {
        return '<h1>404 Not Found</h1><p>The page you requested could not be found.</p>';
    }
});

// Resolve the current route
$router->resolve();
?>