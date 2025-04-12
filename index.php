<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once './routes/Router.php';

$router = new Router();
$GLOBALS['router'] = $router;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Set JSON content type for API routes
if (strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
    header('Content-Type: application/json');
}

try {
    // Load routes
    require_once './routes/api.php';
    require_once './routes/web.php';

    $response = $router->resolve();

    // Ensure proper JSON response
    if (is_string($response) && json_decode($response) === null) {
        $response = json_encode(['data' => $response]);
    }

    echo $response;
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>