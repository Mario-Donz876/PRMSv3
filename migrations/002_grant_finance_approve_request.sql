-- Migration: Grant 'approve_request' permission to Finance Officer role
-- Fixes: Finance Officer unable to approve requests at HOD_APPROVED stage
-- The Finance Officer (role_id=3) needs 'approve_request' (permission_id=3)
-- to access approve_finance.php and see the approval button in view.php.

INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (3, 3);
