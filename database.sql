-- =====================================================
-- Mumbai Glam Studio - Database Schema & Sample Data
-- Complete schema as of June 2026
-- =====================================================

-- Drop and recreate database (optional - remove if you want to keep existing data)
DROP DATABASE IF EXISTS mumbai_glam;
CREATE DATABASE IF NOT EXISTS mumbai_glam;
USE mumbai_glam;

-- =====================================================
-- TABLE: salons
-- =====================================================
CREATE TABLE salons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    locality VARCHAR(100) NOT NULL,              -- Changed from ENUM to VARCHAR for any locality
    address VARCHAR(255) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    price_min INT DEFAULT 0,
    price_max INT DEFAULT 0,
    rain_safe TINYINT(1) DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    is_admin TINYINT(1) DEFAULT 0,               -- NEW: Admin flag
    tagline VARCHAR(255) DEFAULT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: customers
-- =====================================================
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABLE: bookings
-- =====================================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    salon_id INT NOT NULL,
    customer_id INT NULL,                         -- NEW: Link to customers table
    service_type ENUM('haircut', 'bridal', 'mens') NOT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(15) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- ---------- SALONS ----------
-- Password for all sample salons: "demo" (hashed with bcrypt)
-- Note: Replace the password hashes with your own if needed

INSERT INTO salons (name, locality, address, rating, price_min, price_max, rain_safe, verified, is_admin, tagline, username, password) VALUES
('The Bombay Curl Co.', 'Andheri West', '14, Lokhandwala Market, Andheri West', 4.8, 500, 3500, 1, 1, 0, 'Monsoon-proof blow-dry specialists since 2018', 'andheri1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Andheri Hair Oasis', 'Andheri East', '7th Floor, Lotus Corporate Park, Andheri East', 4.3, 300, 2000, 0, 0, 0, 'Your neighbourhood hair haven', 'andheri2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Gloss & Glow Studio', 'Andheri West', '22, Veera Desai Road, Andheri West', 4.6, 600, 4000, 1, 1, 0, 'Bridal magic meets everyday glam', 'andheri3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Bandra Bridal Studio', 'Bandra West', '5, Hill Road, Bandra West', 4.9, 1500, 15000, 1, 1, 0, 'Where brides become legends', 'bandra1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Pali Hill Trimmers', 'Bandra West', '31, Pali Hill Road, Bandra West', 4.1, 250, 1500, 0, 0, 0, 'Sharp cuts for sharper people', 'bandra2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dadar Heritage Salon', 'Dadar West', '48, Shivaji Park, Dadar West', 4.4, 200, 1200, 0, 1, 0, 'Classic cuts, timeless style', 'dadar1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Dadar Bridal & Beauty', 'Dadar West', '12, Senapati Bapat Marg, Dadar West', 4.5, 800, 8000, 1, 0, 0, 'Tradition meets modern elegance', 'dadar2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ---------- ADMIN ACCOUNT ----------
-- Username: admin, Password: admin123
-- Generate your own hash using: password_hash('admin123', PASSWORD_BCRYPT)
-- Replace this hash with your own if needed

INSERT INTO salons (name, locality, address, rating, price_min, price_max, rain_safe, verified, is_admin, tagline, username, password) VALUES
('Admin', 'Mumbai', 'Admin Address', 0, 0, 0, 0, 0, 1, 'System Administrator', 'admin', '$2y$10$8Pq3x.q0v2Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0');

-- ---------- CUSTOMERS ----------
-- Password: "demo123" (hashed with bcrypt)

INSERT INTO customers (name, email, phone, password) VALUES
('Demo Customer', 'demo@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Priya Sharma', 'priya@example.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ananya Desai', 'ananya@example.com', '9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ---------- BOOKINGS ----------
-- Some bookings linked to customers, some without

INSERT INTO bookings (salon_id, customer_id, service_type, booking_date, time_slot, customer_name, customer_phone, status) VALUES
(1, 1, 'haircut', CURDATE() + INTERVAL 2 DAY, '10:00 AM', 'Priya Sharma', '9876543210', 'pending'),
(1, 1, 'bridal', CURDATE() + INTERVAL 3 DAY, '09:00 AM', 'Priya Sharma', '9876543210', 'confirmed'),
(4, 2, 'bridal', CURDATE() + INTERVAL 4 DAY, '11:00 AM', 'Ananya Desai', '9823456712', 'pending'),
(6, NULL, 'mens', CURDATE() + INTERVAL 1 DAY, '02:00 PM', 'Rahul Kulkarni', '9912345678', 'confirmed'),
(3, 1, 'haircut', CURDATE() + INTERVAL 5 DAY, '03:00 PM', 'Priya Sharma', '9876543210', 'completed'),
(3, NULL, 'haircut', CURDATE() + INTERVAL 2 DAY, '01:00 PM', 'Sneha Patil', '9765432109', 'pending'),
(1, NULL, 'haircut', CURDATE() + INTERVAL 1 DAY, '11:00 AM', 'Meera Iyer', '9834567891', 'confirmed');

-- =====================================================
-- VIEW: customer_booking_summary (optional)
-- =====================================================
CREATE OR REPLACE VIEW customer_booking_summary AS
SELECT 
    c.id AS customer_id,
    c.name AS customer_name,
    c.email AS customer_email,
    COUNT(b.id) AS total_bookings,
    SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings,
    SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
    SUM(CASE WHEN b.status = 'completed' THEN 1 ELSE 0 END) AS completed_bookings
FROM customers c
LEFT JOIN bookings b ON c.id = b.customer_id
GROUP BY c.id;

-- =====================================================
-- INDEXES for performance
-- =====================================================
CREATE INDEX idx_bookings_salon_id ON bookings(salon_id);
CREATE INDEX idx_bookings_customer_id ON bookings(customer_id);
CREATE INDEX idx_bookings_booking_date ON bookings(booking_date);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_salons_locality ON salons(locality);
CREATE INDEX idx_salons_verified ON salons(verified);
CREATE INDEX idx_salons_is_admin ON salons(is_admin);
CREATE INDEX idx_customers_email ON customers(email);

-- =====================================================
-- END OF SQL
-- =====================================================