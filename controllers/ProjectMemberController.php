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
        $members = $this->projectMemberModel->getProjectMembers($project_id);

        // Format dates if needed
        foreach ($members as $member) {
            if (isset($member->join_at)) {
                $member->join_at = date('Y-m-d\TH:i:s.v\Z', strtotime($member->join_at));
            }
            // Ensure boolean type for isDelete
            $member->isDelete = (bool)$member->isDelete;
        }

        return $members;
    }

    public function getProjectsByMember($user_id)
    {
        $projects = $this->projectMemberModel->getProjectsByMember($user_id);
        return [
            'success' => true,
            'data' => $projects,
            'count' => count($projects)
        ];
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
