<?php
require_once __DIR__ . '/userRoute.php';
require_once __DIR__ . '/authRoute.php';
require_once __DIR__ . '/leaveRoute.php';
require_once __DIR__ . '/projectRoute.php'; // Add this line
require_once __DIR__ . '/projectMemberRoute.php'; // Add this line
require_once __DIR__ . '/taskRoute.php'; // Add this line
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