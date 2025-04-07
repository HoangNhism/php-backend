<?php
require_once __DIR__ . '/../models/Chat.php';

class ChatController {
    private $chatModel;

    public function __construct() {
        $this->chatModel = new Chat();
    }

    public function sendMessage($data) {
        $result = $this->chatModel->saveMessage($data);
        return [
            'success' => $result,
            'message' => $result ? 'Message sent successfully' : 'Failed to send message'
        ];
    }

    public function getMessages($user1Id, $user2Id) {
        $messages = $this->chatModel->getMessagesBetweenUsers($user1Id, $user2Id);
        return [
            'success' => true,
            'data' => $messages
        ];
    }
}