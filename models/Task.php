<?php
class TaskModel
{
    private $conn;
    private $table_name = "tasks";
    private $projectMemberModel;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->projectMemberModel = new ProjectMemberModel($db);
    }

    /**
     * Create a new task.
     */
    public function createTask($data)
    {
        // Validate required fields
        if (empty($data['project_id']) || empty($data['title']) || empty($data['description'])) {
            return [
                'success' => false,
                'message' => 'Project ID, Title, and Description are required'
            ];
        }

        // Validate status and priority if provided
        if (isset($data['status']) && !in_array($data['status'], ['To Do', 'In Progress', 'Completed'])) {
            return [
                'success' => false,
                'message' => 'Invalid status value'
            ];
        }

        if (isset($data['priority']) && !in_array($data['priority'], ['Low', 'Medium', 'High'])) {
            return [
                'success' => false,
                'message' => 'Invalid priority value'
            ];
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (id, project_id, user_id, title, description, status, priority, dueDate, isDelete, createdAt, updatedAt) 
                  VALUES 
                  (:id, :project_id, :user_id, :title, :description, :status, :priority, :dueDate, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
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
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':priority', $data['priority']);
        $stmt->bindParam(':dueDate', $data['dueDate']);

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
        // Validate status
        if (!in_array($status, ['To Do', 'In Progress', 'Completed'])) {
            return [
                'success' => false,
                'message' => 'Invalid status value'
            ];
        }

        $query = "UPDATE " . $this->table_name . " SET status = :status, updatedAt = CURRENT_TIMESTAMP WHERE id = :id AND isDelete = 0";
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
        // Validate priority
        if (!in_array($priority, ['Low', 'Medium', 'High'])) {
            return [
                'success' => false,
                'message' => 'Invalid priority value'
            ];
        }

        $query = "UPDATE " . $this->table_name . " SET priority = :priority, updatedAt = CURRENT_TIMESTAMP WHERE id = :id AND isDelete = 0";
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

    public function changeAssignee($taskId, $newUserId)
    {
        // Check if the task exists
        $task = $this->getTaskById($taskId);
        if (!$task) {
            return [
                'success' => false,
                'message' => 'Task not found'
            ];
        }

        // Check if the new user is a member of the project
        if (!$this->projectMemberModel->isMember($task->project_id, $newUserId)) {
            return [
                'success' => false,
                'message' => 'New assignee is not a member of the project'
            ];
        }

        // Validate the new user ID
        if (empty($newUserId)) {
            return [
                'success' => false,
                'message' => 'New user ID is required'
            ];
        }

        $query = "UPDATE " . $this->table_name . " SET user_id = :new_user_id, updatedAt = CURRENT_TIMESTAMP WHERE id = :task_id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId);
        $stmt->bindParam(':new_user_id', $newUserId);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Assignee changed successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to change assignee'
        ];
    }
}
