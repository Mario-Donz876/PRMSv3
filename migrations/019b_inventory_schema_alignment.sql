-- ============================================================
-- Migration 019b: Align inventory schema with application code
-- Adds columns expected by PHP that were missing from 019
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. inv_goods_received — add columns PHP expects
-- ============================================================
ALTER TABLE `inv_goods_received`
  ADD COLUMN IF NOT EXISTS `supplier_name` varchar(200) DEFAULT NULL AFTER `supplier_vendor_id`,
  ADD COLUMN IF NOT EXISTS `delivery_note_number` varchar(50) DEFAULT NULL AFTER `donor_source`,
  ADD COLUMN IF NOT EXISTS `invoice_number` varchar(50) DEFAULT NULL AFTER `delivery_note_number`,
  ADD COLUMN IF NOT EXISTS `receiving_location_id` int(11) DEFAULT NULL AFTER `invoice_number`,
  ADD COLUMN IF NOT EXISTS `is_non_exchange_transaction` tinyint(1) DEFAULT 0 AFTER `receiving_location_id`;

-- Expand status enum to include values used by PHP
ALTER TABLE `inv_goods_received`
  MODIFY COLUMN `status` enum('DRAFT','RECEIVED','INSPECTED','ACCEPTED','PARTIAL','REJECTED','QUARANTINE','COMPLETED','INSPECTION') DEFAULT 'DRAFT';

-- ============================================================
-- 2. inv_grn_items — add separate lot/batch and condition
-- ============================================================
ALTER TABLE `inv_grn_items`
  ADD COLUMN IF NOT EXISTS `lot_number` varchar(50) DEFAULT NULL AFTER `batch_lot_number`,
  ADD COLUMN IF NOT EXISTS `batch_number` varchar(50) DEFAULT NULL AFTER `lot_number`,
  ADD COLUMN IF NOT EXISTS `condition_on_receipt` varchar(30) DEFAULT 'GOOD' AFTER `quality_notes`;

-- ============================================================
-- 3. inv_issues — add columns PHP expects
-- ============================================================
ALTER TABLE `inv_issues`
  ADD COLUMN IF NOT EXISTS `requisition_number` varchar(30) DEFAULT NULL AFTER `requisition_id`,
  ADD COLUMN IF NOT EXISTS `from_location_id` int(11) DEFAULT NULL AFTER `issued_to_building_room`,
  ADD COLUMN IF NOT EXISTS `cost_centre` varchar(50) DEFAULT NULL AFTER `from_location_id`;

-- Expand status enum
ALTER TABLE `inv_issues`
  MODIFY COLUMN `status` enum('DRAFT','PENDING_APPROVAL','APPROVED','ISSUED','PARTIAL','CANCELLED','COMPLETED') DEFAULT 'DRAFT';

-- Rename issued_to_department to match PHP's issued_to_department_id
-- (Can't rename if column doesn't exist in some environments, so add alias)
ALTER TABLE `inv_issues`
  ADD COLUMN IF NOT EXISTS `issued_to_department_id` int(11) DEFAULT NULL AFTER `issued_to_department`;

-- ============================================================
-- 4. inv_issue_items — add columns PHP expects
-- ============================================================
ALTER TABLE `inv_issue_items`
  ADD COLUMN IF NOT EXISTS `quantity_requested` decimal(14,4) DEFAULT NULL AFTER `item_id`,
  ADD COLUMN IF NOT EXISTS `lot_number` varchar(50) DEFAULT NULL AFTER `batch_lot_number`,
  ADD COLUMN IF NOT EXISTS `batch_number` varchar(50) DEFAULT NULL AFTER `lot_number`;

-- ============================================================
-- 5. inv_disposals — add columns PHP expects
-- ============================================================
ALTER TABLE `inv_disposals`
  ADD COLUMN IF NOT EXISTS `survey_notes` text DEFAULT NULL AFTER `survey_assessment`,
  ADD COLUMN IF NOT EXISTS `survey_completed_by` int(11) DEFAULT NULL AFTER `survey_notes`,
  ADD COLUMN IF NOT EXISTS `survey_completed_at` datetime DEFAULT NULL AFTER `survey_completed_by`,
  ADD COLUMN IF NOT EXISTS `actual_proceeds` decimal(14,2) DEFAULT 0.00 AFTER `proceeds_amount`,
  ADD COLUMN IF NOT EXISTS `completed_at` datetime DEFAULT NULL AFTER `evidence_notes`;

-- Expand status enum
ALTER TABLE `inv_disposals`
  MODIFY COLUMN `status` enum('DRAFT','RECOMMENDED','PENDING_APPROVAL','APPROVED','COMMITTEE_REVIEW','COMPLETED','REJECTED','CANCELLED','PENDING_SURVEY','IN_PROGRESS') DEFAULT 'DRAFT';

-- Expand disposal_method enum
ALTER TABLE `inv_disposals`
  MODIFY COLUMN `disposal_method` enum('DESTRUCTION','AUCTION','TRANSFER','DONATION','RETURN_TO_SUPPLIER','SCRAP','OTHER','SALE','RECYCLING','TRADE_IN','CANNIBALIZATION') NOT NULL;

-- ============================================================
-- 6. inv_disposal_items — add estimated_value column
-- ============================================================
ALTER TABLE `inv_disposal_items`
  ADD COLUMN IF NOT EXISTS `estimated_value` decimal(14,2) DEFAULT 0.00 AFTER `total_value`;

-- ============================================================
-- 7. inv_stock_counts — add count_lead and expand enum
-- ============================================================
ALTER TABLE `inv_stock_counts`
  ADD COLUMN IF NOT EXISTS `count_lead` int(11) DEFAULT NULL AFTER `conducted_by`,
  ADD COLUMN IF NOT EXISTS `completed_at` datetime DEFAULT NULL AFTER `notes`;

-- Expand count_type enum to include ANNUAL
ALTER TABLE `inv_stock_counts`
  MODIFY COLUMN `count_type` enum('FULL','CYCLE','SPOT','ANNUAL') DEFAULT 'FULL';

-- ============================================================
-- 8. inv_stock_count_items — add variance_quantity, make counted_quantity nullable
-- ============================================================
ALTER TABLE `inv_stock_count_items`
  MODIFY COLUMN `counted_quantity` decimal(14,4) DEFAULT NULL;

ALTER TABLE `inv_stock_count_items`
  ADD COLUMN IF NOT EXISTS `variance_quantity` decimal(14,4) DEFAULT NULL AFTER `variance`;

-- ============================================================
-- 9. inv_items — add unit_cost alias column
-- ============================================================
ALTER TABLE `inv_items`
  ADD COLUMN IF NOT EXISTS `unit_cost` decimal(14,2) DEFAULT 0.00 AFTER `average_cost`;

-- ============================================================
-- 10. inv_locations — add site_name alias column
-- ============================================================
ALTER TABLE `inv_locations`
  ADD COLUMN IF NOT EXISTS `site_name` varchar(100) DEFAULT NULL AFTER `site_campus`;

-- Copy data from site_campus to site_name for any existing rows
UPDATE `inv_locations` SET `site_name` = `site_campus` WHERE `site_name` IS NULL AND `site_campus` IS NOT NULL;

-- ============================================================
-- 11. inv_transactions — expand enum
-- ============================================================
ALTER TABLE `inv_transactions`
  MODIFY COLUMN `transaction_type` enum('RECEIVE','ISSUE','TRANSFER_OUT','TRANSFER_IN','ADJUSTMENT_GAIN','ADJUSTMENT_LOSS','DISPOSAL','COUNT_ADJUST','RETURN','RECEIPT','ADJUSTMENT_IN','ADJUSTMENT_OUT') NOT NULL;

-- ============================================================
-- 12. inv_transfers — add missing columns PHP expects
-- ============================================================
ALTER TABLE `inv_transfers`
  ADD COLUMN IF NOT EXISTS `dispatched_at` datetime DEFAULT NULL AFTER `notes`;

-- ============================================================
-- 13. inv_transfer_items — add quantity_received for receiving workflow
-- ============================================================
ALTER TABLE `inv_transfer_items`
  ADD COLUMN IF NOT EXISTS `quantity_received` decimal(14,4) DEFAULT NULL AFTER `quantity`;

-- ============================================================
-- 14. inv_adjustment_items — add system/actual/variance qty columns
-- ============================================================
ALTER TABLE `inv_adjustment_items`
  ADD COLUMN IF NOT EXISTS `quantity_system` decimal(14,4) DEFAULT NULL AFTER `item_id`,
  ADD COLUMN IF NOT EXISTS `quantity_actual` decimal(14,4) DEFAULT NULL AFTER `quantity_system`,
  ADD COLUMN IF NOT EXISTS `quantity_variance` decimal(14,4) DEFAULT NULL AFTER `quantity_actual`;

-- ============================================================
-- 15. Fix permission name mismatches
--     PHP uses these names but they weren't seeded in 019
-- ============================================================
INSERT INTO `permissions` (`name`, `description`) VALUES
('dispose_stock', 'Create disposal/write-off requests'),
('adjust_stock', 'Create stock adjustments'),
('transfer_stock', 'Create stock transfers'),
('view_inventory_reports', 'View inventory reports')
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- Grant to HOD (role 4): approve + operational permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, id FROM `permissions` WHERE `name` IN ('view_inventory_reports', 'dispose_stock');

-- Grant to Procurement Officer (role 2)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` WHERE `name` IN ('transfer_stock', 'adjust_stock', 'dispose_stock', 'view_inventory_reports');

-- Grant to Finance Officer (role 3)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE `name` IN ('view_inventory_reports');

-- Grant to Requestor (role 12)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 12, id FROM `permissions` WHERE `name` IN ('view_inventory_reports');

SET FOREIGN_KEY_CHECKS = 1;
