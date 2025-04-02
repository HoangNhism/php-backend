<?php
class UserModel
{
    private $conn;
    private $table_name = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Retrieve all users with status 'Active'.
     */
    public function getUsers()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE isDelete = 0 AND status = 'Active'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a user by ID with status 'Active'.
     */
    public function getUserById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND isDelete = 0 AND status = 'Active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a user by email with status 'Active'.
     */
    public function getUserByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email AND isDelete = 0 AND status = 'Active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Add a new user.
     */
    public function addUser($data)
    {
        $query = "INSERT INTO " . $this->table_name . " (id, email, password, full_name, mobile, address, avatarURL, department, position, hire_date, status, role, isDelete) 
                  VALUES (:id, :email, :password, :full_name, :mobile, :address, :avatarURL, :department, :position, :hire_date, :status, :role, 0)";
        $stmt = $this->conn->prepare($query);

        // Generate random string for id
        $data['id'] = bin2hex(random_bytes(8));

        // Hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':avatarURL', $data['avatarURL']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':hire_date', $data['hire_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':role', $data['role']);

        return $stmt->execute();
    }

    /**
     * Update an existing user.
     */
    public function updateUser($id, $data)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET email = :email, password = :password, full_name = :full_name, mobile = :mobile, address = :address, avatarURL = :avatarURL, 
                      department = :department, position = :position, hire_date = :hire_date, status = :status, role = :role 
                  WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':avatarURL', $data['avatarURL']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':hire_date', $data['hire_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':role', $data['role']);

        return $stmt->execute();
    }

    /**
     * Soft delete a user.
     */
    public function deleteUser($id)
    {
        $query = "UPDATE " . $this->table_name . " SET isDelete = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Retrieve users by a specific field with status 'Active'.
     */
    public function getUsersByField($field, $value)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE $field = :value AND isDelete = 0 AND status = 'Active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Block a user (set status to 'Inactive').
     */
    public function blockUser($id)
    {
        $query = "UPDATE " . $this->table_name . " SET status = 'Inactive' WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Upload a file for a user.
     */
    public function uploadFile($userId, $fileData)
    {
        $query = "INSERT INTO employee_documents (employeeId, fileName, fileUrl, fileType) 
                  VALUES (:employeeId, :fileName, :fileUrl, :fileType)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employeeId', $userId);
        $stmt->bindParam(':fileName', $fileData['fileName']);
        $stmt->bindParam(':fileUrl', $fileData['fileUrl']);
        $stmt->bindParam(':fileType', $fileData['fileType']);
        return $stmt->execute();
    }
}