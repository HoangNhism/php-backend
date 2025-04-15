<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$userController = new UserController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Public routes
$router->post('/api/register', function () use ($userController) {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->createUser($data);
    return json_encode([
        'message' => $result ? 'Registration successful' : 'Registration failed',
        'success' => $result
    ]);
});

// Protected routes - Admin only
$router->group(['before' => function () use ($authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin']);
}], function () use ($router, $userController) {

    $router->get('/api/user', function () use ($userController) {
        return json_encode($userController->getAllUsers());
    });

    $router->post('/api/user', function () use ($userController) {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $userController->createUser($data);
        return json_encode([
            'message' => $result ? 'User created successfully' : 'Failed to create user',
            'success' => $result
        ]);
    });

    $router->delete('/api/user/:id', function ($id) use ($userController) {
        return json_encode([
            'success' => $userController->deleteUser($id)
        ]);
    });

    $router->put('/api/user/:id/block', function ($id) use ($userController) {
        return json_encode([
            'success' => $userController->blockUser($id)
        ]);
    });

    $router->put('/api/user/:id/unblock', function ($id) use ($userController) {
        return json_encode([
            'success' => $userController->unblockUser($id)
        ]);
    });

    $router->get('/api/user/role/:role', function ($role) use ($userController) {
        return json_encode($userController->getUsersByRole($role));
    });

    $router->get('/api/user/blocked/all', function () use ($userController) {
        return json_encode($userController->getBlockedUsers());
    });
});

// Protected routes - Any authenticated user
$router->group(['before' => function () use ($authMiddleware) {
    $authMiddleware->handle();
}], function () use ($router, $userController) {

    $router->get('/api/user/:id', function ($id = null) use ($userController) {
        if (!$id) {
            http_response_code(400);
            return json_encode(['error' => 'User ID is required']);
        }
        return json_encode($userController->getUserById($id));
    });

    $router->put('/api/user/:id', function ($id = null) use ($userController) {
        if (!$id) {
            http_response_code(400);
            return json_encode(['error' => 'User ID is required']);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $userController->updateUser($id, $data);
        return json_encode([
            'message' => $result ? 'User updated successfully' : 'Failed to update user',
            'success' => $result
        ]);
    });

    $router->get('/api/user/:id/notifications', function ($id = null) use ($userController) {
        if (!$id) {
            http_response_code(400);
            return json_encode(['error' => 'User ID is required']);
        }
        return json_encode($userController->getUserNotifications($id));
    });
});
