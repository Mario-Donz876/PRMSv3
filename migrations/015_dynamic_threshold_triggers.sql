-- =====================================================================
-- Migration 015: Dynamic Threshold Triggers
-- =====================================================================
-- Updates the requires_rfq triggers to read the procurement threshold
-- from system_config instead of using a hardcoded 500000 value.
-- This ensures that when the threshold is changed in Admin → Settings,
-- the trigger logic adapts automatically.
-- =====================================================================

-- Drop old triggers
DROP TRIGGER IF EXISTS `trg_auto_set_requires_rfq`;
DROP TRIGGER IF EXISTS `trg_auto_update_requires_rfq`;

-- Recreate INSERT trigger reading threshold from system_config
DELIMITER $$
CREATE TRIGGER `trg_auto_set_requires_rfq` BEFORE INSERT ON `procurement_requests` FOR EACH ROW
BEGIN
    DECLARE v_threshold DECIMAL(15,2) DEFAULT 500000.00;

    -- Read threshold dynamically from system_config
    SELECT CAST(config_value AS DECIMAL(15,2))
      INTO v_threshold
      FROM system_config
     WHERE config_key = 'direct_procurement_threshold'
     LIMIT 1;

    -- PETTY_CASH and REIMBURSEMENT never require RFQ (direct workflows)
    -- ALL REGULAR requests now require RFQ regardless of threshold,
    -- but the threshold determines simplified vs full evaluation.
    IF NEW.request_type IN ('PETTY_CASH', 'REIMBURSEMENT') THEN
        SET NEW.requires_rfq = 0;
    ELSE
        -- All regular procurement requires RFQ
        SET NEW.requires_rfq = 1;
    END IF;
END
$$
DELIMITER ;

-- Recreate UPDATE trigger reading threshold from system_config
DELIMITER $$
CREATE TRIGGER `trg_auto_update_requires_rfq` BEFORE UPDATE ON `procurement_requests` FOR EACH ROW
BEGIN
    DECLARE v_threshold DECIMAL(15,2) DEFAULT 500000.00;

    -- Read threshold dynamically from system_config
    SELECT CAST(config_value AS DECIMAL(15,2))
      INTO v_threshold
      FROM system_config
     WHERE config_key = 'direct_procurement_threshold'
     LIMIT 1;

    -- PETTY_CASH and REIMBURSEMENT never require RFQ (direct workflows)
    IF NEW.request_type IN ('PETTY_CASH', 'REIMBURSEMENT') THEN
        SET NEW.requires_rfq = 0;
    ELSE
        -- All regular procurement requires RFQ
        SET NEW.requires_rfq = 1;
    END IF;
END
$$
DELIMITER ;
