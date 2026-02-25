-- Fix for missing request_type column in procurement_requests
-- This script adds the request_type column to support the RFQ workflow logic
-- which determines the next status based on request type

-- Add request_type column if it doesn't already exist
-- Note: If column exists, this will produce an error "Duplicate column name"
-- which is safe to ignore - it means the column is already present
ALTER TABLE `procurement_requests`
ADD COLUMN `request_type` enum('REGULAR','REIMBURSEMENT','PETTY_CASH') NOT NULL DEFAULT 'REGULAR';

-- Create index for query optimization
-- This improves performance for queries filtering by request_type and status
CREATE INDEX IF NOT EXISTS `idx_reimb_request_type` 
ON `procurement_requests`(`request_type`, `status`, `created_at` DESC);

-- All existing requests default to REGULAR procurement with RFQ requirement
-- No data update needed - DEFAULT 'REGULAR' applies to all existing rows
