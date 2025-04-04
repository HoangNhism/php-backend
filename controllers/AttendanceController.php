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
        error_log("[DEBUG] AttendanceController initializing...");
        try {
            $database = new Database();
            $db = $database->getConnection();
            error_log("[DEBUG] Database connection established");
            
            // Fix: remove namespace from AttendanceModel instantiation
            $this->attendanceModel = new AttendanceModel($db);
            $this->userModel = new UserModel($db);
            error_log("[DEBUG] AttendanceController models initialized successfully");
        } catch (Exception $e) {
            error_log("[ERROR] AttendanceController initialization failed: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            throw new Exception("Failed to initialize AttendanceController: " . $e->getMessage());
        }
    }

    /**
     * Get current attendance status for a user
     */
    public function getCurrentUserStatus($user_id)
    {
        error_log("[DEBUG] AttendanceController.getCurrentUserStatus called for user_id: " . $user_id);
        
        try {
            if (empty($user_id)) {
                error_log("[ERROR] User ID is required for getCurrentUserStatus");
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            error_log("[DEBUG] User lookup result: " . ($user ? "User found" : "User not found"));
            
            if (!$user) {
                error_log("[ERROR] User not found with ID: " . $user_id);
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Get attendance status
            error_log("[DEBUG] Getting attendance status for user: " . $user_id);
            $status = $this->attendanceModel->getCurrentUserStatus($user_id);
            error_log("[DEBUG] Retrieved status: " . json_encode($status));
            
            return [
                'status' => 'success',
                'data' => $status
            ];
        } catch (Exception $e) {
            error_log("[ERROR] getCurrentUserStatus exception: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug_info' => 'Check PHP error logs for more details'
            ];
        }
    }

    /**
     * Record user arrival (check-in)
     */
    public function arrive($user_id, $data)
    {
        error_log("[DEBUG] AttendanceController.arrive called for user_id: " . $user_id);
        error_log("[DEBUG] Request data: " . json_encode($data));
        
        try {
            if (empty($user_id)) {
                error_log("[ERROR] User ID is required for arrive");
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            error_log("[DEBUG] User lookup result: " . ($user ? "User found" : "User not found"));
            
            if (!$user) {
                error_log("[ERROR] User not found with ID: " . $user_id);
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Extract data from request
            $check_in_time = isset($data['check_in_time']) ? $data['check_in_time'] : date('Y-m-d H:i:s');
            $gps_location = $data['gps_location'] ?? null;
            
            error_log("[DEBUG] Extracted check_in_time: " . $check_in_time);
            error_log("[DEBUG] Extracted gps_location: " . ($gps_location ? json_encode($gps_location) : "null"));

            // Validate GPS data
            if (empty($gps_location) || !isset($gps_location['latitude']) || !isset($gps_location['longitude'])) {
                error_log("[ERROR] Invalid GPS data: " . json_encode($gps_location));
                return [
                    'status' => 'error',
                    'message' => 'Valid GPS location with latitude and longitude is required',
                    'received_data' => $gps_location
                ];
            }

            // Record arrival
            error_log("[DEBUG] Calling attendanceModel.arrive");
            $attendance = $this->attendanceModel->arrive($user_id, $check_in_time, $gps_location);
            error_log("[DEBUG] arrive result: " . ($attendance ? json_encode($attendance) : "false"));
            
            if ($attendance) {
                return [
                    'status' => 'success',
                    'message' => 'Check-in recorded successfully',
                    'data' => $attendance
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to record check-in'
                ];
            }
        } catch (Exception $e) {
            error_log("[ERROR] arrive exception: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug_info' => 'Check PHP error logs for more details'
            ];
        }
    }

    /**
     * Record user departure (check-out)
     */
    public function leave($user_id, $data)
    {
        error_log("[DEBUG] AttendanceController.leave called for user_id: " . $user_id);
        error_log("[DEBUG] Request data: " . json_encode($data));
        
        try {
            if (empty($user_id)) {
                error_log("[ERROR] User ID is required for leave");
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            error_log("[DEBUG] User lookup result: " . ($user ? "User found" : "User not found"));
            
            if (!$user) {
                error_log("[ERROR] User not found with ID: " . $user_id);
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Extract data from request
            $check_out_time = isset($data['check_out_time']) ? $data['check_out_time'] : date('Y-m-d H:i:s');
            $gps_location = $data['gps_location'] ?? null;
            
            error_log("[DEBUG] Extracted check_out_time: " . $check_out_time);
            error_log("[DEBUG] Extracted gps_location: " . ($gps_location ? json_encode($gps_location) : "null"));

            // Validate GPS data
            if (empty($gps_location) || !isset($gps_location['latitude']) || !isset($gps_location['longitude'])) {
                error_log("[ERROR] Invalid GPS data: " . json_encode($gps_location));
                return [
                    'status' => 'error',
                    'message' => 'Valid GPS location with latitude and longitude is required',
                    'received_data' => $gps_location
                ];
            }

            // Record departure
            error_log("[DEBUG] Calling attendanceModel.leave");
            $attendance = $this->attendanceModel->leave($user_id, $check_out_time, $gps_location);
            error_log("[DEBUG] leave result: " . ($attendance ? json_encode($attendance) : "false"));
            
            if ($attendance) {
                return [
                    'status' => 'success',
                    'message' => 'Check-out recorded successfully',
                    'data' => $attendance
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to record check-out'
                ];
            }
        } catch (Exception $e) {
            error_log("[ERROR] leave exception: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug_info' => 'Check PHP error logs for more details'
            ];
        }
    }

    /**
     * Get attendance history for a user
     */
    public function getUserAttendanceHistory($user_id, $params = [])
    {
        error_log("[DEBUG] AttendanceController.getUserAttendanceHistory called for user_id: " . $user_id);
        error_log("[DEBUG] Params: " . json_encode($params));
        
        try {
            if (empty($user_id)) {
                error_log("[ERROR] User ID is required for getUserAttendanceHistory");
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            // Check if user exists
            $user = $this->userModel->getUserById($user_id);
            error_log("[DEBUG] User lookup result: " . ($user ? "User found" : "User not found"));
            
            if (!$user) {
                error_log("[ERROR] User not found with ID: " . $user_id);
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            // Extract filter parameters
            $start_date = $params['start_date'] ?? null;
            $end_date = $params['end_date'] ?? null;
            $status = $params['status'] ?? null;
            
            error_log("[DEBUG] Extracted start_date: " . ($start_date ?? "null"));
            error_log("[DEBUG] Extracted end_date: " . ($end_date ?? "null"));
            error_log("[DEBUG] Extracted status: " . ($status ?? "null"));

            // Get attendance history
            error_log("[DEBUG] Calling attendanceModel.getUserAttendanceHistory");
            $history = $this->attendanceModel->getUserAttendanceHistory($user_id, $start_date, $end_date, $status);
            error_log("[DEBUG] History records count: " . count($history));
            
            return [
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'department' => $user->department,
                        'position' => $user->position
                    ],
                    'filters' => [
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'status' => $status
                    ],
                    'total_records' => count($history),
                    'records' => $history
                ]
            ];
        } catch (Exception $e) {
            error_log("[ERROR] getUserAttendanceHistory exception: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug_info' => 'Check PHP error logs for more details'
            ];
        }
    }

    /**
     * Get today's attendance records for all users (admin function)
     */
    public function getTodayAttendance()
    {
        error_log("[DEBUG] AttendanceController.getTodayAttendance called");
        
        try {
            $today = date('Y-m-d');
            error_log("[DEBUG] Getting users");
            $users = $this->userModel->getUsers();
            error_log("[DEBUG] Found " . count($users) . " users");
            
            $attendanceData = [];

            foreach ($users as $user) {
                error_log("[DEBUG] Processing user: " . $user->id);
                $status = $this->attendanceModel->getCurrentUserStatus($user->id);
                
                $attendanceData[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'department' => $user->department ?? 'Not assigned',
                    'position' => $user->position ?? 'Not assigned',
                    'attendance_status' => $status['status'],
                    'checked_in' => $status['is_checked_in'],
                    'checked_out' => $status['is_checked_out'],
                    'attendance_data' => $status['attendance_data']
                ];
            }
            
            error_log("[DEBUG] Compiled attendance data for " . count($attendanceData) . " users");

            return [
                'status' => 'success',
                'date' => $today,
                'total_users' => count($users),
                'data' => $attendanceData
            ];
        } catch (Exception $e) {
            error_log("[ERROR] getTodayAttendance exception: " . $e->getMessage());
            error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'debug_info' => 'Check PHP error logs for more details'
            ];
        }
    }
}