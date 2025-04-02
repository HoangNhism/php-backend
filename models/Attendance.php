<?php

namespace App\Models;
use \UserModel;

class AttendanceModel
{
    private $conn;
    private $table_name = "attendance";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Get detailed current attendance status for a specific user
     * Returns: array with detailed status information
     */
    public function getCurrentUserStatus($user_id)
    {
        if (!$user_id) {
            throw new Exception('UserId is required');
        }
        
        $today = date('Y-m-d');
        $startDate = $today . ' 00:00:00';
        $endDate = date('Y-m-d', strtotime('+1 day')) . ' 00:00:00';
        
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = :user_id 
                 AND check_in_time BETWEEN :start_date AND :end_date 
                 ORDER BY check_in_time DESC LIMIT 1";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        
        $record = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$record) {
            return [
                'is_checked_in' => false,
                'is_checked_out' => false,
                'status' => 'Not Checked In',
                'message' => 'You haven\'t checked in today',
                'attendance_data' => null,
                'today_date' => $today
            ];
        } else if ($record->check_out_time) {
            return [
                'is_checked_in' => true,
                'is_checked_out' => true,
                'status' => $record->status,
                'message' => 'You have completed your check-in/out for today',
                'attendance_data' => [
                    'check_in_time' => $record->check_in_time,
                    'check_out_time' => $record->check_out_time,
                    'status' => $record->status,
                    'id' => $record->id
                ],
                'today_date' => $today
            ];
        } else {
            return [
                'is_checked_in' => true,
                'is_checked_out' => false,
                'status' => $record->status,
                'message' => 'You\'re currently checked in',
                'attendance_data' => [
                    'check_in_time' => $record->check_in_time, 
                    'status' => $record->status,
                    'id' => $record->id
                ],
                'today_date' => $today
            ];
        }
    }

    /**
     * Record user arrival (check-in)
     * Returns: array with status and message or attendance ID
     */
    public function arrive($user_id, $check_in_time = null, $gps_location = null)
    {
        try {
            // Validate user ID
            if (!$user_id) {
                throw new Exception('UserId is required');
            }

            // Validate GPS location
            if (!$gps_location || !is_array($gps_location)) {
                throw new Exception('Valid GPS location object is required');
            }

            // Create a clean GPS object to ensure we only store what we need
            $locationData = [
                'latitude' => (float)$gps_location['latitude'],
                'longitude' => (float)$gps_location['longitude']
            ];

            // Add optional accuracy if provided
            if (isset($gps_location['accuracy'])) {
                $locationData['accuracy'] = (float)$gps_location['accuracy'];
            }

            // Use current time if check_in_time not provided
            if (!$check_in_time) {
                $check_in_time = date('Y-m-d H:i:s');
            }

            // Parse the check-in time
            $checkInDateTime = new DateTime($check_in_time);
            
            // Define cutoff time (8:00 AM on the same day)
            $cutoffTime = new DateTime($checkInDateTime->format('Y-m-d') . ' 08:00:00');
            
            // Determine check-in status
            $status = $checkInDateTime > $cutoffTime ? 'Late' : 'On time';

            // Check if user has already checked in today
            // Get today's date based on check-in time
            $today = $checkInDateTime->format('Y-m-d');
            $startDate = $today . ' 00:00:00';
            $endDate = date('Y-m-d', strtotime($today . ' +1 day')) . ' 00:00:00';
            
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE user_id = :user_id 
                     AND check_in_time BETWEEN :start_date AND :end_date 
                     LIMIT 1";
                     
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $existingAttendance = $stmt->fetch(PDO::FETCH_OBJ);
            
            // Uncomment if you want to prevent multiple check-ins
            // if ($existingAttendance) {
            //     throw new Exception('You have already checked in today');
            // }
            
            // Generate a random ID
            $id = bin2hex(random_bytes(8));
            
            // Convert location data to JSON
            $gps_location_json = json_encode($locationData);
            
            // Insert the attendance record
            $query = "INSERT INTO " . $this->table_name . " 
                     (id, user_id, status, check_in_time, gps_location) 
                     VALUES (:id, :user_id, :status, :check_in_time, :gps_location)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':check_in_time', $check_in_time);
            $stmt->bindParam(':gps_location', $gps_location_json);
            
            if ($stmt->execute()) {
                // Get the created attendance record
                $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $attendance = $stmt->fetch(PDO::FETCH_OBJ);
                
                // Log success message
                error_log('✅ Check-in recorded: ' . json_encode($attendance));
                
                return [
                    'status' => 'success',
                    'message' => 'Checked in successfully',
                    'attendance' => $attendance
                ];
            } else {
                throw new Exception('Failed to create attendance record');
            }
        } catch (Exception $e) {
            // Log error message
            error_log('❌ Error in attendanceModel.arrive: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Record user departure (check-out)
     * Returns: array with status and message
     */
    public function leave($user_id, $check_out_time = null, $gps_location = null)
    {
        try {
            // Validate user ID
            if (!$user_id) {
                throw new Exception('UserId is required');
            }

            // Use current time if check_out_time not provided
            if (!$check_out_time) {
                $check_out_time = date('Y-m-d H:i:s');
            }

            // Find the most recent attendance record for the user without checkout time
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE user_id = :user_id 
                     AND check_out_time IS NULL
                     ORDER BY check_in_time DESC LIMIT 1";
                     
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $attendance = $stmt->fetch(PDO::FETCH_OBJ);
            
            if (!$attendance) {
                throw new Exception('No active check-in found. Please check in first.');
            }
            
            // Format GPS location if provided
            $locationData = null;
            if ($attendance->gps_location) {
                $locationData = json_decode($attendance->gps_location, true);
            }
            
            if ($gps_location && is_array($gps_location)) {
                if (!$locationData) {
                    $locationData = [];
                }
                
                // Add checkout location data
                $locationData['checkout_latitude'] = (float)$gps_location['latitude'];
                $locationData['checkout_longitude'] = (float)$gps_location['longitude'];
                
                if (isset($gps_location['accuracy'])) {
                    $locationData['checkout_accuracy'] = (float)$gps_location['accuracy'];
                }
            }
            
            // Convert location data back to JSON
            $locationJson = $locationData ? json_encode($locationData) : null;
            
            // Update the record with check-out time and location
            $update_query = "UPDATE " . $this->table_name . " 
                            SET check_out_time = :check_out_time";
            
            if ($locationJson) {
                $update_query .= ", gps_location = :gps_location";
            }
            
            $update_query .= " WHERE id = :id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':check_out_time', $check_out_time);
            $update_stmt->bindParam(':id', $attendance->id);
            
            if ($locationJson) {
                $update_stmt->bindParam(':gps_location', $locationJson);
            }
            
            if ($update_stmt->execute()) {
                // Get the updated attendance record
                $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $attendance->id);
                $stmt->execute();
                $updatedAttendance = $stmt->fetch(PDO::FETCH_OBJ);
                
                // Calculate duration
                $check_in = strtotime($updatedAttendance->check_in_time);
                $check_out = strtotime($updatedAttendance->check_out_time);
                $duration_seconds = $check_out - $check_in;
                
                // Format duration
                $hours = floor($duration_seconds / 3600);
                $minutes = floor(($duration_seconds % 3600) / 60);
                $formatted_duration = "{$hours}h {$minutes}m";
                
                // Add duration to the attendance object
                $updatedAttendance->duration = $formatted_duration;
                $updatedAttendance->duration_seconds = $duration_seconds;
                
                // Convert GPS JSON back to object
                if ($updatedAttendance->gps_location) {
                    $updatedAttendance->gps_location = json_decode($updatedAttendance->gps_location);
                }
                
                // Log success message
                error_log('✅ Check-out recorded: ' . json_encode($updatedAttendance));
                
                return [
                    'status' => 'success',
                    'message' => 'Checked out successfully',
                    'attendance' => $updatedAttendance
                ];
            } else {
                throw new Exception('Failed to update attendance record');
            }
        } catch (Exception $e) {
            // Log error message
            error_log('❌ Error in attendanceModel.leave: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance history for a specific user
     * Optional date range and status filters
     * Returns: array of formatted attendance records
     */
    public function getUserAttendanceHistory($user_id, $start_date = null, $end_date = null, $status = null)
    {
        try {
            if (!$user_id) {
                throw new Exception('UserId is required');
            }
            
            // Base query with user join
            $query = "SELECT a.*, u.full_name, u.department, u.position 
                     FROM " . $this->table_name . " a
                     LEFT JOIN users u ON a.user_id = u.id
                     WHERE a.user_id = :user_id";
            
            // Add date range filter if provided
            if ($start_date) {
                $start = $start_date . ' 00:00:00';
                $query .= " AND a.check_in_time >= :start_date";
            }
            
            if ($end_date) {
                $end = $end_date . ' 23:59:59';
                $query .= " AND a.check_in_time <= :end_date";
            }
            
            // Add status filter if provided
            if ($status) {
                $query .= " AND a.status = :status";
            }
            
            $query .= " ORDER BY a.check_in_time DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            
            if ($start_date) {
                $stmt->bindParam(':start_date', $start);
            }
            
            if ($end_date) {
                $stmt->bindParam(':end_date', $end);
            }
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the records for better readability
            $formattedRecords = [];
            foreach ($records as $record) {
                // Calculate duration if both check-in and check-out times exist
                $duration = null;
                if (!empty($record['check_in_time']) && !empty($record['check_out_time'])) {
                    $check_in = strtotime($record['check_in_time']);
                    $check_out = strtotime($record['check_out_time']);
                    $duration_ms = ($check_out - $check_in) * 1000; // Convert to milliseconds for consistency
                    
                    // Format as hours and minutes
                    $hours = floor($duration_ms / (1000 * 60 * 60));
                    $minutes = floor(($duration_ms % (1000 * 60 * 60)) / (1000 * 60));
                    $duration = "{$hours}h {$minutes}m";
                }
                
                // Get day of week in English
                $check_in_date = new DateTime($record['check_in_time']);
                $day_of_week = $check_in_date->format('l'); // 'l' returns full day name
                
                // Format the record
                $formattedRecord = [
                    'id' => $record['id'],
                    'date' => date('Y-m-d', strtotime($record['check_in_time'])),
                    'day_of_week' => $day_of_week,
                    'check_in_time' => $record['check_in_time'],
                    'check_out_time' => $record['check_out_time'] ?: null,
                    'status' => $record['status'],
                    'duration' => $duration,
                    'user_name' => $record['full_name'] ?: 'Unknown',
                    'department' => $record['department'] ?: 'Not assigned',
                    'position' => $record['position'] ?: 'Not assigned'
                ];
                
                // Add GPS location if it exists
                if (!empty($record['gps_location'])) {
                    $formattedRecord['gps_location'] = json_decode($record['gps_location']);
                }
                
                $formattedRecords[] = $formattedRecord;
            }
            
            return $formattedRecords;
        } catch (Exception $e) {
            // Log error message
            error_log('❌ Error in attendanceModel.getUserAttendanceHistory: ' . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}