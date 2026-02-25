<?php
$REQUIRE_PERMISSION = 'view_audit_dashboard';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/page_guard.php';
require_once $_SERVER['DOCUMENT_ROOT']."/includes/header.php";
?>

<div style="max-width: 1400px; margin: 2rem auto; padding: 0 1rem;">
    <div style="background: white; border-radius: 12px; border: 1px solid #e0e0e0; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
            <span style="font-size: 1.75em; margin-right: 1rem;">📊</span>
            <div>
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: #333;">Viewer / Audit Dashboard</h2>
                <small style="color: #999; font-size: 0.875rem; display: block; margin-top: 0.25rem;">
                    Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?> •
                    <?php
                    $displayRole = match ($_SESSION['role_name'] ?? '') {
                        'Viewer' => 'Audit / Read-Only User',
                        default => $_SESSION['role_name'] ?? 'User'
                    };
                    ?>
                    <?= htmlspecialchars($displayRole) ?> • <?= date('l, j F Y') ?>
                </small>
            </div>
        </div>

        <!-- Widgets Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <?php include __DIR__."/widgets/kpis.php"; ?>
            <?php include __DIR__."/widgets/alerts.php"; ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <?php include __DIR__."/widgets/pipeline.php"; ?>
            <?php include __DIR__."/widgets/branch_summary.php"; ?>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <?php include __DIR__."/widgets/overdue_invoices.php"; ?>
            <?php include __DIR__."/widgets/monthly_trend.php"; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
            <?php include __DIR__."/widgets/recent_activity.php"; ?>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT']."/includes/footer.php"; ?>
