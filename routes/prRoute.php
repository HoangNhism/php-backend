<?php
require_once __DIR__ . '/../controllers/PrController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$prController = new PrController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Create Performance Review
$router->post('/api/pr/createPr', function() use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    $data = json_decode(file_get_contents('php://input'), true);
    return json_encode($prController->createPr($data));
});

// Delete Performance Review
$router->delete('/api/pr/deletePr/:id', function($id) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->deletePr($id));
});

// Update Performance Review
$router->put('/api/pr/updatePr/:id', function($id) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    $data = json_decode(file_get_contents('php://input'), true);
    return json_encode($prController->updatePr($id, $data));
});

// Get All Performance Reviews
$router->get('/api/pr/performance-reviews', function() use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getPr());
});

// Get Performance Review by ID
$router->get('/api/pr/search/:id', function($id) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getPrById($id));
});

// Get Performance Reviews by User ID
$router->get('/api/pr/search/user/:user_id', function($user_id) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getPrByUserId($user_id));
});

// Get Performance Reviews by Reviewer ID
$router->get('/api/pr/search/reviewer/:reviewer_id', function($reviewer_id) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getPrByReviewerId($reviewer_id));
});

// Get Monthly Performance Reviews
$router->get('/api/pr/search/monthly/:year/:month', function($year, $month) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getMonthlyStats($year, $month));
});

// Get Quarterly Performance Reviews
$router->get('/api/pr/search/quarterly/:year/:quarter', function($year, $quarter) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getQuarterlyStats($year, $quarter));
});

// Get Yearly Performance Reviews
$router->get('/api/pr/search/yearly/:year', function($year) use ($prController, $authMiddleware) {
    $user = $authMiddleware->handle();
    return json_encode($prController->getYearlyStats($year));
});