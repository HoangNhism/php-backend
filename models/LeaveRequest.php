<?php
class LeaveRequest {
    // DB connection
    private $conn;
    
    // Properties mapping to DB columns
    public $id;
    public $user_id;
    public $leave_type;
    public $start_date;
    public $end_date;
    public $reason;
    public $custom_reason;
    public $status;
    public $reject_reason;
    public $created_at;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Tạo yêu cầu nghỉ phép mới
    public function createRequest() {
        // Generate a random 16-character ID
        $this->id = substr(md5(rand()), 0, 16);
        
        // Create query
        $query = "INSERT INTO leave_requests 
                  (id, user_id, leave_type, start_date, end_date, reason, custom_reason, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->leave_type = htmlspecialchars(strip_tags($this->leave_type));
        $this->start_date = htmlspecialchars(strip_tags($this->start_date));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->reason = htmlspecialchars(strip_tags($this->reason ?? 'PERSONAL'));
        $this->custom_reason = isset($this->custom_reason) ? htmlspecialchars(strip_tags($this->custom_reason)) : null;
        
        // Bind parameters
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);
        $stmt->bindParam(3, $this->leave_type);
        $stmt->bindParam(4, $this->start_date);
        $stmt->bindParam(5, $this->end_date);
        $stmt->bindParam(6, $this->reason);
        $stmt->bindParam(7, $this->custom_reason);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Xử lý yêu cầu (phê duyệt/từ chối)
    public function processRequest() {
        // Create query
        $query = "UPDATE leave_requests 
                  SET status = ?, reject_reason = ? 
                  WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->reject_reason = $this->reject_reason ? htmlspecialchars(strip_tags($this->reject_reason)) : null;
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind parameters
        $stmt->bindParam(1, $this->status);
        $stmt->bindParam(2, $this->reject_reason);
        $stmt->bindParam(3, $this->id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Lấy yêu cầu theo ID
    public function getRequestById() {
        // Create query
        $query = "SELECT * FROM leave_requests WHERE id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind ID
        $stmt->bindParam(1, $this->id);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            // Set properties
            $this->user_id = $row['user_id'];
            $this->leave_type = $row['leave_type'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->reason = $row['reason'];
            $this->custom_reason = $row['custom_reason'];
            $this->status = $row['status'];
            $this->reject_reason = $row['reject_reason'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }
    
    // Lấy tất cả yêu cầu nghỉ phép (kèm thông tin người dùng)
    public function getAllRequests() {
        // Create query
        $query = "SELECT r.*, u.full_name, u.email, u.department, u.position 
                  FROM leave_requests r
                  LEFT JOIN users u ON r.user_id = u.id
                  ORDER BY r.created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
    
    // Lấy yêu cầu nghỉ phép của nhân viên cụ thể
    public function getUserRequests($userId) {
        // Create query
        $query = "SELECT * FROM leave_requests 
                  WHERE user_id = ? 
                  ORDER BY created_at DESC";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind user ID
        $stmt->bindParam(1, $userId);
        
        // Execute query
        $stmt->execute();
        
        return $stmt;
    }
}
?> 