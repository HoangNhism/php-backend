<?php
require_once __DIR__ . '/../controllers/TaskController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$taskController = new TaskController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Protected routes - Any authenticated user
$router->group(['before' => function () use ($authMiddleware) {
    $authMiddleware->handle();
}], function () use ($router, $taskController) {

    $router->post('/api/task', function () use ($taskController) {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode($taskController->createTask($data));
    });

    $router->get('/api/task', function () use ($taskController) {
        return json_encode($taskController->getAllTasks());
    });

    $router->get('/api/task/:id', function ($id) use ($taskController) {
        return json_encode($taskController->getTaskById($id));
    });

    $router->get('/api/task/project/:project_id', function ($project_id) use ($taskController) {
        return json_encode($taskController->getTasksByProject($project_id));
    });

    $router->get('/api/task/user/:user_id', function ($user_id) use ($taskController) {
        return json_encode($taskController->getTasksByUser($user_id));
    });

    $router->put('/api/task/:id/status', function ($id) use ($taskController) {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode($taskController->updateTaskStatus($id, $data['status']));
    });

    $router->put('/api/task/:id/priority', function ($id) use ($taskController) {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode($taskController->updateTaskPriority($id, $data['priority']));
    });

    $router->delete('/api/task/:id', function ($id) use ($taskController) {
        return json_encode($taskController->deleteTask($id));
    });
});
