    -- =========================
    -- Create Database
    -- =========================
    CREATE DATABASE IF NOT EXISTS inventory_pos;
    USE inventory_pos;

    -- =========================
    -- Users Table
    -- =========================
    CREATE TABLE users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin','cashier') NOT NULL DEFAULT 'cashier'
        ,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- =========================
    -- Activity Logs Table
    -- =========================
    CREATE TABLE activity_logs (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        action VARCHAR(255) NOT NULL,
        log_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (username) REFERENCES users(username)
            ON UPDATE CASCADE
            ON DELETE CASCADE
    );

    -- =========================
    -- Categories Table
    -- =========================
    CREATE TABLE categories (
        category_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- =========================
    -- Customers Table
    -- =========================
    CREATE TABLE customers (
        customer_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- =========================
    -- Products Table
    -- =========================
    CREATE TABLE products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) DEFAULT NULL,
        product_name VARCHAR(150) NOT NULL,
        barcode VARCHAR(100) UNIQUE,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- =========================
    -- Sales Table
    -- =========================
    CREATE TABLE sales (
        sales_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(150) DEFAULT NULL,
        username VARCHAR(50) DEFAULT NULL,
        sales_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL
    );

    -- =========================
    -- Sale Items Table
    -- =========================
    CREATE TABLE sale_items (
        sales_items_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(150) DEFAULT NULL,
        product_name VARCHAR(150) DEFAULT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL
    );

    CREATE TABLE payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        sales_id INT NOT NULL,
        customer_name VARCHAR(150) DEFAULT NULL,
        payment_method VARCHAR(50) NOT NULL,
        amount_paid DECIMAL(10,2) NOT NULL,
        change_amount DECIMAL(10,2) NOT NULL,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sales_id) REFERENCES sales(sales_id) ON DELETE CASCADE
    );
