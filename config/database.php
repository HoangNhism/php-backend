<?php
class Database {
    public $host = "localhost";
    public $db_name = "erp_hr_management";  // Tên database
    public $username = "root";  // User MySQL
    public $password = "";      // Mật khẩu MySQL
    public $conn;

    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return null;
        }
    }
}
?>
