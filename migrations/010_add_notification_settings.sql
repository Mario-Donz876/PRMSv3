-- Add notification settings to system_config
INSERT INTO system_config (config_key, config_value, description, created_at)
VALUES 
    ('enable_notifications', '1', 'Enable/disable email notifications (1=enabled, 0=disabled)', NOW())
ON DUPLICATE KEY UPDATE config_value = VALUES(config_value);
