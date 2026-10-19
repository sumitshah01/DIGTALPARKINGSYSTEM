-- Migration 002: Parking locations, slots, facilities, EV stations
USE smart_parking;

CREATE TABLE IF NOT EXISTS parking_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address TEXT,
    area VARCHAR(100),
    latitude DECIMAL(10,8) NOT NULL,
    longitude DECIMAL(11,8) NOT NULL,
    total_slots INT DEFAULT 0,
    price_per_hour DECIMAL(8,2) NOT NULL,
    -- ... (rest as in schema.sql)
    INDEX idx_location (latitude, longitude)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- See full schema.sql for complete definitions