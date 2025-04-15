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
        try {
            $database = new Database();
            $db = $database->getConnection();
            $this->attendanceModel = new AttendanceModel($db);
            $this->userModel = new UserModel($db);
        } catch (Exception $e) {
            throw new Exception("Failed to initialize AttendanceController: " . $e->getMessage());
        }
    }

    /**
     * Get current attendance status for a user
     * @param int $user_id The ID of the user
     * @return array Response with status and data
     */
    public function getCurrentUserStatus($user_id)
    {
        try {
            if (empty($user_id)) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            $user = $this->userModel->getUserById($user_id);
            
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

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
     * @param int $user_id The ID of the user
     * @param array $data Check-in data including time and location
     * @return array Response with status and data
     */
    public function arrive($user_id, $data)
    {
        try {
            if (empty($user_id)) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            $user = $this->userModel->getUserById($user_id);
            
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            $check_in_time = isset($data['check_in_time']) ? $data['check_in_time'] : date('Y-m-d H:i:s');
            $gps_location = $data['gps_location'] ?? null;

            if (empty($gps_location) || !isset($gps_location['latitude']) || !isset($gps_location['longitude'])) {
                return [
                    'status' => 'error',
                    'message' => 'Valid GPS location with latitude and longitude is required',
                    'received_data' => $gps_location
                ];
            }

            $attendance = $this->attendanceModel->arrive($user_id, $check_in_time, $gps_location);
            
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
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Record user departure (check-out)
     * @param int $user_id The ID of the user
     * @param array $data Check-out data including time and location
     * @return array Response with status and data
     */
    public function leave($user_id, $data)
    {
        try {
            if (empty($user_id)) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            $user = $this->userModel->getUserById($user_id);
            
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            $check_out_time = isset($data['check_out_time']) ? $data['check_out_time'] : date('Y-m-d H:i:s');
            $gps_location = $data['gps_location'] ?? null;

            if (empty($gps_location) || !isset($gps_location['latitude']) || !isset($gps_location['longitude'])) {
                return [
                    'status' => 'error',
                    'message' => 'Valid GPS location with latitude and longitude is required',
                    'received_data' => $gps_location
                ];
            }

            $attendance = $this->attendanceModel->leave($user_id, $check_out_time, $gps_location);
            
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
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance history for a user
     * @param int $user_id The ID of the user
     * @param array $params Optional parameters for filtering
     * @return array Response with status and data
     */
    public function getUserAttendanceHistory($user_id, $params = [])
    {
        try {
            if (empty($user_id)) {
                return [
                    'status' => 'error',
                    'message' => 'User ID is required'
                ];
            }

            $user = $this->userModel->getUserById($user_id);
            
            if (!$user) {
                return [
                    'status' => 'error',
                    'message' => 'User not found'
                ];
            }

            $start_date = $params['start_date'] ?? null;
            $end_date = $params['end_date'] ?? null;
            $status = $params['status'] ?? null;

            $history = $this->attendanceModel->getUserAttendanceHistory($user_id, $start_date, $end_date, $status);
            
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
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get today's attendance records for all users (admin function)
     * @return array Response with status and data
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
                    'department' => $user->department ?? 'Not assigned',
                    'position' => $user->position ?? 'Not assigned',
                    'attendance_status' => $status['status'],
                    'checked_in' => $status['is_checked_in'],
                    'checked_out' => $status['is_checked_out'],
                    'attendance_data' => $status['attendance_data']
                ];
            }

            return [
                'status' => 'success',
                'date' => $today,
                'total_users' => count($users),
                'data' => $attendanceData
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance records for all users with optional date filtering
     * @param array $params Optional parameters for filtering
     * @return array Response with status and data
     */
    public function getAllUsersAttendance($params = [])
    {
        try {
            $start_date = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $end_date = $params['end_date'] ?? date('Y-m-d');
            $department = $params['department'] ?? null;

            $users = $this->userModel->getUsers($department);
            
            $attendanceData = [];

            foreach ($users as $user) {
                $history = $this->attendanceModel->getUserAttendanceHistory(
                    $user->id,
                    $start_date,
                    $end_date
                );
                
                $total_days = count($history);
                $present_days = 0;
                $absent_days = 0;
                $late_days = 0;
                $early_leaves = 0;
                
                foreach ($history as $record) {
                    if ($record['status'] === 'present') {
                        $present_days++;
                    } elseif ($record['status'] === 'absent') {
                        $absent_days++;
                    } elseif ($record['status'] === 'late') {
                        $late_days++;
                    }
                    
                    if (isset($record['check_out_time'])) {
                        $check_out = strtotime($record['check_out_time']);
                        $standard_end = strtotime(date('Y-m-d', $check_out) . ' 17:00:00');
                        if ($check_out < $standard_end) {
                            $early_leaves++;
                        }
                    }
                }
                
                $attendanceData[] = [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'department' => $user->department ?? 'Not assigned',
                    'position' => $user->position ?? 'Not assigned',
                    'attendance_summary' => [
                        'total_days' => $total_days,
                        'present_days' => $present_days,
                        'absent_days' => $absent_days,
                        'late_days' => $late_days,
                        'early_leaves' => $early_leaves,
                        'attendance_rate' => $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0
                    ],
                    'attendance_records' => $history
                ];
            }

            return [
                'status' => 'success',
                'filters' => [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'department' => $department
                ],
                'total_users' => count($users),
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