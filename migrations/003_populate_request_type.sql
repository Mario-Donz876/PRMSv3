-- Populate petty cash requests
UPDATE procurement_requests pr
SET pr.request_type = 'PETTY_CASH'
WHERE EXISTS (SELECT 1 FROM petty_cash_disbursements pcd WHERE pcd.request_id = pr.request_id)
AND (pr.request_type IS NULL OR pr.request_type = '');

-- Populate reimbursement requests
UPDATE procurement_requests pr
SET pr.request_type = 'REIMBURSEMENT'
WHERE EXISTS (SELECT 1 FROM pre_authorizations pa WHERE pa.request_id = pr.request_id)
AND (pr.request_type IS NULL OR pr.request_type = '');

-- Default remaining to REGULAR
UPDATE procurement_requests
SET request_type = 'REGULAR'
WHERE request_type IS NULL OR request_type = '';