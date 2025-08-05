<?php
require_once 'includes/db_connection.php';

// SQLite schema (converted from MySQL)
$schema_sql = "
CREATE TABLE IF NOT EXISTS products (
    product_id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    type TEXT,
    variety TEXT,
    sowing_time DATE,
    transplanting_time DATE,
    harvest_time DATE,
    seed_per_acre REAL
);

CREATE TABLE IF NOT EXISTS locations (
    location_id INTEGER PRIMARY KEY AUTOINCREMENT,
    district_name TEXT NOT NULL,
    division_name TEXT,
    country TEXT
);

CREATE TABLE IF NOT EXISTS production_history (
    production_id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    location_id INTEGER NOT NULL,
    year INTEGER NOT NULL,
    acreage REAL,
    quantity_produced REAL,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE IF NOT EXISTS price_history (
    price_id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    location_id INTEGER NOT NULL,
    date DATE NOT NULL,
    wholesale_price REAL,
    retail_price REAL,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE IF NOT EXISTS weather_history (
    weather_id INTEGER PRIMARY KEY AUTOINCREMENT,
    location_id INTEGER NOT NULL,
    date DATE NOT NULL,
    rainfall_mm REAL,
    temperature_celsius REAL,
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE IF NOT EXISTS consumption_data (
    consumption_id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    location_id INTEGER NOT NULL,
    year INTEGER NOT NULL,
    month INTEGER NOT NULL,
    per_capita_income REAL,
    per_capita_nutrition_intake REAL,
    consumer_purchase_records REAL,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE IF NOT EXISTS users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);
";

// Sample data
$sample_data_sql = "
-- Insert admin user (password: admin123)
INSERT OR IGNORE INTO users (username, password) VALUES ('admin', '0192023a7bbd73250516f069df18b500');

-- Insert sample locations
INSERT OR IGNORE INTO locations (district_name, division_name, country) VALUES
('Dhaka', 'Dhaka Division', 'Bangladesh'),
('Chittagong', 'Chittagong Division', 'Bangladesh'),
('Rajshahi', 'Rajshahi Division', 'Bangladesh'),
('Khulna', 'Khulna Division', 'Bangladesh'),
('Sylhet', 'Sylhet Division', 'Bangladesh');

-- Insert sample products
INSERT OR IGNORE INTO products (name, type, variety, sowing_time, transplanting_time, harvest_time, seed_per_acre) VALUES
('Rice', 'Grain', 'Boro', '2024-01-15', '2024-02-15', '2024-05-15', 25.50),
('Wheat', 'Grain', 'Sonalika', '2024-11-15', NULL, '2024-03-15', 40.00),
('Potato', 'Vegetable', 'Cardinal', '2024-10-15', NULL, '2024-01-15', 1200.00),
('Jute', 'Fiber', 'Tossa', '2024-03-15', NULL, '2024-07-15', 8.00),
('Sugarcane', 'Cash Crop', 'Isd 37', '2024-02-01', NULL, '2024-12-01', 3500.00);

-- Insert sample production history
INSERT OR IGNORE INTO production_history (product_id, location_id, year, acreage, quantity_produced) VALUES
(1, 1, 2023, 1500.00, 4500.00),
(1, 2, 2023, 1200.00, 3600.00),
(2, 1, 2023, 800.00, 2400.00),
(3, 3, 2023, 600.00, 12000.00),
(4, 4, 2023, 400.00, 800.00),
(5, 5, 2023, 300.00, 15000.00),
(1, 1, 2022, 1400.00, 4200.00),
(1, 2, 2022, 1100.00, 3300.00),
(2, 1, 2022, 750.00, 2250.00),
(3, 3, 2022, 550.00, 11000.00),
(4, 4, 2022, 380.00, 760.00),
(5, 5, 2022, 280.00, 14000.00),
(1, 1, 2021, 1300.00, 3900.00),
(1, 2, 2021, 1000.00, 3000.00),
(2, 1, 2021, 700.00, 2100.00),
(3, 3, 2021, 500.00, 10000.00),
(4, 4, 2021, 360.00, 720.00),
(5, 5, 2021, 260.00, 13000.00);

-- Insert sample price history
INSERT OR IGNORE INTO price_history (product_id, location_id, date, wholesale_price, retail_price) VALUES
(1, 1, '2024-01-01', 45.00, 50.00),
(1, 1, '2024-02-01', 47.00, 52.00),
(1, 1, '2024-03-01', 44.00, 49.00),
(2, 1, '2024-01-01', 35.00, 40.00),
(2, 1, '2024-02-01', 36.00, 41.00),
(3, 3, '2024-01-01', 25.00, 30.00),
(4, 4, '2024-01-01', 55.00, 60.00),
(5, 5, '2024-01-01', 40.00, 45.00);

-- Insert sample weather data
INSERT OR IGNORE INTO weather_history (location_id, date, rainfall_mm, temperature_celsius) VALUES
(1, '2024-01-01', 5.2, 18.5),
(1, '2024-01-02', 0.0, 20.1),
(1, '2024-01-03', 12.5, 17.8),
(2, '2024-01-01', 8.1, 22.3),
(2, '2024-01-02', 3.4, 24.1),
(3, '2024-01-01', 2.1, 16.9),
(4, '2024-01-01', 15.2, 25.5),
(5, '2024-01-01', 8.7, 19.3);

-- Insert sample consumption data
INSERT OR IGNORE INTO consumption_data (product_id, location_id, year, month, per_capita_income, per_capita_nutrition_intake, consumer_purchase_records) VALUES
(1, 1, 2023, 1, 25000.00, 2.5, 150.00),
(1, 1, 2023, 2, 25500.00, 2.6, 155.00),
(1, 1, 2023, 3, 26000.00, 2.4, 148.00),
(2, 1, 2023, 1, 25000.00, 1.8, 120.00),
(3, 3, 2023, 1, 22000.00, 3.2, 200.00),
(4, 4, 2023, 1, 24000.00, 1.5, 80.00),
(5, 5, 2023, 1, 23000.00, 2.8, 180.00);
";

try {
    // Execute schema creation
    $schema_statements = explode(';', $schema_sql);
    foreach ($schema_statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->query($statement);
        }
    }
    
    // Execute sample data insertion
    $data_statements = explode(';', $sample_data_sql);
    foreach ($data_statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $conn->query($statement);
        }
    }
    
    echo "Database setup completed successfully!\n";
    echo "Tables created and sample data inserted.\n";
    
} catch (Exception $e) {
    echo "Error setting up database: " . $e->getMessage() . "\n";
}
?>

