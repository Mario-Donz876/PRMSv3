<?php
if (!isset($pdo)) {
    require_once __DIR__.'/_init.php';
}

$pending = $pdo->query("
    SELECT UPPER(status) AS status, COUNT(*) AS cnt
    FROM procurement_requests
    GROUP BY UPPER(status)
    ORDER BY FIELD(UPPER(status), 'DRAFT', 'SUBMITTED', 'HOD_APPROVED', 'FUNDS_VERIFIED', 'DIRECTOR_APPROVED', 'GC_APPROVED', 'AWARDED', 'COMPLETED', 'DECLINED')
")->fetchAll(PDO::FETCH_ASSOC);

$statusMeta = [
    'DRAFT'                 => ['icon' => '📝', 'bg' => 'secondary',  'label' => 'Draft'],
    'SUBMITTED'             => ['icon' => '⏳', 'bg' => 'warning',   'label' => 'Submitted'],
    'HOD_APPROVED'          => ['icon' => '👤', 'bg' => 'info',      'label' => 'HOD Approved'],
    'FUNDS_VERIFIED'        => ['icon' => '💰', 'bg' => 'primary',   'label' => 'Funds Verified'],
    'DIRECTOR_APPROVED'     => ['icon' => '👤', 'bg' => 'success',   'label' => 'Director Approved'],
    'PROCUREMENT_STAGE'     => ['icon' => '📑', 'bg' => 'dark',      'label' => 'Procurement Stage'],
    'EVALUATION_STAGE'      => ['icon' => '📊', 'bg' => 'dark',      'label' => 'Evaluation Stage'],
    'COMMITTEE_RECOMMENDED' => ['icon' => '🧾', 'bg' => 'info',      'label' => 'Committee Recommended'],
    'GC_APPROVED'           => ['icon' => '🏛', 'bg' => 'success',   'label' => 'GC Approved'],
    'AWARDED'               => ['icon' => '🏆', 'bg' => 'success',   'label' => 'Awarded'],
    'COMPLETED'             => ['icon' => '✅', 'bg' => 'success',   'label' => 'Completed'],
    'DECLINED'              => ['icon' => '❌', 'bg' => 'danger',    'label' => 'Declined'],
];

$total = array_sum(array_column($pending, 'cnt'));
?>

<style>
    .approvals-widget {
        background: white;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
    }
    .approval-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .approval-item:last-child {
        border-bottom: none;
    }
    .approval-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }
    .approval-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 5px;
        font-size: 0.85rem;
        font-weight: 500;
        color: white;
    }
</style>

<div class="approvals-widget">
    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center;">
        <h5 style="margin: 0; font-weight: 600; color: #1a1a1a;"><span style="font-size: 1.3rem;">📊</span> Requests by Status</h5>
        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.35rem 0.75rem; border-radius: 5px; font-size: 0.85rem; font-weight: 500;"><?= $total ?> total</span>
    </div>

    <?php if (empty($pending)): ?>
        <div style="text-align: center; padding: 2rem; color: #999;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">😕</div>
            <div>No requests found</div>
        </div>
    <?php else: ?>
        <div>
            <?php foreach ($pending as $row): ?>
                <?php
                    $meta = $statusMeta[$row['status']] ?? ['icon' => '📋', 'bg' => 'secondary', 'label' => $row['status']];
                    $pct = $total > 0 ? round(($row['cnt'] / $total) * 100) : 0;
                    
                    // Gradient colors based on status
                    $gradientColor = match($row['status']) {
                        'AWARDED', 'GC_APPROVED', 'COMPLETED', 'DIRECTOR_APPROVED' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                        'SUBMITTED', 'FUNDS_VERIFIED' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                        'HOD_APPROVED' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                        'DECLINED' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                        default => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
                    };
                ?>
                <div class="approval-item">
                    <div class="approval-info">
                        <span style="font-size: 1.2rem;"><?= $meta['icon'] ?></span>
                        <span style="font-weight: 600; color: #1a1a1a;"><?= htmlspecialchars($meta['label']) ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 80px; height: 6px; background: #e0e0e0; border-radius: 3px; overflow: hidden;">
                            <div style="background: <?= $gradientColor ?>; height: 100%; width: <?= $pct ?>%; transition: width 0.5s ease;"></div>
                        </div>
                        <span class="approval-badge" style="background: <?= $gradientColor ?>; min-width: 2rem; text-align: center;"><?= $row['cnt'] ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
