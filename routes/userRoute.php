<?php
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$userController = new UserController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// User-related routes
$router->get('/api/users', function () use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $users = $userController->getAllUsers();
    return json_encode($users);
});

$router->get('/api/users/:id', function ($id) use ($userController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $user = $userController->getUserById($id);
    return json_encode($user);
});

$router->post('/api/register', function () use ($userController) {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->createUser($data);
    return json_encode([
        'message' => $result ? 'Đăng ký tài khoản thành công' : 'Đăng ký tài khoản thất bại',
        'success' => $result
    ]);
});

$router->post('/api/users', function () use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->createUser($data);
    return json_encode($result);
});

// $router->post('/api/users', function () use ($userController) {
//     $data = json_decode(file_get_contents('php://input'), true);
//     $result = $userController->createUser($data);
//     return json_encode($result);
// });

$router->put('/api/users/:id', function ($id) use ($userController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->updateUser($id, $data);
    return json_encode($result);
});

$router->put('/api/users/:id/password', function ($id) use ($userController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    // Decode the JSON input and extract the old and new passwords
    $data = json_decode(file_get_contents('php://input'), true);
    $oldPassword = $data['oldPassword'] ?? null;
    $newPassword = $data['newPassword'] ?? null;

    if ($oldPassword === null || $newPassword === null) {
        return json_encode([
            'success' => false,
            'message' => 'Old and new passwords are required'
        ]);
    }

    $result = $userController->changePassword($id, $oldPassword, $newPassword);
    return json_encode($result);
});

$router->delete('/api/users/:id', function ($id) use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $result = $userController->deleteUser($id);
    return json_encode($result);
});

$router->put('/api/users/:id/block', function ($id) use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $result = $userController->blockUser($id);
    return json_encode($result);
});

$router->get('/api/users/blocked-users', function () use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $result = $userController->getBlockedUsers();
    return json_encode($result);
});

$router->put('/api/users/:id/unblock', function ($id) use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $result = $userController->unblockUser($id);
    return json_encode($result);
});


$router->put('/api/users/:id/block', function ($id) use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $result = $userController->blockUser($id);
    return json_encode([
        'message' => $result ? 'User blocked successfully' : 'Failed to block user',
        'success' => $result
    ]);
});
