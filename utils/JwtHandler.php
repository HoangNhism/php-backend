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

        $token = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expirationTime
        ]);

        return JWT::encode($token, $this->secretKey, 'HS256');
    }

    /**
     * Validate a JWT token.
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return [
                'id' => $decoded->id,
                'role' => $decoded->role,
                'iat' => $decoded->iat,
                'exp' => $decoded->exp
            ];
        } catch (Exception $e) {
            return null;
        }
    }
}
