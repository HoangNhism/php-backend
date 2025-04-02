<?php
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';

class AttendanceController
{
    private $attendanceModel;
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();
        $this->attendanceModel = new App\Models\AttendanceModel($db);
        $this->userModel = new UserModel($db);
    }

    /**
     * Get current attendance status for a user
     */
    public function getCurrentUserStatus($user_id)
    {
        try {
            if (!$user_id) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Get attendance status
            $status = $this->attendanceModel->getCurrentUserStatus($user_id);
            
            return [
                'status' => 'success',
                'data' => $status
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Record user arrival (check-in)
     */
    public function arrive($user_id, $data)
    {
        try {
            if (!$user_id) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Extract data from request
            $check_in_time = $data['check_in_time'] ?? date('Y-m-d H:i:s');
            $gps_location = $data['gps_location'] ?? null;

            // Validate GPS data
            if (!$gps_location || !isset($gps_location['latitude']) || !isset($gps_location['longitude'])) {
                return [
                    'status' => 'error',
                    'message' => 'Valid GPS location with latitude and longitude is required'
                ];
            }

            // Record arrival
            $result = $this->attendanceModel->arrive($user_id, $check_in_time, $gps_location);
            
            return $result;
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Record user departure (check-out)
     */
    public function leave($user_id, $data)
    {
        try {
            if (!$user_id) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Extract data from request
            $check_out_time = $data['check_out_time'] ?? date('Y-m-d H:i:s');
            $gps_location = $data['gps_location'] ?? null;

            // Validate GPS data
            if (!$gps_location || !isset($gps_location['latitude']) || !isset($gps_location['longitude'])) {
                return [
                    'status' => 'error',
                    'message' => 'Valid GPS location with latitude and longitude is required'
                ];
            }

            // Record departure
            $result = $this->attendanceModel->leave($user_id, $check_out_time, $gps_location);
            
            return $result;
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance history for a user
     */
    public function getUserAttendanceHistory($user_id, $params = [])
    {
        try {
            if (!$user_id) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Extract filter parameters
            $start_date = $params['start_date'] ?? null;
            $end_date = $params['end_date'] ?? null;
            $status = $params['status'] ?? null;

            // Get attendance history
            $history = $this->attendanceModel->getUserAttendanceHistory($user_id, $start_date, $end_date, $status);
            
            return [
                'status' => 'success',
                'data' => $history
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get today's attendance records for all users (admin function)
     */
    public function getTodayAttendance()
    {
        try {
            $today = date('Y-m-d');
            $users = $this->userModel->getUsers();
            $attendanceData = [];

            foreach ($users as $user) {
                $status = $this->attendanceModel->getCurrentUserStatus($user->id);
                $attendanceData[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'department' => $user->department,
                    'position' => $user->position,
                    'attendance_status' => $status['status'],
                    'checked_in' => $status['is_checked_in'],
                    'checked_out' => $status['is_checked_out'],
                    'attendance_data' => $status['attendance_data']
                ];
            }

            return [
                'status' => 'success',
                'date' => $today,
                'data' => $attendanceData
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}