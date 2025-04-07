<?php
class Chat {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function saveMessage($data) {
        $query = "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$data['sender_id'], $data['receiver_id'], $data['message']]);
    }

    public function getMessagesBetweenUsers($user1Id, $user2Id) {
        $query = "SELECT * FROM chat_messages WHERE 
                  (sender_id = ? AND receiver_id = ?) OR 
                  (sender_id = ? AND receiver_id = ?) 
                  ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user1Id, $user2Id, $user2Id, $user1Id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}