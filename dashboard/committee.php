<?php
$REQUIRE_PERMISSION = 'view_requests';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/workflow.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';

/* RFQs at COMMITTEE_RECOMMENDED awaiting Procurement Committee action,
   plus EVALUATION_STAGE for visibility of upcoming items */
$userId = $_SESSION['user_id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT DISTINCT r.rfq_id, r.rfq_number, pr.request_id, pr.request_number,
           pr.estimated_value, pr.status as request_status, b.branch_name
    FROM rfqs r
    JOIN procurement_requests pr ON r.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    WHERE pr.status IN ('EVALUATION_STAGE', 'COMMITTEE_RECOMMENDED')
      AND r.status <> 'AWARDED'
    ORDER BY pr.created_at ASC
");
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Also get any pending request_approvals for Procurement Committee role */
$raStmt = $pdo->prepare("
    SELECT ra.id, ra.entity_type, ra.entity_id, ra.request_id,
           pr.request_number, pr.estimated_value, pr.status as request_status,
           b.branch_name, ra.created_at
    FROM request_approvals ra
    JOIN procurement_requests pr ON ra.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    WHERE ra.role = 'Procurement Committee'
      AND ra.status = 'pending'
    ORDER BY ra.created_at ASC
");
$raStmt->execute();
$pendingApprovals = $raStmt->fetchAll(PDO::FETCH_ASSOC);

$totalPending = count($requests) + count($pendingApprovals);
?>

<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
  <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
    <div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
      <span style="font-size: 1.5em; margin-right: 1rem;">👥</span>
      <h4 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #333;">Procurement Committee <span style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">Dashboard</span></h4>
    </div>

    <!-- KPI -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
      <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1rem; border-radius: 12px;">
        <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">Total Pending</h6>
        <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= $totalPending ?></h3>
      </div>
      <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 12px;">
        <h6 style="margin: 0; font-weight: 600; opacity: 0.9;">RFQs to Evaluate</h6>
        <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= count($requests) ?></h3>
      </div>
    </div>

    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
      <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
        <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">⏳ RFQs Awaiting Evaluation / Recommendation <span style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($requests) ?></span></h6>
      </div>

      <?php if (empty($requests)): ?>
        <div style="text-align: center; color: #999; padding: 2rem 0;">
          <span style="font-size: 1.5em;">✅</span><br>
          <span style="display: block; margin-top: 0.5rem;">No RFQs pending evaluation</span>
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead style="background: #f5f5f5;">
              <tr>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">RFQ #</th>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Branch</th>
                <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Est. Value</th>
                <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
                <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($requests as $r): ?>
                <tr style="border-bottom: 1px solid #f0f0f0; transition: background 0.3s ease;">
                  <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($r['rfq_number'] ?? '-') ?></td>
                  <td style="padding: 0.75rem 1rem; color: #555;"><?= htmlspecialchars($r['request_number']) ?></td>
                  <td style="padding: 0.75rem 1rem; color: #555;"><?= htmlspecialchars($r['branch_name'] ?? '-') ?></td>
                  <td style="padding: 0.75rem 1rem; text-align: right; color: #555;">$<?= number_format($r['estimated_value'] ?? 0, 2) ?></td>
                  <td style="padding: 0.75rem 1rem; text-align: center;">
                    <span style="background: <?= $r['request_status'] === 'EVALUATION_STAGE' ? '#fff3cd' : '#cce5ff' ?>; color: <?= $r['request_status'] === 'EVALUATION_STAGE' ? '#856404' : '#004085' ?>; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600;"><?= htmlspecialchars($r['request_status']) ?></span>
                  </td>
                  <td style="padding: 0.75rem 1rem; text-align: center;">
                    <a href="/rfq/view.php?id=<?= $r['rfq_id'] ?>"
                       style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block; transition: transform 0.3s ease;">
                      <?= $r['request_status'] === 'EVALUATION_STAGE' ? '📝 Evaluate' : '✅ Recommend' ?>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($pendingApprovals)): ?>
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
      <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
        <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">📋 Pending Approval Chain Items <span style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($pendingApprovals) ?></span></h6>
      </div>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
          <thead style="background: #f5f5f5;">
            <tr>
              <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Type</th>
              <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
              <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Branch</th>
              <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendingApprovals as $pa): ?>
              <tr style="border-bottom: 1px solid #f0f0f0;">
                <td style="padding: 0.75rem 1rem;">
                  <span style="background: #e2e3f1; color: #4a4a8a; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 600;"><?= htmlspecialchars($pa['entity_type']) ?></span>
                </td>
                <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($pa['request_number']) ?></td>
                <td style="padding: 0.75rem 1rem; color: #555;"><?= htmlspecialchars($pa['branch_name'] ?? '-') ?></td>
                <td style="padding: 0.75rem 1rem; text-align: center;">
                  <a href="/procurement/view.php?id=<?= $pa['request_id'] ?>"
                     style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                    ✅ Approve
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
      <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/pipeline.php"; ?>
      <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/top_vendors.php"; ?>
    </div>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
