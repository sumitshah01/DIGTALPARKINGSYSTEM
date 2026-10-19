-- ============================================================
-- SMART PARKING SYSTEM - STORED PROCEDURES
-- ============================================================
USE smart_parking;

DELIMITER $$

-- ------------------------------------------------------------
-- Procedure: Find nearest parking locations
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_find_nearby_parking$$
CREATE PROCEDURE sp_find_nearby_parking(
    IN p_lat DECIMAL(10,8),
    IN p_lng DECIMAL(11,8),
    IN p_limit INT,
    IN p_radius_km DECIMAL(5,2)
)
BEGIN
    SELECT 
        p.id,
        p.name,
        p.address,
        p.area,
        p.latitude,
        p.longitude,
        p.price_per_hour,
        p.rating,
        p.has_ev_charging,
        p.has_accessible,
        p.has_covered,
        p.is_24_7,
        ROUND(
            6371 * 2 * ASIN(SQRT(
                POWER(SIN((p_lat - p.latitude) * PI() / 180 / 2), 2) +
                COS(p_lat * PI() / 180) * COS(p.latitude * PI() / 180) *
                POWER(SIN((p_lng - p.longitude) * PI() / 180 / 2), 2)
            )), 2
        ) AS distance_km,
        (SELECT COUNT(*) FROM parking_slots 
         WHERE parking_id = p.id AND status = 'available') AS available_slots
    FROM parking_locations p
    WHERE p.is_active = 1
    HAVING distance_km <= p_radius_km
    ORDER BY distance_km ASC
    LIMIT p_limit;
END$$

-- ------------------------------------------------------------
-- Procedure: Create reservation with slot locking
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_create_reservation$$
CREATE PROCEDURE sp_create_reservation(
    IN p_user_id INT,
    IN p_parking_id INT,
    IN p_slot_id INT,
    IN p_vehicle_id INT,
    IN p_booking_code VARCHAR(30),
    IN p_entry_time DATETIME,
    IN p_exit_time DATETIME,
    OUT p_reservation_id INT,
    OUT p_total_amount DECIMAL(10,2)
)
BEGIN
    DECLARE v_price DECIMAL(8,2);
    DECLARE v_hours DECIMAL(5,2);
    DECLARE v_amount DECIMAL(10,2);
    DECLARE v_tax DECIMAL(10,2);
    DECLARE v_slot_status VARCHAR(20);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Lock the slot
    SELECT status INTO v_slot_status 
    FROM parking_slots 
    WHERE id = p_slot_id AND parking_id = p_parking_id
    FOR UPDATE;
    
    IF v_slot_status != 'available' THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Slot is not available';
    END IF;
    
    -- Get parking price
    SELECT price_per_hour INTO v_price 
    FROM parking_locations WHERE id = p_parking_id;
    
    -- Calculate hours, amount, tax
    SET v_hours = GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, p_entry_time, p_exit_time) / 60));
    SET v_amount = v_hours * v_price;
    SET v_tax = ROUND(v_amount * 0.18, 2);
    SET p_total_amount = v_amount + v_tax;
    
    -- Insert reservation
    INSERT INTO reservations (
        user_id, parking_id, slot_id, vehicle_id, booking_code,
        entry_time, expected_exit_time, duration_hours,
        amount, tax, total_amount, status
    ) VALUES (
        p_user_id, p_parking_id, p_slot_id, p_vehicle_id, p_booking_code,
        p_entry_time, p_exit_time, v_hours,
        v_amount, v_tax, p_total_amount, 'confirmed'
    );
    
    SET p_reservation_id = LAST_INSERT_ID();
    
    -- Update slot status
    UPDATE parking_slots SET status = 'reserved' WHERE id = p_slot_id;
    
    COMMIT;
END$$

-- ------------------------------------------------------------
-- Procedure: Check-in (QR scan at entry)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_check_in$$
CREATE PROCEDURE sp_check_in(
    IN p_booking_code VARCHAR(30),
    OUT p_slot_number VARCHAR(20),
    OUT p_parking_name VARCHAR(150)
)
BEGIN
    DECLARE v_res_id INT;
    DECLARE v_slot_id INT;
    DECLARE v_parking_id INT;
    DECLARE v_user_id INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    SELECT r.id, r.slot_id, r.parking_id, r.user_id
    INTO v_res_id, v_slot_id, v_parking_id, v_user_id
    FROM reservations r
    WHERE r.booking_code = p_booking_code AND r.status = 'confirmed'
    FOR UPDATE;
    
    IF v_res_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid booking code';
    END IF;
    
    -- Update reservation
    UPDATE reservations 
    SET status = 'active', actual_entry_time = NOW() 
    WHERE id = v_res_id;
    
    -- Update slot
    UPDATE parking_slots SET status = 'occupied' WHERE id = v_slot_id;
    
    -- Create session
    INSERT INTO parking_sessions (
        reservation_id, user_id, parking_id, slot_id, vehicle_id,
        entry_time, status
    )
    SELECT v_res_id, v_user_id, v_parking_id, v_slot_id, vehicle_id,
           NOW(), 'active'
    FROM reservations WHERE id = v_res_id;
    
    -- Get slot and parking info
    SELECT s.slot_number, p.name 
    INTO p_slot_number, p_parking_name
    FROM parking_slots s
    JOIN parking_locations p ON p.id = s.parking_id
    WHERE s.id = v_slot_id;
    
    COMMIT;
END$$

-- ------------------------------------------------------------
-- Procedure: Check-out (Calculate final bill)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_check_out$$
CREATE PROCEDURE sp_check_out(
    IN p_booking_code VARCHAR(30),
    OUT p_duration_hours DECIMAL(5,2),
    OUT p_total_amount DECIMAL(10,2)
)
BEGIN
    DECLARE v_res_id INT;
    DECLARE v_slot_id INT;
    DECLARE v_price DECIMAL(8,2);
    DECLARE v_entry_time DATETIME;
    DECLARE v_hours DECIMAL(5,2);
    DECLARE v_amount DECIMAL(10,2);
    DECLARE v_tax DECIMAL(10,2);
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    SELECT r.id, r.slot_id, r.actual_entry_time, p.price_per_hour
    INTO v_res_id, v_slot_id, v_entry_time, v_price
    FROM reservations r
    JOIN parking_locations p ON p.id = r.parking_id
    WHERE r.booking_code = p_booking_code AND r.status = 'active'
    FOR UPDATE;
    
    IF v_res_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Active reservation not found';
    END IF;
    
    SET v_hours = GREATEST(1, CEIL(TIMESTAMPDIFF(MINUTE, v_entry_time, NOW()) / 60));
    SET v_amount = v_hours * v_price;
    SET v_tax = ROUND(v_amount * 0.18, 2);
    SET p_duration_hours = v_hours;
    SET p_total_amount = v_amount + v_tax;
    
    -- Update reservation
    UPDATE reservations 
    SET status = 'completed', 
        actual_exit_time = NOW(),
        duration_hours = v_hours,
        amount = v_amount,
        tax = v_tax,
        total_amount = p_total_amount
    WHERE id = v_res_id;
    
    -- Update slot
    UPDATE parking_slots SET status = 'available' WHERE id = v_slot_id;
    
    -- Update session
    UPDATE parking_sessions
    SET exit_time = NOW(),
        duration_minutes = v_hours * 60,
        amount = v_amount,
        tax = v_tax,
        total_amount = p_total_amount,
        status = 'completed'
    WHERE reservation_id = v_res_id;
    
    -- Update environmental impact
    INSERT INTO environmental_statistics (
        stat_date, fuel_saved_liters, co2_avoided_kg,
        search_time_saved_hours, vehicles_assisted,
        distance_saved_km, idling_reduced_minutes
    ) VALUES (
        CURDATE(), 0.12, 0.28, 0.13, 1, 1.50, 8
    )
    ON DUPLICATE KEY UPDATE
        fuel_saved_liters = fuel_saved_liters + 0.12,
        co2_avoided_kg = co2_avoided_kg + 0.28,
        search_time_saved_hours = search_time_saved_hours + 0.13,
        vehicles_assisted = vehicles_assisted + 1,
        distance_saved_km = distance_saved_km + 1.50,
        idling_reduced_minutes = idling_reduced_minutes + 8;
    
    COMMIT;
END$$

-- ------------------------------------------------------------
-- Procedure: Get dashboard stats (Admin)
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_admin_dashboard$$
CREATE PROCEDURE sp_admin_dashboard()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM parking_locations WHERE is_active = 1) AS total_locations,
        (SELECT COUNT(*) FROM parking_slots) AS total_slots,
        (SELECT COUNT(*) FROM parking_slots WHERE status = 'available') AS available_slots,
        (SELECT COUNT(*) FROM parking_slots WHERE status = 'occupied') AS occupied_slots,
        (SELECT COUNT(*) FROM parking_slots WHERE status = 'reserved') AS reserved_slots,
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_users,
        (SELECT COUNT(*) FROM vehicles) AS active_vehicles,
        (SELECT COUNT(*) FROM reservations WHERE status IN ('confirmed','active')) AS active_reservations,
        (SELECT COALESCE(SUM(total), 0) FROM payments WHERE status = 'success') AS total_revenue,
        (SELECT COALESCE(SUM(total), 0) FROM payments WHERE status = 'success' AND DATE(created_at) = CURDATE()) AS today_revenue,
        (SELECT fuel_saved_liters FROM environmental_statistics WHERE stat_date = CURDATE()) AS today_fuel_saved,
        (SELECT co2_avoided_kg FROM environmental_statistics WHERE stat_date = CURDATE()) AS today_co2_avoided;
END$$

-- ------------------------------------------------------------
-- Procedure: Auto-expire old reservations
-- ------------------------------------------------------------
DROP PROCEDURE IF EXISTS sp_expire_reservations$$
CREATE PROCEDURE sp_expire_reservations()
BEGIN
    -- Mark as expired if entry time passed by 30 min and still confirmed
    UPDATE reservations 
    SET status = 'expired'
    WHERE status = 'confirmed' 
    AND entry_time < DATE_SUB(NOW(), INTERVAL 30 MINUTE);
    
    -- Free up their slots
    UPDATE parking_slots s
    JOIN reservations r ON r.slot_id = s.id
    SET s.status = 'available'
    WHERE r.status = 'expired' AND s.status = 'reserved';
    
    SELECT ROW_COUNT() AS expired_count;
END$$

DELIMITER ;

SELECT 'Stored procedures created successfully!' AS message;