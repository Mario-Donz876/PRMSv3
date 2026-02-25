-- Migration: Add document upload fields to commitments and purchase_orders
-- Date: 2026-02-19
-- Purpose: Enable users to upload commitment and PO documents directly from the forms

-- ===================================
-- ALTER TABLE commitments
-- ===================================
ALTER TABLE commitments ADD COLUMN IF NOT EXISTS document_path VARCHAR(255) NULL COMMENT 'Path to uploaded commitment document from GFMS' AFTER gfms_commitment_number;

-- Add index for potential file retrieval queries
CREATE INDEX IF NOT EXISTS idx_commitments_document_path ON commitments(document_path);

-- ===================================
-- ALTER TABLE purchase_orders (po)
-- ===================================
ALTER TABLE purchase_orders ADD COLUMN IF NOT EXISTS document_path VARCHAR(255) NULL COMMENT 'Path to uploaded PO document from GFMS' AFTER gfms_po_number;

-- Add index for potential file retrieval queries
CREATE INDEX IF NOT EXISTS idx_po_document_path ON purchase_orders(document_path);

-- ===================================
-- AUDIT LOG ENTRIES
-- ===================================
-- Log this migration in audit_log for compliance
INSERT INTO audit_log (table_name, record_id, action, notes)
VALUES ('DATABASE', 0, 'SCHEMA_CHANGE', 'Added document_path fields to commitments and purchase_orders tables for GFMS integration');

-- ===================================
-- VERIFICATION QUERIES
-- ===================================
-- Verify the new columns exist:
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'commitments' AND COLUMN_NAME = 'document_path';
-- 
-- SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_NAME = 'purchase_orders' AND COLUMN_NAME = 'document_path';
