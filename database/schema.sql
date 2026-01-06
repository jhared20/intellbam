-- Inventory and POS System Database Schema
-- 8 Tables Only

-- Create database
CREATE DATABASE IF NOT EXISTS inventory_pos;
USE inventory_pos;

-- 1. Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    barcode VARCHAR(50) UNIQUE,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Customers table
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(200) NOT NULL,
    contact VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Sales table
CREATE TABLE IF NOT EXISTS sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    user_id INT NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    customer_name VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Sale Items table
CREATE TABLE IF NOT EXISTS sale_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    product_name VARCHAR(200) DEFAULT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Payments table
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    payment_method ENUM('cash', 'gcash', 'paymaya', 'card', 'other') NOT NULL DEFAULT 'cash',
    amount_paid DECIMAL(10, 2) NOT NULL,
    change_amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Activity Logs table
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
-- Note: Run setup.php to generate proper password hashes
-- Or manually hash passwords using: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default cashier user (password: cashier123)
-- Note: Run setup.php to generate proper password hashes
INSERT INTO users (username, password, role) VALUES 
('cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier');

-- Insert sample category
INSERT INTO categories (category_name) VALUES ('General');

-- Add columns (MySQL 8+ supports IF NOT EXISTS)
ALTER TABLE sales ADD COLUMN IF NOT EXISTS customer_name VARCHAR(200) DEFAULT NULL;
ALTER TABLE sale_items ADD COLUMN IF NOT EXISTS product_name VARCHAR(200) DEFAULT NULL;

-- Backfill sales.customer_name from customers.full_name
UPDATE sales s
LEFT JOIN customers c ON s.customer_id = c.customer_id
SET s.customer_name = c.full_name
WHERE s.customer_id IS NOT NULL
  AND (s.customer_name IS NULL OR s.customer_name = '');

-- Mark walk-in sales explicitly (optional)
UPDATE sales
SET customer_name = 'Walk-in'
WHERE customer_id IS NULL
  AND (customer_name IS NULL OR customer_name = '');

-- Backfill sale_items.product_name from products.product_name
UPDATE sale_items si
LEFT JOIN products p ON si.product_id = p.product_id
SET si.product_name = p.product_name
WHERE si.product_id IS NOT NULL
  AND (si.product_name IS NULL OR si.product_name = '');

-- Quick checks
SELECT sale_id, customer_id, customer_name, total_amount FROM sales ORDER BY sale_id DESC LIMIT 20;
SELECT sale_item_id, sale_id, product_id, product_name, quantity FROM sale_items ORDER BY sale_item_id DESC LIMIT 20;

