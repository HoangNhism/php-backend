<?php
require_once __DIR__ . '../controllers/AttendanceController.php';
require_once __DIR__ . '../middlewares/AuthMiddleware.php';
require_once __DIR__ . '../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$attendanceController = new AttendanceController();
// $managerController = new ManagerController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

/**
 * @route   POST /api/attendance/check-in
 * @desc    Record employee check-in
 * @access  Public (For testing)
 */
$router->post('/api/attendance/check-in', function () use ($attendanceController) {
    $data = json_decode(file_get_contents('php://input'), true);
    // Require user_id in request body for testing
    $userId = $data['user_id'] ?? null;
    
    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id is required in request body for testing'
        ]);
    }
    
    $result = $attendanceController->arrive($userId, $data);
    return json_encode($result);
});

/**
 * @route   POST /api/attendance/check-out
 * @desc    Record employee check-out
 * @access  Public (For testing)
 */
$router->post('/api/attendance/check-out', function () use ($attendanceController) {
    $data = json_decode(file_get_contents('php://input'), true);
    // Require user_id in request body for testing
    $userId = $data['user_id'] ?? null;
    
    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id is required in request body for testing'
        ]);
    }
    
    $result = $attendanceController->leave($userId, $data);
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/currentuser
 * @desc    Get current user's attendance status
 * @access  Public (For testing)
 */
$router->get('/api/attendance/currentuser', function () use ($attendanceController) {
    // Require user_id as query parameter for testing
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required for testing'
        ]);
    }
    
    $result = $attendanceController->getCurrentUserStatus($userId);
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/user
 * @desc    Get attendance records for all users
 * @access  Public (For testing)
 */
// $router->get('/api/attendance/user', function () use ($managerController) {
//     $result = $managerController->getAllUsersAttendance();
//     return json_encode($result);
// });

/**
 * @route   GET /api/attendance/history
 * @desc    Get current user's attendance history
 * @access  Public (For testing)
 */
$router->get('/api/attendance/history', function () use ($attendanceController) {
    // Require user_id as query parameter for testing
    $userId = $_GET['user_id'] ?? null;
    
    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required for testing'
        ]);
    }
    
    // Get query parameters
    $params = [
        'start_date' => $_GET['start_date'] ?? null,
        'end_date' => $_GET['end_date'] ?? null,
        'status' => $_GET['status'] ?? null
    ];
    
    $result = $attendanceController->getUserAttendanceHistory($userId, $params);
    return json_encode($result);
});