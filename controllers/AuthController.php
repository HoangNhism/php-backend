<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/JwtHandler.php';

class AuthController
{
    private $userModel;
    private $jwtHandler;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->userModel = new UserModel($db);
        $this->jwtHandler = new JwtHandler();
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
     * Authenticate user and generate JWT.
     */
    public function login($email, $password)
    {
        // Validate email format
        if (!$this->validateEmail($email)) {
            return [
                'success' => false,
                'message' => 'Invalid email format'
            ];
        }

        // Validate password strength
        if (!$this->validatePassword($password)) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character'
            ];
        }

        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            $token = $this->jwtHandler->generateToken([
                'id' => $user->id,
                'role' => $user->role
            ]);
            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,              
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                    'avatarURL' => $user->avatarURL
                ],
                'role' => $user->role // Thêm role trực tiếp ở mức cao nhất
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }
}