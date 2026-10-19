-- ============================================================
-- SMART PARKING SYSTEM - COMPLETE DATABASE SCHEMA
-- Database: smart_parking
-- MySQL Version: 5.7+ / 8.0+
-- ============================================================

DROP DATABASE IF EXISTS smart_parking;
CREATE DATABASE smart_parking 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;
USE smart_parking;

-- ============================================================
-- 1. USERS
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    google_id VARCHAR(100) DEFAULT NULL,
    profile_pic VARCHAR(255) DEFAULT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    is_verified TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    wallet_balance DECIMAL(10,2) DEFAULT 0.00,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_google (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 2. ADMINS (Extended admin data)
-- ============================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    permissions JSON DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 3. VEHICLES
-- ============================================================
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_number VARCHAR(30) NOT NULL,
    vehicle_type ENUM('2-wheeler','4-wheeler','EV') NOT NULL DEFAULT '4-wheeler',
    model VARCHAR(80) DEFAULT NULL,
    color VARCHAR(30) DEFAULT NULL,
    is_default TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_vehicle_number (vehicle_number),
    UNIQUE KEY uniq_user_vehicle (user_id, vehicle_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 4. PARKING LOCATIONS
-- ============================================================
CREATE TABLE parking_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT,
    area VARCHAR(100) DEFAULT NULL,
    city VARCHAR(100) DEFAULT 'Bangalore',
    landmark VARCHAR(150) DEFAULT NULL,
    pincode VARCHAR(10) DEFAULT NULL,
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    total_slots INT DEFAULT 0,
    price_per_hour DECIMAL(8,2) NOT NULL DEFAULT 30.00,
    opening_time TIME DEFAULT '00:00:00',
    closing_time TIME DEFAULT '23:59:59',
    is_24_7 TINYINT(1) DEFAULT 0,
    has_covered TINYINT(1) DEFAULT 0,
    has_ev_charging TINYINT(1) DEFAULT 0,
    has_accessible TINYINT(1) DEFAULT 0,
    has_cctv TINYINT(1) DEFAULT 1,
    has_security TINYINT(1) DEFAULT 1,
    facilities JSON DEFAULT NULL,
    images JSON DEFAULT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_location (latitude, longitude),
    INDEX idx_area (area),
    INDEX idx_active (is_active),
    INDEX idx_price (price_per_hour),
    FULLTEXT idx_search (name, address, area, landmark)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 5. PARKING SLOTS
-- ============================================================
CREATE TABLE parking_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parking_id INT NOT NULL,
    slot_number VARCHAR(20) NOT NULL,
    slot_type ENUM('2-wheeler','4-wheeler','EV','accessible') DEFAULT '4-wheeler',
    status ENUM('available','occupied','reserved','maintenance') DEFAULT 'available',
    floor VARCHAR(10) DEFAULT 'G',
    zone VARCHAR(20) DEFAULT NULL,
    sensor_id VARCHAR(50) DEFAULT NULL,
    hourly_rate DECIMAL(8,2) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_parking_slot (parking_id, slot_number),
    INDEX idx_parking_status (parking_id, status),
    INDEX idx_slot_type (slot_type),
    INDEX idx_sensor (sensor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 6. RESERVATIONS
-- ============================================================
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parking_id INT NOT NULL,
    slot_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    booking_code VARCHAR(30) UNIQUE NOT NULL,
    qr_code VARCHAR(500) DEFAULT NULL,
    entry_time DATETIME NOT NULL,
    expected_exit_time DATETIME NOT NULL,
    actual_entry_time DATETIME DEFAULT NULL,
    actual_exit_time DATETIME DEFAULT NULL,
    duration_hours DECIMAL(5,2) DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT 0.00,
    tax DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending','confirmed','active','completed','cancelled','expired','no-show') 
           DEFAULT 'pending',
    cancellation_reason VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id),
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_booking_code (booking_code),
    INDEX idx_entry_time (entry_time),
    INDEX idx_status (status),
    INDEX idx_parking (parking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 7. PARKING SESSIONS (Actual entry/exit records)
-- ============================================================
CREATE TABLE parking_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    parking_id INT NOT NULL,
    slot_id INT NOT NULL,
    vehicle_id INT DEFAULT NULL,
    vehicle_number VARCHAR(30) DEFAULT NULL,
    entry_time DATETIME NOT NULL,
    exit_time DATETIME DEFAULT NULL,
    duration_minutes INT DEFAULT NULL,
    amount DECIMAL(10,2) DEFAULT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) DEFAULT NULL,
    payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
    status ENUM('active','completed','abandoned') DEFAULT 'active',
    entry_method ENUM('qr','manual','sensor','lpr') DEFAULT 'qr',
    exit_method ENUM('qr','manual','sensor','lpr') DEFAULT 'qr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id),
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_entry (entry_time),
    INDEX idx_parking (parking_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 8. PAYMENTS
-- ============================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reservation_id INT DEFAULT NULL,
    session_id INT DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    method ENUM('upi','card','wallet','cash','netbanking','razorpay') NOT NULL,
    transaction_id VARCHAR(100) DEFAULT NULL,
    gateway_response JSON DEFAULT NULL,
    status ENUM('pending','success','failed','refunded','cancelled') DEFAULT 'pending',
    receipt_number VARCHAR(50) DEFAULT NULL,
    receipt_path VARCHAR(255) DEFAULT NULL,
    refund_amount DECIMAL(10,2) DEFAULT 0.00,
    refunded_at DATETIME DEFAULT NULL,
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    FOREIGN KEY (session_id) REFERENCES parking_sessions(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 9. NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('reservation','payment','reminder','promo','system',
              'cancellation','availability','expiry') DEFAULT 'system',
    priority ENUM('low','normal','high') DEFAULT 'normal',
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    link VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_type (type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 10. REVIEWS
-- ============================================================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parking_id INT NOT NULL,
    reservation_id INT DEFAULT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    cleanliness_rating TINYINT DEFAULT NULL CHECK (cleanliness_rating BETWEEN 1 AND 5),
    security_rating TINYINT DEFAULT NULL CHECK (security_rating BETWEEN 1 AND 5),
    accessibility_rating TINYINT DEFAULT NULL CHECK (accessibility_rating BETWEEN 1 AND 5),
    comment TEXT DEFAULT NULL,
    is_reported TINYINT(1) DEFAULT 0,
    report_reason VARCHAR(255) DEFAULT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    moderated_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL,
    INDEX idx_parking (parking_id),
    INDEX idx_user (user_id),
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 11. EV CHARGING STATIONS
-- ============================================================
CREATE TABLE ev_charging_stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parking_id INT NOT NULL,
    slot_id INT DEFAULT NULL,
    station_code VARCHAR(30) UNIQUE NOT NULL,
    charger_type ENUM('AC','DC-fast','Type2','CCS','CHAdeMO') DEFAULT 'AC',
    power_kw DECIMAL(5,2) DEFAULT 7.20,
    price_per_kwh DECIMAL(6,2) DEFAULT 15.00,
    connector_count INT DEFAULT 1,
    status ENUM('available','in-use','maintenance','offline') DEFAULT 'available',
    last_service TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id) ON DELETE SET NULL,
    INDEX idx_parking (parking_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 12. PARKING FACILITIES
-- ============================================================
CREATE TABLE parking_facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parking_id INT NOT NULL,
    facility_name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    is_available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    INDEX idx_parking (parking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 13. ENVIRONMENTAL STATISTICS
-- ============================================================
CREATE TABLE environmental_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE UNIQUE NOT NULL,
    fuel_saved_liters DECIMAL(10,2) DEFAULT 0.00,
    co2_avoided_kg DECIMAL(10,2) DEFAULT 0.00,
    search_time_saved_hours DECIMAL(10,2) DEFAULT 0.00,
    vehicles_assisted INT DEFAULT 0,
    distance_saved_km DECIMAL(10,2) DEFAULT 0.00,
    idling_reduced_minutes INT DEFAULT 0,
    trees_equivalent DECIMAL(8,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (stat_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 14. AUDIT LOGS
-- ============================================================
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_type ENUM('user','admin','system','api') DEFAULT 'user',
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50) DEFAULT NULL,
    record_id INT DEFAULT NULL,
    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    status ENUM('success','failure') DEFAULT 'success',
    error_message VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 15. SESSIONS (Secure session storage)
-- ============================================================
CREATE TABLE user_sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    payload TEXT,
    last_activity INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 16. PASSWORD RESETS
-- ============================================================
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 17. OCCUPANCY PREDICTIONS (AI/ML data)
-- ============================================================
CREATE TABLE occupancy_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parking_id INT NOT NULL,
    prediction_date DATE NOT NULL,
    hour_of_day TINYINT NOT NULL,
    predicted_occupancy DECIMAL(5,2) DEFAULT 0.00,
    confidence_score DECIMAL(5,2) DEFAULT 0.00,
    actual_occupancy DECIMAL(5,2) DEFAULT NULL,
    model_version VARCHAR(20) DEFAULT 'v1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_prediction (parking_id, prediction_date, hour_of_day),
    INDEX idx_date (prediction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 18. IOT SENSOR LOGS (for future hardware integration)
-- ============================================================
CREATE TABLE iot_sensor_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    sensor_id VARCHAR(50) NOT NULL,
    parking_id INT NOT NULL,
    slot_id INT DEFAULT NULL,
    sensor_type ENUM('occupancy','lpr','temperature','gate') DEFAULT 'occupancy',
    reading VARCHAR(100) NOT NULL,
    metadata JSON DEFAULT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id) ON DELETE SET NULL,
    INDEX idx_sensor (sensor_id),
    INDEX idx_recorded (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 19. PROMO CODES
-- ============================================================
CREATE TABLE promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    discount_type ENUM('fixed','percent') DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL,
    min_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT 100,
    used_count INT DEFAULT 0,
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- 20. FAVORITE PARKINGS (User preferences)
-- ============================================================
CREATE TABLE favorite_parkings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parking_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parking_id) REFERENCES parking_locations(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_parking (user_id, parking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SCHEMA COMPLETE
-- ============================================================
SELECT 'Schema created successfully!' AS message;