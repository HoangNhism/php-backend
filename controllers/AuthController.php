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
     * Authenticate user and generate JWT.
     */
    public function login($email, $password)
    {
        $user = $this->userModel->getUserByEmail($email);

        if ($user && password_verify($password, $user->password)) {
            $token = $this->jwtHandler->generateToken([
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role
            ]);
            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }
}
