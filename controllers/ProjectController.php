<?php
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../config/database.php';

class ProjectController
{
    private $projectModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->projectModel = new ProjectModel($db);
    }

    public function createProject($data)
    {
        return $this->projectModel->createProject($data);
    }

    public function getAllProjects()
    {
        return $this->projectModel->getAllProjects();
    }

    public function getProjectByUser($user_id)
    {
        return $this->projectModel->getProjectByUser($user_id);
    }

    public function getProjectById($id)
    {
        return $this->projectModel->getProjectById($id);
    }

    public function getProjectNameById($id)
    {
        return $this->projectModel->getProjectNameById($id);
    }

    public function getProjectByName($name)
    {
        return $this->projectModel->getProjectByName($name);
    }

    public function getProjectByManager($manager_id)
    {
        return $this->projectModel->getProjectByManager($manager_id);
    }

    public function getProjectProgress($id)
    {
        return $this->projectModel->getProjectProgress($id);
    }

    public function updateProject($id, $data)
    {
        return $this->projectModel->updateProject($id, $data);
    }

    public function deleteProject($id)
    {
        return $this->projectModel->deleteProject($id);
    }
}
