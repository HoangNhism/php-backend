<?php
require_once __DIR__ . '/../controllers/ProjectController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

$router = $GLOBALS['router'];
$projectController = new ProjectController();
$authMiddleware = new AuthMiddleware();

$router->post('/api/projects', function () use ($projectController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $projectController->createProject($data);
    return json_encode($result);
});

$router->get('/api/projects', function () use ($projectController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $projects = $projectController->getAllProjects();
    return json_encode($projects);
});

$router->get('/api/projects/:id', function ($id) use ($projectController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $project = $projectController->getProjectById($id);
    return json_encode($project);
});

$router->get('/api/projects/user/:user_id', function ($user_id) use ($projectController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $projects = $projectController->getProjectByUser($user_id);
    return json_encode($projects);
});

$router->put('/api/projects/:id', function ($id) use ($projectController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $projectController->updateProject($id, $data);
    return json_encode($result);
});

$router->delete('/api/projects/:id', function ($id) use ($projectController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $result = $projectController->deleteProject($id);
    return json_encode($result);
});
