-- ============================================================================
-- Migration 021: Property Management Officer Role
-- ============================================================================
-- Purpose:
--   1. Add the "Property Management Officer" role (id 13).
--   2. Ensure all inventory-module permissions exist (safe INSERT IGNORE /
--      ON DUPLICATE KEY so this is idempotent alongside migrations 019, 019b,
--      019c which may or may not have run first).
--   3. Grant the new role full access to all inventory modules.
--
-- Date: 2026-05-12
-- ============================================================================

-- ═══════════════════════════════════════════════════════════
-- STEP 1: Insert the new role
-- ═══════════════════════════════════════════════════════════

INSERT IGNORE INTO `roles` (`id`, `name`, `description`) VALUES
(13, 'Property Management Officer', 'Manages physical property, stock, and inventory operations');

-- ═══════════════════════════════════════════════════════════
-- STEP 2: Ensure all inventory permissions exist
--         (ON DUPLICATE KEY is safe — keeps existing rows)
-- ═══════════════════════════════════════════════════════════

INSERT INTO `permissions` (`name`, `description`) VALUES
-- Core inventory views
('view_inventory',              'View inventory items and stock levels'),
('view_inv_reports',            'View inventory reports'),
('view_inventory_reports',      'View inventory reports'),
-- Item & location master data
('manage_inventory_items',      'Create and edit inventory item master data'),
('manage_inventory_locations',  'Manage storage locations'),
-- Requisitions
('submit_stock_requisition',    'Submit stock requisitions'),
('approve_stock_requisition',   'Approve stock requisitions'),
-- Receiving & inspection
('receive_goods',               'Record goods received notes'),
('inspect_goods',               'Inspect goods on GRN'),
-- Issuing
('issue_stock',                 'Issue stock from stores'),
('approve_stock_issue',         'Approve stock issues for controlled items'),
('approve_issue',               'Approve stock issue vouchers'),
-- Transfers
('create_transfer',             'Create stock transfers'),
('transfer_stock',              'Create stock transfers'),
('approve_transfer',            'Approve stock transfers'),
-- Adjustments
('create_adjustment',           'Create stock adjustments'),
('adjust_stock',                'Create stock adjustments'),
('approve_adjustment',          'Approve stock adjustments'),
-- Disposals & write-downs
('create_disposal',             'Create disposal/write-off requests'),
('dispose_stock',               'Create disposal/write-off requests'),
('approve_disposal',            'Approve disposals and write-offs'),
('manage_write_downs',          'Create and approve write-downs (NRV)'),
-- Stock counts
('conduct_stock_count',         'Conduct physical stock counts'),
('approve_stock_count',         'Approve completed stock counts'),
-- Quality / compliance modules
('manage_quarantine',           'Move stock into/out of quarantine'),
('manage_recalls',              'Initiate and manage recall/withdrawal'),
('manage_returns',              'Create and manage return-to-supplier'),
('approve_return',              'Approve return-to-supplier requests'),
('manage_incidents',            'Report and manage incident/loss reports'),
-- Administration
('manage_inv_roles',            'Manage inventory user roles'),
('manage_inv_delegations',      'Manage inventory delegations'),
('inventory_admin',             'Full inventory administration access'),
('manage_fiscal_periods',       'Open/close fiscal periods')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- ═══════════════════════════════════════════════════════════
-- STEP 3: Assign all inventory permissions to role 13
-- ═══════════════════════════════════════════════════════════

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 13, id FROM `permissions` WHERE `name` IN (
    -- Core views
    'view_inventory',
    'view_inv_reports',
    'view_inventory_reports',
    -- Item & location management
    'manage_inventory_items',
    'manage_inventory_locations',
    -- Requisitions
    'submit_stock_requisition',
    'approve_stock_requisition',
    -- Receiving & inspection
    'receive_goods',
    'inspect_goods',
    -- Issuing
    'issue_stock',
    'approve_stock_issue',
    'approve_issue',
    -- Transfers
    'create_transfer',
    'transfer_stock',
    'approve_transfer',
    -- Adjustments
    'create_adjustment',
    'adjust_stock',
    'approve_adjustment',
    -- Disposals & write-downs
    'create_disposal',
    'dispose_stock',
    'approve_disposal',
    'manage_write_downs',
    -- Stock counts
    'conduct_stock_count',
    'approve_stock_count',
    -- Quality / compliance
    'manage_quarantine',
    'manage_recalls',
    'manage_returns',
    'approve_return',
    'manage_incidents',
    -- Administration
    'manage_inv_roles',
    'manage_inv_delegations',
    'inventory_admin',
    'manage_fiscal_periods',
    -- Procurement visibility (needed to match GRNs against POs)
    'view_requests',
    'view_purchase_orders',
    'view_vendors',
    -- Print & export
    'print_request',
    'export_requests'
);

-- ═══════════════════════════════════════════════════════════
-- STEP 4: Verification
-- ═══════════════════════════════════════════════════════════

SELECT id, name, description FROM `roles` WHERE id = 13;

SELECT COUNT(*) AS inventory_permissions_assigned
FROM `role_permissions` rp
JOIN `permissions` p ON rp.permission_id = p.id
WHERE rp.role_id = 13;

SELECT p.name, p.description
FROM `role_permissions` rp
JOIN `permissions` p ON rp.permission_id = p.id
WHERE rp.role_id = 13
ORDER BY p.name;
