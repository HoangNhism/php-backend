<?php
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';

class ChatController
{
    private $chatModel;
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->chatModel = new Chat($db);
        $this->userModel = new UserModel($db);
    }

    public function getAllEmployees()
    {
        try {
            $users = $this->userModel->getUsers();
            return [
                'success' => true,
                'data' => $users
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch employees',
                'error' => $e->getMessage()
            ];
        }
    }


    public function getMessages($user1_id, $user2_id)
    {
        try {
            $messages = $this->chatModel->getMessages($user1_id, $user2_id);
            error_log("Messages from model: " . json_encode($messages));
            return [
                'success' => true,
                'data' => $messages
            ];
        } catch (Exception $e) {
            error_log("Error in getMessages: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to retrieve messages'
            ];
        }
    }

    public function sendMessage($data)
    {
        try {
            if (!isset($data['sender_id']) || !isset($data['receiver_id']) || !isset($data['message'])) {
                return [
                    'success' => false,
                    'message' => 'Missing required fields'
                ];
            }

            $this->chatModel->sender_id = $data['sender_id'];
            $this->chatModel->receiver_id = $data['receiver_id'];
            $this->chatModel->message = $data['message'];

            if ($this->chatModel->create()) {
                return [
                    'success' => true,
                    'data' => [
                        'id' => $this->chatModel->id,
                        'sender_id' => $data['sender_id'],
                        'receiver_id' => $data['receiver_id'],
                        'message' => $data['message'],
                        'created_at' => $this->chatModel->created_at,
                    ],
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to save message',
            ];
        } catch (Exception $e) {
            error_log("Error sending message: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Server error while sending message',
            ];
        }
    }
}
