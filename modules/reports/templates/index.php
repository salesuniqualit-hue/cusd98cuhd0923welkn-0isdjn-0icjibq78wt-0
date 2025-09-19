<?php
// modules/reports/templates/index.php
?>

<div class="page-header">
    <h1><?php echo e($page_title); ?></h1>
</div>

<div class="card">
    <div class="card-body">
        <p>Select a report from the list below to view its details.</p>
        
        <div class="list-group">
            <?php if (!empty($reports)): ?>
                <?php foreach ($reports as $report): ?>
                    <a href="<?php echo url('/reports/' . $report['slug']); ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo e($report['title']); ?></h5>
                        </div>
                        <p class="mb-1"><?php echo e($report['description']); ?></p>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">No reports are available for your user role at this time.</div>
            <?php endif; ?>
        </div>
    </div>
</div>