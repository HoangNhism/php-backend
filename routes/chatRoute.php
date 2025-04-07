<?php
require_once __DIR__ . '/../controllers/ChatController.php';

$chatController = new ChatController();

$router->post('/api/chat/send', function () use ($chatController) {
    $data = json_decode(file_get_contents('php://input'), true);
    return json_encode($chatController->sendMessage($data));
});

$router->get('/api/chat/messages', function () use ($chatController) {
    $user1Id = $_GET['user1_id'];
    $user2Id = $_GET['user2_id'];
    return json_encode($chatController->getMessages($user1Id, $user2Id));
});