<?php
require_once __DIR__ . '/../controllers/ProjectController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../middlewares/RoleMiddleware.php';

$router = $GLOBALS['router'];
$projectController = new ProjectController();
$authMiddleware = new AuthMiddleware();
$roleMiddleware = new RoleMiddleware();

// Protected routes - Any authenticated user
$router->group(['before' => function () use ($authMiddleware) {
    $authMiddleware->handle();
}], function () use ($router, $projectController) {

    $router->get('/api/project', function () use ($projectController) {
        return json_encode($projectController->getAllProjects());
    });

    $router->get('/api/project/:id', function ($id) use ($projectController) {
        return json_encode($projectController->getProjectById($id));
    });

    $router->get('/api/project/user/:user_id', function ($user_id) use ($projectController) {
        return json_encode($projectController->getProjectByUser($user_id));
    });
});

// Protected routes - Admin only
$router->group(['before' => function () use ($authMiddleware, $roleMiddleware) {
    $user = $authMiddleware->handle();
    $roleMiddleware->handle($user, ['Admin']);
}], function () use ($router, $projectController) {

    $router->post('/api/project', function () use ($projectController) {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode($projectController->createProject($data));
    });

    $router->put('/api/project/:id', function ($id) use ($projectController) {
        $data = json_decode(file_get_contents('php://input'), true);
        return json_encode($projectController->updateProject($id, $data));
    });

    $router->delete('/api/project/:id', function ($id) use ($projectController) {
        return json_encode($projectController->deleteProject($id));
    });
});
