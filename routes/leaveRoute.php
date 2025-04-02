<?php
// API routes for leave requests
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Xử lý preflight request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include controller
require_once __DIR__ . '/../controllers/LeaveController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$leaveController = new LeaveController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// API test - kiểm tra route có hoạt động không
$router->get('/api/leave/test', function () {
    return json_encode(array("message" => "Leave API route is working!"));
});

// API yêu cầu nghỉ phép
$router->post('/api/leave/request', function () use ($leaveController, $authMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $leaveController->requestLeave($user, $data);
    return json_encode($result);
});

// API xử lý yêu cầu nghỉ phép (phê duyệt/từ chối)
$router->post('/api/leave/process', function () use ($leaveController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, ['Admin', 'Manager']); // Restrict to Admin or Manager
    
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $leaveController->processLeaveRequest($data);
    return json_encode($result);
});

// API lấy số ngày phép còn lại
$router->get('/api/leave/balance', function () use ($leaveController, $authMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    
    $result = $leaveController->getLeaveBalance($user);
    return json_encode($result);
});

// API khởi tạo số ngày nghỉ phép cho nhân viên
$router->post('/api/leave/initialize-balance', function () use ($leaveController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin
    
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $leaveController->initializeLeaveBalance($data);
    return json_encode($result);
});

// API lấy tất cả yêu cầu nghỉ phép (Admin/Manager only)
$router->get('/api/leave/all', function () use ($leaveController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, ['Admin', 'Manager']); // Restrict to Admin or Manager
    
    $result = $leaveController->getAllLeaveRequests();
    return json_encode($result);
});

// API lấy yêu cầu nghỉ phép của user hiện tại
$router->get('/api/leave/my-requests', function () use ($leaveController, $authMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    
    $result = $leaveController->getUserLeaveRequests($user);
    return json_encode($result);
});
?> 