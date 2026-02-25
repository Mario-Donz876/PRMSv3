<?php
$REQUIRE_PERMISSION = 'view_requests';

require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/workflow.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';

/* Self-heal: seed any missing approval chains for SUBMITTED requests */
ensureApprovalChainsExist($pdo);

/* ═════════════════════════════════════════════════════════
   SECTION 1: Analytical & Advisory Branch Approvals
   DGC is sole approver for Analytical & Advisory branch (all amounts)
═════════════════════════════════════════════════════════ */

/* Requests awaiting DGC approval (Analytical & Advisory branch) */
$stmt = $pdo->prepare("
    SELECT 
        pr.request_id,
        pr.request_number,
        pr.estimated_value,
        pr.currency,
        pr.created_at,
        b.branch_name,
        ra.role as required_role,
        ra.stage_order
    FROM procurement_requests pr
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    LEFT JOIN request_approvals ra ON pr.request_id = ra.request_id AND ra.status = 'pending'
    WHERE UPPER(pr.status) = 'SUBMITTED'
    AND b.branch_id = 6  /* Analytical & Advisory Branch */
    AND ra.role = 'Deputy Government Chemist'
    AND ra.status = 'pending'
    ORDER BY pr.created_at ASC
");
$stmt->execute();
$branchRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Summary Counts */
$branchPending = count($branchRequests);

/* ═════════════════════════════════════════════════════════
   SECTION 2: All other pending DGC approvals
   (non branch-6 approvals if any come through the chain)
═════════════════════════════════════════════════════════ */

/* Pending DGC request approvals (excludes branch-6 already shown above) */
$stmt = $pdo->prepare("
    SELECT pr.request_id, pr.request_number, pr.estimated_value,
           pr.currency,
           pr.status as request_status, pr.external_approval_required,
           pr.created_at, b.branch_name, b.branch_id
    FROM request_approvals ra
    JOIN procurement_requests pr ON ra.request_id = pr.request_id AND ra.entity_type = 'REQUEST'
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    WHERE ra.entity_type = 'REQUEST'
      AND ra.role = 'Deputy Government Chemist'
      AND ra.status = 'pending'
      AND UPPER(pr.status) NOT IN ('DECLINED', 'COMPLETED', 'AWARDED')
      AND b.branch_id != 6
    ORDER BY pr.created_at ASC
");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Pending RFQ GC approvals (over-threshold RFQs at COMMITTEE_RECOMMENDED) */
$rfqGcStmt = $pdo->prepare("
    SELECT r.rfq_id, r.rfq_number, pr.request_id, pr.request_number,
           pr.estimated_value, pr.currency, pr.status as request_status, b.branch_name
    FROM rfqs r
    JOIN procurement_requests pr ON r.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    WHERE pr.status = 'COMMITTEE_RECOMMENDED'
      AND r.status != 'AWARDED'
    ORDER BY pr.created_at ASC
");
$rfqGcStmt->execute();
$rfqGcItems = $rfqGcStmt->fetchAll(PDO::FETCH_ASSOC);

/* Pending commitment approvals for DGC — no longer needed (auto-approved) */
$pendingCommitments = [];

/* Pending PO approvals for DGC — no longer needed (auto-approved) */
$pendingPOs = [];

$totalPending = $branchPending + count($requests) + count($rfqGcItems);
?>


<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
          <span style="font-size: 1.5em; margin-right: 1rem;">🏛️</span>
            <h4 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #333;">Deputy Government Chemist <span style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">Dashboard</span></h4>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 2px solid #e0e0e0;">
            <a href="/procurement/list.php" style="background: white; border: 1px solid #667eea; color: #667eea; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                <i class="bi bi-list-task" style="margin-right: 0.5rem;"></i>All Requests
            </a>
            <a href="/commitments/list.php" style="background: white; border: 1px solid #fa709a; color: #fa709a; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                <i class="bi bi-pin-angle" style="margin-right: 0.5rem;"></i>Commitments
            </a>
            <a href="/po/list.php" style="background: white; border: 1px solid #4facfe; color: #4facfe; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                <i class="bi bi-file-earmark-text" style="margin-right: 0.5rem;"></i>Purchase Orders
            </a>
            <a href="/dashboard/approval_queue.php" style="background: linear-gradient(135deg, #f5576c 0%, #ff6f91 100%); color: white; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(245, 87, 108, 0.3);">
                <i class="bi bi-clock-history" style="margin-right: 0.5rem;"></i>Approval Queue
            </a>
        </div>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- SECTION 1: Branch-Based Approvals (Analytical & Advisory) -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div style="margin-bottom: 1.5rem;">
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">📊 Analytical & Advisory Approvals</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= $branchPending ?></h3>
                <small style="display: block; margin-top: 0.5rem; opacity: 0.9;">Analytical & Advisory Branch Pending</small>
            </div>
        </div>

        <?php if ($branchPending > 0): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">📋 Analytical & Advisory Branch Approvals</h6>
            </div>
            <p style="color: #777; margin-bottom: 1rem; font-size: 0.875rem;">
                ℹ️ As Deputy Government Chemist, you are the sole approver for Analytical & Advisory branch requests.
            </p>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Value</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Date Submitted</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($branchRequests as $r): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($r['request_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #666;"><?= htmlspecialchars($r['currency'] ?? 'JMD') ?> <?= number_format($r['estimated_value'],2) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="/procurement/view.php?id=<?= $r['request_id'] ?>" style="background: white; border: 1px solid #4facfe; color: #4facfe; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block; margin-right: 0.5rem; transition: all 0.3s ease;">View</a>
                                    <a href="/procurement/approve.php?id=<?= $r['request_id'] ?>" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block; transition: transform 0.3s ease;">Approve</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- ═══════════════════════════════════════════════════════ -->
        <!-- SECTION 2: Pending DGC Approvals (all types)           -->
        <!-- ═══════════════════════════════════════════════════════ -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">Total Pending</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= $totalPending ?></h3>
            </div>
            <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">Requests</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= count($requests) ?></h3>
            </div>
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">RFQ GC Approvals</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= count($rfqGcItems) ?></h3>
            </div>
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">Branch Approvals</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= $branchPending ?></h3>
            </div>
        </div>

        <!-- Pending Request Approvals for DGC -->
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">📋 Pending Request Approvals <span style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($requests) ?></span></h6>
            </div>
            <?php if (empty($requests)): ?>
                <div style="text-align: center; color: #999; padding: 2rem 0;"><span style="font-size: 1.5em;">✅</span><br><span style="display: block; margin-top: 0.5rem;">No pending request approvals</span></div>
            <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Branch</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Value</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Date</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($r['request_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($r['branch_name'] ?? 'N/A') ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; color: #666;"><?= htmlspecialchars($r['currency'] ?? 'JMD') ?> <?= number_format($r['estimated_value'], 2) ?></td>
                                <td style="padding: 0.75rem 1rem;"><span style="background: #fff3cd; color: #856404; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;"><?= htmlspecialchars($r['request_status']) ?></span></td>
                                <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="/procurement/view.php?id=<?= $r['request_id'] ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pending RFQ GC Approvals (over-threshold) -->
        <?php if (!empty($rfqGcItems)): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">🏛️ RFQ GC Approvals (Over-Threshold) <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($rfqGcItems) ?></span></h6>
            </div>
            <p style="color: #777; margin-bottom: 1rem; font-size: 0.875rem;">ℹ️ Committee-recommended RFQs requiring GC approval before vendor award (SOP Step 10).</p>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">RFQ #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Branch</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Est. Value</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rfqGcItems as $rq): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($rq['rfq_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($rq['request_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($rq['branch_name'] ?? 'N/A') ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;"><?= htmlspecialchars($rq['currency'] ?? 'JMD') ?> <?= number_format($rq['estimated_value'], 2) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="/rfq/gc_approve.php?rfq_id=<?= $rq['rfq_id'] ?>" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">Approve</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending Commitment Approvals -->
        <?php if (!empty($pendingCommitments)): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">💰 Pending Commitment Approvals <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($pendingCommitments) ?></span></h6>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Commitment #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Branch</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Total</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Date</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingCommitments as $c): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($c['commitment_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($c['request_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($c['branch_name'] ?? 'N/A') ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;">JMD <?= number_format($c['commitment_total'], 2) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="/commitments/view.php?id=<?= $c['commitment_id'] ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending PO Approvals -->
        <?php if (!empty($pendingPOs)): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">📄 Pending PO Approvals <span style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($pendingPOs) ?></span></h6>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">PO #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Branch</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Total</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Date</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPOs as $po): ?>
                            <tr style="border-bottom: 1px solid #f0f0f0;">
                                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($po['po_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($po['request_number']) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($po['branch_name'] ?? 'N/A') ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;">JMD <?= number_format($po['po_total'], 2) ?></td>
                                <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?= date('d M Y', strtotime($po['created_at'])) ?></td>
                                <td style="padding: 0.75rem 1rem; text-align: center;">
                                    <a href="/po/view.php?id=<?= $po['po_id'] ?>" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pending Actions Widget -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
      <?php include $_SERVER['DOCUMENT_ROOT'].'/dashboard/widgets/pending_actions.php'; ?>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
      <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/pipeline.php"; ?>
      <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/top_vendors.php"; ?>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
      <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/overdue_invoices.php"; ?>
      <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/recent_activity.php"; ?>
    </div>

</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
