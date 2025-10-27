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
('Admin', 'admin@example.com', '$2y$10$u7o6wHhLqf2fR8tFqTcV1eG1k2v2d1Yw3zO6Z0c4bqH1c9wQw1u3K', 'admin'),
('Staff', 'staff@example.com', '$2y$10$u7o6wHhLqf2fR8tFqTcV1eG1k2v2d1Yw3zO6Z0c4bqH1c9wQw1u3K', 'staff');

-- //(password: password123)
-- // admin@example.com
-- // styaff@example.com
