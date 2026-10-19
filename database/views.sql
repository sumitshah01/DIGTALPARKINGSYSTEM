-- ============================================================
-- SMART PARKING SYSTEM - VIEWS
-- ============================================================
USE smart_parking;

-- ------------------------------------------------------------
-- View: Live parking availability summary
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_parking_availability AS
SELECT 
    p.id AS parking_id,
    p.name,
    p.area,
    p.city,
    p.latitude,
    p.longitude,
    p.price_per_hour,
    p.rating,
    p.has_ev_charging,
    p.has_accessible,
    p.has_covered,
    p.is_24_7,
    COUNT(s.id) AS total_slots,
    SUM(CASE WHEN s.status = 'available' THEN 1 ELSE 0 END) AS available_slots,
    SUM(CASE WHEN s.status = 'occupied' THEN 1 ELSE 0 END) AS occupied_slots,
    SUM(CASE WHEN s.status = 'reserved' THEN 1 ELSE 0 END) AS reserved_slots,
    SUM(CASE WHEN s.status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_slots,
    SUM(CASE WHEN s.slot_type = 'EV' AND s.status = 'available' THEN 1 ELSE 0 END) AS ev_available,
    SUM(CASE WHEN s.slot_type = 'accessible' AND s.status = 'available' THEN 1 ELSE 0 END) AS accessible_available,
    ROUND(
        (SUM(CASE WHEN s.status = 'occupied' THEN 1 ELSE 0 END) / 
        NULLIF(COUNT(s.id), 0)) * 100, 2
    ) AS occupancy_rate
FROM parking_locations p
LEFT JOIN parking_slots s ON s.parking_id = p.id
WHERE p.is_active = 1
GROUP BY p.id;

-- ------------------------------------------------------------
-- View: User reservation history with details
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_user_reservation_history AS
SELECT 
    r.id,
    r.booking_code,
    r.user_id,
    u.name AS user_name,
    u.email,
    r.parking_id,
    p.name AS parking_name,
    p.area,
    r.slot_id,
    s.slot_number,
    s.slot_type,
    r.vehicle_id,
    v.vehicle_number,
    v.vehicle_type,
    v.model,
    r.entry_time,
    r.expected_exit_time,
    r.actual_entry_time,
    r.actual_exit_time,
    r.duration_hours,
    r.amount,
    r.tax,
    r.total_amount,
    r.status,
    r.created_at
FROM reservations r
JOIN users u ON u.id = r.user_id
JOIN parking_locations p ON p.id = r.parking_id
JOIN parking_slots s ON s.id = r.slot_id
JOIN vehicles v ON v.id = r.vehicle_id;

-- ------------------------------------------------------------
-- View: Active parking sessions
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_active_sessions AS
SELECT 
    ps.id,
    ps.vehicle_number,
    ps.entry_time,
    TIMESTAMPDIFF(MINUTE, ps.entry_time, NOW()) AS duration_minutes,
    ps.amount,
    ps.total_amount,
    ps.payment_status,
    p.name AS parking_name,
    p.address AS parking_address,
    s.slot_number,
    u.name AS user_name,
    u.phone
FROM parking_sessions ps
JOIN parking_locations p ON p.id = ps.parking_id
JOIN parking_slots s ON s.id = ps.slot_id
LEFT JOIN users u ON u.id = ps.user_id
WHERE ps.status = 'active';

-- ------------------------------------------------------------
-- View: Parking revenue summary (daily)
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_daily_revenue AS
SELECT 
    DATE(created_at) AS revenue_date,
    COUNT(*) AS total_transactions,
    SUM(CASE WHEN status = 'success' THEN total ELSE 0 END) AS total_revenue,
    SUM(CASE WHEN status = 'success' THEN tax ELSE 0 END) AS total_tax,
    SUM(CASE WHEN method = 'upi' THEN total ELSE 0 END) AS upi_revenue,
    SUM(CASE WHEN method = 'card' THEN total ELSE 0 END) AS card_revenue,
    SUM(CASE WHEN method = 'wallet' THEN total ELSE 0 END) AS wallet_revenue,
    SUM(CASE WHEN method = 'cash' THEN total ELSE 0 END) AS cash_revenue
FROM payments
GROUP BY DATE(created_at)
ORDER BY revenue_date DESC;

-- ------------------------------------------------------------
-- View: Top rated parking locations
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_top_parkings AS
SELECT 
    p.id,
    p.name,
    p.area,
    p.rating,
    p.total_reviews,
    p.price_per_hour,
    (SELECT COUNT(*) FROM parking_slots WHERE parking_id = p.id AND status = 'available') AS available_slots
FROM parking_locations p
WHERE p.is_active = 1 AND p.total_reviews > 0
ORDER BY p.rating DESC, p.total_reviews DESC;

-- ------------------------------------------------------------
-- View: Peak hour analysis
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_peak_hours AS
SELECT 
    HOUR(entry_time) AS hour_of_day,
    COUNT(*) AS session_count,
    ROUND(AVG(TIMESTAMPDIFF(MINUTE, entry_time, exit_time)), 2) AS avg_duration_minutes
FROM parking_sessions
WHERE exit_time IS NOT NULL
GROUP BY HOUR(entry_time)
ORDER BY hour_of_day;

-- ------------------------------------------------------------
-- View: Environmental impact summary
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_environmental_summary AS
SELECT 
    stat_date,
    fuel_saved_liters,
    co2_avoided_kg,
    search_time_saved_hours,
    vehicles_assisted,
    distance_saved_km,
    idling_reduced_minutes,
    trees_equivalent
FROM environmental_statistics
ORDER BY stat_date DESC;

-- ------------------------------------------------------------
-- View: User dashboard stats
-- ------------------------------------------------------------
CREATE OR REPLACE VIEW v_user_dashboard_stats AS
SELECT 
    u.id AS user_id,
    u.name,
    u.email,
    COUNT(DISTINCT v.id) AS total_vehicles,
    COUNT(DISTINCT r.id) AS total_reservations,
    SUM(CASE WHEN r.status IN ('confirmed','active') THEN 1 ELSE 0 END) AS active_reservations,
    COUNT(DISTINCT ps.id) AS total_sessions,
    COALESCE(SUM(CASE WHEN pay.status = 'success' THEN pay.total ELSE 0 END), 0) AS total_spent,
    COUNT(DISTINCT n.id) AS unread_notifications
FROM users u
LEFT JOIN vehicles v ON v.user_id = u.id
LEFT JOIN reservations r ON r.user_id = u.id
LEFT JOIN parking_sessions ps ON ps.user_id = u.id
LEFT JOIN payments pay ON pay.user_id = u.id
LEFT JOIN notifications n ON n.user_id = u.id AND n.is_read = 0
WHERE u.role = 'user'
GROUP BY u.id;

SELECT 'Views created successfully!' AS message;