<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';

use Firebase\JWT\JWT;

// Thông tin tài khoản admin
$adminData = [
    'email' => 'admin@gmail.com',
    'password' => 'Admin@123',
    'full_name' => 'System Administrator',
    'mobile' => '0987654321',
    'address' => '123 Admin Street, Hanoi',
    'department' => 'IT',
    'position' => 'System Admin',
    'hire_date' => '2024-01-01',
    'role' => 'Admin'
];

try {
    // Kết nối database
    $database = new Database();
    $conn = $database->getConnection();

    // Kiểm tra xem email đã tồn tại chưa
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $checkStmt->execute(['email' => $adminData['email']]);
    
    if ($checkStmt->rowCount() > 0) {
        echo "Tài khoản admin đã tồn tại!\n";
        exit;
    }

    // Mã hóa mật khẩu
    $hashedPassword = password_hash($adminData['password'], PASSWORD_BCRYPT);

    // Tạo ID ngẫu nhiên
    $id = bin2hex(random_bytes(8));

    // Chuẩn bị câu lệnh SQL
    $sql = "INSERT INTO users (
        id, email, password, full_name, mobile, address, 
        department, position, hire_date, role, created_at, updated_at
    ) VALUES (
        :id, :email, :password, :full_name, :mobile, :address,
        :department, :position, :hire_date, :role, NOW(), NOW()
    )";

    $stmt = $conn->prepare($sql);
    
    // Thực thi câu lệnh
    $stmt->execute([
        'id' => $id,
        'email' => $adminData['email'],
        'password' => $hashedPassword,
        'full_name' => $adminData['full_name'],
        'mobile' => $adminData['mobile'],
        'address' => $adminData['address'],
        'department' => $adminData['department'],
        'position' => $adminData['position'],
        'hire_date' => $adminData['hire_date'],
        'role' => $adminData['role']
    ]);

    echo "Tạo tài khoản admin thành công!\n";
    echo "Email: " . $adminData['email'] . "\n";
    echo "Mật khẩu: " . $adminData['password'] . "\n";

} catch(PDOException $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
?> 