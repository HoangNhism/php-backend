<?php
class ProjectModel
{
    private $conn;
    private $table_name = "projects";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Create a new project.
     */
    public function createProject($data)
    {
        // Validate required fields
        if (empty($data['name']) || strlen($data['name']) > 255) {
            return [
                'success' => false,
                'message' => 'Project name is required and must not exceed 255 characters'
            ];
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return [
                'success' => false,
                'message' => 'Start date and end date are required'
            ];
        }

        // Validate date format and logic
        $startDate = strtotime($data['start_date']);
        $endDate = strtotime($data['end_date']);
        if ($startDate === false || $endDate === false || $endDate < $startDate) {
            return [
                'success' => false,
                'message' => 'Invalid date format or end date is earlier than start date'
            ];
        }

        // Sanitize input data
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        // Proceed with project creation
        $query = "INSERT INTO " . $this->table_name . " 
              (id, name, description, start_date, end_date, manager_id, isDelete) 
              VALUES 
              (:id, :name, :description, :start_date, :end_date, :manager_id, 0)";

        $stmt = $this->conn->prepare($query);

        // Generate random ID
        $data['id'] = bin2hex(random_bytes(8));

        // Bind parameters
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':manager_id', $data['manager_id']);

        if ($stmt->execute()) {
            // Add manager to project members
            $projectMemberModel = new ProjectMemberModel($this->conn);
            $projectMemberModel->addProjectMember($data['id'], $data['manager_id']);

            return [
                'success' => true,
                'message' => 'Project created successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create project'
            ];
        }
    }

    /**
     * Retrieve all projects.
     */
    public function getAllProjects()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve projects by user ID.
     */
    public function getProjectByUser($user_id)
    {
        $query = "SELECT p.* FROM " . $this->table_name . " p 
                  JOIN tasks t ON p.id = t.project_id 
                  WHERE t.user_id = :user_id AND p.isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a project by ID.
     */
    public function getProjectById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a project name by ID.
     */
    public function getProjectNameById($id)
    {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ? $result->name : null;
    }

    /**
     * Retrieve projects by name (partial match).
     */
    public function getProjectByName($name)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE name LIKE :name AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $name = "%$name%";
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve projects by manager ID.
     */
    public function getProjectByManager($manager_id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE manager_id = :manager_id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':manager_id', $manager_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve project progress by ID.
     */
    public function getProjectProgress($id)
    {
        $query = "SELECT t.status FROM tasks t 
                  WHERE t.project_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!$tasks) {
            return 0; // No tasks found
        }

        $totalTasks = count($tasks);
        $completedTasks = count(array_filter($tasks, fn($task) => $task->status === 'completed'));
        return $totalTasks === 0 ? 0 : round(($completedTasks / $totalTasks) * 100);
    }

    /**
     * Update a project.
     */
    public function updateProject($id, $data)
    {
        // Validate required fields
        if (empty($data['name']) || strlen($data['name']) > 255) {
            return [
                'success' => false,
                'message' => 'Project name is required and must not exceed 255 characters'
            ];
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return [
                'success' => false,
                'message' => 'Start date and end date are required'
            ];
        }

        // Validate date format and logic
        $startDate = strtotime($data['start_date']);
        $endDate = strtotime($data['end_date']);
        if ($startDate === false || $endDate === false || $endDate < $startDate) {
            return [
                'success' => false,
                'message' => 'Invalid date format or end date is earlier than start date'
            ];
        }

        // Sanitize input data
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        $query = "UPDATE " . $this->table_name . " 
              SET name = :name, description = :description, start_date = :start_date, end_date = :end_date, manager_id = :manager_id 
              WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':manager_id', $data['manager_id']);

        if ($stmt->execute()) {
            // Add manager to project members if not already added
            $projectMemberModel = new ProjectMemberModel($this->conn);
            if (!$projectMemberModel->isMember($id, $data['manager_id'])) {
                $projectMemberModel->addProjectMember($id, $data['manager_id']);
            }

            return [
                'success' => true,
                'message' => 'Project updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update project'
        ];
    }

    /**
     * Soft delete a project.
     */
    public function deleteProject($id)
    {
        $query = "UPDATE " . $this->table_name . " SET isDelete = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
