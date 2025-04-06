<?php
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$attendanceController = new AttendanceController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

/**
 * @route   POST /api/attendance/check-in
 * @desc    Record employee check-in
 * @access  Public (For testing)
 */
$router->post('/api/attendance/check-in', function () use ($attendanceController) {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id is required in request body'
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
    $userId = $data['user_id'] ?? null;

    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id is required in request body'
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
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required'
        ]);
    }

    $result = $attendanceController->getCurrentUserStatus($userId);
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/history
 * @desc    Get user's attendance history with optional filters
 * @access  Public (For testing)
 */
$router->get('/api/attendance/history', function () use ($attendanceController) {
    try {
        $userId = $_GET['user_id'] ?? null;

        if (!$userId) {
            throw new Exception('user_id query parameter is required');
        }

        if (strlen($userId) !== 16) {
            throw new Exception('Invalid user_id format. Must be 16 characters');
        }

        $params = [];

        if (isset($_GET['start_date'])) {
            $start_date = date('Y-m-d', strtotime($_GET['start_date']));
            if ($start_date === '1970-01-01') {
                throw new Exception('Invalid start date format. Use YYYY-MM-DD');
            }
            $params['start_date'] = $start_date;
        }

        if (isset($_GET['end_date'])) {
            $end_date = date('Y-m-d', strtotime($_GET['end_date']));
            if ($end_date === '1970-01-01') {
                throw new Exception('Invalid end date format. Use YYYY-MM-DD');
            }
            $params['end_date'] = $end_date;
        }

        if (isset($_GET['status'])) {
            $valid_statuses = ['present', 'absent', 'late'];
            if (!in_array($_GET['status'], $valid_statuses)) {
                throw new Exception('Invalid status. Must be one of: ' . implode(', ', $valid_statuses));
            }
            $params['status'] = $_GET['status'];
        }

        $result = $attendanceController->getUserAttendanceHistory($userId, $params);

        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        return json_encode($result);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);

        return json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
});

/**
 * @route   GET /api/attendance/today
 * @desc    Get all users' attendance for today (Admin function)
 * @access  Public (For testing)
 */
$router->get('/api/attendance/today', function () use ($attendanceController) {
    $result = $attendanceController->getTodayAttendance();
    return json_encode($result);
});

/**
 * @route   GET /api/attendance/stats
 * @desc    Get attendance statistics for a user (e.g., monthly summary)
 * @access  Public (For testing)
 */
$router->get('/api/attendance/stats', function () use ($attendanceController) {
    $userId = $_GET['user_id'] ?? null;

    if (!$userId) {
        return json_encode([
            'status' => 'error',
            'message' => 'user_id query parameter is required'
        ]);
    }

    return json_encode([
        'status' => 'error',
        'message' => 'This feature is not yet implemented',
        'debug_info' => 'AttendanceController needs a getAttendanceStats method'
    ]);
});