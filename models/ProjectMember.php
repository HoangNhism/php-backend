<?php
class ProjectMemberModel
{
    private $conn;
    private $table_name = "project_members";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Add a project member.
     */
    public function addProjectMember($project_id, $user_id)
    {
        $query = "INSERT INTO " . $this->table_name . " (project_id, user_id) VALUES (:project_id, :user_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
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

        return $stmt->execute();
    }
}
