CREATE DATABASE IF NOT EXISTS food_ordering_db;
USE food_ordering_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('admin', 'staff') DEFAULT 'staff'
);

INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@example.com', '1234', 'admin'),
('Staff', 'staff@example.com', '1234', 'staff');

-- //(password: password123)
-- // admin@example.com
-- // styaff@example.com
