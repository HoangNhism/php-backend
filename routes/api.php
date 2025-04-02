<?php
require_once __DIR__ . '/userRoute.php';
require_once __DIR__ . '/authRoute.php';
require_once __DIR__ . '/leaveRoute.php';
require_once __DIR__ . '/projectRoute.php'; // Add this line
require_once __DIR__ . '/projectMemberRoute.php'; // Add this line
require_once __DIR__ . '/taskRoute.php'; // Add this line

$router = $GLOBALS['router'];

// General API route
$router->get('/api', function() {
    return json_encode([
        'message' => 'Welcome to the API',
        'version' => '1.0.0'
    ]);
});