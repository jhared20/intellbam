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
        full_name VARCHAR(150) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- =========================
    -- Products Table
    -- =========================
    CREATE TABLE products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT DEFAULT NULL,
        product_name VARCHAR(150) NOT NULL,
        barcode VARCHAR(100) UNIQUE,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(category_id)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
    );

    -- =========================
    -- Sales Table
    -- =========================
    CREATE TABLE sales (
        sale_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT DEFAULT NULL,
        customer_name VARCHAR(150) DEFAULT NULL,
        user_id INT DEFAULT NULL,
        sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
            ON UPDATE CASCADE
            ON DELETE SET NULL,
        FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
            ON UPDATE CASCADE
            ON DELETE SET NULL
    );

    -- =========================
    -- Sale Items Table
    -- =========================
    CREATE TABLE sale_items (
        sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(150) DEFAULT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (sale_id) REFERENCES sales(sale_id)
            ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(product_id)
            ON DELETE RESTRICT
    );

    CREATE TABLE payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        sale_id INT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        amount_paid DECIMAL(10,2) NOT NULL,
        change_amount DECIMAL(10,2) NOT NULL,
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sale_id) REFERENCES sales(sale_id)
                ON DELETE CASCADE
    );
