-- =====================================================
-- Mumbai Glam Studio - Database Schema & Sample Data
-- Complete schema as of June 2026
-- =====================================================

DROP DATABASE IF EXISTS mumbai_glam;
CREATE DATABASE IF NOT EXISTS mumbai_glam;
USE mumbai_glam;

-- =====================================================
-- TABLE: salons
-- =====================================================
CREATE TABLE salons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    locality VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 0.0,
    review_count INT DEFAULT 0,
    avg_rating DECIMAL(2,1) DEFAULT 0.0,
    price_min INT DEFAULT 0,
    price_max INT DEFAULT 0,
    rain_safe TINYINT(1) DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    is_admin TINYINT(1) DEFAULT 0,
    tagline VARCHAR(255) DEFAULT NULL,
    description TEXT NULL,
    services TEXT NULL,
    working_hours VARCHAR(255) NULL,
    contact_phone VARCHAR(20) NULL,
    contact_email VARCHAR(100) NULL,
    website VARCHAR(255) NULL,
    established_year YEAR NULL,
    facilities TEXT NULL,
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
    customer_id INT NULL,
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
-- TABLE: customer_reviews
-- =====================================================
CREATE TABLE customer_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    salon_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved', 'hidden') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (salon_id, customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SAMPLE DATA
-- =====================================================

INSERT INTO salons (name, locality, address, rating, avg_rating, review_count, price_min, price_max, rain_safe, verified, is_admin, tagline, description, services, working_hours, contact_phone, contact_email, website, established_year, facilities, username, password) VALUES
('The Bombay Curl Co.', 'Andheri West', '14, Lokhandwala Market, Andheri West', 4.8, 4.8, 45, 500, 3500, 1, 1, 0, 'Monsoon-proof blow-dry specialists since 2018', 'The Bombay Curl Co. is Andheri\'s premier destination for premium haircare and styling. With over 5 years of experience, our team of expert stylists specializes in modern cuts, vibrant colors, and luxurious treatments.', 'Haircut & Styling, Hair Color & Highlights, Keratin Treatment, Bridal Makeup, Blow-dry & Styling, Hair Spa & Scalp Treatments', 'Mon–Sat: 9:00 AM – 9:00 PM, Sunday: 10:00 AM – 6:00 PM', '+91 98765 43210', 'info@bombaycurl.com', 'www.bombaycurl.com', 2018, 'Parking Available, Free Wi-Fi, Card Payments Accepted, Air Conditioning, Wheelchair Accessible', 'andheri1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

('Andheri Hair Oasis', 'Andheri East', '7th Floor, Lotus Corporate Park, Andheri East', 4.3, 4.3, 12, 300, 2000, 0, 0, 0, 'Your neighbourhood hair haven', 'Andheri Hair Oasis offers premium haircare services in the heart of Andheri East. Our experienced team provides personalized styling solutions.', 'Haircut, Hair Color, Beard Styling, Shampoo & Conditioning', 'Mon–Sat: 10:00 AM – 8:00 PM, Sunday: Closed', '+91 98765 43211', 'info@andherihair.com', 'www.andherihair.com', 2020, 'Free Wi-Fi, Air Conditioning', 'andheri2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

('Gloss & Glow Studio', 'Andheri West', '22, Veera Desai Road, Andheri West', 4.6, 4.6, 32, 600, 4000, 1, 1, 0, 'Bridal magic meets everyday glam', 'Gloss & Glow Studio is a premium beauty destination in Andheri West, offering a perfect blend of bridal elegance and everyday glamour. Our team of award-winning artists ensures every client leaves feeling confident and beautiful.', 'Bridal Makeup, Party Makeup, Hair Styling, Nail Art, Threading & Waxing, Facials & Skincare', 'Mon–Sat: 10:00 AM – 8:00 PM, Sunday: Closed', '+91 98765 43211', 'info@glossandglow.com', 'www.glossandglow.com', 2020, 'Parking Available, Free Wi-Fi, Card Payments Accepted, Air Conditioning', 'andheri3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

('Bandra Bridal Studio', 'Bandra West', '5, Hill Road, Bandra West', 4.9, 4.9, 78, 1500, 15000, 1, 1, 0, 'Where brides become legends', 'Bandra Bridal Studio is a luxury bridal destination where every bride becomes a legend. With over a decade of experience, our expert team creates stunning bridal looks that blend tradition with modern elegance.', 'Bridal Makeup, Bridal Hair, Pre-Wedding Services, Party Makeup, Mehendi, Saree Draping', 'Mon–Sat: 9:00 AM – 10:00 PM, Sunday: 10:00 AM – 8:00 PM', '+91 98765 43212', 'info@bandrabridal.com', 'www.bandrabridal.com', 2015, 'Valet Parking, Free Wi-Fi, Card Payments Accepted, Air Conditioning, Bridal Suite', 'bandra1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

('Pali Hill Trimmers', 'Bandra West', '31, Pali Hill Road, Bandra West', 4.1, 4.1, 8, 250, 1500, 0, 0, 0, 'Sharp cuts for sharper people', 'Pali Hill Trimmers is a classic barbershop offering precision cuts and grooming services for the modern gentleman.', 'Men\'s Haircut, Beard Trim, Hot Towel Shave', 'Mon–Sat: 9:00 AM – 7:00 PM, Sunday: Closed', '+91 98765 43213', 'info@palihill.com', 'www.palihill.com', 2019, 'Free Wi-Fi', 'bandra2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

('Dadar Heritage Salon', 'Dadar West', '48, Shivaji Park, Dadar West', 4.4, 4.4, 15, 200, 1200, 0, 1, 0, 'Classic cuts, timeless style', 'Dadar Heritage Salon offers classic cuts and timeless styling in the heart of Dadar. Our experienced team brings decades of expertise.', 'Haircut, Hair Color, Bridal Services, Traditional Grooming', 'Mon–Sat: 9:00 AM – 8:00 PM, Sunday: 10:00 AM – 6:00 PM', '+91 98765 43214', 'info@dadarheritage.com', 'www.dadarheritage.com', 2017, 'Parking Available, Air Conditioning', 'dadar1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),

('Dadar Bridal & Beauty', 'Dadar West', '12, Senapati Bapat Marg, Dadar West', 4.5, 4.5, 20, 800, 8000, 1, 0, 0, 'Tradition meets modern elegance', 'Dadar Bridal & Beauty offers traditional bridal services with a modern touch. Our expert team ensures every bride feels beautiful on her special day.', 'Bridal Makeup, Bridal Hair, Beauty Services, Mehendi', 'Mon–Sat: 10:00 AM – 9:00 PM, Sunday: 10:00 AM – 6:00 PM', '+91 98765 43215', 'info@dadarbridal.com', 'www.dadarbridal.com', 2018, 'Parking Available, Free Wi-Fi, Card Payments Accepted', 'dadar2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ADMIN ACCOUNT
INSERT INTO salons (name, locality, address, rating, price_min, price_max, rain_safe, verified, is_admin, tagline, username, password) VALUES
('Admin', 'Mumbai', 'Admin Address', 0, 0, 0, 0, 0, 1, 'System Administrator', 'admin', '$2y$10$8Pq3x.q0v2Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0Qv0Bq0');

-- CUSTOMERS
INSERT INTO customers (name, email, phone, password) VALUES
('Demo Customer', 'demo@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Priya Sharma', 'priya@example.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- SAMPLE REVIEWS
INSERT INTO customer_reviews (salon_id, customer_id, rating, comment, status) VALUES
(1, 1, 5, 'Absolutely loved my experience! The stylist understood exactly what I wanted and delivered perfection. Highly recommend!', 'approved'),
(1, 2, 4, 'Great service and friendly staff. My haircut turned out exactly as I wanted. Will definitely come back.', 'approved'),
(4, 1, 5, 'The best bridal makeup I could have asked for. Made me feel like a princess on my big day. Thank you!', 'approved');

-- =====================================================
-- INDEXES
-- =====================================================
CREATE INDEX idx_bookings_salon_id ON bookings(salon_id);
CREATE INDEX idx_bookings_customer_id ON bookings(customer_id);
CREATE INDEX idx_bookings_booking_date ON bookings(booking_date);
CREATE INDEX idx_salons_locality ON salons(locality);
CREATE INDEX idx_salons_verified ON salons(verified);
CREATE INDEX idx_customers_email ON customers(email);
CREATE INDEX idx_reviews_salon_id ON customer_reviews(salon_id);
CREATE INDEX idx_reviews_customer_id ON customer_reviews(customer_id);