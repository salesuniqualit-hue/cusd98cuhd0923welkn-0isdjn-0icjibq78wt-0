<?php
// modules/reports/templates/_subscription_summary.php
// This is a partial file included by the action function.
// The $stats variable is available here.
?>
<div class="report-container">
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Total Subscriptions</div>
            <div class="summary-value"><?php echo e($stats['total'] ?? 0); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Currently Running</div>
            <div class="summary-value text-success"><?php echo e($stats['running'] ?? 0); ?></div>
        </div>
    </div>

    <hr>
    <h4>Expiring Soon</h4>
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">In Next 7 Days</div>
            <div class="summary-value text-warning"><?php echo e($stats['expiring_7_days'] ?? 0); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">In 8 to 30 Days</div>
            <div class="summary-value text-warning"><?php echo e($stats['expiring_8_30_days'] ?? 0); ?></div>
        </div>
    </div>

    <hr>
    <h4>Expired Subscriptions</h4>
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">In Last 7 Days</div>
            <div class="summary-value text-danger"><?php echo e($stats['expired_7_days'] ?? 0); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">8 to 30 Days Ago</div>
            <div class="summary-value text-danger"><?php echo e($stats['expired_8_30_days'] ?? 0); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">31 to 365 Days Ago</div>
            <div class="summary-value text-danger"><?php echo e($stats['expired_31_365_days'] ?? 0); ?></div>
        </div>
         <div class="summary-card">
            <div class="summary-label">Over A Year Ago</div>
            <div class="summary-value text-danger"><?php echo e($stats['expired_over_1_year'] ?? 0); ?></div>
        </div>
    </div>
</div>