<?php
require_once __DIR__ . '/userRoute.php';
require_once __DIR__ . '/authRoute.php';

$router = $GLOBALS['router'];

// General API route
$router->get('/api', function() {
    return json_encode([
        'message' => 'Welcome to the API',
        'version' => '1.0.0'
    ]);
});