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
    return json_encode([
        'message' => $result ? 'User created successfully' : 'Failed to create user',
        'success' => $result
    ]);
});

$router->put('/api/users/:id', function ($id) use ($userController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->updateUser($id, $data);
    return json_encode([
        'message' => $result ? 'User updated successfully' : 'Failed to update user',
        'success' => $result
    ]);
});

$router->delete('/api/users/:id', function ($id) use ($userController, $authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle(); // Validate token
    $roleMiddleware->handle($user, 'Admin'); // Restrict to Admin role

    $result = $userController->deleteUser($id);
    return json_encode([
        'message' => $result ? 'User deleted successfully' : 'Failed to delete user',
        'success' => $result
    ]);
});

$router->get('/api/users/field/:field/:value', function ($field, $value) use ($userController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $users = $userController->getUsersByField($field, $value);
    return json_encode($users);
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

$router->post('/api/users/:id/upload', function ($id) use ($userController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $fileData = [
        'fileName' => $_FILES['file']['name'],
        'fileUrl' => $_FILES['file']['tmp_name'],
        'fileType' => pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)
    ];
    $result = $userController->uploadFile($id, $fileData);
    return json_encode([
        'message' => $result ? 'File uploaded successfully' : 'Failed to upload file',
        'success' => $result
    ]);
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
