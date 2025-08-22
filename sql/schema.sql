CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100),
    variety VARCHAR(100),
    sowing_time DATE,
    transplanting_time DATE,
    harvest_time DATE,
    seed_per_acre DECIMAL(10, 2)
);

CREATE TABLE locations (
    location_id INT AUTO_INCREMENT PRIMARY KEY,
    district_name VARCHAR(255) NOT NULL,
    division_name VARCHAR(255),
    country VARCHAR(255)
);

CREATE TABLE production_history (
    production_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    location_id INT NOT NULL,
    year INT NOT NULL,
    season VARCHAR(20),
    temperature DECIMAL(5, 1),
    acreage DECIMAL(10, 2),
    quantity_produced DECIMAL(10, 2),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE price_history (
    price_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    location_id INT NOT NULL,
    date DATE NOT NULL,
    wholesale_price DECIMAL(10, 2),
    retail_price DECIMAL(10, 2),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE weather_history (
    weather_id INT AUTO_INCREMENT PRIMARY KEY,
    location_id INT NOT NULL,
    date DATE NOT NULL,
    rainfall_mm DECIMAL(10, 2),
    temperature_celsius DECIMAL(10, 2),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE consumption_data (
    consumption_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    location_id INT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    per_capita_income DECIMAL(15, 2),
    per_capita_nutrition_intake DECIMAL(10, 2),
    consumer_purchase_records DECIMAL(10, 2),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (location_id) REFERENCES locations(location_id)
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);