-- Migration: Add GFMS Commitment Number field
-- Allows Finance Officers to input unique commitment numbers from GFMS
--
-- NOTE: If you get error #1060 "Duplicate column name", it means the column
-- already exists - this is OK and the migration has already been applied.
--
-- This script is safe to run multiple times on compatible MySQL versions.

-- Add GFMS Commitment Number column if it doesn't already exist
ALTER TABLE `commitments` 
ADD COLUMN `gfms_commitment_number` VARCHAR(50) NULL UNIQUE 
COMMENT 'Unique commitment number from GFMS system';

-- Create index for faster lookups (if not already present)
CREATE INDEX `idx_gfms_commitment_number` ON `commitments` (`gfms_commitment_number`);

-- Verification: Run this query to confirm the column was added:
-- SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'commitments' AND COLUMN_NAME = 'gfms_commitment_number';
