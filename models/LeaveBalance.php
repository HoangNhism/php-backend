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
        
        // Tính số ngày nghỉ phép dựa trên tháng tuyển dụng
        if (!isset($this->total_days)) {
            // Lấy thông tin hire_date của nhân viên
            $query = "SELECT hire_date FROM users WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->user_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && isset($result['hire_date'])) {
                $hireDate = new DateTime($result['hire_date']);
                $currentDate = new DateTime();
                
                // Nếu năm tuyển dụng khác năm hiện tại thì được hưởng đủ 12 ngày
                if ($hireDate->format('Y') < $currentDate->format('Y')) {
                    $this->total_days = 12;
                } else {
                    // Nếu cùng năm, tính theo số tháng còn lại trong năm
                    $hireMonth = (int)$hireDate->format('m');
                    $monthsLeft = 12 - $hireMonth + 1; // +1 vì tính cả tháng hiện tại
                    $this->total_days = round($monthsLeft * (12/12)); // Số ngày phép tỷ lệ với số tháng làm việc
                    
                    // Đảm bảo số ngày phép tối thiểu là 1
                    if ($this->total_days < 1) {
                        $this->total_days = 1;
                    }
                }
            } else {
                // Nếu không có hire_date, sử dụng tháng hiện tại
                $currentMonth = (int)date('m');
                $monthsLeft = 12 - $currentMonth + 1;
                $this->total_days = round($monthsLeft * (12/12));
                
                // Đảm bảo số ngày phép tối thiểu là 1
                if ($this->total_days < 1) {
                    $this->total_days = 1;
                }
            }
        }
        
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
    
    // Reset lại số ngày phép hàng năm cho tất cả nhân viên
    public function resetAnnualBalance() {
        // Lấy danh sách tất cả nhân viên
        $query = "SELECT id, hire_date FROM users WHERE status = 'Active' AND isDelete = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resetCount = 0;
        $currentYear = (int)date('Y');
        
        foreach($users as $user) {
            // Tính toán số ngày phép mới cho mỗi nhân viên dựa trên thâm niên
            if (isset($user['hire_date']) && !empty($user['hire_date'])) {
                $hireDate = new DateTime($user['hire_date']);
                $now = new DateTime();
                $years = $now->diff($hireDate)->y;
                
                // Kiểm tra xem nhân viên có phải mới vào năm nay không
                if ($hireDate->format('Y') == $currentYear) {
                    // Nếu mới vào năm nay, tính theo số tháng còn lại
                    $hireMonth = (int)$hireDate->format('m');
                    $monthsLeft = 12 - $hireMonth + 1; // +1 vì tính cả tháng hiện tại
                    $totalDays = round($monthsLeft * (12/12));
                    
                    // Đảm bảo số ngày phép tối thiểu là 1
                    if ($totalDays < 1) {
                        $totalDays = 1;
                    }
                } else {
                    // Logic: 12 ngày cơ bản + thêm ngày dựa trên thâm niên (tối đa 20 ngày)
                    $totalDays = 12;
                    
                    // Cộng thêm ngày dựa trên thâm niên
                    if($years >= 5) {
                        $totalDays += 5; // thêm 5 ngày nếu làm việc từ 5 năm trở lên
                    } elseif($years >= 3) {
                        $totalDays += 3; // thêm 3 ngày nếu làm việc từ 3-4 năm
                    } elseif($years >= 1) {
                        $totalDays += 1; // thêm 1 ngày nếu làm việc từ 1-2 năm
                    }
                }
            } else {
                // Nếu không có hire_date, cho mặc định 12 ngày
                $totalDays = 12;
            }
            
            // Cập nhật số ngày phép
            $updateQuery = "UPDATE leave_balance SET total_days = ? WHERE user_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(1, $totalDays);
            $updateStmt->bindParam(2, $user['id']);
            
            // Nếu nhân viên chưa có bản ghi trong leave_balance, tạo mới
            if($updateStmt->execute()) {
                $resetCount++;
            } else {
                // Tạo bản ghi mới nếu chưa tồn tại
                $checkQuery = "SELECT id FROM leave_balance WHERE user_id = ?";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(1, $user['id']);
                $checkStmt->execute();
                
                if($checkStmt->rowCount() == 0) {
                    $insertId = substr(md5(rand()), 0, 16);
                    $insertQuery = "INSERT INTO leave_balance (id, user_id, total_days) VALUES (?, ?, ?)";
                    $insertStmt = $this->conn->prepare($insertQuery);
                    $insertStmt->bindParam(1, $insertId);
                    $insertStmt->bindParam(2, $user['id']);
                    $insertStmt->bindParam(3, $totalDays);
                    
                    if($insertStmt->execute()) {
                        $resetCount++;
                    }
                }
            }
        }
        
        return $resetCount;
    }
}
?> 