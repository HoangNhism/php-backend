<?php
require_once __DIR__ . '/../controllers/UserController.php';

$router = $GLOBALS['router'];
$userController = new UserController();

// User-related routes
$router->get('/api/users', function () use ($userController) {
    $users = $userController->getAllUsers();
    return json_encode($users);
});

$router->get('/api/users/:id', function ($id) use ($userController) {
    $user = $userController->getUserById($id);
    return json_encode($user);
});

$router->post('/api/users', function () use ($userController) {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->createUser($data);
    return json_encode([
        'message' => $result ? 'User created successfully' : 'Failed to create user',
        'success' => $result
    ]);
});

$router->put('/api/users/:id', function ($id) use ($userController) {
    $data = json_decode(file_get_contents('php://input'), true);
    $result = $userController->updateUser($id, $data);
    return json_encode([
        'message' => $result ? 'User updated successfully' : 'Failed to update user',
        'success' => $result
    ]);
});

$router->delete('/api/users/:id', function ($id) use ($userController) {
    $result = $userController->deleteUser($id);
    return json_encode([
        'message' => $result ? 'User deleted successfully' : 'Failed to delete user',
        'success' => $result
    ]);
});
