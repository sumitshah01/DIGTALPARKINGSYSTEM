-- ============================================================
-- SMART PARKING SYSTEM - SEED DATA
-- ============================================================
USE smart_parking;

-- ------------------------------------------------------------
-- USERS (password for all: "password123")
-- Hash below is for "password123" using bcrypt
-- ------------------------------------------------------------
INSERT INTO users (name, email, phone, password_hash, role, is_verified, wallet_balance) VALUES
('System Admin', 'admin@smartpark.com', '9000000001', 
 '$2y$10$Z1/IOH3vpnoS96G4Julc2ubCE1dtW2etOdnZSob1PXZ0rUSL6llOm', 'admin', 1, 0.00),
('Rahul Sharma', 'rahul@example.com', '9876543210', 
 '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBq0y4bB2bF.3m4S', 'user', 1, 500.00),
('Priya Patel', 'priya@example.com', '9876543211', 
 '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBq0y4bB2bF.3m4S', 'user', 1, 250.00),
('Amit Kumar', 'amit@example.com', '9876543212', 
 '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBq0y4bB2bF.3m4S', 'user', 1, 100.00),
('Sneha Reddy', 'sneha@example.com', '9876543213', 
 '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS4bZJ18JuywdBq0y4bB2bF.3m4S', 'user', 1, 750.00);

-- ------------------------------------------------------------
-- ADMINS
-- ------------------------------------------------------------
INSERT INTO admins (user_id, permissions, department) VALUES
(1, '{"all": true}', 'System Administration');

-- ------------------------------------------------------------
-- VEHICLES
-- ------------------------------------------------------------
INSERT INTO vehicles (user_id, vehicle_number, vehicle_type, model, color, is_default) VALUES
(2, 'KA01AB1234', '4-wheeler', 'Honda City', 'White', 1),
(2, 'KA01XY5678', '2-wheeler', 'Royal Enfield', 'Black', 0),
(3, 'KA05CD9012', 'EV', 'Tata Nexon EV', 'Blue', 1),
(4, 'KA03EF3456', '4-wheeler', 'Maruti Swift', 'Red', 1),
(5, 'KA02GH7890', '4-wheeler', 'Hyundai Creta', 'Silver', 1),
(5, 'KA02IJ1122', 'EV', 'MG ZS EV', 'Green', 0);

-- ------------------------------------------------------------
-- PARKING LOCATIONS
-- ------------------------------------------------------------
INSERT INTO parking_locations 
(name, address, area, city, landmark, latitude, longitude, total_slots, price_per_hour, 
 opening_time, closing_time, is_24_7, has_covered, has_ev_charging, has_accessible, 
 facilities, rating, total_reviews) 
VALUES
('City Center Parking', 'MG Road, Bangalore - 560001', 'MG Road', 'Bangalore', 
 'Near Metro Station', 12.9716, 77.5946, 100, 40.00, 
 '06:00:00', '23:00:00', 0, 1, 1, 1, 
 '["CCTV","Security","Restroom","WiFi","Valet"]', 4.50, 128),

('Tech Park Parking', 'Whitefield Main Road, Bangalore - 560066', 'Whitefield', 'Bangalore', 
 'Near ITPL', 12.9698, 77.7500, 250, 30.00, 
 '00:00:00', '23:59:59', 1, 0, 1, 1, 
 '["CCTV","Security","EV Charging","Cafeteria"]', 4.20, 89),

('Phoenix Mall Parking', 'Whitefield Road, Bangalore - 560048', 'Whitefield', 'Bangalore', 
 'Phoenix Mall', 12.9959, 77.6967, 500, 50.00, 
 '09:00:00', '23:30:00', 0, 1, 1, 1, 
 '["CCTV","Valet","Restroom","Food Court","Kids Area"]', 4.70, 312),

('Koramangala Smart Park', '80 Feet Road, Koramangala, Bangalore - 560034', 'Koramangala', 'Bangalore', 
 'Near Forum Mall', 12.9352, 77.6245, 80, 35.00, 
 '07:00:00', '22:00:00', 0, 1, 0, 1, 
 '["CCTV","Security"]', 4.10, 45),

('Indiranagar Parking Hub', '100 Feet Road, Indiranagar, Bangalore - 560038', 'Indiranagar', 'Bangalore', 
 'Near Sony Signal', 12.9784, 77.6408, 60, 45.00, 
 '06:00:00', '23:00:00', 0, 0, 1, 0, 
 '["CCTV","EV Charging"]', 4.30, 67),

('Airport Road Parking', 'Airport Road, Devanahalli, Bangalore - 562110', 'Devanahalli', 'Bangalore', 
 'Near Airport', 13.1986, 77.7066, 400, 25.00, 
 '00:00:00', '23:59:59', 1, 1, 1, 1, 
 '["CCTV","Security","Restroom","Shuttle"]', 4.60, 203),

('Jayanagar Parking', '11th Main Road, Jayanagar, Bangalore - 560041', 'Jayanagar', 'Bangalore', 
 'Near Jayanagar 4th Block', 12.9250, 77.5938, 45, 30.00, 
 '07:00:00', '22:00:00', 0, 0, 0, 1, 
 '["CCTV"]', 3.90, 28);

-- ------------------------------------------------------------
-- PARKING SLOTS (Sample for each parking)
-- ------------------------------------------------------------

-- City Center Parking (id=1) - 100 slots total, sample 20
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status, floor) VALUES
(1, 'A-01', '4-wheeler', 'available', 'G'),
(1, 'A-02', '4-wheeler', 'occupied', 'G'),
(1, 'A-03', '4-wheeler', 'available', 'G'),
(1, 'A-04', '4-wheeler', 'available', 'G'),
(1, 'A-05', '4-wheeler', 'reserved', 'G'),
(1, 'A-06', '4-wheeler', 'available', 'G'),
(1, 'A-07', '4-wheeler', 'occupied', 'G'),
(1, 'A-08', '4-wheeler', 'available', 'G'),
(1, 'A-09', '4-wheeler', 'available', 'G'),
(1, 'A-10', '4-wheeler', 'available', 'G'),
(1, 'E-01', 'EV', 'available', 'G'),
(1, 'E-02', 'EV', 'occupied', 'G'),
(1, 'E-03', 'EV', 'available', 'G'),
(1, 'W-01', '2-wheeler', 'available', 'G'),
(1, 'W-02', '2-wheeler', 'available', 'G'),
(1, 'W-03', '2-wheeler', 'occupied', 'G'),
(1, 'W-04', '2-wheeler', 'available', 'G'),
(1, 'W-05', '2-wheeler', 'available', 'G'),
(1, 'X-01', 'accessible', 'available', 'G'),
(1, 'X-02', 'accessible', 'available', 'G');

-- Tech Park Parking (id=2) - 250 slots total, sample 20
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status, floor) VALUES
(2, 'B-01', '4-wheeler', 'available', 'G'),
(2, 'B-02', '4-wheeler', 'available', 'G'),
(2, 'B-03', '4-wheeler', 'occupied', 'G'),
(2, 'B-04', '4-wheeler', 'available', 'G'),
(2, 'B-05', '4-wheeler', 'available', 'G'),
(2, 'B-06', '4-wheeler', 'available', 'G'),
(2, 'B-07', '4-wheeler', 'reserved', 'G'),
(2, 'B-08', '4-wheeler', 'available', 'G'),
(2, 'B-09', '4-wheeler', 'available', 'G'),
(2, 'B-10', '4-wheeler', 'available', 'G'),
(2, 'E-01', 'EV', 'available', 'G'),
(2, 'E-02', 'EV', 'available', 'G'),
(2, 'E-03', 'EV', 'occupied', 'G'),
(2, 'E-04', 'EV', 'available', 'G'),
(2, 'E-05', 'EV', 'available', 'G'),
(2, 'X-01', 'accessible', 'available', 'G'),
(2, 'X-02', 'accessible', 'occupied', 'G'),
(2, 'W-01', '2-wheeler', 'available', 'G'),
(2, 'W-02', '2-wheeler', 'available', 'G'),
(2, 'W-03', '2-wheeler', 'available', 'G');

-- Phoenix Mall Parking (id=3)
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status, floor) VALUES
(3, 'P1-01', '4-wheeler', 'available', 'P1'),
(3, 'P1-02', '4-wheeler', 'occupied', 'P1'),
(3, 'P1-03', '4-wheeler', 'available', 'P1'),
(3, 'P1-04', '4-wheeler', 'available', 'P1'),
(3, 'P1-05', '4-wheeler', 'available', 'P1'),
(3, 'P2-01', '4-wheeler', 'available', 'P2'),
(3, 'P2-02', '4-wheeler', 'reserved', 'P2'),
(3, 'P2-03', '4-wheeler', 'available', 'P2'),
(3, 'E-01', 'EV', 'available', 'P1'),
(3, 'E-02', 'EV', 'available', 'P1'),
(3, 'X-01', 'accessible', 'available', 'P1');

-- Koramangala Smart Park (id=4)
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES
(4, 'K-01', '4-wheeler', 'available'),
(4, 'K-02', '4-wheeler', 'occupied'),
(4, 'K-03', '4-wheeler', 'available'),
(4, 'K-04', '4-wheeler', 'available'),
(4, 'K-05', '4-wheeler', 'available'),
(4, 'K-06', '4-wheeler', 'available'),
(4, 'K-07', '4-wheeler', 'available'),
(4, 'K-08', '4-wheeler', 'reserved'),
(4, 'X-01', 'accessible', 'available');

-- Indiranagar Parking Hub (id=5)
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES
(5, 'I-01', '4-wheeler', 'available'),
(5, 'I-02', '4-wheeler', 'available'),
(5, 'I-03', '4-wheeler', 'occupied'),
(5, 'I-04', '4-wheeler', 'available'),
(5, 'I-05', '4-wheeler', 'available'),
(5, 'E-01', 'EV', 'available'),
(5, 'E-02', 'EV', 'available'),
(5, 'E-03', 'EV', 'available');

-- Airport Road Parking (id=6)
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status, floor) VALUES
(6, 'AP-01', '4-wheeler', 'available', 'G'),
(6, 'AP-02', '4-wheeler', 'occupied', 'G'),
(6, 'AP-03', '4-wheeler', 'available', 'G'),
(6, 'AP-04', '4-wheeler', 'available', 'G'),
(6, 'AP-05', '4-wheeler', 'available', 'G'),
(6, 'AP-06', '4-wheeler', 'available', 'G'),
(6, 'E-01', 'EV', 'available', 'G'),
(6, 'X-01', 'accessible', 'available', 'G');

-- Jayanagar Parking (id=7)
INSERT INTO parking_slots (parking_id, slot_number, slot_type, status) VALUES
(7, 'J-01', '4-wheeler', 'available'),
(7, 'J-02', '4-wheeler', 'available'),
(7, 'J-03', '4-wheeler', 'occupied'),
(7, 'J-04', '4-wheeler', 'available'),
(7, 'J-05', '4-wheeler', 'available'),
(7, 'X-01', 'accessible', 'available');

-- ------------------------------------------------------------
-- EV CHARGING STATIONS
-- ------------------------------------------------------------
INSERT INTO ev_charging_stations 
(parking_id, slot_id, station_code, charger_type, power_kw, price_per_kwh, status) 
VALUES
(1, 11, 'EVC-CC-001', 'DC-fast', 50.00, 18.00, 'available'),
(1, 12, 'EVC-CC-002', 'DC-fast', 50.00, 18.00, 'in-use'),
(1, 13, 'EVC-CC-003', 'AC', 7.20, 12.00, 'available'),
(2, 21, 'EVC-TP-001', 'DC-fast', 60.00, 18.00, 'available'),
(2, 22, 'EVC-TP-002', 'DC-fast', 60.00, 18.00, 'available'),
(2, 23, 'EVC-TP-003', 'AC', 7.20, 12.00, 'in-use'),
(2, 24, 'EVC-TP-004', 'AC', 7.20, 12.00, 'available'),
(2, 25, 'EVC-TP-005', 'Type2', 22.00, 15.00, 'available'),
(3, 62, 'EVC-PM-001', 'DC-fast', 50.00, 20.00, 'available'),
(3, 63, 'EVC-PM-002', 'DC-fast', 50.00, 20.00, 'available'),
(5, 78, 'EVC-IN-001', 'AC', 7.20, 12.00, 'available'),
(5, 79, 'EVC-IN-002', 'AC', 7.20, 12.00, 'available'),
(5, 80, 'EVC-IN-003', 'Type2', 22.00, 15.00, 'available'),
(6, 100, 'EVC-AP-001', 'DC-fast', 60.00, 18.00, 'available');

-- ------------------------------------------------------------
-- PARKING FACILITIES
-- ------------------------------------------------------------
INSERT INTO parking_facilities (parking_id, facility_name, icon) VALUES
(1, 'CCTV Surveillance', 'bi-camera-video'),
(1, '24/7 Security', 'bi-shield-check'),
(1, 'Restroom', 'bi-door-open'),
(1, 'WiFi', 'bi-wifi'),
(1, 'Valet Service', 'bi-person-badge'),
(2, 'CCTV Surveillance', 'bi-camera-video'),
(2, 'Security Guard', 'bi-shield-check'),
(2, 'EV Charging', 'bi-ev-station'),
(2, 'Cafeteria', 'bi-cup-hot'),
(3, 'Valet Parking', 'bi-person-badge'),
(3, 'Food Court', 'bi-shop'),
(3, 'Kids Area', 'bi-emoji-smile'),
(3, 'Restroom', 'bi-door-open'),
(6, 'Airport Shuttle', 'bi-bus-front'),
(6, 'Long-term Parking', 'bi-calendar-check');

-- ------------------------------------------------------------
-- RESERVATIONS (Sample)
-- ------------------------------------------------------------
INSERT INTO reservations 
(user_id, parking_id, slot_id, vehicle_id, booking_code, entry_time, expected_exit_time, 
 duration_hours, amount, tax, total_amount, status) 
VALUES
(2, 1, 1, 1, 'SP1A2B3C4D', 
 DATE_ADD(NOW(), INTERVAL 1 HOUR), DATE_ADD(NOW(), INTERVAL 3 HOUR), 
 2.00, 80.00, 14.40, 94.40, 'confirmed'),

(3, 2, 21, 3, 'SP2B3C4D5E', 
 DATE_ADD(NOW(), INTERVAL 30 MINUTE), DATE_ADD(NOW(), INTERVAL 2 HOUR), 
 1.50, 45.00, 8.10, 53.10, 'confirmed'),

(4, 3, 61, 4, 'SP3C4D5E6F', 
 DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_ADD(NOW(), INTERVAL 1 HOUR), 
 2.00, 100.00, 18.00, 118.00, 'active'),

(5, 6, 92, 5, 'SP4D5E6F7G', 
 DATE_SUB(NOW(), INTERVAL 3 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR), 
 2.00, 50.00, 9.00, 59.00, 'completed');

-- ------------------------------------------------------------
-- PARKING SESSIONS
-- ------------------------------------------------------------
INSERT INTO parking_sessions 
(reservation_id, user_id, parking_id, slot_id, vehicle_id, vehicle_number, 
 entry_time, exit_time, duration_minutes, amount, tax, total_amount, 
 payment_status, status, entry_method, exit_method) 
VALUES
(4, 5, 6, 92, 5, 'KA02GH7890', 
 DATE_SUB(NOW(), INTERVAL 3 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR), 
 120, 50.00, 9.00, 59.00, 'paid', 'completed', 'qr', 'qr'),

(3, 4, 3, 61, 4, 'KA03EF3456', 
 DATE_SUB(NOW(), INTERVAL 1 HOUR), NULL, 
 NULL, 100.00, 18.00, 118.00, 'pending', 'active', 'qr', NULL);

-- ------------------------------------------------------------
-- PAYMENTS
-- ------------------------------------------------------------
INSERT INTO payments 
(user_id, reservation_id, session_id, amount, tax, total, method, 
 transaction_id, status, receipt_number, paid_at) 
VALUES
(5, 4, 1, 50.00, 9.00, 59.00, 'upi', 'TXN9A8B7C6D5E', 'success', 
 'RCPT-2024-001', DATE_SUB(NOW(), INTERVAL 1 HOUR)),

(2, 1, NULL, 80.00, 14.40, 94.40, 'card', 'TXN1F2E3D4C5B', 'success', 
 'RCPT-2024-002', NOW()),

(3, 2, NULL, 45.00, 8.10, 53.10, 'wallet', 'TXN2G3H4I5J6K', 'success', 
 'RCPT-2024-003', NOW()),

(4, 3, 2, 100.00, 18.00, 118.00, 'upi', 'TXN3L4M5N6O7P', 'pending', 
 NULL, NULL);

-- ------------------------------------------------------------
-- NOTIFICATIONS
-- ------------------------------------------------------------
INSERT INTO notifications (user_id, title, message, type, icon) VALUES
(2, 'Reservation Confirmed', 
 'Your booking SP1A2B3C4D at City Center Parking is confirmed.', 
 'reservation', 'bi-check-circle'),

(2, 'Welcome to SmartPark!', 
 'Start finding parking in seconds. Save fuel, save time.', 
 'system', 'bi-stars'),

(3, 'Reservation Confirmed', 
 'Your booking SP2B3C4D5E at Tech Park Parking is confirmed.', 
 'reservation', 'bi-check-circle'),

(4, 'Parking Time Nearing Expiry', 
 'Your parking session at Phoenix Mall ends in 30 minutes.', 
 'reminder', 'bi-alarm'),

(5, 'Payment Successful', 
 'Your payment of ₹59.00 was successful. Receipt: RCPT-2024-001', 
 'payment', 'bi-credit-card'),

(2, 'Special Offer! 🎉', 
 'Get 20% off on your next booking. Use code: SAVE20', 
 'promo', 'bi-tag');

-- ------------------------------------------------------------
-- REVIEWS
-- ------------------------------------------------------------
INSERT INTO reviews 
(user_id, parking_id, rating, cleanliness_rating, security_rating, 
 accessibility_rating, comment, is_approved) 
VALUES
(2, 1, 5, 5, 5, 4, 'Excellent parking! Very clean and secure. Easy to find slots.', 1),
(3, 1, 4, 4, 5, 4, 'Good location. Slightly expensive but worth it.', 1),
(4, 2, 4, 4, 4, 5, 'Great for EV charging. Fast chargers available.', 1),
(5, 3, 5, 5, 5, 5, 'Best parking in the area. Valet service is fantastic!', 1),
(2, 6, 5, 4, 5, 5, 'Perfect for airport. Shuttle service is very convenient.', 1),
(3, 4, 4, 4, 4, 4, 'Decent parking, good security.', 1);

-- ------------------------------------------------------------
-- ENVIRONMENTAL STATISTICS (Last 7 days)
-- ------------------------------------------------------------
INSERT INTO environmental_statistics 
(stat_date, fuel_saved_liters, co2_avoided_kg, search_time_saved_hours, 
 vehicles_assisted, distance_saved_km, idling_reduced_minutes, trees_equivalent) 
VALUES
(DATE_SUB(CURDATE(), INTERVAL 6 DAY), 98.50, 227.54, 35.00, 980, 1477.50, 7840, 10.35),
(DATE_SUB(CURDATE(), INTERVAL 5 DAY), 105.20, 243.01, 38.50, 1052, 1578.00, 8416, 11.05),
(DATE_SUB(CURDATE(), INTERVAL 4 DAY), 115.80, 267.50, 42.00, 1158, 1737.00, 9264, 12.16),
(DATE_SUB(CURDATE(), INTERVAL 3 DAY), 122.30, 282.51, 44.00, 1223, 1834.50, 9784, 12.84),
(DATE_SUB(CURDATE(), INTERVAL 2 DAY), 118.70, 274.20, 43.00, 1187, 1780.50, 9496, 12.46),
(DATE_SUB(CURDATE(), INTERVAL 1 DAY), 130.40, 301.22, 47.00, 1304, 1956.00, 10432, 13.69),
(CURDATE(), 125.50, 290.20, 46.00, 1250, 1875.00, 10000, 13.18);

-- ------------------------------------------------------------
-- PROMO CODES
-- ------------------------------------------------------------
INSERT INTO promo_codes 
(code, description, discount_type, discount_value, min_amount, max_discount, 
 usage_limit, valid_from, valid_until, is_active) 
VALUES
('WELCOME10', 'Welcome offer - 10% off', 'percent', 10.00, 50.00, 30.00, 
 1000, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1),

('SAVE20', 'Special 20% off', 'percent', 20.00, 100.00, 50.00, 
 500, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY), 1),

('FLAT50', 'Flat ₹50 off', 'fixed', 50.00, 200.00, 50.00, 
 200, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 1),

('EV100', 'EV special ₹100 off', 'fixed', 100.00, 300.00, 100.00, 
 100, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1);

-- ------------------------------------------------------------
-- FAVORITE PARKINGS
-- ------------------------------------------------------------
INSERT INTO favorite_parkings (user_id, parking_id) VALUES
(2, 1),
(2, 3),
(3, 2),
(4, 3),
(5, 6);

-- ------------------------------------------------------------
-- OCCUPANCY PREDICTIONS (Sample AI data)
-- ------------------------------------------------------------
INSERT INTO occupancy_predictions 
(parking_id, prediction_date, hour_of_day, predicted_occupancy, confidence_score) 
VALUES
(1, CURDATE(), 9, 75.50, 88.00),
(1, CURDATE(), 12, 85.20, 90.00),
(1, CURDATE(), 18, 95.80, 92.00),
(2, CURDATE(), 10, 68.30, 85.00),
(2, CURDATE(), 14, 72.10, 87.00),
(3, CURDATE(), 19, 92.40, 91.00);

-- ============================================================
-- SEED DATA COMPLETE
-- ============================================================
SELECT 'Seed data inserted successfully!' AS message;
SELECT 
    (SELECT COUNT(*) FROM users) AS users,
    (SELECT COUNT(*) FROM vehicles) AS vehicles,
    (SELECT COUNT(*) FROM parking_locations) AS parkings,
    (SELECT COUNT(*) FROM parking_slots) AS slots,
    (SELECT COUNT(*) FROM reservations) AS reservations,
    (SELECT COUNT(*) FROM payments) AS payments;