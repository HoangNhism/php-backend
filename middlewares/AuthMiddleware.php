<?php
require_once __DIR__ . '/../utils/JwtHandler.php';

class AuthMiddleware
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
    }

    public function handle()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;
        $token = str_replace('Bearer ', '', $authHeader);

        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'No token provided']);
            exit;
        }

        $decoded = $this->jwtHandler->validateToken($token);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit;
        }

        return $decoded;
    }
}
