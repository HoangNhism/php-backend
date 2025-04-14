<?php
class NotificationModel
{
    private $conn;
    private $table_name = "notifications";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createNotification($userId, $type, $message, $link)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id, user_id, type, message, link, isRead, created_at) 
                  VALUES 
                  (:id, :user_id, :type, :message, :link, 0, CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);

        // Generate a random ID for the notification
        $id = bin2hex(random_bytes(8)); // Generates a 16-character random string

        // Sanitize input data
        $userId = htmlspecialchars(strip_tags($userId));
        $type = htmlspecialchars(strip_tags($type));
        $message = htmlspecialchars(strip_tags($message));
        $link = htmlspecialchars(strip_tags($link));

        // Bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':link', $link);

        return $stmt->execute();
    }

    public function getNotificationsByUser($userId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function markAsRead($notificationId)
    {
        $query = "UPDATE " . $this->table_name . " SET isRead = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notificationId);
        return $stmt->execute();
    }
}
