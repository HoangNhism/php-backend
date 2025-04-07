<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../controllers/PayrollController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$payrollController = new PayrollController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Create payroll
$router->post('/api/payroll/create', function() use ($payrollController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin', 'Accountant']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $payrollController->createPayroll($data, $user);
    return json_encode($result);
});

// Get all payrolls
$router->get('/api/payroll/all', function() use ($payrollController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin', 'Accountant']);
    
    $result = $payrollController->getAllPayrolls();
    return json_encode($result);
});

// Get payroll by ID
$router->get('/api/payroll/:id', function($id) use ($payrollController, $authMiddleware) {
    $user = $authMiddleware->handle();
    $result = $payrollController->getPayrollById($id);
    return json_encode($result);
});

// Update payroll
$router->put('/api/payroll/update/:id', function($id) use ($payrollController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin', 'Accountant']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $data['user'] = $user;
    
    $result = $payrollController->updatePayroll($id, $data);
    return json_encode($result);
});

// Delete payroll
$router->delete('/api/payroll/delete/:id', function($id) use ($payrollController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin', 'Accountant']);
    
    $result = $payrollController->deletePayroll($id);
    return json_encode($result);
});

// Export payroll to PDF
$router->get('/api/payroll/export/:id', function($id) use ($payrollController, $authMiddleware) {
    $user = $authMiddleware->handle();
    $result = $payrollController->exportPayrollPDF($id);
    return json_encode($result);
});