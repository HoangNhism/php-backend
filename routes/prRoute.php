<?php
require_once __DIR__ . '/../controllers/PRController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Initialize database connection
require_once __DIR__ . '/../config/Database.php';
$database = new Database();
$db = $database->getConnection();
$prController = new PRController($db);

// Performance review-related routes
$router->get('/api/reviews', function () use ($prController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $prController->getAllReviews();
});

$router->get('/api/reviews/:id', function ($id) use ($prController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $prController->getReviewById($id);
});

$router->post('/api/reviews', function () use ($prController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Manager role

    $data = json_decode(file_get_contents('php://input'), true);
    $prController->addReview($data);
});

$router->put('/api/reviews/:id', function ($id) use ($prController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Manager role

    $data = json_decode(file_get_contents('php://input'), true);
    $prController->updateReview($id, $data);
});

$router->delete('/api/reviews/:id', function ($id) use ($prController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Manager role

    $prController->deleteReview($id);
});