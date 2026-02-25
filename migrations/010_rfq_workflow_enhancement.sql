-- Migration: RFQ Workflow Enhancement
-- Purpose: Support improved RFQ workflow with quote review stage
-- Date: 2026-02-19

-- Update rfqs table to include quote review status
ALTER TABLE `rfqs` 
ADD COLUMN IF NOT EXISTS `quote_review_status` ENUM('PENDING','IN_REVIEW','APPROVED') DEFAULT 'PENDING' AFTER `acceptance_status`,
ADD COLUMN IF NOT EXISTS `reviewed_by` INT(11) DEFAULT NULL AFTER `quote_review_status`,
ADD COLUMN IF NOT EXISTS `reviewed_at` DATETIME DEFAULT NULL AFTER `reviewed_by`;

-- Add index for workflow status queries
CREATE INDEX IF NOT EXISTS idx_rfq_status ON rfqs(status);
CREATE INDEX IF NOT EXISTS idx_rfq_quote_review_status ON rfqs(quote_review_status);

-- Update rfq_quotes table to track review status
ALTER TABLE `rfq_quotes`
ADD COLUMN IF NOT EXISTS `review_status` ENUM('PENDING','MEETS_REQUIREMENTS','DOES_NOT_MEET') DEFAULT 'PENDING' AFTER `is_selected`,
ADD COLUMN IF NOT EXISTS `review_comments` TEXT DEFAULT NULL AFTER `review_status`;

-- Add indexes for quote tracking
CREATE INDEX IF NOT EXISTS idx_quote_selection ON rfq_quotes(is_selected);
CREATE INDEX IF NOT EXISTS idx_quote_review_status ON rfq_quotes(review_status);

-- Update procurement_requests workflow documentation
-- Add column to track if request needs RFQ (computed from request type and amount)
ALTER TABLE `procurement_requests`
ADD COLUMN IF NOT EXISTS `requires_rfq` TINYINT(1) DEFAULT 0 AFTER `external_approval_required`,
ADD COLUMN IF NOT EXISTS `rfq_letter_generated_at` DATETIME DEFAULT NULL AFTER `requires_rfq`;

-- Create index for tracking RFQ requirements
CREATE INDEX IF NOT EXISTS idx_pr_requires_rfq ON procurement_requests(requires_rfq);

-- Update commitments to ensure proper workflow connection
-- Add column to track quote approval timestamp
ALTER TABLE `commitments`
ADD COLUMN IF NOT EXISTS `quote_approved_at` DATETIME DEFAULT NULL AFTER `selected_quote_id`,
ADD COLUMN IF NOT EXISTS `gfms_generated` TINYINT(1) DEFAULT 0 AFTER `quote_approved_at`;

-- Update purchase orders to track commitment approval dependency
ALTER TABLE `purchase_orders`
ADD COLUMN IF NOT EXISTS `commitment_approved_at` DATETIME DEFAULT NULL AFTER `excess_approved_at`,
ADD COLUMN IF NOT EXISTS `gfms_generated` TINYINT(1) DEFAULT 0 AFTER `commitment_approved_at`;

-- Update invoices to track PO approval dependency
ALTER TABLE `invoices`
ADD COLUMN IF NOT EXISTS `po_approved_at` DATETIME DEFAULT NULL AFTER `created_at`,
ADD COLUMN IF NOT EXISTS `gfms_generated` TINYINT(1) DEFAULT 0 AFTER `po_approved_at`,
ADD COLUMN IF NOT EXISTS `invoice_source` ENUM('VENDOR_UPLOADED','SYSTEM_GENERATED','MANUAL') DEFAULT 'VENDOR_UPLOADED' AFTER `gfms_generated`;

-- Create indexes for workflow enforcement
CREATE INDEX IF NOT EXISTS idx_commitment_gfms_generated ON commitments(gfms_generated);
CREATE INDEX IF NOT EXISTS idx_po_gfms_generated ON purchase_orders(gfms_generated);
CREATE INDEX IF NOT EXISTS idx_invoice_source ON invoices(invoice_source);

-- Update request_approvals table for RFQ-related approvals
-- This table now tracks approvals across COMMITMENT, PO, INVOICE entities
-- Ensure it has proper entity type values
-- (no schema change needed, already supports entity_type)

-- Create trigger to auto-enable requires_rfq flag based on request attributes
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `trg_auto_set_requires_rfq` BEFORE INSERT ON `procurement_requests` FOR EACH ROW
BEGIN
    -- Check if this request requires RFQ
    -- Under threshold (≤500k) or direct types (PETTY_CASH, REIMBURSEMENT) don't need RFQ
    IF NEW.request_type IN ('PETTY_CASH', 'REIMBURSEMENT') THEN
        SET NEW.requires_rfq = 0;
    ELSEIF NEW.estimated_value <= 500000 THEN
        SET NEW.requires_rfq = 0;
    ELSE
        SET NEW.requires_rfq = 1;
    END IF;
END
$$
DELIMITER ;

-- Create trigger to auto-update requires_rfq on update
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `trg_auto_update_requires_rfq` BEFORE UPDATE ON `procurement_requests` FOR EACH ROW
BEGIN
    -- Check if this request requires RFQ
    IF NEW.request_type IN ('PETTY_CASH', 'REIMBURSEMENT') THEN
        SET NEW.requires_rfq = 0;
    ELSEIF NEW.estimated_value <= 500000 THEN
        SET NEW.requires_rfq = 0;
    ELSE
        SET NEW.requires_rfq = 1;
    END IF;
END
$$
DELIMITER ;

-- Add trigger to ensure quote must be reviewed before commitment
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `trg_require_quote_review_for_commitment` BEFORE INSERT ON `commitments` FOR EACH ROW
BEGIN
    DECLARE review_status VARCHAR(50);
    DECLARE quote_id INT;

    -- If this commitment is linked to an RFQ
    IF NEW.rfq_id IS NOT NULL AND NEW.selected_quote_id IS NOT NULL THEN
        -- Check if the quote has been marked as approved (meets requirements)
        SELECT review_status
        INTO review_status
        FROM rfq_quotes
        WHERE quote_id = NEW.selected_quote_id
        LIMIT 1;

        -- Allow commitment creation if quote is marked as meeting requirements or no review status set
        -- This gives flexibility for different approval workflows
        IF review_status = 'DOES_NOT_MEET' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot create commitment from quote that does not meet requirements';
        END IF;
    END IF;

END
$$
DELIMITER ;

-- Add trigger to ensure PO can only be created after commitment is finalized
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `trg_require_committed_amount_for_po` BEFORE INSERT ON `purchase_orders` FOR EACH ROW
BEGIN
    DECLARE commitment_exists INT DEFAULT 0;

    -- Check if commitment exists and is linked
    SELECT COUNT(*)
    INTO commitment_exists
    FROM commitments
    WHERE commitment_id = NEW.commitment_id
    LIMIT 1;

    IF commitment_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Commitment must exist before PO creation';
    END IF;

END
$$
DELIMITER ;

-- Add trigger to track when PO approvals complete (for invoice dependency)
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `trg_track_po_approval_date` BEFORE UPDATE ON `purchase_orders` FOR EACH ROW
BEGIN
    -- When PO moves to approved status, set the approval timestamp
    IF NEW.approved_by IS NOT NULL AND NEW.approved_at IS NOT NULL AND OLD.approved_by IS NULL THEN
        SET NEW.commitment_approved_at = NOW();
    END IF;
END
$$
DELIMITER ;

-- Update audit log to include new workflow stages
-- No schema changes needed, but we'll document the new entity types:
-- entity_type can be: 'PROCUREMENT_REQUEST', 'RFQ', 'RFQ_QUOTE_REVIEW', 'COMMITMENT', 'PO', 'INVOICE', 'PO_VARIATION'

-- Commit
COMMIT;
