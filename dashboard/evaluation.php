<?php
$REQUIRE_PERMISSION = 'view_requests';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/workflow.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';

$rfqs = []; // ALWAYS initialize

$stmt = $pdo->prepare("
        SELECT r.rfq_id, r.rfq_number, pr.request_number, pr.status AS pr_status, r.status AS rfq_status
        FROM rfqs r
        JOIN procurement_requests pr ON r.request_id = pr.request_id
        JOIN rfq_evaluation_committee ec ON r.rfq_id = ec.rfq_id
        WHERE (pr.status = 'EVALUATION_STAGE'
               OR (r.status IN ('PUBLISHED','EVALUATION') AND pr.status NOT IN ('AWARDED','COMPLETED')))
            AND r.status <> 'AWARDED'
            AND ec.user_id = ?
        ORDER BY r.created_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
$rfqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
          <span style="font-size: 1.5em; margin-right: 1rem;">📊</span>
            <h4 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #333;">Evaluation Committee <span style="background: #333; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">Dashboard</span></h4>
        </div>

        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">⏳ RFQs Pending Evaluation <span style="background: #333; color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($rfqs) ?></span></h6>
            </div>

            <?php if (empty($rfqs)): ?>
                <div style="text-align: center; color: #999; padding: 2rem 0;">
                    <span style="font-size: 1.5em;">✅</span><br>
                    <span style="display: block; margin-top: 0.5rem;">No RFQs in evaluation stage</span>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead style="background: #f5f5f5;">
                            <tr>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">RFQ #</th>
                                <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                                <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
                                <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rfqs as $r): ?>
                                <?php
                                    /* Committee Count */
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rfq_evaluation_committee WHERE rfq_id = ?");
                                    $stmt->execute([$r['rfq_id']]);
                                    $committeeCount = $stmt->fetchColumn();
                                    /* Evaluation Report Check */
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rfq_evaluation_reports WHERE rfq_id = ?");
                                    $stmt->execute([$r['rfq_id']]);
                                    $reportCount = $stmt->fetchColumn();
                                    $compliant = ($committeeCount >= 3 && $reportCount > 0);
                                ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($r['rfq_number']) ?></td>
                                    <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($r['request_number']) ?></td>
                                    <td style="padding: 0.75rem 1rem; text-align: center;">
                                        <?php if (!$compliant): ?>
                                            <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">❌ Non-Compliant</span>
                                        <?php else: ?>
                                            <span style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block;">✅ Compliant</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; text-align: center;">
                                        <a href="/rfq/view.php?id=<?= $r['rfq_id'] ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block; transition: transform 0.3s ease;">Evaluate</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pending Actions -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
          <?php include $_SERVER['DOCUMENT_ROOT'].'/dashboard/widgets/pending_actions.php'; ?>
        </div>

        <!-- Additional Widgets -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
          <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/top_vendors.php"; ?>
          <?php include $_SERVER['DOCUMENT_ROOT']."/dashboard/widgets/pipeline.php"; ?>
        </div>
    </div>
</div>
<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
