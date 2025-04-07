<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';

class UserController
{
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->userModel = new UserModel($db);
    }

    /**
     * Get all users.
     */
    public function getAllUsers()
    {
        return $this->userModel->getUsers();
    }

    /**
     * Get a user by ID.
     */
    public function getUserById($id)
    {
        return $this->userModel->getUserById($id);
    }

    /**
     * Add a new user.
     */
    public function createUser($data)
    {
        return $this->userModel->addUser($data);
    }

    /**
     * Update an existing user.
     */
    public function updateUser($id, $data)
    {
        return $this->userModel->updateUser($id, $data);
    }

    /**
     * Delete a user.
     */
    public function deleteUser($id)
    {
        return $this->userModel->deleteUser($id);
    }

    public function changePassword($id, $oldPassword, $newPassword)
    {
        return $this->userModel->changePassword($id, $oldPassword, $newPassword);
    }

    public function blockUser($id)
    {
        return $this->userModel->blockUser($id);
    }

    public function getBlockedUsers()
    {
        return $this->userModel->getBlockedUsers();
    }

    public function unblockUser($id)
    {
        return $this->userModel->unblockUser($id);
    }
}
