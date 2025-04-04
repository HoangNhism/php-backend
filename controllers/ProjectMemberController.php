<?php
require_once __DIR__ . '/../models/ProjectMember.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../config/database.php';

class ProjectMemberController
{
    private $projectMemberModel;
    private $projectModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->projectMemberModel = new ProjectMemberModel($db);
        $this->projectModel = new ProjectModel($db);
    }

    public function addProjectMember($project_id, $user_id)
    {
        $result = $this->projectMemberModel->addProjectMember($project_id, $user_id);

        if ($result) {
            $projectName = $this->projectModel->getProjectNameById($project_id);
            $message = "You have been added to project \"" . ($projectName ?? "Unknown") . "\"";
            // Add notification logic here if needed
        }

        return $result;
    }

    public function getAllMembers()
    {
        return $this->projectMemberModel->getAllMembers();
    }

    public function getProjectMembers($project_id)
    {
        return $this->projectMemberModel->getProjectMembers($project_id);
    }

    public function getProjectsByMember($user_id)
    {
        $projectIds = $this->projectMemberModel->getProjectsByMember($user_id);
        $projects = [];

        foreach ($projectIds as $project) {
            $projectData = $this->projectModel->getProjectById($project->project_id);
            if ($projectData) {
                $projects[] = $projectData;
            }
        }

        return $projects;
    }

    public function removeMember($project_id, $user_id)
    {
        $result = $this->projectMemberModel->removeMember($project_id, $user_id);

        if ($result) {
            $projectName = $this->projectModel->getProjectNameById($project_id);
            $message = "You have been removed from project \"" . ($projectName ?? "Unknown") . "\"";
            // Add notification logic here if needed
        }

        return $result;
    }
}
