<?php
require_once __DIR__ . '/../controllers/AuthController.php';

$router = $GLOBALS['router'];
$authController = new AuthController();

// Authentication-related routes
$router->post('/api/auth/login', function () use ($authController) {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$email || !$password) {
        return json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
    }

    $response = $authController->login($email, $password);
    return json_encode($response);
});
