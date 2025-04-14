<?php
require_once __DIR__ . '/../controllers/ProjectMemberController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

$router = $GLOBALS['router'];
$projectMemberController = new ProjectMemberController();
$authMiddleware = new AuthMiddleware();

// Specific routes with parameters should come first
$router->get('/api/project-member/:project_id', function ($project_id) use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle();
    header('Content-Type: application/json');
    return json_encode($projectMemberController->getProjectMembers($project_id));
});

$router->get('/api/project-member/user/:user_id', function ($user_id) use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle();
    header('Content-Type: application/json');
    return json_encode($projectMemberController->getProjectsByMember($user_id));
});

// Generic routes should come last
$router->get('/api/project-member', function () use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $members = $projectMemberController->getAllMembers();
    return json_encode($members);
});

$router->post('/api/project-member', function () use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $projectMemberController->addProjectMember($data['project_id'], $data['user_id']);
    return json_encode($result);
});

$router->delete('/api/project-member', function () use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $projectMemberController->removeMember($data['project_id'], $data['user_id']);
    return json_encode($result);
});
