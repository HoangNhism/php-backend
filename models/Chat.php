<?php
class Chat
{
    private $conn;
    private $table = 'chats';

    public $id;
    public $sender_id;
    public $receiver_id;
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
        // Test connection
        try {
            $this->conn->query("SELECT 1");
            error_log("Database connection successful");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $this->id = $this->generateUUID();

            error_log("Attempting to save chat with data: " . json_encode([
                'id' => $this->id,
                'sender_id' => $this->sender_id,
                'receiver_id' => $this->receiver_id,
                'message' => $this->message,
                'created_at' => $this->created_at
            ]));

            $query = "INSERT INTO chats 
                     (id, sender_id, receiver_id, message, created_at) 
                     VALUES (:id, :sender_id, :receiver_id, :message, :created_at)";

            $stmt = $this->conn->prepare($query);

            // Clean data
            $this->sender_id = htmlspecialchars(strip_tags($this->sender_id));
            $this->receiver_id = htmlspecialchars(strip_tags($this->receiver_id));
            $this->message = htmlspecialchars(strip_tags($this->message));
            $this->created_at = $this->created_at ?? date('Y-m-d H:i:s');

            // Bind parameters
            $stmt->bindParam(':id', $this->id);
            $stmt->bindParam(':sender_id', $this->sender_id);
            $stmt->bindParam(':receiver_id', $this->receiver_id);
            $stmt->bindParam(':message', $this->message);
            $stmt->bindParam(':created_at', $this->created_at);

            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                error_log("Database error: " . json_encode($error));
                return false;
            }

            return true;
        } catch (PDOException $e) {
            error_log("Chat create error: " . $e->getMessage());
            return false;
        }
    }

    // Hàm tạo UUID ngẫu nhiên
    private function generateUUID()
    {
        return bin2hex(random_bytes(8)); // Tạo chuỗi 16 ký tự ngẫu nhiên
    }


    public function getMessages($user1_id, $user2_id)
    {
        try {
            $query = "SELECT * FROM chats 
                     WHERE (sender_id = $user1_id AND receiver_id = $user2_id)
                        OR (sender_id = $user2_id AND receiver_id = $user1_id)
                     ORDER BY created_at ASC";
    
            $stmt = $this->conn->prepare($query);
    
            if (!$stmt->execute()) {
                error_log("Failed to execute chat query: " . json_encode($stmt->errorInfo()));
                return [];
            }
    
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Query result: " . json_encode($messages));
            return $messages;
        } catch (PDOException $e) {
            error_log("Database error in getMessages: " . $e->getMessage());
            return [];
        }
    }
}
