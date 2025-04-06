<?php

require_once __DIR__ . '/Notification.php';
class ProjectMemberModel
{
    private $conn;
    private $table_name = "project_members";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function isMember($project_id, $user_id)
    {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE project_id = :project_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->count > 0;
    }

    public function sendNotification($userId, $type, $message, $link)
    {
        // Assuming you have a NotificationModel to handle notifications
        $notificationModel = new NotificationModel($this->conn);
        $notificationModel->createNotification($userId, $type, $message, $link);
    }

    /**
     * Add a project member.
     */
    public function addProjectMember($project_id, $user_id)
    {
        if ($this->isMember($project_id, $user_id)) {
            return [
                'success' => false,
                'message' => 'User is already a member of this project'
            ];
        }

        $query = "INSERT INTO " . $this->table_name . " (project_id, user_id) VALUES (:project_id, :user_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            // Send notification
            $message = "You have been added to project ID: $project_id";
            $link = "/projects/$project_id";
            $this->sendNotification($user_id, "PROJECT_MEMBER_ADDED", $message, $link);

            return [
                'success' => true,
                'message' => 'User added to project successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to add user to project'
        ];
    }

    /**
     * Retrieve all project members.
     */
    public function getAllMembers()
    {
        $query = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve members of a specific project.
     */
    public function getProjectMembers($project_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE project_id = :project_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve projects by a specific member.
     */
    public function getProjectsByMember($user_id)
    {
        $query = "SELECT project_id FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Remove a project member.
     */
    public function removeMember($project_id, $user_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE project_id = :project_id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            // Send notification
            $message = "You have been removed from project ID: $project_id";
            $link = "/projects/$project_id";
            $this->sendNotification($user_id, "PROJECT_MEMBER_REMOVED", $message, $link);

            return true;
        }

        return false;
    }
}
