-- Fix for RFQ Funds Verification Circular Dependency
-- 
-- ISSUE: The trigger trg_block_rfq_without_funds was blocking RFQ creation 
-- when funds_available was not yet verified, but finance couldn't verify 
-- funds until AFTER quotes were received from vendors.
--
-- SOLUTION: Disable the RFQ-blocking check and move funds verification 
-- to the commitment/PO stage when vendors are actually being paid.
--
-- Workflow is now:
-- 1. RFQ Created → Send to Vendors
-- 2. Quotes Received → Procurement reviews and selects
-- 3. Commitment Created → Finance verifies funds at THIS stage
-- 4. PO Created → Funds transfered
-- 5. Invoice Paid → Completion

-- Drop the problematic trigger
DROP TRIGGER IF EXISTS `trg_block_rfq_without_funds`;

-- Recreate with no validation (funds verification moved to commitment stage)
DELIMITER $$
CREATE TRIGGER `trg_block_rfq_without_funds` BEFORE INSERT ON `rfqs` FOR EACH ROW BEGIN
    -- Funds verification moved to commitment stage to avoid circular dependency
    -- RFQ can now be created without pre-verification
END
$$
DELIMITER ;

-- Also allow SUBMITTED status to transition directly to RFQ_LETTER_AVAILABLE
-- This is needed for simplified procurement workflows

-- Verify trigger was recreated
SHOW TRIGGERS LIKE 'trg_block_rfq_without_funds';
