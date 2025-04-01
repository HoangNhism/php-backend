<?php
class RoleMiddleware
{
    /**
     * Check if the user has the required role.
     * @param object $user User object with role property
     * @param string|array $requiredRole Required role(s)
     */
    public function handle($user, $requiredRole)
    {
        // Lấy role từ user object, có thể nằm ở user->role hoặc trong cấu trúc khác
        $userRole = '';
        if (is_object($user)) {
            if (property_exists($user, 'role')) {
                $userRole = $user->role;
            } elseif (isset($user->data) && is_object($user->data) && property_exists($user->data, 'role')) {
                $userRole = $user->data->role;
            }
        } else if (is_array($user)) {
            if (isset($user['role'])) {
                $userRole = $user['role'];
            } elseif (isset($user['data']['role'])) {
                $userRole = $user['data']['role'];
            }
        }
        
        // Debug - chỉ để kiểm tra
        error_log('User Role: ' . $userRole);
        if (is_array($requiredRole)) {
            error_log('Required Roles: ' . implode(',', $requiredRole));
        } else {
            error_log('Required Role: ' . $requiredRole);
        }
        
        // Nếu $requiredRole là array, kiểm tra xem userRole có nằm trong array không
        if (is_array($requiredRole)) {
            if (!in_array($userRole, $requiredRole)) {
                http_response_code(403);
                echo json_encode(['message' => 'Forbidden: Insufficient permissions']);
                exit;
            }
        } 
        // Nếu $requiredRole là string, kiểm tra xem userRole có bằng $requiredRole không
        else {
            if ($userRole !== $requiredRole) {
                http_response_code(403);
                echo json_encode(['message' => 'Forbidden: Insufficient permissions']);
                exit;
            }
        }
    }
}
