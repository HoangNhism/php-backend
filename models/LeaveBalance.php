<?php
class LeaveBalance {
    // DB connection
    private $conn;
    
    // Properties
    public $id;
    public $user_id;
    public $total_days;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Lấy số ngày phép còn lại
    public function getBalance() {
        // Create query
        $query = "SELECT * FROM leave_balance WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(1, $this->user_id);
        
        // Execute query
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->id = $row['id'];
            $this->total_days = $row['total_days'];
            return true;
        }
        
        return false;
    }
    
    // Cập nhật số ngày phép
    public function updateBalance() {
        // Create query
        $query = "UPDATE leave_balance SET total_days = ? WHERE user_id = ?";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->total_days = htmlspecialchars(strip_tags($this->total_days));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        
        // Bind parameters
        $stmt->bindParam(1, $this->total_days);
        $stmt->bindParam(2, $this->user_id);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Khởi tạo số ngày phép cho nhân viên mới
    public function initialize() {
        // Check if balance already exists
        if($this->getBalance()) {
            return $this->updateBalance();
        }
        
        // Generate a random 16-character ID
        $this->id = substr(md5(rand()), 0, 16);
        
        // Create query
        $query = "INSERT INTO leave_balance (id, user_id, total_days) VALUES (?, ?, ?)";
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        // Sanitize inputs
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->total_days = htmlspecialchars(strip_tags($this->total_days));
        
        // Bind parameters
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->user_id);
        $stmt->bindParam(3, $this->total_days);
        
        // Execute query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?> 