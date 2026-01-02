-- SQL to create example tables for the project

CREATE DATABASE IF NOT EXISTS eproject CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eproject;

-- Users table (role defaults to 'user')
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  address VARCHAR(255),
  contactNo VARCHAR(50),
  role VARCHAR(50) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  image VARCHAR(255) DEFAULT 'photos/default.png',
  stock INT NOT NULL DEFAULT 0,
  category_id INT DEFAULT NULL,
  approved TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example product insert
INSERT INTO products (name, description, price, image, stock) VALUES
('Sample Product 1', 'This is a sample product.', 19.99, 'photos/product1.jpg', 10),
('Sample Product 2', 'Another sample.', 29.99, 'photos/product2.jpg', 5);

-- Categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO categories (name) VALUES ('Electronics'), ('Fashion'), ('Home'), ('Toys');
