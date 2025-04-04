<?php
class TaskModel
{
    private $conn;
    private $table_name = "tasks";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Create a new task.
     */
    public function createTask($data)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id, project_id, user_id, description, status, priority, isDelete) 
                  VALUES 
                  (:id, :project_id, :user_id, :description, :status, :priority, 0)";
        $stmt = $this->conn->prepare($query);

        // Generate random ID
        $data['id'] = bin2hex(random_bytes(8));

        // Sanitize input data
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        // Bind parameters
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':project_id', $data['project_id']);
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);

        return $stmt->execute();
    }

    /**
     * Retrieve all tasks.
     */
    public function getAllTasks()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a task by ID.
     */
    public function getTaskById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve tasks by project ID.
     */
    public function getTasksByProject($project_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE project_id = :project_id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve tasks by user ID.
     */
    public function getTasksByUser($user_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE user_id = :user_id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Update a task's status.
     */
    public function updateTaskStatus($id, $status)
    {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    /**
     * Update a task's priority.
     */
    public function updateTaskPriority($id, $priority)
    {
        $query = "UPDATE " . $this->table_name . " SET priority = :priority WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':priority', $priority);
        return $stmt->execute();
    }

    /**
     * Soft delete a task.
     */
    public function deleteTask($id)
    {
        $query = "UPDATE " . $this->table_name . " SET isDelete = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
