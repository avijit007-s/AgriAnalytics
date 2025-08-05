-- MySQL Schema for Agricultural Analysis System
-- Create database
CREATE DATABASE IF NOT EXISTS agricultural_analysis;
USE agricultural_analysis;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50),
    variety VARCHAR(100),
    sowing_time DATE,
    transplanting_time DATE,
    harvest_time DATE,
    seed_per_acre DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Production table
CREATE TABLE IF NOT EXISTS production (
    production_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    location VARCHAR(100),
    year INT,
    acreage DECIMAL(10,2),
    quantity_produced DECIMAL(15,2),
    yield_per_acre DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Prices table
CREATE TABLE IF NOT EXISTS prices (
    price_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    location VARCHAR(100),
    date DATE,
    wholesale_price DECIMAL(10,2),
    retail_price DECIMAL(10,2),
    market_price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Weather table
CREATE TABLE IF NOT EXISTS weather (
    weather_id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(100),
    date DATE,
    rainfall DECIMAL(5,2),
    temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    conditions VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Supply Demand table
CREATE TABLE IF NOT EXISTS supply_demand (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product VARCHAR(100),
    location VARCHAR(100),
    year INT,
    supply_quantity DECIMAL(15,2),
    demand_quantity DECIMAL(15,2),
    wholesale_price DECIMAL(10,2),
    retail_price DECIMAL(10,2),
    margin DECIMAL(10,2),
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT IGNORE INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin');

-- Insert sample products
INSERT IGNORE INTO products (product_id, name, type, variety, sowing_time, transplanting_time, harvest_time, seed_per_acre) VALUES
(1, 'Rice', 'Grain', 'Boro', '2023-01-15', '2023-02-15', '2023-06-15', 25.00),
(2, 'Wheat', 'Grain', 'Sonalika', '2023-11-15', NULL, '2024-04-15', 40.00),
(3, 'Potato', 'Vegetable', 'Cardinal', '2023-10-01', NULL, '2024-01-15', 1500.00),
(4, 'Jute', 'Fiber', 'Tossa', '2023-04-01', NULL, '2023-08-15', 8.00),
(5, 'Sugarcane', 'Cash Crop', 'Isd 37', '2023-02-01', NULL, '2024-01-01', 3000.00);

-- Insert sample production data
INSERT IGNORE INTO production (product_id, location, year, acreage, quantity_produced, yield_per_acre) VALUES
(1, 'Dhaka, Dhaka Division', 2023, 1500.00, 4500.00, 3.00),
(2, 'Chittagong, Chittagong Division', 2023, 1200.00, 3600.00, 3.00),
(3, 'Dhaka, Dhaka Division', 2023, 800.00, 2400.00, 3.00),
(4, 'Rajshahi, Rajshahi Division', 2023, 600.00, 12000.00, 20.00),
(5, 'Khulna, Khulna Division', 2023, 400.00, 800.00, 2.00),
(6, 'Sylhet, Sylhet Division', 2023, 300.00, 15000.00, 50.00);

-- Insert sample price data
INSERT IGNORE INTO prices (product_id, location, date, wholesale_price, retail_price, market_price) VALUES
(1, 'Dhaka, Dhaka Division', '2023-06-15', 45.00, 50.00, 47.50),
(2, 'Chittagong, Chittagong Division', '2023-04-15', 35.00, 40.00, 37.50),
(3, 'Rajshahi, Rajshahi Division', '2023-01-15', 25.00, 30.00, 27.50),
(4, 'Khulna, Khulna Division', '2023-08-15', 80.00, 90.00, 85.00),
(5, 'Sylhet, Sylhet Division', '2023-12-01', 15.00, 18.00, 16.50);

-- Insert sample weather data
INSERT IGNORE INTO weather (location, date, rainfall, temperature, humidity, conditions) VALUES
('Dhaka, Dhaka Division', '2023-01-01', 5.2, 18.5, 65.0, 'Moderate'),
('Chittagong, Chittagong Division', '2023-01-01', 8.1, 22.3, 70.0, 'Normal'),
('Rajshahi, Rajshahi Division', '2023-01-01', 2.1, 16.9, 55.0, 'Dry'),
('Khulna, Khulna Division', '2023-01-01', 15.2, 25.5, 75.0, 'Normal'),
('Sylhet, Sylhet Division', '2023-01-01', 8.7, 19.3, 68.0, 'Normal');

-- Insert sample supply demand data
INSERT IGNORE INTO supply_demand (product, location, year, supply_quantity, demand_quantity, wholesale_price, retail_price, margin, status) VALUES
('Rice', 'Dhaka, Dhaka Division', 2023, 4500.00, 5000.00, 45.00, 50.00, 5.00, 'Deficit'),
('Wheat', 'Chittagong, Chittagong Division', 2023, 3600.00, 3200.00, 35.00, 40.00, 5.00, 'Surplus'),
('Potato', 'Rajshahi, Rajshahi Division', 2023, 12000.00, 10000.00, 25.00, 30.00, 5.00, 'Surplus'),
('Jute', 'Khulna, Khulna Division', 2023, 800.00, 900.00, 80.00, 90.00, 10.00, 'Deficit'),
('Sugarcane', 'Sylhet, Sylhet Division', 2023, 15000.00, 14000.00, 15.00, 18.00, 3.00, 'Surplus');

