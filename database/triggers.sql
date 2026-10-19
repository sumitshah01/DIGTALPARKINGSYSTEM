-- ============================================================
-- SMART PARKING SYSTEM - TRIGGERS
-- ============================================================
USE smart_parking;

DELIMITER $$

-- ------------------------------------------------------------
-- Trigger: Auto-update parking rating after review insert
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_after_review_insert$$
CREATE TRIGGER trg_after_review_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE parking_locations
    SET rating = (
        SELECT ROUND(AVG(rating), 2) 
        FROM reviews 
        WHERE parking_id = NEW.parking_id AND is_approved = 1
    ),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE parking_id = NEW.parking_id AND is_approved = 1
    )
    WHERE id = NEW.parking_id;
END$$

-- ------------------------------------------------------------
-- Trigger: Auto-update parking rating after review update
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_after_review_update$$
CREATE TRIGGER trg_after_review_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE parking_locations
    SET rating = COALESCE((
        SELECT ROUND(AVG(rating), 2) 
        FROM reviews 
        WHERE parking_id = NEW.parking_id AND is_approved = 1
    ), 0),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE parking_id = NEW.parking_id AND is_approved = 1
    )
    WHERE id = NEW.parking_id;
END$$

-- ------------------------------------------------------------
-- Trigger: Auto-update parking rating after review delete
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_after_review_delete$$
CREATE TRIGGER trg_after_review_delete
AFTER DELETE ON reviews
FOR EACH ROW
BEGIN
    UPDATE parking_locations
    SET rating = COALESCE((
        SELECT ROUND(AVG(rating), 2) 
        FROM reviews 
        WHERE parking_id = OLD.parking_id AND is_approved = 1
    ), 0),
    total_reviews = (
        SELECT COUNT(*) 
        FROM reviews 
        WHERE parking_id = OLD.parking_id AND is_approved = 1
    )
    WHERE id = OLD.parking_id;
END$$

-- ------------------------------------------------------------
-- Trigger: Auto-log payment success to audit
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_payment_success_audit$$
CREATE TRIGGER trg_payment_success_audit
AFTER UPDATE ON payments
FOR EACH ROW
BEGIN
    IF NEW.status = 'success' AND OLD.status != 'success' THEN
        INSERT INTO audit_logs (
            user_id, user_type, action, table_name, record_id,
            new_values, status
        ) VALUES (
            NEW.user_id, 'system', 'payment_success', 'payments', NEW.id,
            JSON_OBJECT(
                'amount', NEW.total,
                'method', NEW.method,
                'transaction_id', NEW.transaction_id
            ),
            'success'
        );
    END IF;
END$$

-- ------------------------------------------------------------
-- Trigger: Update user's last_login
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_update_last_login$$
CREATE TRIGGER trg_update_last_login
AFTER INSERT ON audit_logs
FOR EACH ROW
BEGIN
    IF NEW.action = 'login' AND NEW.user_id IS NOT NULL THEN
        UPDATE users SET last_login = NOW() WHERE id = NEW.user_id;
    END IF;
END$$

DELIMITER ;

SELECT 'Triggers created successfully!' AS message;