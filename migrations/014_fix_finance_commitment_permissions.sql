-- Migration 014: Fix Finance Officer Missing Permissions
-- =====================================================
-- Finance Officer (role_id=3) is missing: create_commitment, verify_funds, upload_commitment
-- Procurement Officer (role_id=2) already has: create_commitment, upload_commitment
-- This migration ensures both roles can create commitments at QUOTE_APPROVED stage.

-- Add missing permissions to Finance Officer (role_id = 3)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'create_commitment',
    'verify_funds',
    'upload_commitment'
);

-- Ensure verify_funds permission exists
INSERT IGNORE INTO permissions (name, description)
VALUES ('verify_funds', 'Verify fund availability for procurement requests');
