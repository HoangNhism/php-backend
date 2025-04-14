<?php
require_once __DIR__ . '/../models/Payroll.php';
require_once __DIR__ . '/../config/database.php';

class PayrollController
{
    private $database;
    private $db;
    private $payroll;

    public function __construct()
    {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
        $this->payroll = new Payroll($this->db);
    }

    public function createPayroll($data, $user)
    {
        // Check if user is array and convert to object if needed
        $userRole = is_array($user) ? $user['role'] : $user->role;

        // Check permissions
        if ($userRole !== 'Admin' && $userRole !== 'Accountant') {
            return array(
                "success" => false,
                "message" => "Không có quyền tạo bảng lương"
            );
        }

        // Validate input
        if (!isset($data['employee_id']) || !isset($data['base_salary'])) {
            return array(
                "success" => false,
                "message" => "Thiếu thông tin bắt buộc"
            );
        }

        // Set payroll properties
        $this->payroll->employee_id = $data['employee_id'];
        $this->payroll->base_salary = $data['base_salary'];
        $this->payroll->allowances = $data['allowances'] ?? 0;
        $this->payroll->deductions = $data['deductions'] ?? 0;
        $this->payroll->social_insurance = $data['social_insurance'];
        $this->payroll->health_insurance = $data['health_insurance'];
        $this->payroll->unemployment_insurance = $data['unemployment_insurance'];
        $this->payroll->personal_income_tax = $data['personal_income_tax'];
        $this->payroll->total_deductions = $data['total_deductions'];
        $this->payroll->net_salary = $data['net_salary'];
        $this->payroll->pay_period = $data['pay_period'] ?? 'Monthly';
        $this->payroll->region = $data['region'] ?? 'I';
        $this->payroll->status = $data['status'] ?? 'Completed';

        if ($this->payroll->create()) {
            return array(
                "success" => true,
                "message" => "Tạo bảng lương thành công"
            );
        }

        return array(
            "success" => false,
            "message" => "Không thể tạo bảng lương"
        );
    }

    public function getAllPayrolls()
    {
        $result = $this->payroll->getAll();
        $payrolls = $result->fetchAll(PDO::FETCH_ASSOC);

        return array(
            "success" => true,
            "data" => $payrolls
        );
    }

    public function getPayrollById($id)
    {
        $payroll = $this->payroll->getById($id);

        if ($payroll) {
            return array(
                "success" => true,
                "data" => $payroll
            );
        }

        return array(
            "success" => false,
            "message" => "Không tìm thấy bảng lương"
        );
    }

    public function updatePayroll($id, $data)
    {
        try {
            // Set payroll properties
            $this->payroll->id = $id;
            $this->payroll->employee_id = $data['employee_id'];
            $this->payroll->base_salary = $data['base_salary'];
            $this->payroll->allowances = $data['allowances'] ?? 0;
            $this->payroll->deductions = $data['deductions'] ?? 0;
            $this->payroll->social_insurance = $data['social_insurance'];
            $this->payroll->health_insurance = $data['health_insurance'];
            $this->payroll->unemployment_insurance = $data['unemployment_insurance'];
            $this->payroll->personal_income_tax = $data['personal_income_tax'];
            $this->payroll->total_deductions = $data['total_deductions'];
            $this->payroll->net_salary = $data['net_salary'];
            $this->payroll->pay_period = $data['pay_period'] ?? 'Monthly';
            $this->payroll->region = $data['region'] ?? 'I';
            $this->payroll->status = $data['status'] ?? 'Completed';

            if ($this->payroll->update()) {
                // Fetch updated payroll to return
                $updatedPayroll = $this->payroll->getById($id);
                return array(
                    "success" => true,
                    "message" => "Cập nhật bảng lương thành công",
                    "data" => $updatedPayroll
                );
            }

            return array(
                "success" => false,
                "message" => "Không thể cập nhật bảng lương"
            );
        } catch (Exception $e) {
            return array(
                "success" => false,
                "message" => "Lỗi cập nhật: " . $e->getMessage()
            );
        }
    }

    public function deletePayroll($id)
    {
        if ($this->payroll->delete($id)) {
            return array(
                "success" => true,
                "message" => "Xóa bảng lương thành công"
            );
        }

        return array(
            "success" => false,
            "message" => "Không thể xóa bảng lương"
        );
    }

    public function exportPayrollPDF($id)
    {
        $payroll = $this->payroll->getById($id);

        if (!$payroll) {
            return array(
                "success" => false,
                "message" => "Không tìm thấy bảng lương"
            );
        }

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('HR ERP System');
        $pdf->SetAuthor('HR System');
        $pdf->SetTitle('Payroll Report');

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('dejavusans', '', 12);

        // Add content
        $html = $this->generatePayrollHTML($payroll);
        $pdf->writeHTML($html, true, false, true, false, '');

        // Generate PDF
        $pdfContent = $pdf->Output('payroll.pdf', 'S');

        return array(
            "success" => true,
            "data" => base64_encode($pdfContent),
            "filename" => "payroll_{$id}.pdf"
        );
    }

    private function generatePayrollHTML($payroll)
    {
        // Format currency
        $formatCurrency = function ($amount) {
            return number_format($amount, 0, ',', '.') . ' VND';
        };

        return "
            <h1>PHIẾU LƯƠNG</h1>
            <p>Nhân viên: {$payroll['full_name']}</p>
            <p>Email: {$payroll['email']}</p>
            <p>Lương cơ bản: {$formatCurrency($payroll['base_salary'])}</p>
            <p>Phụ cấp: {$formatCurrency($payroll['allowances'])}</p>
            <p>BHXH: {$formatCurrency($payroll['social_insurance'])}</p>
            <p>BHYT: {$formatCurrency($payroll['health_insurance'])}</p>
            <p>BHTN: {$formatCurrency($payroll['unemployment_insurance'])}</p>
            <p>Thuế TNCN: {$formatCurrency($payroll['personal_income_tax'])}</p>
            <p>Tổng khấu trừ: {$formatCurrency($payroll['total_deductions'])}</p>
            <p>Lương thực nhận: {$formatCurrency($payroll['net_salary'])}</p>
        ";
    }
}
