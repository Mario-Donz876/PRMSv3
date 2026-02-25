-- Migration: Add GFMS PO Number field
-- Allows Finance Officers/Procurement to input unique PO numbers from GFMS
--
-- NOTE: If you get error #1060 "Duplicate column name", it means the column
-- already exists - this is OK and the migration has already been applied.
--
-- This script is safe to run multiple times on compatible MySQL versions.

-- Add GFMS PO Number column if it doesn't already exist
ALTER TABLE `purchase_orders` 
ADD COLUMN `gfms_po_number` VARCHAR(50) NULL UNIQUE 
COMMENT 'Unique PO number from GFMS system';

-- Create index for faster lookups (if not already present)
CREATE INDEX `idx_gfms_po_number` ON `purchase_orders` (`gfms_po_number`);

-- Verification: Run this query to confirm the column was added:
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'gfms_po_number';
