<?php
require_once __DIR__ . '/../controllers/ChatController.php';
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';

$chatController = new ChatController();
$authMiddleware = new AuthMiddleware();

// Lấy danh sách nhân viên
$router->get('/api/chat/employees', function () use ($chatController) {
    return json_encode($chatController->getAllEmployees());
});

// Lấy tin nhắn giữa 2 user
$router->get('/api/chat/messages', function () use ($chatController) {
    $user1_id = $_GET['user1_id'] ?? null;
    $user2_id = $_GET['user2_id'] ?? null;

    error_log("Fetching messages for user1_id: $user1_id, user2_id: $user2_id");

    if (!$user1_id || !$user2_id) {
        return json_encode([
            'success' => false,
            'message' => 'Missing user IDs'
        ]);
    }

    $result = $chatController->getMessages($user1_id, $user2_id);
    error_log("Messages fetched: " . json_encode($result));

    return json_encode([
        'success' => true,
        'data' => $result
    ]);
});

// Send message
$router->post('/api/chat/send', function () use ($chatController, $authMiddleware) {
    try {
        /** @var \stdClass $user */
        $user = $authMiddleware->handle();   
        
        // Get and validate request data
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['receiver_id']) || !isset($data['message'])) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'message' => 'Missing required fields'
            ]);
        }

        // Add sender ID from authenticated user
        $data['sender_id'] = $user->id;
        
        // Log the attempt
        error_log('Attempting to send message: ' . json_encode($data));
        
        $result = $chatController->sendMessage($data);
        
        // Log the result
        error_log('Send message result: ' . json_encode($result));
        
        return json_encode($result);
        
    } catch (Exception $e) {
        error_log('Chat send error: ' . $e->getMessage());
        http_response_code(500);
        return json_encode([
            'success' => false,
            'message' => 'Server error while sending message'
        ]);
    }
});