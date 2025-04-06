<?php
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

$router = $GLOBALS['router'];
$taskController = new TaskController();
$authMiddleware = new AuthMiddleware();

$router->post('/api/tasks', function () use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $taskController->createTask($data);
    return json_encode($result);
});

$router->get('/api/tasks', function () use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $tasks = $taskController->getAllTasks();
    return json_encode($tasks);
});

$router->get('/api/tasks/:id', function ($id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $task = $taskController->getTaskById($id);
    return json_encode($task);
});

$router->get('/api/tasks/project/:project_id', function ($project_id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $tasks = $taskController->getTasksByProject($project_id);
    return json_encode($tasks);
});

$router->get('/api/tasks/user/:user_id', function ($user_id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $tasks = $taskController->getTasksByUser($user_id);
    return json_encode($tasks);
});

$router->put('/api/tasks/:id/status', function ($id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $taskController->updateTaskStatus($id, $data['status']);
    return json_encode($result);
});

$router->put('/api/tasks/:id/priority', function ($id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $taskController->updateTaskPriority($id, $data['priority']);
    return json_encode($result);
});

$router->delete('/api/tasks/:id', function ($id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $result = $taskController->deleteTask($id);
    return json_encode($result);
});

$router->put('/api/tasks/:id/assignee', function ($id) use ($taskController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $taskController->changeAssignee($id, $data['new_user_id']);
    return json_encode($result);
});
