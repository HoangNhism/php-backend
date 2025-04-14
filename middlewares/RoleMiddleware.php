<?php
class RoleMiddleware
{
    public function handle($user, $roles)
    {
        // Convert single role to array for consistent handling
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRole = '';
        if (isset($user['role'])) {
            $userRole = $user['role'];
        } elseif (isset($user['data']['role'])) {
            $userRole = $user['data']['role'];
        }

        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            echo json_encode(['message' => 'Forbidden: Insufficient role']);
            exit;
        }
    }
}
