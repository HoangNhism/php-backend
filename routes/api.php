<?php
require_once __DIR__ . '/userRoute.php';
require_once __DIR__ . '/authRoute.php';
<<<<<<< HEAD
require_once __DIR__ . '/projectRoute.php'; // Add this line
require_once __DIR__ . '/projectMemberRoute.php'; // Add this line
require_once __DIR__ . '/taskRoute.php'; // Add this line
=======
require_once __DIR__ . '/leaveRoute.php';
>>>>>>> origin/Loc

$router = $GLOBALS['router'];

// General API route
$router->get('/api', function() {
    return json_encode([
        'message' => 'Welcome to the API',
        'version' => '1.0.0'
    ]);
});