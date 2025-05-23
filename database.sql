-- Create database
CREATE DATABASE IF NOT EXISTS vulcanizing_kiosk;
USE vulcanizing_kiosk;

-- Create admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    vehicle_type ENUM('Car', 'Motorcycle', 'Truck') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    category ENUM('tire', 'wheel', 'other') DEFAULT 'other',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
);

-- Create queue_status table
CREATE TABLE IF NOT EXISTS queue_status (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    queue_number VARCHAR(10) NOT NULL,
    status ENUM('pending', 'processing', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

-- Insert default admin account
INSERT INTO admin (username, password) VALUES ('admin', '1234567890')
    ON DUPLICATE KEY UPDATE password = '1234567890';

-- Insert sample services
INSERT INTO services (name, description, price, duration, category, status) VALUES
('Tire Repair', 'Fix punctured tires with patch or plug', 150.00, 30, 'tire', 'active'),
('Tire Replacement', 'Replace old tires with new ones', 2500.00, 45, 'tire', 'active'),
('Wheel Alignment', 'Adjust wheel angles to manufacturer specifications', 500.00, 60, 'wheel', 'active'),
('Tire Balancing', 'Balance tires to prevent vibration', 300.00, 30, 'wheel', 'active'),
('Tire Rotation', 'Rotate tires to ensure even wear', 200.00, 30, 'tire', 'active'),
('Tire Pressure Check', 'Check and adjust tire pressure', 50.00, 15, 'tire', 'active'),
('Valve Stem Replacement', 'Replace damaged valve stems', 100.00, 20, 'tire', 'active'),
('Tire Mounting', 'Mount new tires on wheels', 150.00, 30, 'tire', 'active'),
('Tire Dismounting', 'Remove old tires from wheels', 100.00, 20, 'tire', 'active'),
('Tire Cleaning', 'Clean and shine tires', 100.00, 20, 'tire', 'active');

-- Insert sample customers
INSERT INTO customers (name, phone, vehicle_type) VALUES
('John Doe', '09123456789', 'Car'),
('Jane Smith', '09234567890', 'Motorcycle'),
('Mike Johnson', '09345678901', 'Truck'),
('Sarah Williams', '09456789012', 'Car'),
('David Brown', '09567890123', 'Motorcycle');

-- Insert sample orders
INSERT INTO orders (customer_id, total_amount, status) VALUES
(1, 150.00, 'completed'),
(2, 300.00, 'completed'),
(3, 500.00, 'processing'),
(4, 2500.00, 'pending'),
(5, 200.00, 'pending');

-- Insert sample order items
INSERT INTO order_items (order_id, service_id, quantity, price) VALUES
(1, 1, 1, 150.00),
(2, 5, 1, 200.00),
(2, 6, 2, 50.00),
(3, 3, 1, 500.00),
(4, 2, 1, 2500.00),
(5, 5, 1, 200.00);

-- Insert sample queue status
INSERT INTO queue_status (order_id, queue_number, status) VALUES
(1, 'Q001', 'completed'),
(2, 'Q002', 'completed'),
(3, 'Q003', 'processing'),
(4, 'Q004', 'pending'),
(5, 'Q005', 'pending');

-- Ensure default client account exists
INSERT INTO customers (name, phone, vehicle_type) VALUES ('1234567890', 'client', 'Car')
    ON DUPLICATE KEY UPDATE name = '1234567890', vehicle_type = 'Car'; 