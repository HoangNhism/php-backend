<?php
require_once __DIR__ . '/../utils/JwtHandler.php';

class AuthMiddleware
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JwtHandler();
    }

    /**
     * Validate JWT token from the Authorization header.
     */
    public function handle()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Missing or invalid token']);
            exit;
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $decoded = $this->jwtHandler->validateToken($token);

        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized: Invalid token']);
            exit;
        }

        return $decoded->data; // Return decoded token data (e.g., user info)
    }
}
