-- Migration 004: Payments and notifications
USE smart_parking;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    method ENUM('upi','card','wallet','cash','netbanking','razorpay') NOT NULL,
    status ENUM('pending','success','failed','refunded','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- See full schema.sql for complete definitions