-- Migration 003: Reservations and parking sessions
USE smart_parking;

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    parking_id INT NOT NULL,
    slot_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    booking_code VARCHAR(30) UNIQUE NOT NULL,
    entry_time DATETIME NOT NULL,
    expected_exit_time DATETIME NOT NULL,
    status ENUM('pending','confirmed','active','completed','cancelled','expired') DEFAULT 'pending',
    -- ... (rest as in schema.sql)
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_booking_code (booking_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- See full schema.sql for complete definitions