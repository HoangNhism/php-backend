<?php
class RoleMiddleware
{
    /**
     * Check if the user has the required role.
     */
    public function handle($user, $requiredRole)
    {
        if ($user->role !== $requiredRole) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden: Insufficient permissions']);
            exit;
        }
    }
}
