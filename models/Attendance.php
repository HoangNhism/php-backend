<?php
class AttendanceModel
{
    private $conn;
    private $table_name = "attendance";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Record user check-in (arrive)
     *
     * @param string $userId - User ID
     * @param string $checkInTime - Check-in timestamp (optional, defaults to current time)
     * @param array $gpsLocation - GPS location data
     * @return object|false - Attendance record or false on failure
     */
    public function arrive($userId, $checkInTime = null, $gpsLocation = null)
    {
        try {
            // Validate user ID
            if (empty($userId)) {
                throw new Exception('UserId is required');
            }

            // Validate GPS location
            if (empty($gpsLocation) || !is_array($gpsLocation)) {
                throw new Exception('Valid GPS location object is required');
            }

            // Set check-in time to current time if not provided
            if (empty($checkInTime)) {
                $checkInTime = date('Y-m-d H:i:s');
            }

            // Create a clean GPS object to ensure we only store what we need
            $locationData = [
                'latitude' => (float) $gpsLocation['latitude'],
                'longitude' => (float) $gpsLocation['longitude']
            ];

            // Add optional accuracy if provided
            if (isset($gpsLocation['accuracy'])) {
                $locationData['accuracy'] = (float) $gpsLocation['accuracy'];
            }

            // Define cutoff time (8:00 AM)
            $cutoffDate = new DateTime($checkInTime);
            $cutoffDate->setTime(8, 0, 0);
            $cutoffTime = $cutoffDate->format('Y-m-d H:i:s');

            // Determine check-in status
            $checkInDateTime = new DateTime($checkInTime);
            $status = $checkInDateTime > $cutoffDate ? 'Late' : 'Present';

            // Check if user already has a check-in for today
            $todayStart = date('Y-m-d 00:00:00', strtotime($checkInTime));
            $todayEnd = date('Y-m-d 23:59:59', strtotime($checkInTime));
            
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE user_id = :user_id 
                      AND check_in_time BETWEEN :today_start AND :today_end";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':today_start', $todayStart);
            $stmt->bindParam(':today_end', $todayEnd);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Uncomment to prevent multiple check-ins per day
                // throw new Exception('You have already checked in today');
            }

            // Generate random ID
            $id = bin2hex(random_bytes(8)); // 16 hex characters
            
            // Store GPS location as JSON
            $locationJson = json_encode($locationData);

            // Insert attendance record
            $query = "INSERT INTO " . $this->table_name . " 
                      (id, user_id, status, check_in_time, gps_location) 
                      VALUES (:id, :user_id, :status, :check_in_time, :gps_location)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':check_in_time', $checkInTime);
            $stmt->bindParam(':gps_location', $locationJson);
            
            if ($stmt->execute()) {
                // Fetch the created record
                $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_OBJ);
            }
            
            return false;
        } catch (Exception $error) {
            error_log('❌ Error in AttendanceModel::arrive: ' . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Record user check-out (leave)
     *
     * @param string $userId - User ID
     * @param string $checkOutTime - Check-out timestamp (optional, defaults to current time)
     * @param array $gpsLocation - GPS location data
     * @return object|false - Updated attendance record or false on failure
     */
    public function leave($userId, $checkOutTime = null, $gpsLocation = null)
    {
        try {
            // Validate user ID
            if (empty($userId)) {
                throw new Exception('UserId is required');
            }

            // Set check-out time to current time if not provided
            if (empty($checkOutTime)) {
                $checkOutTime = date('Y-m-d H:i:s');
            }

            // Find the most recent attendance record for the user without checkout time
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE user_id = :user_id 
                      AND check_out_time IS NULL 
                      ORDER BY check_in_time DESC 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                throw new Exception('No active check-in found. Please check in first.');
            }
            
            $attendance = $stmt->fetch(PDO::FETCH_OBJ);
            
            // Format GPS location if provided
            if (!empty($gpsLocation) && is_array($gpsLocation)) {
                // Get existing location data
                $locationData = json_decode($attendance->gps_location, true) ?? [];
                
                // Add checkout location data
                $locationData['checkout_latitude'] = (float) $gpsLocation['latitude'];
                $locationData['checkout_longitude'] = (float) $gpsLocation['longitude'];
                
                if (isset($gpsLocation['accuracy'])) {
                    $locationData['checkout_accuracy'] = (float) $gpsLocation['accuracy'];
                }
                
                // Update with new combined location data
                $locationJson = json_encode($locationData);
            } else {
                // Keep existing location data
                $locationJson = $attendance->gps_location;
            }
            
            // Update checkout time and location
            $query = "UPDATE " . $this->table_name . " 
                      SET check_out_time = :check_out_time, 
                          gps_location = :gps_location 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':check_out_time', $checkOutTime);
            $stmt->bindParam(':gps_location', $locationJson);
            $stmt->bindParam(':id', $attendance->id);
            
            if ($stmt->execute()) {
                // Fetch the updated record
                $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $attendance->id);
                $stmt->execute();
                
                return $stmt->fetch(PDO::FETCH_OBJ);
            }
            
            return false;
        } catch (Exception $error) {
            error_log('❌ Error in AttendanceModel::leave: ' . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Get current user's attendance status for today
     *
     * @param string $userId - User ID
     * @return array - User's attendance status
     */
    public function getCurrentUserStatus($userId)
    {
        try {
            if (empty($userId)) {
                throw new Exception('UserId is required');
            }

            // Get today's date range
            $today = date('Y-m-d 00:00:00');
            $tomorrow = date('Y-m-d 00:00:00', strtotime('+1 day'));
            
            // Find today's attendance record for the user
            $query = "SELECT a.*, u.full_name, u.department, u.position 
                      FROM " . $this->table_name . " a
                      JOIN users u ON a.user_id = u.id
                      WHERE a.user_id = :user_id 
                      AND a.check_in_time BETWEEN :today AND :tomorrow
                      ORDER BY a.check_in_time DESC 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':today', $today);
            $stmt->bindParam(':tomorrow', $tomorrow);
            $stmt->execute();
            
            // Return different responses based on attendance status
            if ($stmt->rowCount() == 0) {
                return [
                    'is_checked_in' => false,
                    'is_checked_out' => false,
                    'status' => 'Not Checked In',
                    'message' => 'You haven\'t checked in today',
                    'attendance_data' => null,
                    'today_date' => date('Y-m-d')
                ];
            }
            
            $attendance = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($attendance->check_out_time) {
                return [
                    'is_checked_in' => true,
                    'is_checked_out' => true,
                    'status' => $attendance->status,
                    'message' => 'You have completed your check-in/out for today',
                    'attendance_data' => [
                        'check_in_time' => $attendance->check_in_time,
                        'check_out_time' => $attendance->check_out_time,
                        'status' => $attendance->status,
                        'id' => $attendance->id
                    ],
                    'today_date' => date('Y-m-d')
                ];
            }
            
            return [
                'is_checked_in' => true,
                'is_checked_out' => false,
                'status' => $attendance->status,
                'message' => 'You\'re currently checked in',
                'attendance_data' => [
                    'check_in_time' => $attendance->check_in_time,
                    'status' => $attendance->status,
                    'id' => $attendance->id
                ],
                'today_date' => date('Y-m-d')
            ];
        } catch (Exception $error) {
            error_log('❌ Error in AttendanceModel::getCurrentUserStatus: ' . $error->getMessage());
            throw $error;
        }
    }

    /**
     * Get user's attendance history
     *
     * @param string $userId - User ID
     * @param string|null $startDate - Optional start date filter (YYYY-MM-DD)
     * @param string|null $endDate - Optional end date filter (YYYY-MM-DD)
     * @param string|null $status - Optional status filter
     * @return array - Array of attendance records
     */
    public function getUserAttendanceHistory($userId, $startDate = null, $endDate = null, $status = null)
    {
        try {
            if (empty($userId)) {
                throw new Exception('UserId is required');
            }
            
            // Start building the query
            $query = "SELECT a.*, u.full_name, u.department, u.position 
                      FROM " . $this->table_name . " a
                      JOIN users u ON a.user_id = u.id
                      WHERE a.user_id = :user_id";
            
            $params = [':user_id' => $userId];
            
            // Add date range filter if provided
            if ($startDate) {
                $query .= " AND a.check_in_time >= :start_date";
                $params[':start_date'] = date('Y-m-d 00:00:00', strtotime($startDate));
            }
            
            if ($endDate) {
                $query .= " AND a.check_in_time <= :end_date";
                $params[':end_date'] = date('Y-m-d 23:59:59', strtotime($endDate));
            }
            
            // Add status filter if provided
            if ($status) {
                $query .= " AND a.status = :status";
                $params[':status'] = $status;
            }
            
            // Order by check-in time (most recent first)
            $query .= " ORDER BY a.check_in_time DESC";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindParam($key, $params[$key]);
            }
            
            $stmt->execute();
            
            // Fetch all records
            $records = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Format the records for better readability
            $formattedRecords = [];
            
            foreach ($records as $record) {
                // Calculate duration if both check-in and check-out exist
                $duration = null;
                if ($record->check_in_time && $record->check_out_time) {
                    $checkIn = new DateTime($record->check_in_time);
                    $checkOut = new DateTime($record->check_out_time);
                    $durationInterval = $checkOut->diff($checkIn);
                    
                    $hours = $durationInterval->h + ($durationInterval->days * 24);
                    $minutes = $durationInterval->i;
                    
                    $duration = "{$hours}h {$minutes}m";
                }
                
                // Get the day of week
                $date = new DateTime($record->check_in_time);
                $dayOfWeek = $date->format('l'); // Monday, Tuesday, etc.
                
                // Format the record
                $formattedRecords[] = [
                    'id' => $record->id,
                    'date' => date('Y-m-d', strtotime($record->check_in_time)),
                    'day_of_week' => $dayOfWeek,
                    'check_in_time' => $record->check_in_time,
                    'check_out_time' => $record->check_out_time,
                    'status' => $record->status,
                    'duration' => $duration,
                    'user_name' => $record->full_name,
                    'department' => $record->department ?: 'Not assigned',
                    'position' => $record->position ?: 'Not assigned',
                    'gps_location' => json_decode($record->gps_location, true)
                ];
            }
            
            return $formattedRecords;
        } catch (Exception $error) {
            error_log('❌ Error in AttendanceModel::getUserAttendanceHistory: ' . $error->getMessage());
            throw $error;
        }
    }
}