<?php
// modules/dashboard/templates/index.php
// This template is included by the layout.php file.
// Variables like $dashboard_data are passed from routes.php.
?>

<div class="dashboard">
    <div class="page-header">
        <h1>Dashboard</h1>
        <!-- <p class="lead">Welcome back, <?php echo e($dashboard_data['user_name']); ?>!</p> -->
    </div>

    <?php if (!empty($dashboard_data['stats'])): ?>
    <div class="stats-grid">
        <?php foreach ($dashboard_data['stats'] as $label => $stat): ?>
        <div class="stat-card">
            <div class="stat-card-label"><?php echo e($label); ?></div>
            <div class="stat-card-value"><?php echo e($stat['value']); ?></div>
            <div class="stat-card-change"><?php echo e($stat['change']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <p>No dashboard widgets are configured for your role yet.</p>
    </div>
    <?php endif; ?>

</div>