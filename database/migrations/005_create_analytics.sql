-- Migration 005: Environmental statistics, predictions, audit logs
USE smart_parking;

CREATE TABLE IF NOT EXISTS environmental_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_date DATE UNIQUE NOT NULL,
    fuel_saved_liters DECIMAL(10,2) DEFAULT 0,
    co2_avoided_kg DECIMAL(10,2) DEFAULT 0,
    vehicles_assisted INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- See full schema.sql for complete definitions