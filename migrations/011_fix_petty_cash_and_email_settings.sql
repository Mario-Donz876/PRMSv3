-- Update Petty Cash Limit and Procurement Threshold to Correct Values
-- Run this query to fix both values immediately

UPDATE `system_config` 
SET `config_value` = '5000', `description` = 'Maximum amount for petty cash procurement without formal approval (JMD)'
WHERE `config_key` = 'petty_cash_limit';

UPDATE `system_config` 
SET `config_value` = '500000', `description` = 'Threshold value for direct procurement eligibility (JMD)'
WHERE `config_key` = 'direct_procurement_threshold';

-- Ensure notifications are enabled
INSERT INTO `system_config` (`config_key`, `config_value`, `description`)
VALUES ('enable_notifications', '1', 'Enable/disable email notifications (1=enabled, 0=disabled)')
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

-- Verify the changes
SELECT `config_key`, `config_value`, `description` 
FROM `system_config` 
WHERE `config_key` IN ('petty_cash_limit', 'direct_procurement_threshold', 'enable_notifications');
