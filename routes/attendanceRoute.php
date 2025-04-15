<?php
require_once __DIR__ . '/../controllers/AttendanceController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$attendanceController = new AttendanceController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Protected routes - Any authenticated user
$router->group(['before' => function () use ($authMiddleware) {
    // This will store the user data in $GLOBALS['currentUser']
    $authMiddleware->handle();
}], function () use ($router, $attendanceController) {

    /**
     * @route   POST /api/attendance/check-in
     * @desc    Record employee check-in
     * @access  Private - Requires authentication token
     */
    $router->post('/api/attendance/check-in', function () use ($attendanceController) {
        try {
            // Get the Authorization header and extract the token
            $authHeader = getallheaders()['Authorization'] ?? '';
            $token = '';
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
            
            // Decode the token manually
            $tokenParts = explode('.', $token);
            $decodedTokenManual = null;
            if (count($tokenParts) === 3) {
                $payload = $tokenParts[1];
                $payload = str_replace(['-', '_'], ['+', '/'], $payload);
                $payload = base64_decode($payload);
                $decodedTokenManual = json_decode($payload, true);
            }
            
            // If we have a decoded token, use it for authentication
            if ($decodedTokenManual && isset($decodedTokenManual['id'])) {
                // Set the current user in the global variable
                $GLOBALS['currentUser'] = $decodedTokenManual;
            }
            
            // Get user ID from the global variable
            if (!isset($GLOBALS['currentUser']) || !isset($GLOBALS['currentUser']['id'])) {
                header('Content-Type: application/json');
                return json_encode([
                    'status' => 'error',
                    'message' => 'Authentication failed. Please log in again.'
                ]);
            }
            
            $userId = $GLOBALS['currentUser']['id'];
            $currentTime = new DateTime();
            $currentTime->modify('+7 hours');
            
            // Always use current time for check-in
            $data = [
                'check_in_time' => $currentTime->format('Y-m-d H:i:s')
            ];
            
            // Get GPS location from request if available
            $requestData = json_decode(file_get_contents('php://input'), true);
            if (isset($requestData['gps_location'])) {
                // Handle GPS location in different formats
                if (is_string($requestData['gps_location'])) {
                    $locationParts = explode(' ', $requestData['gps_location']);
                    if (count($locationParts) >= 2) {
                        $data['gps_location'] = [
                            'latitude' => (float)$locationParts[0],
                            'longitude' => (float)$locationParts[1]
                        ];
                    } else {
                        return json_encode([
                            'status' => 'error',
                            'message' => 'Invalid GPS location format. Expected "latitude longitude"',
                            'received_data' => $requestData['gps_location']
                        ]);
                    }
                } 
                // If gps_location is already an object with latitude and longitude
                else if (is_array($requestData['gps_location']) && isset($requestData['gps_location']['latitude']) && isset($requestData['gps_location']['longitude'])) {
                    $data['gps_location'] = $requestData['gps_location'];
                }
                // If gps_location is an array with latitude and longitude as first two elements
                else if (is_array($requestData['gps_location']) && count($requestData['gps_location']) >= 2) {
                    $data['gps_location'] = [
                        'latitude' => (float)$requestData['gps_location'][0],
                        'longitude' => (float)$requestData['gps_location'][1]
                    ];
                }
            }
            
            $result = $attendanceController->arrive($userId, $data);
            return json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    });

    /**
     * @route   POST /api/attendance/check-out
     * @desc    Record employee check-out
     * @access  Private - Requires authentication token
     */
    $router->post('/api/attendance/check-out', function () use ($attendanceController) {
        try {
            // Get the Authorization header and extract the token
            $authHeader = getallheaders()['Authorization'] ?? '';
            $token = '';
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
            
            // Decode the token manually
            $tokenParts = explode('.', $token);
            $decodedTokenManual = null;
            if (count($tokenParts) === 3) {
                $payload = $tokenParts[1];
                $payload = str_replace(['-', '_'], ['+', '/'], $payload);
                $payload = base64_decode($payload);
                $decodedTokenManual = json_decode($payload, true);
            }
            
            // If we have a decoded token, use it for authentication
            if ($decodedTokenManual && isset($decodedTokenManual['id'])) {
                // Set the current user in the global variable
                $GLOBALS['currentUser'] = $decodedTokenManual;
            }
            
            // Get user ID from the global variable
            if (!isset($GLOBALS['currentUser']) || !isset($GLOBALS['currentUser']['id'])) {
                header('Content-Type: application/json');
                return json_encode([
                    'status' => 'error',
                    'message' => 'Authentication failed. Please log in again.'
                ]);
            }
            
            $userId = $GLOBALS['currentUser']['id'];
            $currentTime = new DateTime();
            $currentTime->modify('+7 hours');
            
            // Always use current time for check-out
            $data = [
                'check_out_time' => $currentTime->format('Y-m-d H:i:s')
            ];
            
            // Get GPS location from request if available
            $requestData = json_decode(file_get_contents('php://input'), true);
            if (isset($requestData['gps_location'])) {
                // Handle GPS location in different formats
                if (is_string($requestData['gps_location'])) {
                    $locationParts = explode(' ', $requestData['gps_location']);
                    if (count($locationParts) >= 2) {
                        $data['gps_location'] = [
                            'latitude' => (float)$locationParts[0],
                            'longitude' => (float)$locationParts[1]
                        ];
                    } else {
                        return json_encode([
                            'status' => 'error',
                            'message' => 'Invalid GPS location format. Expected "latitude longitude"',
                            'received_data' => $requestData['gps_location']
                        ]);
                    }
                } 
                // If gps_location is already an object with latitude and longitude
                else if (is_array($requestData['gps_location']) && isset($requestData['gps_location']['latitude']) && isset($requestData['gps_location']['longitude'])) {
                    $data['gps_location'] = $requestData['gps_location'];
                }
                // If gps_location is an array with latitude and longitude as first two elements
                else if (is_array($requestData['gps_location']) && count($requestData['gps_location']) >= 2) {
                    $data['gps_location'] = [
                        'latitude' => (float)$requestData['gps_location'][0],
                        'longitude' => (float)$requestData['gps_location'][1]
                    ];
                }
            }
            
            $result = $attendanceController->leave($userId, $data);
            return json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    });

    /**
     * @route   GET /api/attendance/currentuser
     * @desc    Get current user's attendance status
     * @access  Private - Requires authentication token
     */
    $router->get('/api/attendance/currentuser', function () use ($attendanceController) {
        try {
            // Get the Authorization header and extract the token
            $authHeader = getallheaders()['Authorization'] ?? '';
            $token = '';
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
            
            // Decode the token manually
            $tokenParts = explode('.', $token);
            $decodedTokenManual = null;
            if (count($tokenParts) === 3) {
                $payload = $tokenParts[1];
                $payload = str_replace(['-', '_'], ['+', '/'], $payload);
                $payload = base64_decode($payload);
                $decodedTokenManual = json_decode($payload, true);
            }
            
            // If we have a decoded token, use it for authentication
            if ($decodedTokenManual && isset($decodedTokenManual['id'])) {
                // Set the current user in the global variable
                $GLOBALS['currentUser'] = $decodedTokenManual;
            }
            
            // Get user ID from the global variable
            if (!isset($GLOBALS['currentUser']) || !isset($GLOBALS['currentUser']['id'])) {
                header('Content-Type: application/json');
                return json_encode([
                    'status' => 'error',
                    'message' => 'Authentication failed. Please log in again.'
                ]);
            }
            
            $userId = $GLOBALS['currentUser']['id'];
            return json_encode($attendanceController->getCurrentUserStatus($userId));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    });

    /**
     * @route   GET /api/attendance/history
     * @desc    Get user's attendance history with optional filters
     * @access  Private - Requires authentication token
     */
    $router->get('/api/attendance/history', function () use ($attendanceController) {
        try {
            // Get the Authorization header and extract the token
            $authHeader = getallheaders()['Authorization'] ?? '';
            $token = '';
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
            
            // Decode the token manually
            $tokenParts = explode('.', $token);
            $decodedTokenManual = null;
            if (count($tokenParts) === 3) {
                $payload = $tokenParts[1];
                $payload = str_replace(['-', '_'], ['+', '/'], $payload);
                $payload = base64_decode($payload);
                $decodedTokenManual = json_decode($payload, true);
            }
            
            // If we have a decoded token, use it for authentication
            if ($decodedTokenManual && isset($decodedTokenManual['id'])) {
                // Set the current user in the global variable
                $GLOBALS['currentUser'] = $decodedTokenManual;
            }
            
            // Get user ID from the global variable
            if (!isset($GLOBALS['currentUser']) || !isset($GLOBALS['currentUser']['id'])) {
                header('Content-Type: application/json');
                return json_encode([
                    'status' => 'error',
                    'message' => 'Authentication failed. Please log in again.'
                ]);
            }
            
            $userId = $GLOBALS['currentUser']['id'];
            
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
                'message' => $e->getMessage()
            ]);
        }
    });
});

// Protected routes - Admin only
$router->group(['before' => function () use ($authMiddleware, $roleMiddleware) {
    // This will store the user data in $GLOBALS['currentUser']
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin']);
}], function () use ($router, $attendanceController) {

    /**
     * @route   GET /api/attendance/user
     * @desc    Get attendance data for all users
     * @access  Private - Requires authentication token and admin role
     */
    $router->get('/api/attendance/user', function () use ($attendanceController) {
        try {
            // Get the Authorization header and extract the token
            $authHeader = getallheaders()['Authorization'] ?? '';
            $token = '';
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
            
            // Decode the token manually
            $tokenParts = explode('.', $token);
            $decodedTokenManual = null;
            if (count($tokenParts) === 3) {
                $payload = $tokenParts[1];
                $payload = str_replace(['-', '_'], ['+', '/'], $payload);
                $payload = base64_decode($payload);
                $decodedTokenManual = json_decode($payload, true);
            }
            
            // If we have a decoded token, use it for authentication
            if ($decodedTokenManual && isset($decodedTokenManual['id'])) {
                // Set the current user in the global variable
                $GLOBALS['currentUser'] = $decodedTokenManual;
            }
            
            // Get user ID from the global variable
            if (!isset($GLOBALS['currentUser']) || !isset($GLOBALS['currentUser']['id'])) {
                header('Content-Type: application/json');
                return json_encode([
                    'status' => 'error',
                    'message' => 'Authentication failed. Please log in again.'
                ]);
            }
            
            // Prepare parameters for getAllUsersAttendance
            $params = [];
            
            // Handle date parameter
            if (isset($_GET['date'])) {
                $date = $_GET['date'];
                // If a specific date is provided, use it as both start and end date
                $params['start_date'] = $date;
                $params['end_date'] = $date;
            }
            
            // Handle department parameter if provided
            if (isset($_GET['department'])) {
                $params['department'] = $_GET['department'];
            }
            
            $result = $attendanceController->getAllUsersAttendance($params);
            
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
                'message' => $e->getMessage()
            ]);
        }
    });

    /**
     * @route   GET /api/attendance/today
     * @desc    Get today's attendance for the current user
     * @access  Private - Requires authentication token
     */
    $router->get('/api/attendance/today', function () use ($attendanceController) {
        try {
            // Get the Authorization header and extract the token
            $authHeader = getallheaders()['Authorization'] ?? '';
            $token = '';
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
            
            // Decode the token manually
            $tokenParts = explode('.', $token);
            $decodedTokenManual = null;
            if (count($tokenParts) === 3) {
                $payload = $tokenParts[1];
                $payload = str_replace(['-', '_'], ['+', '/'], $payload);
                $payload = base64_decode($payload);
                $decodedTokenManual = json_decode($payload, true);
            }
            
            // If we have a decoded token, use it for authentication
            if ($decodedTokenManual && isset($decodedTokenManual['id'])) {
                // Set the current user in the global variable
                $GLOBALS['currentUser'] = $decodedTokenManual;
            }
            
            // Get user ID from the global variable
            if (!isset($GLOBALS['currentUser']) || !isset($GLOBALS['currentUser']['id'])) {
                header('Content-Type: application/json');
                return json_encode([
                    'status' => 'error',
                    'message' => 'Authentication failed. Please log in again.'
                ]);
            }
            
            $userId = $GLOBALS['currentUser']['id'];
            $result = $attendanceController->getTodayAttendance($userId);
            
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
                'message' => $e->getMessage()
            ]);
        }
    });
});