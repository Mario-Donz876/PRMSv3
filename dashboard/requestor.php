<?php
$REQUIRE_PERMISSION = 'view_own_requests';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/workflow.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/helper.php';
$userId = $_SESSION['user_id'];

/* ═══════════════════════════════════════════════════════
   1. My Requests (recent 30)
═══════════════════════════════════════════════════════ */
$stmt = $pdo->prepare("SELECT request_id, request_number, request_type, estimated_value, status, request_date, currency
    FROM procurement_requests
    WHERE created_by = ?
    ORDER BY request_date DESC LIMIT 30");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ═══════════════════════════════════════════════════════
   2. KPIs for my requests
═══════════════════════════════════════════════════════ */
$kpiStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN UPPER(status) = 'SUBMITTED' THEN 1 ELSE 0 END) AS pending_approval,
        SUM(CASE WHEN UPPER(status) NOT IN ('SUBMITTED','DECLINED','COMPLETED','AWARDED') THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN UPPER(status) IN ('COMPLETED','AWARDED') THEN 1 ELSE 0 END) AS completed
    FROM procurement_requests
    WHERE created_by = ?
");
$kpiStmt->execute([$userId]);
$kpi = $kpiStmt->fetch(PDO::FETCH_ASSOC);

/* ═══════════════════════════════════════════════════════
   3. Quotes awaiting MY review (QUOTE_REVIEW_PENDING)
═══════════════════════════════════════════════════════ */
$quoteReviewStmt = $pdo->prepare("
    SELECT 
        r.rfq_id, r.rfq_number,
        pr.request_id, pr.request_number, pr.estimated_value, pr.currency,
        b.branch_name,
        (SELECT COUNT(*) FROM rfq_quotes q 
         JOIN rfq_vendors rv ON q.rfq_vendor_id = rv.rfq_vendor_id
         WHERE rv.rfq_id = r.rfq_id AND q.review_status IS NULL) AS unreviewed_quotes
    FROM rfqs r
    JOIN procurement_requests pr ON r.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    WHERE pr.created_by = ?
      AND UPPER(pr.status) = 'QUOTE_REVIEW_PENDING'
      AND r.status != 'AWARDED'
    ORDER BY pr.created_at ASC
");
$quoteReviewStmt->execute([$userId]);
$quoteReviews = $quoteReviewStmt->fetchAll(PDO::FETCH_ASSOC);

/* ═══════════════════════════════════════════════════════
   4. RFQs needing action (RFQ_LETTER_AVAILABLE / PROCUREMENT_STAGE)
═══════════════════════════════════════════════════════ */
$rfqActionStmt = $pdo->prepare("
    SELECT 
        r.rfq_id, r.rfq_number,
        pr.request_id, pr.request_number, pr.estimated_value, pr.currency,
        pr.status as request_status,
        b.branch_name
    FROM rfqs r
    JOIN procurement_requests pr ON r.request_id = pr.request_id
    LEFT JOIN branches b ON pr.branch_id = b.branch_id
    WHERE pr.created_by = ?
      AND UPPER(pr.status) IN ('RFQ_LETTER_AVAILABLE', 'PROCUREMENT_STAGE')
      AND r.status != 'AWARDED'
    ORDER BY pr.created_at ASC
");
$rfqActionStmt->execute([$userId]);
$rfqActions = $rfqActionStmt->fetchAll(PDO::FETCH_ASSOC);

$totalActionItems = count($quoteReviews) + count($rfqActions);

require_once $_SERVER['DOCUMENT_ROOT'].'/includes/header.php';
?>

<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
            <span style="font-size: 1.75em; margin-right: 1rem;">📋</span>
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #333;">My Dashboard</h2>
                <small style="color: #999; font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                    Welcome back, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?> &bull; <?= date('l, j F Y') ?>
                </small>
            </div>
        </div>

        <!-- Quick Links -->
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 2px solid #e0e0e0;">
            <a href="/procurement/add.php" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                <i class="bi bi-plus-circle" style="margin-right: 0.5rem;"></i>New Request
            </a>
            <a href="/procurement/list.php" style="background: white; border: 1px solid #667eea; color: #667eea; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                <i class="bi bi-list-task" style="margin-right: 0.5rem;"></i>All Requests
            </a>
            <a href="/rfq/list.php" style="background: white; border: 1px solid #4facfe; color: #4facfe; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                <i class="bi bi-file-earmark-text" style="margin-right: 0.5rem;"></i>RFQs
            </a>
        </div>

        <!-- KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9; font-size: 0.8rem;">My Requests</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= (int)$kpi['total'] ?></h3>
            </div>
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9; font-size: 0.8rem;">Pending Approval</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= (int)$kpi['pending_approval'] ?></h3>
            </div>
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9; font-size: 0.8rem;">In Progress</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= (int)$kpi['in_progress'] ?></h3>
            </div>
            <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9; font-size: 0.8rem;">Completed</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= (int)$kpi['completed'] ?></h3>
            </div>
            <?php if ($totalActionItems > 0): ?>
            <div style="background: linear-gradient(135deg, #f5576c 0%, #ff6f91 100%); color: white; padding: 1rem; border-radius: 12px;">
                <h6 style="margin: 0; font-weight: 600; opacity: 0.9; font-size: 0.8rem;">Action Required</h6>
                <h3 style="margin: 0.5rem 0 0 0; font-size: 2rem; font-weight: 700;"><?= $totalActionItems ?></h3>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quotes Awaiting Review -->
        <?php if (!empty($quoteReviews)): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">
                    <i class="bi bi-chat-dots"></i> Quotes Awaiting Your Review
                    <span style="background: linear-gradient(135deg, #f5576c 0%, #ff6f91 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($quoteReviews) ?></span>
                </h6>
            </div>
            <p style="color: #666; font-size: 0.875rem; margin-bottom: 1rem;">
                <i class="bi bi-info-circle"></i> Vendors have submitted quotes for your requests. Review each quote to help Finance select the best one.
            </p>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">RFQ #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Est. Value</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Unreviewed</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($quoteReviews as $qr): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($qr['rfq_number']) ?></td>
                            <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($qr['request_number']) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;"><?= htmlspecialchars(normalizeCurrency($qr['currency'] ?? 'JMD')) ?> <?= number_format((float)$qr['estimated_value'], 2) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <span style="background: #fff3cd; color: #856404; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;"><?= (int)$qr['unreviewed_quotes'] ?> quote<?= $qr['unreviewed_quotes'] != 1 ? 's' : '' ?></span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <a href="/rfq/view.php?id=<?= $qr['rfq_id'] ?>" style="background: linear-gradient(135deg, #f5576c 0%, #ff6f91 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">Review Quotes</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- RFQs Needing Action -->
        <?php if (!empty($rfqActions)): ?>
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">
                    <i class="bi bi-exclamation-triangle"></i> RFQs Needing Action
                    <span style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;"><?= count($rfqActions) ?></span>
                </h6>
            </div>
            <p style="color: #666; font-size: 0.875rem; margin-bottom: 1rem;">
                <i class="bi bi-info-circle"></i> These RFQs for your requests are ready for evaluation or quote review.
            </p>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">RFQ #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Est. Value</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rfqActions as $ra): ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($ra['rfq_number']) ?></td>
                            <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($ra['request_number']) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;"><?= htmlspecialchars(normalizeCurrency($ra['currency'] ?? 'JMD')) ?> <?= number_format((float)$ra['estimated_value'], 2) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <span style="background: #fff3cd; color: #856404; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;"><?= htmlspecialchars($ra['request_status']) ?></span>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <a href="/rfq/view.php?id=<?= $ra['rfq_id'] ?>" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600;">View RFQ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- No Action Items Message -->
        <?php if ($totalActionItems === 0): ?>
        <div style="background: #e8f5e9; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
            <span style="font-size: 1.5em;">✅</span>
            <p style="margin: 0.5rem 0 0 0; color: #2e7d32; font-weight: 600;">No pending actions — all caught up!</p>
        </div>
        <?php endif; ?>

        <!-- My Requests Table -->
        <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h6 style="margin: 0; font-size: 1rem; font-weight: 700; color: #333;">📝 My Requests</h6>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead style="background: #f5f5f5;">
                        <tr>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Request #</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Type</th>
                            <th style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Value</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Status</th>
                            <th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Date</th>
                            <th style="padding: 0.75rem 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="6" style="text-align: center; color: #999; padding: 2rem 0;">No requests found.</td></tr>
                    <?php else: foreach ($rows as $row):
                        $statusColors = [
                            'SUBMITTED' => ['bg' => '#fff3cd', 'color' => '#856404'],
                            'DECLINED' => ['bg' => '#f8d7da', 'color' => '#721c24'],
                            'COMPLETED' => ['bg' => '#d4edda', 'color' => '#155724'],
                            'AWARDED' => ['bg' => '#d4edda', 'color' => '#155724'],
                        ];
                        $sc = $statusColors[strtoupper($row['status'])] ?? ['bg' => '#e2e3e5', 'color' => '#383d41'];
                    ?>
                        <tr style="border-bottom: 1px solid #f0f0f0;">
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: #333;"><?= htmlspecialchars($row['request_number']) ?></td>
                            <td style="padding: 0.75rem 1rem; color: #666;"><?= htmlspecialchars($row['request_type']) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 600; color: #333;"><?= htmlspecialchars(normalizeCurrency($row['currency'] ?? 'JMD')) ?> <?= number_format((float)$row['estimated_value'], 2) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <span style="background: <?= $sc['bg'] ?>; color: <?= $sc['color'] ?>; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #999; font-size: 0.8rem;"><?= date('d M Y', strtotime($row['request_date'])) ?></td>
                            <td style="padding: 0.75rem 1rem; text-align: center;">
                                <a href="/procurement/view.php?id=<?= $row['request_id'] ?>" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; font-weight: 600; display: inline-block;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?>
