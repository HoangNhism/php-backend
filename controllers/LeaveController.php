<?php
// Bao gồm các phần cần thiết
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/LeaveRequest.php';
require_once __DIR__ . '/../models/LeaveBalance.php';

class LeaveController {
    private $database;
    private $db;
    
    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
    }
    
    // Yêu cầu nghỉ phép
    public function requestLeave($user, $data) {
        // Kiểm tra dữ liệu đầu vào
        if(
            !isset($data['leaveType']) || 
            !isset($data['startDate']) || 
            !isset($data['endDate'])
        ) {
            return array(
                "success" => false,
                "message" => "Thiếu thông tin: leaveType, startDate và endDate là bắt buộc"
            );
        }
        
        // Kiểm tra ngày hợp lệ
        $startDate = new DateTime($data['startDate']);
        $endDate = new DateTime($data['endDate']);
        $today = new DateTime();
        
        // Kiểm tra ngày bắt đầu không ở quá khứ
        if($startDate < $today) {
            return array(
                "success" => false,
                "message" => "Ngày bắt đầu nghỉ phép không thể ở quá khứ."
            );
        }
        
        // Kiểm tra ngày kết thúc phải sau hoặc trùng ngày bắt đầu
        if($endDate < $startDate) {
            return array(
                "success" => false,
                "message" => "Ngày kết thúc phải sau hoặc trùng ngày bắt đầu."
            );
        }
        
        // Khởi tạo đối tượng
        $leaveRequest = new LeaveRequest($this->db);
        $leaveBalance = new LeaveBalance($this->db);
        
        // Lấy số ngày nghỉ phép còn lại
        $leaveBalance->user_id = $user->id;
        if(!$leaveBalance->getBalance()) {
            // Để class LeaveBalance xử lý tính toán dựa trên hire_date
            $leaveBalance->initialize();
        }
        
        // Kiểm tra số ngày nghỉ
        $interval = $startDate->diff($endDate);
        $diffDays = $interval->days + 1; // +1 để tính cả ngày cuối
        
        if($leaveBalance->total_days < $diffDays) {
            return array(
                "success" => false,
                "message" => "Không đủ số ngày phép để nghỉ. Số ngày còn lại: " . $leaveBalance->total_days . ", Số ngày yêu cầu: " . $diffDays
            );
        }
        
        // Thiết lập dữ liệu
        $leaveRequest->user_id = $user->id;
        $leaveRequest->leave_type = $data['leaveType'];
        $leaveRequest->start_date = $data['startDate'];
        $leaveRequest->end_date = $data['endDate'];
        $leaveRequest->reason = isset($data['reason']) ? $data['reason'] : 'PERSONAL';
        $leaveRequest->custom_reason = isset($data['customReason']) && $data['reason'] == 'OTHER' ? $data['customReason'] : null;
        
        // Kiểm tra trùng lặp trước khi tạo yêu cầu
        if($leaveRequest->checkOverlap()) {
            return array(
                "success" => false,
                "message" => "Bạn đã có yêu cầu nghỉ phép trong khoảng thời gian này."
            );
        }
        
        // Tạo yêu cầu
        if($leaveRequest->createRequest()) {
            return array(
                "success" => true,
                "message" => "Đã tạo yêu cầu nghỉ phép thành công."
            );
        } else {
            return array(
                "success" => false,
                "message" => "Không thể tạo yêu cầu nghỉ phép. Vui lòng kiểm tra lại thông tin."
            );
        }
    }
    
    // Xử lý yêu cầu nghỉ phép (phê duyệt hoặc từ chối)
    public function processLeaveRequest($data) {
        // Kiểm tra dữ liệu đầu vào
        if(!isset($data['requestId']) || !isset($data['status'])) {
            return array(
                "success" => false,
                "message" => "Thiếu thông tin cần thiết."
            );
        }
        
        if($data['status'] == 'Rejected' && !isset($data['rejectReason'])) {
            return array(
                "success" => false,
                "message" => "Vui lòng cung cấp lý do từ chối."
            );
        }
        
        // Khởi tạo đối tượng
        $leaveRequest = new LeaveRequest($this->db);
        $leaveBalance = new LeaveBalance($this->db);
        
        // Kiểm tra yêu cầu tồn tại
        $leaveRequest->id = $data['requestId'];
        if(!$leaveRequest->getRequestById()) {
            return array(
                "success" => false,
                "message" => "Yêu cầu nghỉ phép không tồn tại."
            );
        }
        
        // Kiểm tra trạng thái hiện tại
        if($leaveRequest->status != 'Pending') {
            return array(
                "success" => false,
                "message" => "Yêu cầu nghỉ phép này đã được xử lý trước đó với trạng thái: " . $leaveRequest->status
            );
        }
        
        // Xử lý theo trạng thái
        if($data['status'] == 'Approved') {
            // Lấy số ngày nghỉ phép
            $leaveBalance->user_id = $leaveRequest->user_id;
            if(!$leaveBalance->getBalance()) {
                // Nếu chưa có thông tin số ngày phép, khởi tạo
                $leaveBalance->initialize();
                if(!$leaveBalance->getBalance()) {
                    return array(
                        "success" => false,
                        "message" => "Không thể khởi tạo thông tin số ngày phép của nhân viên."
                    );
                }
            }
            
            // Tính số ngày nghỉ
            $startDate = new DateTime($leaveRequest->start_date);
            $endDate = new DateTime($leaveRequest->end_date);
            $interval = $startDate->diff($endDate);
            $diffDays = $interval->days + 1; // +1 để tính cả ngày cuối
            
            if($leaveBalance->total_days < $diffDays) {
                return array(
                    "success" => false,
                    "message" => "Không đủ ngày phép để phê duyệt. Số ngày còn lại: " . $leaveBalance->total_days . ", Số ngày yêu cầu: " . $diffDays
                );
            }
            
            // Trừ số ngày phép
            $leaveBalance->total_days = $leaveBalance->total_days - $diffDays;
            if(!$leaveBalance->updateBalance()) {
                return array(
                    "success" => false,
                    "message" => "Không thể cập nhật số ngày phép."
                );
            }
        }
        
        // Cập nhật trạng thái
        $leaveRequest->status = $data['status'];
        $leaveRequest->reject_reason = isset($data['rejectReason']) ? $data['rejectReason'] : null;
        
        if($leaveRequest->processRequest()) {
            return array(
                "success" => true,
                "message" => "Đã " . ($data['status'] == 'Approved' ? 'phê duyệt' : 'từ chối') . " yêu cầu nghỉ phép thành công."
            );
        } else {
            return array(
                "success" => false,
                "message" => "Không thể xử lý yêu cầu nghỉ phép."
            );
        }
    }
    
    // Lấy số ngày nghỉ phép còn lại
    public function getLeaveBalance($user) {
        // Khởi tạo đối tượng
        $leaveBalance = new LeaveBalance($this->db);
        $leaveBalance->user_id = $user->id;
        
        if($leaveBalance->getBalance()) {
            return array(
                "success" => true,
                "data" => $leaveBalance->total_days
            );
        } else {
            // Để class LeaveBalance xử lý tính toán dựa trên hire_date
            $leaveBalance->initialize();
            
            return array(
                "success" => true,
                "data" => $leaveBalance->total_days
            );
        }
    }
    
    // Khởi tạo số ngày nghỉ phép cho nhân viên
    public function initializeLeaveBalance($data) {
        // Kiểm tra dữ liệu đầu vào
        if(!isset($data['userId'])) {
            return array(
                "success" => false,
                "message" => "Thiếu thông tin người dùng."
            );
        }
        
        // Khởi tạo đối tượng
        $leaveBalance = new LeaveBalance($this->db);
        $leaveBalance->user_id = $data['userId'];
        
        // Nếu có số ngày ban đầu được chỉ định, sử dụng giá trị đó
        if (isset($data['initialDays'])) {
            $leaveBalance->total_days = $data['initialDays'];
        }
        // Ngược lại, để LeaveBalance.initialize() xử lý tính toán dựa trên hire_date
        
        if($leaveBalance->initialize()) {
            return array(
                "success" => true,
                "message" => "Đã khởi tạo số ngày nghỉ phép thành công.",
                "data" => array(
                    "user_id" => $leaveBalance->user_id,
                    "total_days" => $leaveBalance->total_days
                )
            );
        } else {
            return array(
                "success" => false,
                "message" => "Không thể khởi tạo số ngày nghỉ phép."
            );
        }
    }
    
    // Lấy tất cả yêu cầu nghỉ phép (cho admin và manager)
    public function getAllLeaveRequests() {
        // Khởi tạo đối tượng
        $leaveRequest = new LeaveRequest($this->db);
        $result = $leaveRequest->getAllRequests();
        $num = $result->rowCount();
        
        if($num > 0) {
            $leave_requests = array();
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $leave_item = array(
                    "id" => $row['id'],
                    "user_id" => $row['user_id'],
                    "full_name" => $row['full_name'],
                    "email" => $row['email'],
                    "department" => $row['department'],
                    "position" => $row['position'],
                    "leave_type" => $row['leave_type'],
                    "start_date" => $row['start_date'],
                    "end_date" => $row['end_date'],
                    "status" => $row['status'],
                    "reject_reason" => $row['reject_reason'],
                    "reason" => $row['reason'],
                    "custom_reason" => $row['custom_reason'],
                    "created_at" => $row['created_at']
                );
                
                $leave_requests[] = $leave_item;
            }
            
            return array(
                "success" => true,
                "data" => $leave_requests
            );
        } else {
            return array(
                "success" => true,
                "data" => array(),
                "message" => "Không có yêu cầu nghỉ phép nào."
            );
        }
    }
    
    // Lấy yêu cầu nghỉ phép của user hiện tại
    public function getUserLeaveRequests($user) {
        // Khởi tạo đối tượng
        $leaveRequest = new LeaveRequest($this->db);
        $result = $leaveRequest->getUserRequests($user->id);
        $num = $result->rowCount();
        
        if($num > 0) {
            $leave_requests = array();
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $leave_item = array(
                    "id" => $row['id'],
                    "user_id" => $row['user_id'],
                    "leave_type" => $row['leave_type'],
                    "start_date" => $row['start_date'],
                    "end_date" => $row['end_date'],
                    "status" => $row['status'],
                    "reject_reason" => $row['reject_reason'],
                    "reason" => $row['reason'],
                    "custom_reason" => $row['custom_reason'],
                    "created_at" => $row['created_at']
                );
                
                $leave_requests[] = $leave_item;
            }
            
            return array(
                "success" => true,
                "data" => $leave_requests
            );
        } else {
            return array(
                "success" => true,
                "data" => array(),
                "message" => "Bạn không có yêu cầu nghỉ phép nào."
            );
        }
    }
    
    // Reset lại số ngày nghỉ phép hàng năm cho tất cả nhân viên (thực hiện định kỳ đầu năm)
    public function resetAnnualLeaveBalances() {
        // Chỉ Admin mới có quyền thực hiện chức năng này
        $leaveBalance = new LeaveBalance($this->db);
        $resetCount = $leaveBalance->resetAnnualBalance();
        
        if($resetCount > 0) {
            return array(
                "success" => true,
                "message" => "Đã cập nhật số ngày nghỉ phép cho $resetCount nhân viên."
            );
        } else {
            return array(
                "success" => false,
                "message" => "Không có nhân viên nào được cập nhật số ngày nghỉ phép."
            );
        }
    }
}
?> 