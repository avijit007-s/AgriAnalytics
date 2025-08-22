-- Insert admin user (password: admin123)
INSERT INTO users (username, password) VALUES ('admin', MD5('admin123'));

-- Insert sample locations
INSERT INTO locations (district_name, division_name, country) VALUES
('Dhaka', 'Dhaka Division', 'Bangladesh'),
('Chittagong', 'Chittagong Division', 'Bangladesh'),
('Rajshahi', 'Rajshahi Division', 'Bangladesh'),
('Khulna', 'Khulna Division', 'Bangladesh'),
('Sylhet', 'Sylhet Division', 'Bangladesh');

-- Insert sample products
INSERT INTO products (name, type, variety, sowing_time, transplanting_time, harvest_time, seed_per_acre) VALUES
('Rice', 'Grain', 'Boro', '2024-01-15', '2024-02-15', '2024-05-15', 25.50),
('Wheat', 'Grain', 'Sonalika', '2024-11-15', NULL, '2024-03-15', 40.00),
('Potato', 'Vegetable', 'Cardinal', '2024-10-15', NULL, '2024-01-15', 1200.00),
('Jute', 'Fiber', 'Tossa', '2024-03-15', NULL, '2024-07-15', 8.00),
('Sugarcane', 'Cash Crop', 'Isd 37', '2024-02-01', NULL, '2024-12-01', 3500.00);

-- Insert sample production history with season and temperature
INSERT INTO production_history (product_id, location_id, year, season, temperature, acreage, quantity_produced) VALUES
(1, 1, 2023, 'Spring', 25.5, 1500.00, 4500.00),
(1, 2, 2023, 'Spring', 26.2, 1200.00, 3600.00),
(2, 1, 2023, 'Winter', 18.7, 800.00, 2400.00),
(3, 3, 2023, 'Fall', 22.3, 600.00, 12000.00),
(4, 4, 2023, 'Summer', 28.1, 400.00, 800.00),
(5, 5, 2023, 'Winter', 19.4, 300.00, 15000.00);

-- Insert sample price history
INSERT INTO price_history (product_id, location_id, date, wholesale_price, retail_price) VALUES
(1, 1, '2024-01-01', 45.00, 50.00),
(1, 1, '2024-02-01', 47.00, 52.00),
(1, 1, '2024-03-01', 44.00, 49.00),
(2, 1, '2024-01-01', 35.00, 40.00),
(2, 1, '2024-02-01', 36.00, 41.00),
(3, 3, '2024-01-01', 25.00, 30.00);

-- Insert sample weather data
INSERT INTO weather_history (location_id, date, rainfall_mm, temperature_celsius) VALUES
(1, '2024-01-01', 5.2, 18.5),
(1, '2024-01-02', 0.0, 20.1),
(1, '2024-01-03', 12.5, 17.8),
(2, '2024-01-01', 8.1, 22.3),
(2, '2024-01-02', 3.4, 24.1),
(3, '2024-01-01', 2.1, 16.9);

-- Insert sample consumption data
INSERT INTO consumption_data (product_id, location_id, year, month, per_capita_income, per_capita_nutrition_intake, consumer_purchase_records) VALUES
(1, 1, 2023, 1, 25000.00, 2.5, 150.00),
(1, 1, 2023, 2, 25500.00, 2.6, 155.00),
(1, 1, 2023, 3, 26000.00, 2.4, 148.00),
(2, 1, 2023, 1, 25000.00, 1.8, 120.00),
(3, 3, 2023, 1, 22000.00, 3.2, 200.00);