<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHandler
{
    private $secretKey = "aTSJ5IERiJEl0nkoQWeQyhwcsPniE7gU"; // Replace with a secure key

    /**
     * Generate a JWT token.
     */
    public function generateToken($payload)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600; // Token valid for 1 hour

        $token = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $payload
        ];

        return JWT::encode($token, $this->secretKey, 'HS256');
    }

    /**
     * Validate a JWT token.
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
