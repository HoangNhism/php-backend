<?php
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../config/database.php';

class TaskController
{
    private $taskModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->taskModel = new TaskModel($db);
    }

    public function createTask($data)
    {
        return $this->taskModel->createTask($data);
    }

    public function getAllTasks()
    {
        return $this->taskModel->getAllTasks();
    }

    public function getTaskById($id)
    {
        return $this->taskModel->getTaskById($id);
    }

    public function getTasksByProject($project_id)
    {
        return $this->taskModel->getTasksByProject($project_id);
    }

    public function getTasksByUser($user_id)
    {
        return $this->taskModel->getTasksByUser($user_id);
    }

    public function updateTaskStatus($id, $status)
    {
        return $this->taskModel->updateTaskStatus($id, $status);
    }

    public function updateTaskPriority($id, $priority)
    {
        return $this->taskModel->updateTaskPriority($id, $priority);
    }

    public function deleteTask($id)
    {
        return $this->taskModel->deleteTask($id);
    }

    public function changeAssignee($taskId, $newUserId)
    {
        return $this->taskModel->changeAssignee($taskId, $newUserId);
    }
}
