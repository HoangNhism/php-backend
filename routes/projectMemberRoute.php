<?php
require_once __DIR__ . '/../controllers/ProjectMemberController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

$router = $GLOBALS['router'];
$projectMemberController = new ProjectMemberController();
$authMiddleware = new AuthMiddleware();

$router->post('/api/project-members', function () use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $projectMemberController->addProjectMember($data['project_id'], $data['user_id']);
    return json_encode([
        'message' => $result ? 'Member added successfully' : 'Failed to add member',
        'success' => $result
    ]);
});

$router->get('/api/project-members', function () use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $members = $projectMemberController->getAllMembers();
    return json_encode($members);
});

$router->get('/api/project-members/:project_id', function ($project_id) use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $members = $projectMemberController->getProjectMembers($project_id);
    return json_encode($members);
});

$router->get('/api/project-members/user/:user_id', function ($user_id) use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $projects = $projectMemberController->getProjectsByMember($user_id);
    return json_encode($projects);
});

$router->delete('/api/project-members', function () use ($projectMemberController, $authMiddleware) {
    $authMiddleware->handle(); // Validate token

    $data = json_decode(file_get_contents('php://input'), true);
    $result = $projectMemberController->removeMember($data['project_id'], $data['user_id']);
    return json_encode([
        'message' => $result ? 'Member removed successfully' : 'Failed to remove member',
        'success' => $result
    ]);
});
