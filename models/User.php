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
     * Validate email format
     */
    private function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     * Requirements:
     * - At least 8 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
     * - At least one special character
     */
    private function validatePassword($password)
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);
        $length = strlen($password) >= 8;

        return $uppercase && $lowercase && $number && $specialChars && $length;
    }

    /**
     * Validate mobile number format
     */
    private function validateMobile($mobile)
    {
        return preg_match('/^[0-9]{10,15}$/', $mobile);
    }

    /**
     * Validate required fields
     */
    private function validateRequiredFields($data)
    {
        $requiredFields = ['email', 'password', 'full_name', 'mobile', 'role'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        return $missingFields;
    }

    /**
     * Retrieve all users.
     */
    public function getUsers()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a user by ID.
     */
    public function getUserById($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve a user by email.
     */
    public function getUserByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Retrieve users by role.
     */
    public function getUsersByRole($role)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE role = :role AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Add a new user.
     */
    public function addUser($data)
    {
        // Validate required fields
        $missingFields = $this->validateRequiredFields($data);
        if (!empty($missingFields)) {
            return [
                'success' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }

        // Validate email format
        if (!$this->validateEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        // Validate password strength
        if (!$this->validatePassword($data['password'])) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character'
            ];
        }

        // Validate mobile number
        if (!$this->validateMobile($data['mobile'])) {
            return [
                'success' => false,
                'message' => 'Invalid mobile number format. Must be 10-15 digits'
            ];
        }

        // Check if email already exists
        $existingUser = $this->getUserByEmail($data['email']);
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Email already exists'
            ];
        }

        $query = "INSERT INTO " . $this->table_name . " (id, email, password, full_name, mobile, address, avatarURL, department, position, hire_date, status, role, isDelete) 
                  VALUES (:id, :email, :password, :full_name, :mobile, :address, :avatarURL, :department, :position, :hire_date, :status, :role, 0)";
        $stmt = $this->conn->prepare($query);

        // Generate random string for id
        $data['id'] = bin2hex(random_bytes(8));

        // Hash the password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Sanitize all input data
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

        if ($stmt->execute()) {
            // Initialize leave balance for the new user
            require_once __DIR__ . '/LeaveBalance.php';
            $leaveBalance = new LeaveBalance($this->conn);
            $leaveBalance->user_id = $data['id'];
            $leaveBalance->initialize();

            return [
                'success' => true,
                'message' => 'User added successfully',
                'id' => $data['id']
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to add user'
        ];
    }

    /**
     * Update an existing user.
     */
    public function updateUser($id, $data)
    {
        // Validate required fields (excluding password)
        $requiredFields = ['email', 'full_name', 'mobile', 'role'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return [
                'success' => false,
                'message' => 'Missing required fields: ' . implode(', ', $missingFields)
            ];
        }

        // Validate email format
        if (!$this->validateEmail($data['email'])) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        // Validate mobile number
        if (!$this->validateMobile($data['mobile'])) {
            return [
                'success' => false,
                'message' => 'Invalid mobile number format. Must be 10-15 digits'
            ];
        }

        $query = "UPDATE " . $this->table_name . " 
              SET email = :email, full_name = :full_name, mobile = :mobile, address = :address, avatarURL = :avatarURL, 
                  department = :department, position = :position, hire_date = :hire_date, status = :status, role = :role 
              WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags($value));
        }

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':full_name', $data['full_name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':avatarURL', $data['avatarURL']);
        $stmt->bindParam(':department', $data['department']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':hire_date', $data['hire_date']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':role', $data['role']);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update user'
        ];
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

    public function changePassword($id, $oldPassword, $newPassword)
    {
        // Retrieve the current password hash from the database
        $user = $this->getUserById($id);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }

        // Verify the old password
        if (!password_verify($oldPassword, $user->password)) {
            return [
                'success' => false,
                'message' => 'Old password is incorrect'
            ];
        }

        // Validate new password strength
        if (!$this->validatePassword($newPassword)) {
            return [
                'success' => false,
                'message' => 'New password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character'
            ];
        }

        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $hashedPassword);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to change password'
        ];
    }

    public function blockUser($id)
    {
        // Use 'Inactive' to represent a blocked user
        $blockedStatus = 'Inactive';

        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $blockedStatus);

        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'User blocked successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to block user'
        ];
    }

    public function getBlockedUsers()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'Inactive' AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function unblockUser($id)
    {
        $query = "UPDATE " . $this->table_name . " SET status = 'Active' WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Retrieve the role of a user by ID.
     */
    public function getUserRole($id)
    {
        $query = "SELECT role FROM " . $this->table_name . " WHERE id = :id AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        if ($result) {
            return [
                'success' => true,
                'role' => $result->role
            ];
        }

        return [
            'success' => false,
            'message' => 'User not found'
        ];
    }

    /**
     * Retrieve all users with Manager role.
     */
    public function getManagerUsers()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE role = 'Manager' AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}