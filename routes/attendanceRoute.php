<?php
error_log("[DEBUG] Loading attendanceRoute.php file");

// Fix path issues - added missing slash
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

error_log("[DEBUG] Required files loaded");

$router = $GLOBALS['router'];
error_log("[DEBUG] Router retrieved from globals");

try {
    $attendanceController = new AttendanceController();
    error_log("[DEBUG] AttendanceController instantiated successfully");
} catch (Exception $e) {
    error_log("[ERROR] Failed to instantiate AttendanceController: " . $e->getMessage());
    error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
}

$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

/**
 * @route   POST /api/attendance/check-in
 * @desc    Record employee check-in
 * @access  Public (For testing)
 */
$router->post('/api/attendance/check-in', function () use ($attendanceController) {
    error_log("[DEBUG] POST /api/attendance/check-in route accessed");
    
    $input = file_get_contents('php://input');
    error_log("[DEBUG] Raw request body: " . $input);
    
    $data = json_decode($input, true);
    error_log("[DEBUG] Parsed request data: " . json_encode($data));
    
    // Require user_id in request body for testing
    $userId = $data['user_id'] ?? null;
    error_log("[DEBUG] Extracted user_id: " . ($userId ?? "null"));
    
    if (!$userId) {
        error_log("[ERROR] user_id is required but not provided");
        return json_encode([
            'status' => 'error',
            'message' => 'user_id is required in request body for testing'
        ]);
    }
    
    error_log("[DEBUG] Calling attendanceController->arrive with user_id: " . $userId);
    $result = $attendanceController->arrive($userId, $data);
    error_log("[DEBUG] Result from arrive: " . json_encode($result));
    
    return json_encode($result);
});

/**
 * @route   POST /api/attendance/check-out
 * @desc    Record employee check-out
 * @access  Public (For testing)
 */
$router->post('/api/attendance/check-out', function () use ($attendanceController) {
    error_log("[DEBUG] POST /api/attendance/check-out route accessed");
    
    $input = file_get_contents('php://input');
    error_log("[DEBUG] Raw request body: " . $input);
    
    $data = json_decode($input, true);
    error_log("[DEBUG] Parsed request data: " . json_encode($data));
    
    // Require user_id in request body for testing
    $userId = $data['user_id'] ?? null;
    error_log("[DEBUG] Extracted user_id: " . ($userId ?? "null"));
    
    if (!$userId) {
        error_log("[ERROR] user_id is required but not provided");
        return json_encode([
            'status' => 'error',
            'message' => 'user_id is required in request body for testing'
        ]);
    }
    
    error_log("[DEBUG] Calling attendanceController->leave with user_id: " . $userId);
    $result = $attendanceController->leave($userId, $data);
    error_log("[DEBUG] Result from leave: " . json_encode($result));
    
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/currentuser
 * @desc    Get current user's attendance status
 * @access  Public (For testing)
 */
$router->get('/api/attendance/currentuser', function () use ($attendanceController) {
    error_log("[DEBUG] GET /api/attendance/currentuser route accessed");
    error_log("[DEBUG] Query parameters: " . json_encode($_GET));
    
    // Require user_id as query parameter for testing
    $userId = $_GET['user_id'] ?? null;
    error_log("[DEBUG] Extracted user_id from query: " . ($userId ?? "null"));
    
    if (!$userId) {
        error_log("[ERROR] user_id query parameter is required but not provided");
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required for testing'
        ]);
    }
    
    error_log("[DEBUG] Calling attendanceController->getCurrentUserStatus with user_id: " . $userId);
    $result = $attendanceController->getCurrentUserStatus($userId);
    error_log("[DEBUG] Result from getCurrentUserStatus: " . json_encode($result));
    
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/history
 * @desc    Get user's attendance history with optional filters
 * @access  Public (For testing)
 */
$router->get('/api/attendance/history', function () use ($attendanceController) {
    error_log("[DEBUG] GET /api/attendance/history route accessed");
    error_log("[DEBUG] Query parameters: " . json_encode($_GET));
    
    // Require user_id as query parameter for testing
    $userId = $_GET['user_id'] ?? null;
    error_log("[DEBUG] Extracted user_id from query: " . ($userId ?? "null"));
    
    if (!$userId) {
        error_log("[ERROR] user_id query parameter is required but not provided");
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required for testing'
        ]);
    }
    
    // Extract optional filter parameters
    $params = [
        'start_date' => $_GET['start_date'] ?? null,
        'end_date' => $_GET['end_date'] ?? null,
        'status' => $_GET['status'] ?? null
    ];
    
    error_log("[DEBUG] Filter parameters: " . json_encode($params));
    
    error_log("[DEBUG] Calling attendanceController->getUserAttendanceHistory with user_id: " . $userId);
    $result = $attendanceController->getUserAttendanceHistory($userId, $params);
    error_log("[DEBUG] Result from getUserAttendanceHistory: " . json_encode($result));
    
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/today
 * @desc    Get all users' attendance for today (Admin function)
 * @access  Public (For testing)
 */
$router->get('/api/attendance/today', function () use ($attendanceController) {
    error_log("[DEBUG] GET /api/attendance/today route accessed");
    
    error_log("[DEBUG] Calling attendanceController->getTodayAttendance");
    $result = $attendanceController->getTodayAttendance();
    error_log("[DEBUG] Result from getTodayAttendance: " . json_encode($result));
    
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/stats
 * @desc    Get attendance statistics for a user (e.g., monthly summary)
 * @access  Public (For testing)
 */
$router->get('/api/attendance/stats', function () use ($attendanceController) {
    error_log("[DEBUG] GET /api/attendance/stats route accessed");
    error_log("[DEBUG] Query parameters: " . json_encode($_GET));
    
    // Require user_id as query parameter for testing
    $userId = $_GET['user_id'] ?? null;
    error_log("[DEBUG] Extracted user_id from query: " . ($userId ?? "null"));
    
    if (!$userId) {
        error_log("[ERROR] user_id query parameter is required but not provided");
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required for testing'
        ]);
    }
    
    // Extract month and year parameters
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    error_log("[DEBUG] Month parameter: " . $month);
    error_log("[DEBUG] Year parameter: " . $year);
    
    // This endpoint would require implementing a new method in your controller
    // For now, returning a placeholder response
    return json_encode([
        'status' => 'error',
        'message' => 'This feature is not yet implemented',
        'debug_info' => 'AttendanceController needs a getAttendanceStats method'
    ]);
});

error_log("[DEBUG] attendanceRoute.php loaded successfully");