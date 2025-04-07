<?php
class Payroll {
    private $conn;
    
    // Payroll properties
    public $id;
    public $employee_id;
    public $base_salary;
    public $allowances;
    public $deductions;
    public $social_insurance;
    public $health_insurance;
    public $unemployment_insurance;
    public $personal_income_tax;
    public $total_deductions;
    public $net_salary;
    public $pay_period;
    public $region;
    public $status;
    public $created_at;
    
    // Constants for tax and insurance rates
    const PERSONAL_INCOME_TAX_RATE = 0.1;
    const SOCIAL_INSURANCE_RATE = 0.08;
    const HEALTH_INSURANCE_RATE = 0.015;
    const UNEMPLOYMENT_INSURANCE_RATE = 0.01;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO payrolls SET
                  id = :id,
                  employee_id = :employee_id,
                  base_salary = :base_salary,
                  allowances = :allowances,
                  deductions = :deductions,
                  social_insurance = :social_insurance,
                  health_insurance = :health_insurance,
                  unemployment_insurance = :unemployment_insurance,
                  personal_income_tax = :personal_income_tax,
                  total_deductions = :total_deductions,
                  net_salary = :net_salary,
                  pay_period = :pay_period,
                  region = :region,
                  status = :status";
    
        $stmt = $this->conn->prepare($query);
    
        // Generate a UUID for the id field
        $this->id = $this->id ?? $this->generateUUID();
    
        // Sanitize and bind
        $this->sanitize();
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':employee_id', $this->employee_id);
        $stmt->bindParam(':base_salary', $this->base_salary);
        $stmt->bindParam(':allowances', $this->allowances);
        $stmt->bindParam(':deductions', $this->deductions);
        $stmt->bindParam(':social_insurance', $this->social_insurance);
        $stmt->bindParam(':health_insurance', $this->health_insurance);
        $stmt->bindParam(':unemployment_insurance', $this->unemployment_insurance);
        $stmt->bindParam(':personal_income_tax', $this->personal_income_tax);
        $stmt->bindParam(':total_deductions', $this->total_deductions);
        $stmt->bindParam(':net_salary', $this->net_salary);
        $stmt->bindParam(':pay_period', $this->pay_period);
        $stmt->bindParam(':region', $this->region);
        $stmt->bindParam(':status', $this->status);
    
        return $stmt->execute();
    }
    
    private function generateUUID() {
        return bin2hex(random_bytes(8));
    }

    // Get all payrolls
    public function getAll() {
        $query = "SELECT p.*, u.full_name, u.email 
                  FROM payrolls p 
                  LEFT JOIN users u ON p.employee_id = u.id 
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Get payroll by ID
    public function getById($id) {
        $query = "SELECT p.*, u.full_name, u.email 
                  FROM payrolls p 
                  LEFT JOIN users u ON p.employee_id = u.id 
                  WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update payroll
    public function update() {
        $query = "UPDATE payrolls SET
                  base_salary = :base_salary,
                  allowances = :allowances,
                  deductions = :deductions,
                  social_insurance = :social_insurance,
                  health_insurance = :health_insurance,
                  unemployment_insurance = :unemployment_insurance,
                  personal_income_tax = :personal_income_tax,
                  total_deductions = :total_deductions,
                  net_salary = :net_salary,
                  pay_period = :pay_period,
                  region = :region,
                  status = :status
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->sanitize();
        
        // Bind parameters
        $stmt->bindParam(':base_salary', $this->base_salary);
        $stmt->bindParam(':allowances', $this->allowances);
        $stmt->bindParam(':deductions', $this->deductions);
        $stmt->bindParam(':social_insurance', $this->social_insurance);
        $stmt->bindParam(':health_insurance', $this->health_insurance);
        $stmt->bindParam(':unemployment_insurance', $this->unemployment_insurance);
        $stmt->bindParam(':personal_income_tax', $this->personal_income_tax);
        $stmt->bindParam(':total_deductions', $this->total_deductions);
        $stmt->bindParam(':net_salary', $this->net_salary);
        $stmt->bindParam(':pay_period', $this->pay_period);
        $stmt->bindParam(':region', $this->region);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        
        return $stmt->execute();
    }

    // Delete payroll
    public function delete($id) {
        $query = "DELETE FROM payrolls WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        return $stmt->execute();
    }

    private function sanitize() {
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->base_salary = htmlspecialchars(strip_tags($this->base_salary));
        $this->allowances = htmlspecialchars(strip_tags($this->allowances));
        $this->deductions = htmlspecialchars(strip_tags($this->deductions));
        $this->social_insurance = htmlspecialchars(strip_tags($this->social_insurance));
        $this->health_insurance = htmlspecialchars(strip_tags($this->health_insurance));
        $this->unemployment_insurance = htmlspecialchars(strip_tags($this->unemployment_insurance));
        $this->personal_income_tax = htmlspecialchars(strip_tags($this->personal_income_tax));
        $this->total_deductions = htmlspecialchars(strip_tags($this->total_deductions));
        $this->net_salary = htmlspecialchars(strip_tags($this->net_salary));
        $this->pay_period = htmlspecialchars(strip_tags($this->pay_period));
        $this->region = htmlspecialchars(strip_tags($this->region));
        $this->status = htmlspecialchars(strip_tags($this->status));
    }
}
?>