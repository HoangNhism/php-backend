# php-backend
remember to create a .env and .htacess file to run project
SQL QUERY:
-- Create database
CREATE DATABASE IF NOT EXISTS erp_hr_management;
USE erp_hr_management;

-- Create Users table
CREATE TABLE users (
  id VARCHAR(16) PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  mobile VARCHAR(20),
  address VARCHAR(255),
  avatarURL VARCHAR(255),
  department VARCHAR(100),
  position VARCHAR(100),
  hire_date DATE,
  status ENUM('Active', 'Inactive', 'Resigned') DEFAULT 'Active',
  role ENUM('Admin', 'Employee', 'Manager', 'Account') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  isDelete BOOLEAN DEFAULT FALSE
);

-- Create Attendance table
CREATE TABLE attendance (
  id VARCHAR(16) PRIMARY KEY,
  user_id VARCHAR(16) NOT NULL,
  status ENUM('On time', 'Late', 'Absent', 'Leave') NOT NULL,
  check_in_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  check_out_time TIMESTAMP NULL,
  gps_location JSON,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create Projects table
CREATE TABLE projects (
  id VARCHAR(16) PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  start_date TIMESTAMP NULL,
  end_date TIMESTAMP NULL,
  create_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  manager_id VARCHAR(16),
  isDelete BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- Create ProjectMembers table
CREATE TABLE project_members (
  project_id VARCHAR(16) NOT NULL,
  user_id VARCHAR(16) NOT NULL,
  role ENUM('Manager', 'Member') DEFAULT 'Member',
  join_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  isDelete BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (project_id, user_id),
  FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Tasks table
CREATE TABLE tasks (
  id VARCHAR(16) PRIMARY KEY,
  project_id VARCHAR(16) NOT NULL,
  user_id VARCHAR(16),
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('To Do', 'In Progress', 'Completed') DEFAULT 'To Do',
  priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
  dueDate TIMESTAMP NULL,
  isDelete BOOLEAN DEFAULT FALSE,
  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (project_id) REFERENCES projects(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create Chats table
CREATE TABLE chats (
  id VARCHAR(16) PRIMARY KEY,
  sender_id VARCHAR(16) NOT NULL,
  receiver_id VARCHAR(16) NOT NULL,
  message TEXT NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(id),
  FOREIGN KEY (receiver_id) REFERENCES users(id)
);

-- Create LeaveRequests table
CREATE TABLE leave_requests (
  id VARCHAR(16) PRIMARY KEY,
  user_id VARCHAR(16) NOT NULL,
  leave_type ENUM('Annual', 'Sick', 'Maternity', 'Unpaid') NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
  reject_reason VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create LeaveBalance table
CREATE TABLE leave_balance (
  id VARCHAR(16) PRIMARY KEY,
  user_id VARCHAR(16) NOT NULL,
  total_days INT DEFAULT 12 NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE (user_id)
);

-- Create Payrolls table
CREATE TABLE payrolls (
  id VARCHAR(16) PRIMARY KEY,
  employee_id VARCHAR(16) NOT NULL,
  base_salary FLOAT NOT NULL,
  allowances FLOAT DEFAULT 0,
  deductions FLOAT DEFAULT 0,
  net_salary FLOAT NOT NULL,
  pay_period VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Performance Reviews table
CREATE TABLE performance_reviews (
  id VARCHAR(16) PRIMARY KEY,
  user_id VARCHAR(16) NOT NULL,
  review_period ENUM('Monthly', 'Quarterly', 'Yearly') DEFAULT 'Monthly',
  score INT NOT NULL CHECK (score BETWEEN 0 AND 100),
  reviewer_id VARCHAR(16) NOT NULL,
  comments TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Overtime table
CREATE TABLE overtime (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(16) NOT NULL,
  hours FLOAT NOT NULL,
  reason VARCHAR(255),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX idx_attendance_user_id ON attendance(user_id);
CREATE INDEX idx_attendance_date ON attendance(check_in_time);
CREATE INDEX idx_projects_manager ON projects(manager_id);
CREATE INDEX idx_tasks_user ON tasks(user_id);
CREATE INDEX idx_tasks_project ON tasks(project_id);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_department ON users(department);
CREATE INDEX idx_leave_user ON leave_requests(user_id);
CREATE INDEX idx_leave_dates ON leave_requests(start_date, end_date);

-- Insert default admin user (password: 123)
INSERT INTO users (
  id, 
  email, 
  password, 
  full_name, 
  role, 
  created_at, 
  updated_at
) VALUES (
  SUBSTRING(MD5(RAND()) FROM 1 FOR 16),
  'admin@gmail.com',
  '$2a$10$X7FXzQGwHLATkDN4K9EwLuiZ3RUAdIBQe1RQxl6LQ7cJ60g45JRiS',
  'Administrator',
  'Admin',
  NOW(),
  NOW()
);
