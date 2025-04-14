<?php
require_once __DIR__ . '/userRoute.php';
require_once __DIR__ . '/authRoute.php';
require_once __DIR__ . '/leaveRoute.php';
require_once __DIR__ . '/projectRoute.php'; 
require_once __DIR__ . '/projectMemberRoute.php'; 
require_once __DIR__ . '/taskRoute.php'; 
require_once __DIR__ . '/attendanceRoute.php';
require_once __DIR__ . '/prRoute.php';
require_once __DIR__ . '/payrollRoute.php'; 

$router = $GLOBALS['router'];

// General API route
$router->get('/api', function() {
    return json_encode([
        'message' => 'Welcome to the API',
        'version' => '1.0.0'
    ]);
});

// Default notifications endpoint khi không có userId
$router->get('/api/notifications', function() {
    return json_encode([
        'success' => true,
        'data' => [],
        'message' => 'Không có thông báo'
    ]);
});

// Add a debug route to check registered routes
$router->get('/api/debug/routes', function () use ($router) {
    return json_encode([
        'success' => true,
        'routes' => $router->getRegisteredRoutes() // You'll need to implement this method
    ]);
});